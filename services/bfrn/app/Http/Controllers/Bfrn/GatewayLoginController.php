<?php

namespace App\Http\Controllers\Bfrn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GatewayLoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $token = (string) $request->query('token');

        if (!$token) {
            abort(403, 'Missing gateway token.');
        }

        $tokenHash = hash_hmac('sha256', $token, env('AUTH_GATEWAY_HANDOFF_SECRET'));

        $handoff = DB::connection('auth_gateway_mysql')
            ->table('auth_handoff_tokens')
            ->where('token_hash', $tokenHash)
            ->whereNull('used_at')
            ->where('expires_at', '>=', now())
            ->first();

        if (!$handoff) {
            abort(403, 'Invalid or expired gateway token.');
        }

        $user = DB::table('users')
            ->where('id', $handoff->bfrn_user_id)
            ->first();

        if (!$user) {
            abort(403, 'Linked BFRN user not found.');
        }

        if ($user->welcome_valid_until && strtotime($user->welcome_valid_until) < time()) {
            abort(403, 'Linked BFRN user is disabled.');
        }

        $businessUnits = $this->userBusinessUnits((int) $user->id);

        if ($businessUnits->count() < 1) {
            abort(403, 'Linked BFRN user has no active business unit access.');
        }

        DB::connection('auth_gateway_mysql')
            ->table('auth_handoff_tokens')
            ->where('id', $handoff->id)
            ->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::loginUsingId($user->id);
        $request->session()->regenerate();

        $preferredBusinessUnit = $businessUnits->firstWhere('id', 8) ?: $businessUnits->first();

        $this->setActiveBusinessUnit($request, $preferredBusinessUnit);

        $request->session()->save();

        return redirect('/bfrn/operations/dashboard');
    }

    private function userBusinessUnits(int $userId)
    {
        return DB::table('users_has_bu')
            ->join('bu', 'bu.id', '=', 'users_has_bu.bu_id')
            ->join('user_has_system', function ($join) use ($userId) {
                $join->on('user_has_system.system_id', '=', 'bu.system_id')
                    ->where('user_has_system.users_id', '=', $userId)
                    ->where('user_has_system.full_access', '=', 1);
            })
            ->leftJoin('company', 'company.id', '=', 'bu.company_id')
            ->where('users_has_bu.users_id', $userId)
            ->where('users_has_bu.has_access', 1)
            ->select(
                'bu.id',
                'bu.bu_name',
                'bu.short_code',
                'bu.company_id',
                'bu.system_id',
                'company.name as company_name'
            )
            ->distinct()
            ->orderBy('company.name')
            ->orderBy('bu.bu_name')
            ->get();
    }

    private function setActiveBusinessUnit(Request $request, $businessUnit): void
    {
        $request->session()->put([
            'active_bu_id' => $businessUnit->id,
            'active_bu_name' => $businessUnit->bu_name,
            'active_bu_short_code' => $businessUnit->short_code,
            'active_company_id' => $businessUnit->company_id ?? null,
            'active_company_name' => $businessUnit->company_name ?? null,
            'active_system_id' => $businessUnit->system_id ?? null,
        ]);
    }
}
