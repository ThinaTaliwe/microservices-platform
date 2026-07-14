<?php

namespace App\Services\Session;

use Illuminate\Support\Facades\DB;

class SessionValidationService
{
    public function validate(int $sessionId, int $bfrnUserId): ?object
    {
        $session = DB::table('auth_sessions as s')
            ->join('auth_identities as i', 'i.id', '=', 's.auth_identity_id')
            ->where('s.id', $sessionId)
            ->where('i.bfrn_user_id', $bfrnUserId)
            ->where('s.status', 'active')
            ->whereNull('s.revoked_at')
            ->where('s.expires_at', '>=', now())
            ->where('s.reauthentication_valid_until', '>=', now())
            ->select([
                's.id',
                's.auth_identity_id',
                's.status',
                's.last_seen_at',
                's.expires_at',
                's.reauthentication_valid_until',
                'i.bfrn_user_id',
            ])
            ->first();

        if (!$session) {
            return null;
        }

        DB::table('auth_sessions')
            ->where('id', $session->id)
            ->update([
                'last_seen_at' => now(),
                'updated_at' => now(),
            ]);

        return (object) [
            'session_id' => (int) $session->id,
            'auth_identity_id' => (int) $session->auth_identity_id,
            'bfrn_user_id' => (int) $session->bfrn_user_id,
            'status' => (string) $session->status,
            'expires_at' => $session->expires_at,
            'reauthentication_valid_until' =>
                $session->reauthentication_valid_until,
        ];
    }
}
