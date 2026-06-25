<?php

namespace App\Http\Controllers\Bfrn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (auth()->check()) {
            return session()->has('active_bu_id')
                ? $this->redirectRelative('/bfrn/operations/dashboard')
                : $this->redirectRelative('/bfrn/select-business-unit');
        }

        return view('bfrn.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Invalid email or password.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->welcome_valid_until && now()->greaterThan($user->welcome_valid_until)) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'This user account is disabled or expired.',
            ])->onlyInput('email');
        }

        if ((int) ($user->must_change_password ?? 0) === 1) {
            return $this->redirectRelative('/bfrn/password/change');
        }

        $businessUnits = $this->userBusinessUnits(Auth::id());

        if ($businessUnits->count() === 1) {
            $this->setActiveBusinessUnit($request, $businessUnits->first());
            return $this->redirectRelative('/bfrn/operations/dashboard');
        }

        return $this->redirectRelative('/bfrn/select-business-unit');
    }

    public function showBusinessUnitSelect(Request $request)
    {
        if (session()->has('active_bu_id') && !$request->boolean('switch')) {
            return $this->redirectRelative('/bfrn/operations/dashboard');
        }

        if ($request->boolean('switch')) {
            $request->session()->put('bu_switch_return_to', url()->previous());
        }

        $businessUnits = $this->userBusinessUnits(Auth::id());

        return view('bfrn.auth.select-bu', [
            'businessUnits' => $businessUnits,
            'isSwitching' => $request->boolean('switch'),
        ]);
    }

    public function selectBusinessUnit(Request $request)
    {
        $validated = $request->validate([
            'bu_id' => ['required', 'integer'],
        ]);

        $businessUnit = $this->userBusinessUnits(Auth::id())
            ->firstWhere('id', (int) $validated['bu_id']);

        if (!$businessUnit) {
            return back()->withErrors([
                'bu_id' => 'You do not have access to the selected business unit.',
            ]);
        }

        $this->setActiveBusinessUnit($request, $businessUnit);

        $returnTo = $request->session()->pull('bu_switch_return_to');

        if ($returnTo && str_contains($returnTo, '/bfrn/') && !str_contains($returnTo, '/bfrn/login')) {
            return redirect($returnTo);
        }

        return $this->redirectRelative('/bfrn/operations/dashboard');
    }

    public function showChangePassword()
    {
        return view('bfrn.auth.change-password');
    }

    public function updateChangePassword(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::table('users')
            ->where('id', Auth::id())
            ->update([
                'password' => Hash::make($data['password']),
                'must_change_password' => 0,
                'updated_at' => now(),
            ]);

        $businessUnits = $this->userBusinessUnits(Auth::id());
        if ($businessUnits->count() === 1) {
            $this->setActiveBusinessUnit($request, $businessUnits->first());
            return $this->redirectRelative('/bfrn/operations/dashboard');
        }

        return $this->redirectRelative('/bfrn/select-business-unit');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->forget([
            'active_bu_id',
            'active_bu_name',
            'active_bu_short_code',
            'active_company_id',
            'active_company_name',
            'active_system_id',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->redirectRelative('/bfrn/login');
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

    private function redirectRelative(string $path)
    {
        return response('', 302)->header('Location', $path);
    }
}
