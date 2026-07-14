<?php

namespace App\Services\Handoff;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class HandoffTokenService
{
    public function consume(string $token): ?object
    {
        $token = trim($token);

        if ($token === '') {
            return null;
        }

        $tokenHash = hash_hmac(
            'sha256',
            $token,
            (string) env('AUTH_GATEWAY_HANDOFF_SECRET')
        );

        return DB::transaction(function () use ($tokenHash) {
            $handoff = DB::table('auth_handoff_tokens')
                ->where('token_hash', $tokenHash)
                ->whereNull('used_at')
                ->where('expires_at', '>=', now())
                ->lockForUpdate()
                ->first();

            if (!$handoff) {
                return null;
            }

            $updated = DB::table('auth_handoff_tokens')
                ->where('id', $handoff->id)
                ->whereNull('used_at')
                ->update([
                    'used_at' => now(),
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                return null;
            }

            DB::table('auth_security_events')->insert([
                'auth_identity_id' => $handoff->auth_identity_id,
                'login_attempt_id' => null,
                'event' => 'handoff_token_consumed',
                'payload_encrypted' => Crypt::encryptString(json_encode([
                    'handoff_token_id' => $handoff->id,
                    'iam_session_id' => $handoff->iam_session_id
                        ? (int) $handoff->iam_session_id
                        : null,
                    'bfrn_user_id' => $handoff->bfrn_user_id,
                    'consumed_at' => now()->toDateTimeString(),
                ])),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return (object) [
                'auth_identity_id' => (int) $handoff->auth_identity_id,
                'iam_session_id' => $handoff->iam_session_id
                    ? (int) $handoff->iam_session_id
                    : null,
                'bfrn_user_id' => (int) $handoff->bfrn_user_id,
            ];
        });
    }
}
