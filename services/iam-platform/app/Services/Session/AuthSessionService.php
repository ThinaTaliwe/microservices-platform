<?php

namespace App\Services\Session;

use Carbon\CarbonImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AuthSessionService
{
    private const ABSOLUTE_HOURS = 12;
    private const INACTIVITY_MINUTES = 30;

    public function createAfterOtp(
        int $identityId,
        int $loginAttemptId
    ): object {
        $attempt = DB::table('auth_login_attempts')
            ->where('id', $loginAttemptId)
            ->where('auth_identity_id', $identityId)
            ->first();

        if (!$attempt) {
            throw new RuntimeException('Login attempt not found.');
        }

        $payload = json_decode(
            Crypt::decryptString($attempt->payload_encrypted),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $timezone = $this->normaliseTimezone(
            (string) ($payload['timezone'] ?? 'Africa/Johannesburg')
        );

        $now = CarbonImmutable::now('UTC');

        $endOfLocalDay = $now
            ->setTimezone($timezone)
            ->endOfDay()
            ->setTimezone('UTC');

        $absoluteExpiry = $now->addHours(self::ABSOLUTE_HOURS);

        $reauthenticationValidUntil = $endOfLocalDay->lessThan($absoluteExpiry)
            ? $endOfLocalDay
            : $absoluteExpiry;

        DB::table('auth_sessions')
            ->where('auth_identity_id', $identityId)
            ->where('device_hash', (string) $attempt->device_hash)
            ->where('status', 'active')
            ->whereNull('revoked_at')
            ->update([
                'status' => 'revoked',
                'revoked_at' => $now,
                'updated_at' => $now,
            ]);

        $sessionToken = bin2hex(random_bytes(40));

        $sessionId = DB::table('auth_sessions')->insertGetId([
            'auth_identity_id' => $identityId,
            'login_attempt_id' => $loginAttemptId,
            'session_token_hash' => $this->hashToken($sessionToken),
            'device_hash' => (string) $attempt->device_hash,
            'ip_hash' => $attempt->ip_hash,
            'timezone' => $timezone,
            'risk_level' => (string) $attempt->risk_level,
            'status' => 'active',
            'reauthenticated_at' => $now,
            'reauthentication_valid_until' => $reauthenticationValidUntil,
            'started_at' => $now,
            'last_seen_at' => $now,
            'expires_at' => $absoluteExpiry,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('auth_security_events')->insert([
            'auth_identity_id' => $identityId,
            'login_attempt_id' => $loginAttemptId,
            'event' => 'iam_session_created',
            'payload_encrypted' => Crypt::encryptString(json_encode([
                'iam_session_id' => $sessionId,
                'older_same_device_sessions_revoked' => true,
                'timezone' => $timezone,
                'reauthentication_valid_until' =>
                    $reauthenticationValidUntil->toDateTimeString(),
                'expires_at' => $absoluteExpiry->toDateTimeString(),
            ])),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (object) [
            'session_id' => $sessionId,
            'session_token' => $sessionToken,
            'reauthentication_valid_until' =>
                $reauthenticationValidUntil->toDateTimeString(),
            'expires_at' => $absoluteExpiry->toDateTimeString(),
        ];
    }

    public function findReusableByToken(
        string $sessionToken,
        int $identityId,
        string $deviceHash,
        string $riskLevel
    ): ?object {
        $sessionToken = trim($sessionToken);

        if ($sessionToken === '' || $riskLevel !== 'low') {
            return null;
        }

        $now = now();

        $session = DB::table('auth_sessions')
            ->where('session_token_hash', $this->hashToken($sessionToken))
            ->where('auth_identity_id', $identityId)
            ->where('device_hash', $deviceHash)
            ->where('status', 'active')
            ->whereNull('revoked_at')
            ->where('risk_level', 'low')
            ->where('reauthentication_valid_until', '>=', $now)
            ->where('expires_at', '>=', $now)
            ->where(
                'last_seen_at',
                '>=',
                now()->subMinutes(self::INACTIVITY_MINUTES)
            )
            ->lockForUpdate()
            ->first();

        if (!$session) {
            return null;
        }

        DB::table('auth_sessions')
            ->where('id', $session->id)
            ->update([
                'last_seen_at' => $now,
                'updated_at' => $now,
            ]);

        return $session;
    }

    public function createHandoff(
        int $sessionId,
        int $identityId,
        int $bfrnUserId
    ): string {
        return DB::transaction(function () use (
            $sessionId,
            $identityId,
            $bfrnUserId
        ): string {
            $session = DB::table('auth_sessions')
                ->where('id', $sessionId)
                ->where('auth_identity_id', $identityId)
                ->where('status', 'active')
                ->whereNull('revoked_at')
                ->where('reauthentication_valid_until', '>=', now())
                ->where('expires_at', '>=', now())
                ->lockForUpdate()
                ->first();

            if (!$session) {
                throw new RuntimeException(
                    'Reusable IAM session is no longer valid.'
                );
            }

            $handoffToken = bin2hex(random_bytes(40));

            DB::table('auth_handoff_tokens')->insert([
                'auth_identity_id' => $identityId,
                'iam_session_id' => $sessionId,
                'bfrn_user_id' => $bfrnUserId,
                'token_hash' => hash_hmac(
                    'sha256',
                    $handoffToken,
                    (string) env('AUTH_GATEWAY_HANDOFF_SECRET')
                ),
                'expires_at' => now()->addMinutes(
                    (int) env('AUTH_GATEWAY_TOKEN_TTL_MINUTES', 5)
                ),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $handoffToken;
        });
    }

    public function revoke(int $sessionId): void
    {
        DB::table('auth_sessions')
            ->where('id', $sessionId)
            ->where('status', 'active')
            ->update([
                'status' => 'revoked',
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function hashToken(string $token): string
    {
        return hash_hmac(
            'sha256',
            $token,
            (string) config('app.key')
        );
    }

    private function normaliseTimezone(string $timezone): string
    {
        try {
            return (new DateTimeZone($timezone))->getName();
        } catch (\Throwable) {
            return 'Africa/Johannesburg';
        }
    }
}
