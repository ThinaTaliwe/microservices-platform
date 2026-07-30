<?php

namespace App\Services\Verification;

use App\Authorization\Bootstrap\BootstrapAdministratorService;
use App\Exceptions\VerificationChallengeException;
use App\Services\Bfrn\BfrnApiClient;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class BootstrapApiLoginService
{
    public function __construct(
        private readonly BfrnApiClient $bfrnApiClient,
        private readonly BootstrapAdministratorService $bootstrapAdministrators,
        private readonly VerificationChallengeService $challenges,
    ) {
    }

    public function create(
        array $input,
        ?string $ip,
        string $userAgent
    ): object {
        $email = Str::lower(
            trim((string) ($input['email'] ?? ''))
        );

        if (
            !$this->bootstrapAdministrators
                ->isBootstrapAdministrator($email)
        ) {
            throw new VerificationChallengeException(
                'BOOTSTRAP_ADMIN_REQUIRED',
                'Email-based API login is restricted to configured '
                    . 'bootstrap administrators.',
                403
            );
        }

        $bfrnUser = $this->bfrnApiClient
            ->findUserByEmail($email);

        if (!$bfrnUser) {
            $defaultBusinessUnitId = (int) env(
                'BFRN_DEFAULT_BU_ID',
                8
            );

            $provisionedUser = $this->bfrnApiClient
                ->provisionUser(
                    $email,
                    $defaultBusinessUnitId
                );

            if (
                !$provisionedUser
                || empty($provisionedUser->user_id)
            ) {
                throw new VerificationChallengeException(
                    'BFRN_BOOTSTRAP_PROVISIONING_FAILED',
                    'The bootstrap administrator could not be '
                        . 'provisioned in BFRN.',
                    502
                );
            }

            $bfrnUser = $this->bfrnApiClient
                ->findUserByEmail($email);
        }

        if (!$bfrnUser || empty($bfrnUser->id)) {
            throw new VerificationChallengeException(
                'BFRN_BOOTSTRAP_USER_NOT_FOUND',
                'The bootstrap administrator could not be '
                    . 'resolved in BFRN.',
                502
            );
        }

        $bfrnUserActive =
            empty($bfrnUser->welcome_valid_until)
            || strtotime(
                (string) $bfrnUser->welcome_valid_until
            ) >= time();

        if (!$bfrnUserActive) {
            throw new VerificationChallengeException(
                'BFRN_BOOTSTRAP_USER_INACTIVE',
                'The bootstrap administrator BFRN account '
                    . 'is inactive.',
                409
            );
        }

        $emailHash = hash_hmac(
            'sha256',
            $email,
            config('app.key')
        );

        $deviceName = trim(
            (string) ($input['device_name'] ?? 'API client')
        );

        $timezone = trim(
            (string) ($input['timezone'] ?? '')
        );

        $screen = trim(
            (string) ($input['screen'] ?? '')
        );

        $platform = trim(
            (string) ($input['platform'] ?? '')
        );

        $language = trim(
            (string) ($input['language'] ?? '')
        );

        $deviceSource = implode('|', [
            $userAgent,
            $deviceName,
            $timezone,
            $screen,
            $platform,
            $language,
        ]);

        $deviceHash = hash_hmac(
            'sha256',
            $deviceSource,
            config('app.key')
        );

        $ipHash = $ip
            ? hash_hmac(
                'sha256',
                $ip,
                config('app.key')
            )
            : null;

        $identityId = DB::table('auth_identities')
            ->where('email_hash', $emailHash)
            ->value('id');

        if (!$identityId) {
            $identityId = DB::table(
                'auth_identities'
            )->insertGetId([
                'bfrn_user_id' => (int) $bfrnUser->id,
                'email_hash' => $emailHash,
                'email_encrypted' =>
                    Crypt::encryptString($email),
                'status' => 'active',
                'last_seen_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('auth_identities')
                ->where('id', $identityId)
                ->update([
                    'bfrn_user_id' =>
                        (int) $bfrnUser->id,
                    'status' => 'active',
                    'last_seen_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        $this->bootstrapAdministrators->provision(
            authIdentityId: (int) $identityId,
            email: $email,
        );

        $payload = [
            'email' => $email,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'device_name' => $deviceName,
            'timezone' => $timezone ?: null,
            'screen' => $screen ?: null,
            'platform' => $platform ?: null,
            'language' => $language ?: null,
            'bfrn_user_exists' => true,
            'bfrn_user_active' => true,
            'bfrn_user_id' => (int) $bfrnUser->id,
            'risk_level' => 'low',
            'decision' => 'captured',
            'bootstrap_administrator' => true,
            'api_login' => true,
            'submitted_at' => now()->toDateTimeString(),
        ];

        $attemptId = DB::transaction(
            function () use (
                $identityId,
                $emailHash,
                $deviceHash,
                $ipHash,
                $payload
            ): int {
                DB::table('auth_devices')->updateOrInsert(
                    [
                        'auth_identity_id' =>
                            (int) $identityId,
                        'device_hash' => $deviceHash,
                    ],
                    [
                        'device_payload_encrypted' =>
                            Crypt::encryptString(
                                json_encode(
                                    $payload,
                                    JSON_THROW_ON_ERROR
                                )
                            ),
                        'trusted' => 1,
                        'last_seen_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $attemptId = DB::table(
                    'auth_login_attempts'
                )->insertGetId([
                    'auth_identity_id' =>
                        (int) $identityId,
                    'email_hash' => $emailHash,
                    'ip_hash' => $ipHash,
                    'device_hash' => $deviceHash,
                    'payload_encrypted' =>
                        Crypt::encryptString(
                            json_encode(
                                $payload,
                                JSON_THROW_ON_ERROR
                            )
                        ),
                    'risk_level' => 'low',
                    'decision' => 'captured',
                    'decision_reason' =>
                        'Configured bootstrap administrator. '
                        . 'API OTP verification required.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table(
                    'auth_security_events'
                )->insert([
                    'auth_identity_id' =>
                        (int) $identityId,
                    'login_attempt_id' => $attemptId,
                    'event' =>
                        'bootstrap_api_login_attempt_captured',
                    'payload_encrypted' =>
                        Crypt::encryptString(
                            json_encode([
                                'email_hash' => $emailHash,
                                'device_hash' => $deviceHash,
                                'ip_hash' => $ipHash,
                                'risk_level' => 'low',
                                'decision' => 'captured',
                                'api_login' => true,
                            ], JSON_THROW_ON_ERROR)
                        ),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return (int) $attemptId;
            },
            3
        );

        $challenge = $this->challenges
            ->createForLoginAttempt($attemptId);

        $challenge->login_attempt_id = $attemptId;
        $challenge->bootstrap_administrator = true;
        $challenge->otp_sent = true;

        return $challenge;
    }
}
