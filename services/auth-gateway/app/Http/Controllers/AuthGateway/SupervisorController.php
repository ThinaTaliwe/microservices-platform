<?php

namespace App\Http\Controllers\AuthGateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
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
                'a.expires_at',
                'a.created_at',
                'l.risk_level',
                'l.decision',
                'l.decision_reason',
                'i.email_hash',
            ])
            ->orderByDesc('a.id')
            ->get();

        $summary = [
            'pending' => $approvals->where('status', 'pending')->count(),
            'approved' => $approvals->where('status', 'approved')->count(),
            'blocked' => $approvals->where('status', 'blocked')->count(),
            'expired' => $approvals->where('status', 'expired')->count(),
        ];

        return view('auth-gateway.supervisor.index', compact('approvals', 'summary'));
    }

    public function approve(Request $request, int $id)
    {
        return $this->decide($id, 'approved', 'Supervisor approved login request.');
    }

    public function block(Request $request, int $id)
    {
        return $this->decide($id, 'blocked', 'Supervisor blocked login request.');
    }

    private function decide(int $id, string $status, string $reason)
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

        DB::transaction(function () use ($approval, $id, $status, $reason) {
            DB::table('auth_pending_approvals')
                ->where('id', $id)
                ->update([
                    'status' => $status,
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
                $identity = DB::table('auth_identities')
                    ->where('id', $approval->auth_identity_id)
                    ->first(['id', 'bfrn_user_id']);

                if ($identity && $identity->bfrn_user_id) {
                    $handoffToken = Str::random(80);
                    $handoffUrl = rtrim((string) env('BFRN_BASE_URL'), '/') . '/gateway-login?token=' . $handoffToken;

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

        return back()->with('success', $reason);
    }
}
