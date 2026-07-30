<?php

namespace App\Services\Otp;

use App\Mail\LoginOtpMail;
use App\Services\Session\AuthSessionService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class OtpChallengeService
{
    public function __construct(
        private readonly AuthSessionService $authSessions
    ) {
    }

    private const PURPOSE = 'login';
    private const EXPIRY_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;

    public function issue(
        int $identityId,
        int $loginAttemptId,
        string $email,
        int $resendCount = 0
    ): object {
        $code = (string) random_int(100000, 999999);
        $accessToken = bin2hex(random_bytes(32));
        $challengeUuid = (string) Str::uuid();
        $apiToken = Str::random(80);

        $challengeId = DB::transaction(function () use (
            $identityId,
            $loginAttemptId,
            $code,
            $accessToken,
            $challengeUuid,
            $apiToken,
            $resendCount
        ): int {
            DB::table('auth_otp_challenges')
                ->where('auth_identity_id', $identityId)
                ->where('login_attempt_id', $loginAttemptId)
                ->where('purpose', self::PURPOSE)
                ->where('status', 'pending')
                ->update([
                    'status' => 'superseded',
                    'updated_at' => now(),
                ]);

            $challengeId = DB::table('auth_otp_challenges')->insertGetId([
                'challenge_uuid' => $challengeUuid,
                'auth_identity_id' => $identityId,
                'login_attempt_id' => $loginAttemptId,
                'purpose' => self::PURPOSE,
                'access_token_hash' => $this->hashAccessToken($accessToken),
                'api_token_hash' => $this->hashApiToken($apiToken),
                'code_hash' => $this->hashCode($code),
                'status' => 'pending',
                'attempts' => 0,
                'max_attempts' => self::MAX_ATTEMPTS,
                'resend_count' => $resendCount,
                'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->recordEvent(
                $identityId,
                $loginAttemptId,
                'login_otp_issued',
                [
                    'otp_challenge_id' => $challengeId,
                    'challenge_uuid' => $challengeUuid,
                    'verification_methods' => [
                        'otp',
                        'api_token',
                    ],
                    'expires_minutes' => self::EXPIRY_MINUTES,
                    'resend_count' => $resendCount,
                    'issued_at' => now()->toDateTimeString(),
                ]
            );

            return $challengeId;
        });

        $this->sendMailWithRetry(
            $email,
            new LoginOtpMail([
                'code' => $code,
                'expires_minutes' => self::EXPIRY_MINUTES,
                'requested_at' => now()->toDateTimeString(),
                'verify_url' => rtrim((string) config('app.url'), '/')
                    . '/otp/verify?token=' . urlencode($accessToken),
            ])
        );

        return (object) [
            'challenge_id' => $challengeId,
            'challenge_uuid' => $challengeUuid,
            'access_token' => $accessToken,
            'api_token' => $apiToken,
            'expires_in_seconds' => self::EXPIRY_MINUTES * 60,
            'reused_challenge' => false,
        ];
    }

    public function verify(int $challengeId, string $code): ?object
    {
        return DB::transaction(function () use ($challengeId, $code): ?object {
            $challenge = DB::table('auth_otp_challenges')
                ->where('id', $challengeId)
                ->lockForUpdate()
                ->first();

            if (!$challenge || $challenge->status !== 'pending') {
                return null;
            }

            if (now()->greaterThan($challenge->expires_at)) {
                DB::table('auth_otp_challenges')
                    ->where('id', $challenge->id)
                    ->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);

                $this->recordEvent(
                    (int) $challenge->auth_identity_id,
                    (int) $challenge->login_attempt_id,
                    'login_otp_expired',
                    ['otp_challenge_id' => $challenge->id]
                );

                return null;
            }

            if ((int) $challenge->attempts >= (int) $challenge->max_attempts) {
                DB::table('auth_otp_challenges')
                    ->where('id', $challenge->id)
                    ->update([
                        'status' => 'locked',
                        'updated_at' => now(),
                    ]);

                return null;
            }

            if (!hash_equals($challenge->code_hash, $this->hashCode($code))) {
                $attempts = (int) $challenge->attempts + 1;
                $status = $attempts >= (int) $challenge->max_attempts
                    ? 'locked'
                    : 'pending';

                DB::table('auth_otp_challenges')
                    ->where('id', $challenge->id)
                    ->update([
                        'attempts' => $attempts,
                        'status' => $status,
                        'updated_at' => now(),
                    ]);

                $this->recordEvent(
                    (int) $challenge->auth_identity_id,
                    (int) $challenge->login_attempt_id,
                    'login_otp_failed',
                    [
                        'otp_challenge_id' => $challenge->id,
                        'attempts' => $attempts,
                        'status' => $status,
                    ]
                );

                return null;
            }

            $identity = DB::table('auth_identities')
                ->where('id', $challenge->auth_identity_id)
                ->lockForUpdate()
                ->first(['id', 'bfrn_user_id']);

            if (!$identity || !$identity->bfrn_user_id) {
                throw new RuntimeException(
                    'OTP identity is not linked to an active BFRN user.'
                );
            }

            $iamSession = $this->authSessions->createAfterOtp(
                (int) $challenge->auth_identity_id,
                (int) $challenge->login_attempt_id
            );

            $handoffToken = bin2hex(random_bytes(40));

            DB::table('auth_handoff_tokens')->insert([
                'auth_identity_id' => (int) $identity->id,
                'iam_session_id' => (int) $iamSession->session_id,
                'bfrn_user_id' => (int) $identity->bfrn_user_id,
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

            DB::table('auth_otp_challenges')
                ->where('id', $challenge->id)
                ->update([
                    'status' => 'verified',
                    'verified_at' => now(),
                    'verified_via' => 'otp',
                    'consumed_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('auth_otp_challenges')
                ->where('login_attempt_id', $challenge->login_attempt_id)
                ->where('id', '!=', $challenge->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'superseded',
                    'updated_at' => now(),
                ]);

            $this->recordEvent(
                (int) $challenge->auth_identity_id,
                (int) $challenge->login_attempt_id,
                'login_otp_verified',
                [
                    'otp_challenge_id' => $challenge->id,
                    'challenge_uuid' => $challenge->challenge_uuid,
                    'verified_via' => 'otp',
                    'handoff_created' => true,
                    'iam_session_id' => (int) $iamSession->session_id,
                    'bfrn_user_id' => (int) $identity->bfrn_user_id,
                ]
            );

            return (object) [
                'challenge_id' => (int) $challenge->id,
                'challenge_uuid' => (string) $challenge->challenge_uuid,
                'verified_via' => 'otp',
                'auth_identity_id' => (int) $challenge->auth_identity_id,
                'login_attempt_id' => (int) $challenge->login_attempt_id,
                'bfrn_user_id' => (int) $identity->bfrn_user_id,
                'iam_session_id' => (int) $iamSession->session_id,
                'iam_session_token' => $iamSession->session_token,
                'iam_session_expires_at' => $iamSession->expires_at,
                'handoff_token' => $handoffToken,
            ];
        });
    }

    public function issueApiToken(
        int $challengeId
    ): ?object {
        return DB::transaction(function () use (
            $challengeId
        ): ?object {
            $challenge = DB::table('auth_otp_challenges')
                ->where('id', $challengeId)
                ->lockForUpdate()
                ->first();

            if (!$challenge || $challenge->status !== 'pending') {
                return null;
            }

            if (now()->greaterThan($challenge->expires_at)) {
                DB::table('auth_otp_challenges')
                    ->where('id', $challenge->id)
                    ->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);

                return null;
            }

            if (
                (int) $challenge->attempts
                >= (int) $challenge->max_attempts
            ) {
                DB::table('auth_otp_challenges')
                    ->where('id', $challenge->id)
                    ->update([
                        'status' => 'locked',
                        'updated_at' => now(),
                    ]);

                return null;
            }

            $apiToken = Str::random(80);

            DB::table('auth_otp_challenges')
                ->where('id', $challenge->id)
                ->update([
                    'api_token_hash' =>
                        $this->hashApiToken($apiToken),
                    'updated_at' => now(),
                ]);

            $this->recordEvent(
                (int) $challenge->auth_identity_id,
                (int) $challenge->login_attempt_id,
                'login_api_token_issued',
                [
                    'otp_challenge_id' =>
                        (int) $challenge->id,
                    'challenge_uuid' =>
                        (string) $challenge->challenge_uuid,
                    'rotated' =>
                        $challenge->api_token_hash !== null,
                    'issued_at' =>
                        now()->toDateTimeString(),
                ]
            );

            $expiresAt = \Illuminate\Support\Carbon::parse(
                $challenge->expires_at
            );

            return (object) [
                'challenge_id' => (int) $challenge->id,
                'challenge_uuid' =>
                    (string) $challenge->challenge_uuid,
                'api_token' => $apiToken,
                'expires_in_seconds' => max(
                    0,
                    $expiresAt->getTimestamp()
                        - now()->getTimestamp()
                ),
                'reused_challenge' => true,
            ];
        });
    }

    public function verifyApiToken(
        string $challengeUuid,
        string $apiToken
    ): ?object {
        return DB::transaction(function () use (
            $challengeUuid,
            $apiToken
        ): ?object {
            $challenge = DB::table('auth_otp_challenges')
                ->where('challenge_uuid', $challengeUuid)
                ->lockForUpdate()
                ->first();

            if (!$challenge || $challenge->status !== 'pending') {
                return null;
            }

            if (now()->greaterThan($challenge->expires_at)) {
                DB::table('auth_otp_challenges')
                    ->where('id', $challenge->id)
                    ->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);

                $this->recordEvent(
                    (int) $challenge->auth_identity_id,
                    (int) $challenge->login_attempt_id,
                    'login_api_token_expired',
                    [
                        'otp_challenge_id' => $challenge->id,
                        'challenge_uuid' =>
                            $challenge->challenge_uuid,
                    ]
                );

                return null;
            }

            if (
                (int) $challenge->attempts
                >= (int) $challenge->max_attempts
            ) {
                DB::table('auth_otp_challenges')
                    ->where('id', $challenge->id)
                    ->update([
                        'status' => 'locked',
                        'updated_at' => now(),
                    ]);

                return null;
            }

            $storedHash = (string) (
                $challenge->api_token_hash ?? ''
            );

            if (
                $storedHash === ''
                || !hash_equals(
                    $storedHash,
                    $this->hashApiToken($apiToken)
                )
            ) {
                $attempts = (int) $challenge->attempts + 1;
                $status = $attempts
                    >= (int) $challenge->max_attempts
                    ? 'locked'
                    : 'pending';

                DB::table('auth_otp_challenges')
                    ->where('id', $challenge->id)
                    ->update([
                        'attempts' => $attempts,
                        'status' => $status,
                        'updated_at' => now(),
                    ]);

                $this->recordEvent(
                    (int) $challenge->auth_identity_id,
                    (int) $challenge->login_attempt_id,
                    'login_api_token_failed',
                    [
                        'otp_challenge_id' => $challenge->id,
                        'challenge_uuid' =>
                            $challenge->challenge_uuid,
                        'attempts' => $attempts,
                        'status' => $status,
                    ]
                );

                return null;
            }

            $identity = DB::table('auth_identities')
                ->where('id', $challenge->auth_identity_id)
                ->lockForUpdate()
                ->first([
                    'id',
                    'bfrn_user_id',
                ]);

            if (!$identity || !$identity->bfrn_user_id) {
                throw new RuntimeException(
                    'API verification identity is not linked '
                    . 'to an active BFRN user.'
                );
            }

            $iamSession = $this->authSessions->createAfterOtp(
                (int) $challenge->auth_identity_id,
                (int) $challenge->login_attempt_id
            );

            $handoffToken = bin2hex(random_bytes(40));

            DB::table('auth_handoff_tokens')->insert([
                'auth_identity_id' => (int) $identity->id,
                'iam_session_id' => (int) $iamSession->session_id,
                'bfrn_user_id' => (int) $identity->bfrn_user_id,
                'token_hash' => hash_hmac(
                    'sha256',
                    $handoffToken,
                    (string) env('AUTH_GATEWAY_HANDOFF_SECRET')
                ),
                'expires_at' => now()->addMinutes(
                    (int) env(
                        'AUTH_GATEWAY_TOKEN_TTL_MINUTES',
                        5
                    )
                ),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('auth_otp_challenges')
                ->where('id', $challenge->id)
                ->update([
                    'status' => 'verified',
                    'verified_at' => now(),
                    'verified_via' => 'api_token',
                    'consumed_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('auth_otp_challenges')
                ->where(
                    'login_attempt_id',
                    $challenge->login_attempt_id
                )
                ->where('id', '!=', $challenge->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'superseded',
                    'updated_at' => now(),
                ]);

            $this->recordEvent(
                (int) $challenge->auth_identity_id,
                (int) $challenge->login_attempt_id,
                'login_api_token_verified',
                [
                    'otp_challenge_id' => $challenge->id,
                    'challenge_uuid' =>
                        $challenge->challenge_uuid,
                    'verified_via' => 'api_token',
                    'handoff_created' => true,
                    'iam_session_id' =>
                        (int) $iamSession->session_id,
                    'bfrn_user_id' =>
                        (int) $identity->bfrn_user_id,
                ]
            );

            return (object) [
                'challenge_id' => (int) $challenge->id,
                'challenge_uuid' =>
                    (string) $challenge->challenge_uuid,
                'verified_via' => 'api_token',
                'auth_identity_id' =>
                    (int) $challenge->auth_identity_id,
                'login_attempt_id' =>
                    (int) $challenge->login_attempt_id,
                'bfrn_user_id' =>
                    (int) $identity->bfrn_user_id,
                'iam_session_id' =>
                    (int) $iamSession->session_id,
                'iam_session_token' =>
                    $iamSession->session_token,
                'iam_session_expires_at' =>
                    $iamSession->expires_at,
                'handoff_token' => $handoffToken,
            ];
        });
    }

    public function findByAccessToken(string $accessToken): ?object
    {
        return DB::table('auth_otp_challenges')
            ->where('access_token_hash', $this->hashAccessToken($accessToken))
            ->first();
    }

    private function hashAccessToken(string $accessToken): string
    {
        return hash_hmac(
            'sha256',
            trim($accessToken),
            (string) config('app.key')
        );
    }

    private function hashApiToken(string $apiToken): string
    {
        return hash_hmac(
            'sha256',
            trim($apiToken),
            (string) config('app.key')
        );
    }

    private function hashCode(string $code): string
    {
        return hash_hmac(
            'sha256',
            trim($code),
            (string) config('app.key')
        );
    }

    private function recordEvent(
        int $identityId,
        int $loginAttemptId,
        string $event,
        array $payload
    ): void {
        DB::table('auth_security_events')->insert([
            'auth_identity_id' => $identityId,
            'login_attempt_id' => $loginAttemptId,
            'event' => $event,
            'payload_encrypted' => Crypt::encryptString(json_encode($payload)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function sendMailWithRetry(string $email, $mailable): void
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                Mail::to($email)->send($mailable);

                return;
            } catch (\Throwable $exception) {
                $lastException = $exception;

                if ($attempt < 2) {
                    usleep(500000);
                }
            }
        }

        throw new RuntimeException(
            'Unable to send login verification code.',
            previous: $lastException
        );
    }
}
