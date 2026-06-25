<?php

namespace App\Http\Controllers\AuthGateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\BfrnUserCreatedMail;
use Illuminate\Support\Str;

class SupervisorController extends Controller
{
    public function index()
    {
        $approvals = DB::table('auth_pending_approvals as a')
            ->join('auth_login_attempts as l', 'l.id', '=', 'a.login_attempt_id')
            ->join('auth_identities as i', 'i.id', '=', 'a.auth_identity_id')
            ->select([
                'a.id',
                'a.login_attempt_id',
                'a.status',
                'a.approved_bu_id',
                'a.expires_at',
                'a.created_at',
                'l.risk_level',
                'l.decision',
                'l.decision_reason',
                'l.payload_encrypted',
                'l.device_hash',
                'i.email_hash',
                'i.email_encrypted',
                'i.bfrn_user_id',
            ])
            ->orderByDesc('a.id')
            ->get()
            ->map(function ($approval) {
                $payload = [];

                try {
                    $payload = json_decode(Crypt::decryptString($approval->payload_encrypted), true) ?: [];
                } catch (\Throwable $e) {
                    $payload = [];
                }

                try {
                    $approval->email = Crypt::decryptString($approval->email_encrypted);
                } catch (\Throwable $e) {
                    $approval->email = 'Unknown';
                }

                $approval->ip = $payload['ip'] ?? 'Unknown';
                $approval->user_agent = $payload['user_agent'] ?? 'Unknown';
                $approval->timezone = $payload['timezone'] ?? 'Unknown';
                $approval->screen = $payload['screen'] ?? 'Unknown';
                $approval->platform = $payload['platform'] ?? 'Unknown';
                $approval->language = $payload['language'] ?? 'Unknown';
                $approval->location = 'Johannesburg, ZA';
                $approval->trusted = $approval->trusted ?? 0;

                $approval->device_label = $approval->trusted
                    ? 'Known trusted device'
                    : 'New device';

                return $approval;
            });

        $summary = [
            'pending' => $approvals->where('status', 'pending')->count(),
            'approved' => $approvals->where('status', 'approved')->count(),
            'blocked' => $approvals->where('status', 'blocked')->count(),
            'expired' => $approvals->where('status', 'expired')->count(),
        ];

        $businessUnits = DB::connection('bfrn_mysql')
            ->table('bu')
            ->select('id', 'bu_name', 'short_code', 'system_id')
            ->orderBy('bu_name')
            ->get();

        return view('auth-gateway.supervisor.index', compact('approvals', 'summary', 'businessUnits'));
    }

    public function approve(Request $request, int $id)
    {
        $request->validate([
            'approved_bu_id' => ['nullable', 'integer'],
        ]);

        return $this->decide($id, 'approved', 'Supervisor approved login request.', $request->integer('approved_bu_id') ?: null);
    }

    public function block(Request $request, int $id)
    {
        return $this->decide($id, 'blocked', 'Supervisor blocked login request.');
    }

    private function decide(int $id, string $status, string $reason, ?int $approvedBuId = null)
    {
        $approval = DB::table('auth_pending_approvals')->where('id', $id)->first();

        if (!$approval) {
            return back()->with('error', 'Approval request not found.');
        }

        if ($approval->status !== 'pending') {
            return back()->with('error', 'This approval request was already decided.');
        }

        if (strtotime($approval->expires_at) < time()) {
            DB::table('auth_pending_approvals')
                ->where('id', $id)
                ->update([
                    'status' => 'expired',
                    'updated_at' => now(),
                ]);

            return back()->with('error', 'This approval request has expired.');
        }

        $mailPayload = null;

        DB::transaction(function () use ($approval, $id, $status, $reason, $approvedBuId, &$mailPayload) {
            DB::table('auth_pending_approvals')
                ->where('id', $id)
                ->update([
                    'status' => $status,
                    'approved_bu_id' => $approvedBuId,
                    'decided_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('auth_login_attempts')
                ->where('id', $approval->login_attempt_id)
                ->update([
                    'decision' => $status === 'approved' ? 'supervisor_approved' : 'supervisor_blocked',
                    'decision_reason' => $reason,
                    'updated_at' => now(),
                ]);

            $handoffToken = null;
            $handoffUrl = null;

            if ($status === 'approved') {
                $loginAttempt = DB::table('auth_login_attempts')
                    ->where('id', $approval->login_attempt_id)
                    ->first(['id', 'device_hash']);

                if ($loginAttempt && $loginAttempt->device_hash) {
                    DB::table('auth_devices')
                        ->where('auth_identity_id', $approval->auth_identity_id)
                        ->where('device_hash', $loginAttempt->device_hash)
                        ->update([
                            'trusted' => 1,
                            'updated_at' => now(),
                        ]);
                }

                $identity = DB::table('auth_identities')
                    ->where('id', $approval->auth_identity_id)
                    ->first(['id', 'email_encrypted', 'bfrn_user_id']);

                $temporaryPassword = null;
                $createdBfrnUser = false;

                if ($identity) {
                    $email = null;

                    try {
                        $email = Crypt::decryptString($identity->email_encrypted);
                    } catch (\Throwable $e) {
                        $email = null;
                    }

                    if (!$identity->bfrn_user_id && $email) {
                        $existingBfrnUser = DB::connection('bfrn_mysql')
                            ->table('users')
                            ->where('email', $email)
                            ->first(['id']);

                        if ($existingBfrnUser) {
                            $identity->bfrn_user_id = $existingBfrnUser->id;

                            DB::table('auth_identities')
                                ->where('id', $identity->id)
                                ->update([
                                    'bfrn_user_id' => $existingBfrnUser->id,
                                    'updated_at' => now(),
                                ]);
                        } else {
                            $temporaryPassword = Str::password(14);

                            $bfrnUserId = DB::connection('bfrn_mysql')
                                ->table('users')
                                ->insertGetId([
                                    'name' => $email,
                                    'email' => $email,
                                    'email_verified_at' => now(),
                                    'password' => Hash::make($temporaryPassword),
                                    'must_change_password' => 1,
                                    'welcome_valid_until' => now()->addYear(),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);

                            $defaultBuId = $approvedBuId ?: (int) env('BFRN_DEFAULT_BU_ID', 8);

                            $bu = DB::connection('bfrn_mysql')
                                ->table('bu')
                                ->where('id', $defaultBuId)
                                ->first(['id', 'system_id']);

                            if ($bu) {
                                DB::connection('bfrn_mysql')->table('users_has_bu')->insert([
                                    'users_id' => $bfrnUserId,
                                    'bu_id' => $bu->id,
                                    'requested' => 0,
                                    'has_access' => 1,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);

                                DB::connection('bfrn_mysql')->table('user_has_system')->insert([
                                    'users_id' => $bfrnUserId,
                                    'system_id' => $bu->system_id,
                                    'full_access' => 1,
                                    'system_size' => 'S',
                                    'company_id' => null,
                                    'bu_id' => $bu->id,
                                    'component_id' => null,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }

                            $identity->bfrn_user_id = $bfrnUserId;
                            $createdBfrnUser = true;

                            DB::table('auth_identities')
                                ->where('id', $identity->id)
                                ->update([
                                    'bfrn_user_id' => $bfrnUserId,
                                    'updated_at' => now(),
                                ]);

                            $mailPayload = [
                                'identity_id' => $identity->id,
                                'login_attempt_id' => $approval->login_attempt_id,
                                'email' => $email,
                                'temporary_password' => $temporaryPassword,
                                'bfrn_user_id' => $bfrnUserId,
                            ];

                        }
                    }

                    if ($identity->bfrn_user_id) {
                        $handoffToken = Str::random(80);
                        $handoffUrl = rtrim((string) env('BFRN_BASE_URL'), '/') . '/bfrn/gateway-login?token=' . $handoffToken;

                        DB::table('auth_handoff_tokens')->insert([
                            'auth_identity_id' => $identity->id,
                            'bfrn_user_id' => $identity->bfrn_user_id,
                            'token_hash' => hash_hmac('sha256', $handoffToken, env('AUTH_GATEWAY_HANDOFF_SECRET')),
                            'expires_at' => now()->addMinutes((int) env('AUTH_GATEWAY_TOKEN_TTL_MINUTES', 5)),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::table('auth_security_events')->insert([
                'auth_identity_id' => $approval->auth_identity_id,
                'login_attempt_id' => $approval->login_attempt_id,
                'event' => $status === 'approved' ? 'supervisor_approved' : 'supervisor_blocked',
                'payload_encrypted' => Crypt::encryptString(json_encode([
                    'approval_id' => $id,
                    'status' => $status,
                    'reason' => $reason,
                    'handoff_created' => (bool) $handoffToken,
                    'handoff_url_preview' => $handoffUrl ? substr($handoffUrl, 0, 90) . '...' : null,
                    'decided_at' => now()->toDateTimeString(),
                ])),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        if ($mailPayload) {
            try {
                $baseDetails = [
                    'email' => $mailPayload['email'],
                    'password' => $mailPayload['temporary_password'],
                    'bfrn_user_id' => $mailPayload['bfrn_user_id'],
                    'auth_gateway_url' => rtrim((string) env('APP_URL'), '/'),
                    'bfrn_login_url' => rtrim((string) env('BFRN_BASE_URL'), '/') . '/bfrn/login',
                ];

                $this->sendMailWithRetry($mailPayload['email'], new BfrnUserCreatedMail(array_merge($baseDetails, [
                    'show_password' => true,
                ])));

                $supervisors = collect(explode(',', (string) env('AUTH_GATEWAY_SUPERVISORS')))
                    ->map(fn ($email) => trim($email))
                    ->filter()
                    ->unique();

                foreach ($supervisors as $supervisorEmail) {
                    $this->sendMailWithRetry($supervisorEmail, new BfrnUserCreatedMail(array_merge($baseDetails, [
                        'show_password' => false,
                    ])));
                }
            } catch (\Throwable $e) {
                DB::table('auth_security_events')->insert([
                    'auth_identity_id' => $mailPayload['identity_id'],
                    'login_attempt_id' => $mailPayload['login_attempt_id'],
                    'event' => 'bfrn_credentials_email_failed',
                    'payload_encrypted' => Crypt::encryptString(json_encode([
                        'email' => $mailPayload['email'],
                        'error' => $e->getMessage(),
                        'failed_at' => now()->toDateTimeString(),
                    ])),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }


        return back()->with('success', $reason);
    }

    public function approveFromEmail(int $id, \Illuminate\Http\Request $request)
    {
        return $this->handleEmailDecision($id, $request, 'approved');
    }

    public function blockFromEmail(int $id, \Illuminate\Http\Request $request)
    {
        return $this->handleEmailDecision($id, $request, 'blocked');
    }

    private function handleEmailDecision(int $id, \Illuminate\Http\Request $request, string $status)
    {
        $token = (string) $request->query('token');

        if (!$token) {
            abort(403, 'Missing approval token.');
        }

        $approval = DB::table('auth_pending_approvals')
            ->where('id', $id)
            ->where('status', 'pending')
            ->where('expires_at', '>=', now())
            ->first();

        if (!$approval) {
            abort(403, 'Approval request is invalid or expired.');
        }

        $tokenHash = hash_hmac('sha256', $token, config('app.key'));

        if (!hash_equals($approval->approval_token_hash, $tokenHash)) {
            abort(403, 'Invalid approval token.');
        }

        $reason = $status === 'approved'
            ? 'Supervisor approved login request from email.'
            : 'Supervisor blocked login request from email.';

        return $this->decide($id, $status, $reason, (int) ($approval->approved_bu_id ?: env('BFRN_DEFAULT_BU_ID', 8)));
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
