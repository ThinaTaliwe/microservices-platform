<?php

namespace App\Services\Verification;

use App\Exceptions\VerificationChallengeException;
use App\Services\Otp\OtpChallengeService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

final class VerificationChallengeService
{
    private const ALLOWED_LOGIN_DECISIONS = [
        'captured',
        'approved',
    ];

    private const MAX_RESENDS = 3;

    public function __construct(
        private readonly OtpChallengeService $otpChallenges
    ) {
    }

    public function createForLoginAttempt(int $loginAttemptId): object
    {
        $context = $this->resolveEligibleLoginAttempt($loginAttemptId);

        $activeChallengeId = DB::table(
            'auth_otp_challenges'
        )
            ->where(
                'login_attempt_id',
                $context->login_attempt_id
            )
            ->where('status', 'pending')
            ->where('expires_at', '>=', now())
            ->value('id');

        if ($activeChallengeId) {
            $challenge = $this->otpChallenges->issueApiToken(
                (int) $activeChallengeId
            );

            if (!$challenge) {
                throw new VerificationChallengeException(
                    'VERIFICATION_CHALLENGE_NOT_ACTIVE',
                    'The verification challenge is no longer active.',
                    409
                );
            }

            return $challenge;
        }

        return $this->otpChallenges->issue(
            $context->auth_identity_id,
            $context->login_attempt_id,
            $context->email
        );
    }

    public function findStatus(string $challengeUuid): ?object
    {
        $challenge = DB::table('auth_otp_challenges')
            ->where('challenge_uuid', $challengeUuid)
            ->first([
                'challenge_uuid',
                'purpose',
                'status',
                'attempts',
                'max_attempts',
                'resend_count',
                'expires_at',
                'verified_at',
                'verified_via',
                'consumed_at',
                'revoked_at',
                'created_at',
                'updated_at',
            ]);

        if (!$challenge) {
            return null;
        }

        $effectiveStatus = (string) $challenge->status;

        if (
            $effectiveStatus === 'pending'
            && now()->greaterThan($challenge->expires_at)
        ) {
            $effectiveStatus = 'expired';
        }

        return (object) [
            'challenge_uuid' => (string) $challenge->challenge_uuid,
            'purpose' => (string) $challenge->purpose,
            'status' => $effectiveStatus,
            'attempts' => (int) $challenge->attempts,
            'max_attempts' => (int) $challenge->max_attempts,
            'remaining_attempts' => max(
                0,
                (int) $challenge->max_attempts
                    - (int) $challenge->attempts
            ),
            'resend_count' => (int) $challenge->resend_count,
            'max_resends' => self::MAX_RESENDS,
            'expires_at' => $challenge->expires_at,
            'verified_at' => $challenge->verified_at,
            'verified_via' => $challenge->verified_via,
            'consumed_at' => $challenge->consumed_at,
            'revoked_at' => $challenge->revoked_at,
            'created_at' => $challenge->created_at,
            'updated_at' => $challenge->updated_at,
        ];
    }

    public function verify(
        string $challengeUuid,
        ?string $code,
        ?string $apiToken
    ): ?object {
        $hasCode = $code !== null && trim($code) !== '';
        $hasApiToken = $apiToken !== null && trim($apiToken) !== '';

        if ($hasCode === $hasApiToken) {
            throw new VerificationChallengeException(
                'VERIFICATION_CREDENTIAL_INVALID',
                'Provide exactly one verification credential.',
                422
            );
        }

        if ($hasCode) {
            $challengeId = DB::table('auth_otp_challenges')
                ->where('challenge_uuid', $challengeUuid)
                ->value('id');

            if (!$challengeId) {
                throw new VerificationChallengeException(
                    'VERIFICATION_CHALLENGE_NOT_FOUND',
                    'Verification challenge was not found.',
                    404
                );
            }

            return $this->otpChallenges->verify(
                (int) $challengeId,
                trim((string) $code)
            );
        }

        return $this->otpChallenges->verifyApiToken(
            $challengeUuid,
            trim((string) $apiToken)
        );
    }

    public function resend(string $challengeUuid): object
    {
        $challenge = DB::transaction(function () use (
            $challengeUuid
        ): object {
            $challenge = DB::table('auth_otp_challenges')
                ->where('challenge_uuid', $challengeUuid)
                ->lockForUpdate()
                ->first();

            if (!$challenge) {
                throw new VerificationChallengeException(
                    'VERIFICATION_CHALLENGE_NOT_FOUND',
                    'Verification challenge was not found.',
                    404
                );
            }

            if ($challenge->status !== 'pending') {
                throw new VerificationChallengeException(
                    'VERIFICATION_CHALLENGE_NOT_ACTIVE',
                    'Only a pending challenge can be resent.',
                    409
                );
            }

            if (now()->greaterThan($challenge->expires_at)) {
                DB::table('auth_otp_challenges')
                    ->where('id', $challenge->id)
                    ->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);

                throw new VerificationChallengeException(
                    'VERIFICATION_CHALLENGE_EXPIRED',
                    'The verification challenge has expired.',
                    410
                );
            }

            $nextResendCount = (int) $challenge->resend_count + 1;

            if ($nextResendCount > self::MAX_RESENDS) {
                throw new VerificationChallengeException(
                    'VERIFICATION_RESEND_LIMIT_REACHED',
                    'The verification resend limit has been reached.',
                    429
                );
            }

            DB::table('auth_otp_challenges')
                ->where('id', $challenge->id)
                ->update([
                    'resend_count' => $nextResendCount,
                    'updated_at' => now(),
                ]);

            $this->recordEvent(
                (int) $challenge->auth_identity_id,
                (int) $challenge->login_attempt_id,
                'login_verification_resend_requested',
                [
                    'otp_challenge_id' => (int) $challenge->id,
                    'challenge_uuid' =>
                        (string) $challenge->challenge_uuid,
                    'resend_count' => $nextResendCount,
                    'requested_at' => now()->toDateTimeString(),
                ]
            );

            return (object) [
                'login_attempt_id' =>
                    (int) $challenge->login_attempt_id,
                'resend_count' => $nextResendCount,
            ];
        });

        $context = $this->resolveEligibleLoginAttempt(
            $challenge->login_attempt_id
        );

        return $this->otpChallenges->issue(
            $context->auth_identity_id,
            $context->login_attempt_id,
            $context->email,
            $challenge->resend_count
        );
    }

    public function revoke(string $challengeUuid): object
    {
        return DB::transaction(function () use (
            $challengeUuid
        ): object {
            $challenge = DB::table('auth_otp_challenges')
                ->where('challenge_uuid', $challengeUuid)
                ->lockForUpdate()
                ->first();

            if (!$challenge) {
                throw new VerificationChallengeException(
                    'VERIFICATION_CHALLENGE_NOT_FOUND',
                    'Verification challenge was not found.',
                    404
                );
            }

            if ($challenge->status !== 'pending') {
                throw new VerificationChallengeException(
                    'VERIFICATION_CHALLENGE_NOT_ACTIVE',
                    'Only a pending challenge can be revoked.',
                    409
                );
            }

            DB::table('auth_otp_challenges')
                ->where('id', $challenge->id)
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->recordEvent(
                (int) $challenge->auth_identity_id,
                (int) $challenge->login_attempt_id,
                'login_verification_revoked',
                [
                    'otp_challenge_id' => (int) $challenge->id,
                    'challenge_uuid' =>
                        (string) $challenge->challenge_uuid,
                    'revoked_at' => now()->toDateTimeString(),
                ]
            );

            return (object) [
                'challenge_uuid' =>
                    (string) $challenge->challenge_uuid,
                'status' => 'revoked',
                'revoked_at' => now()->toDateTimeString(),
            ];
        });
    }

    private function resolveEligibleLoginAttempt(
        int $loginAttemptId
    ): object {
        $attempt = DB::table('auth_login_attempts as attempts')
            ->join(
                'auth_identities as identities',
                'identities.id',
                '=',
                'attempts.auth_identity_id'
            )
            ->where('attempts.id', $loginAttemptId)
            ->first([
                'attempts.id as login_attempt_id',
                'attempts.auth_identity_id',
                'attempts.decision',
                'identities.status as identity_status',
                'identities.email_encrypted',
            ]);

        if (!$attempt) {
            throw new VerificationChallengeException(
                'LOGIN_ATTEMPT_NOT_FOUND',
                'The login attempt was not found.',
                404
            );
        }

        if (
            !in_array(
                (string) $attempt->decision,
                self::ALLOWED_LOGIN_DECISIONS,
                true
            )
        ) {
            throw new VerificationChallengeException(
                'LOGIN_ATTEMPT_NOT_ELIGIBLE',
                'The login attempt is not eligible for verification.',
                409
            );
        }

        if ((string) $attempt->identity_status !== 'active') {
            throw new VerificationChallengeException(
                'AUTH_IDENTITY_INACTIVE',
                'The authentication identity is not active.',
                403
            );
        }

        try {
            $email = Crypt::decryptString(
                (string) $attempt->email_encrypted
            );
        } catch (\Throwable $exception) {
            throw new VerificationChallengeException(
                'AUTH_IDENTITY_EMAIL_UNAVAILABLE',
                'The authentication identity email is unavailable.',
                500
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new VerificationChallengeException(
                'AUTH_IDENTITY_EMAIL_INVALID',
                'The authentication identity email is invalid.',
                500
            );
        }

        return (object) [
            'login_attempt_id' => (int) $attempt->login_attempt_id,
            'auth_identity_id' =>
                (int) $attempt->auth_identity_id,
            'email' => $email,
        ];
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
            'payload_encrypted' => Crypt::encryptString(
                json_encode($payload)
            ),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
