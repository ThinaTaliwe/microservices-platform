<?php

namespace App\Http\Controllers\AuthGateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SupervisorLoginApprovalMail;
use App\Services\Bfrn\BfrnApiClient;
use App\Services\Otp\OtpChallengeService;
use App\Services\Session\AuthSessionService;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function __construct(
        private readonly BfrnApiClient $bfrnApiClient,
        private readonly OtpChallengeService $otpChallenges,
        private readonly AuthSessionService $authSessions
    ) {
    }

    public function show()
    {
        return view('auth-gateway.login');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'screen' => ['nullable', 'string', 'max:100'],
            'platform' => ['nullable', 'string', 'max:120'],
            'language' => ['nullable', 'string', 'max:80'],
        ]);

        $email = Str::lower(trim($validated['email']));
        $emailHash = hash_hmac('sha256', $email, config('app.key'));

        $bfrnUser = $this->bfrnApiClient->findUserByEmail($email);

        $bfrnUserExists = (bool) $bfrnUser;

        $bfrnUserActive = $bfrnUserExists
            && (!$bfrnUser->welcome_valid_until || strtotime($bfrnUser->welcome_valid_until) >= time());

        $ip = $request->ip();
        $userAgent = (string) $request->userAgent();

        $deviceSource = implode('|', [
            $userAgent,
            $validated['timezone'] ?? '',
            $validated['screen'] ?? '',
            $validated['platform'] ?? '',
            $validated['language'] ?? '',
        ]);

        $deviceHash = hash_hmac('sha256', $deviceSource, config('app.key'));
        $ipHash = $ip ? hash_hmac('sha256', $ip, config('app.key')) : null;

        $knownTrustedDevice = false;

        if ($bfrnUserExists && $bfrnUserActive) {
            $existingIdentityId = DB::table('auth_identities')->where('email_hash', $emailHash)->value('id');

            if ($existingIdentityId) {
                $knownTrustedDevice = DB::table('auth_devices')
                    ->where('auth_identity_id', $existingIdentityId)
                    ->where('device_hash', $deviceHash)
                    ->where('trusted', 1)
                    ->exists();
            }
        }

        $riskLevel = match (true) {
            !$bfrnUserExists => 'high',
            !$bfrnUserActive => 'high',
            !$knownTrustedDevice => 'high',
            default => 'low',
        };

        $decision = $riskLevel === 'low'
            ? 'captured'
            : 'supervisor_review';

        $decisionReason = match (true) {
            !$bfrnUserExists => 'Email does not exist in BFRN users table. Supervisor review required.',
            !$bfrnUserActive => 'BFRN user exists but is disabled. Supervisor review required.',
            !$knownTrustedDevice => 'New device detected. Supervisor review required.',
            default => 'Known active BFRN user using trusted device.',
        };

        $payload = [
            'email' => $email,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'timezone' => $validated['timezone'] ?? null,
            'screen' => $validated['screen'] ?? null,
            'platform' => $validated['platform'] ?? null,
            'language' => $validated['language'] ?? null,
            'accept_language' => $request->header('Accept-Language'),
            'referer' => $request->header('Referer'),
            'bfrn_user_exists' => $bfrnUserExists,
            'bfrn_user_active' => $bfrnUserActive,
            'bfrn_user_id' => $bfrnUser->id ?? null,
            'risk_level' => $riskLevel,
            'decision' => $decision,
            'submitted_at' => now()->toDateTimeString(),
        ];

        $identityId = DB::table('auth_identities')->where('email_hash', $emailHash)->value('id');

        if (!$identityId) {
            $identityId = DB::table('auth_identities')->insertGetId([
                'bfrn_user_id' => $bfrnUser->id ?? null,
                'email_hash' => $emailHash,
                'email_encrypted' => Crypt::encryptString($email),
                'status' => 'active',
                'last_seen_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('auth_identities')
                ->where('id', $identityId)
                ->update([
                    'bfrn_user_id' => $bfrnUser->id ?? null,
                    'last_seen_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        DB::table('auth_devices')->updateOrInsert(
            [
                'auth_identity_id' => $identityId,
                'device_hash' => $deviceHash,
            ],
            [
                'device_payload_encrypted' => Crypt::encryptString(json_encode($payload)),
                'last_seen_at' => now(),
                'updated_at' => now(),
            ]
        );

        $attemptId = DB::table('auth_login_attempts')->insertGetId([
            'auth_identity_id' => $identityId,
            'email_hash' => $emailHash,
            'ip_hash' => $ipHash,
            'device_hash' => $deviceHash,
            'payload_encrypted' => Crypt::encryptString(json_encode($payload)),
            'risk_level' => $riskLevel,
            'decision' => $decision,
            'decision_reason' => $decisionReason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('auth_security_events')->insert([
            'auth_identity_id' => $identityId,
            'login_attempt_id' => $attemptId,
            'event' => 'login_attempt_captured',
            'payload_encrypted' => Crypt::encryptString(json_encode([
                'email_hash' => $emailHash,
                'ip_hash' => $ipHash,
                'device_hash' => $deviceHash,
                'risk_level' => $riskLevel,
                'decision' => $decision,
                'bfrn_user_exists' => $bfrnUserExists,
                'bfrn_user_active' => $bfrnUserActive,
            ])),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($decision === 'supervisor_review') {
            $supervisors = collect(explode(',', (string) env('AUTH_GATEWAY_SUPERVISORS')))
                ->map(fn ($email) => Str::lower(trim($email)))
                ->filter()
                ->unique()
                ->values();

            foreach ($supervisors as $supervisorEmail) {
                $approvalToken = Str::random(64);

                $approvalId = DB::table('auth_pending_approvals')->insertGetId([
                    'login_attempt_id' => $attemptId,
                    'auth_identity_id' => $identityId,
                    'supervisor_email_hash' => hash_hmac('sha256', $supervisorEmail, config('app.key')),
                    'approval_token_hash' => hash_hmac('sha256', $approvalToken, config('app.key')),
                    'status' => 'pending',
                    'expires_at' => now()->addMinutes((int) env('AUTH_GATEWAY_APPROVAL_TTL_MINUTES', 30)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('auth_security_events')->insert([
                    'auth_identity_id' => $identityId,
                    'login_attempt_id' => $attemptId,
                    'event' => 'supervisor_approval_created',
                    'payload_encrypted' => Crypt::encryptString(json_encode([
                        'supervisor_email' => $supervisorEmail,
                        'approval_token_preview' => substr($approvalToken, 0, 8) . '...',
                        'expires_minutes' => (int) env('AUTH_GATEWAY_APPROVAL_TTL_MINUTES', 30),
                    ])),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->sendMailWithRetry($supervisorEmail, new SupervisorLoginApprovalMail([
                    'email' => $email,
                    'masked_email' => $email,
                    'device' => $knownTrustedDevice ? 'Known trusted device' : 'New device',
                    'location' => 'Johannesburg, ZA',
                    'risk' => ucfirst($riskLevel),
                    'reason' => $decisionReason,
                    'ip' => $ip ?? 'Unknown',
                    'platform' => $validated['platform'] ?? 'Unknown',
                    'screen' => $validated['screen'] ?? 'Unknown',
                    'timezone' => $validated['timezone'] ?? 'Unknown',
                    'language' => $validated['language'] ?? 'Unknown',
                    'browser' => $userAgent ?? 'Unknown',
                    'bfrn_user_id' => $bfrnUser->id ?? 'Not linked',
                    'attempt_time' => now()->toDateTimeString(),
                    'supervisor_url' => rtrim((string) env('APP_URL'), '/') . '/supervisor?approval=' . $approvalId,
                    'approve_url' => rtrim((string) env('APP_URL'), '/') . '/supervisor/email/' . $approvalId . '/approve?token=' . urlencode($approvalToken),
                    'block_url' => rtrim((string) env('APP_URL'), '/') . '/supervisor/email/' . $approvalId . '/block?token=' . urlencode($approvalToken),
                ]));
            }
        }

        if ($decision === 'captured') {
            $iamSessionToken = trim(
                (string) $request->cookie('iam_session_token')
            );

            $reusableSession = $this->authSessions->findReusableByToken(
                $iamSessionToken,
                $identityId,
                $deviceHash,
                $riskLevel
            );

            if ($reusableSession && $bfrnUser && !empty($bfrnUser->id)) {
                $handoffToken = $this->authSessions->createHandoff(
                    (int) $reusableSession->id,
                    $identityId,
                    (int) $bfrnUser->id
                );

                DB::table('auth_security_events')->insert([
                    'auth_identity_id' => $identityId,
                    'login_attempt_id' => $attemptId,
                    'event' => 'iam_session_reused',
                    'payload_encrypted' => Crypt::encryptString(json_encode([
                        'iam_session_id' => (int) $reusableSession->id,
                        'device_hash' => $deviceHash,
                        'risk_level' => $riskLevel,
                        'reused_at' => now()->toDateTimeString(),
                    ])),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return redirect(
                    rtrim((string) env('BFRN_BASE_URL'), '/')
                    . '/bfrn/gateway-login?token='
                    . urlencode($handoffToken)
                );
            }

            $otpChallenge = $this->otpChallenges->issue(
                $identityId,
                $attemptId,
                $email
            );

            return redirect()->route('otp.verify', [
                'token' => $otpChallenge->access_token,
            ]);
        }

        return back()->with('success', 'Login request captured for supervisor review.');
    }


    private function sendMailWithRetry(string $email, $mailable): void
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                Mail::to($email)->send($mailable);
                return;
            } catch (\Throwable $e) {
                $lastException = $e;
                sleep(2);
            }
        }

        throw $lastException;
    }

}
