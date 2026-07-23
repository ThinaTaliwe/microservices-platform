<?php

namespace App\Http\Controllers\AuthGateway;

use App\Authorization\Resolver\TrustedSessionContextService;
use App\Http\Controllers\Controller;
use App\Services\Otp\OtpChallengeService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OtpController extends Controller
{
    public function __construct(
        private readonly OtpChallengeService $otpChallenges,
        private readonly TrustedSessionContextService $trustedContext,
    ) {
    }

    public function show(Request $request): View
    {
        $token = trim((string) $request->query('token'));

        abort_if($token === '', 403, 'Missing OTP verification token.');

        $challenge = $this->otpChallenges->findByAccessToken($token);

        abort_if(!$challenge, 403, 'Invalid OTP verification token.');

        return view('auth-gateway.otp-verify', [
            'token' => $token,
            'challenge' => $challenge,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'size:64'],
            'code' => ['required', 'digits:6'],
        ]);

        $challenge = $this->otpChallenges->findByAccessToken($data['token']);

        if (!$challenge) {
            return back()->withErrors([
                'code' => 'Invalid OTP verification request.',
            ]);
        }

        $verified = $this->otpChallenges->verify(
            (int) $challenge->id,
            $data['code']
        );

        if (!$verified) {
            return back()
                ->withInput(['token' => $data['token']])
                ->withErrors([
                    'code' => 'The code is invalid, expired, or locked.',
                ]);
        }

        $context = $this->trustedContext->resolve(
            authIdentityId: (int) $verified->auth_identity_id,
            loginAttemptId: (int) $verified->login_attempt_id,
        );

        $this->trustedContext->store(
            $request,
            $context
        );

        $cookieMinutes = max(
            1,
            CarbonImmutable::now('UTC')->diffInMinutes(
                CarbonImmutable::parse(
                    $verified->iam_session_expires_at,
                    'UTC'
                ),
                false
            )
        );

        return redirect(
            rtrim((string) env('BFRN_BASE_URL'), '/')
            . '/bfrn/gateway-login?token='
            . urlencode($verified->handoff_token)
        )->withCookie(cookie(
            name: 'iam_session_token',
            value: $verified->iam_session_token,
            minutes: $cookieMinutes,
            path: '/',
            domain: null,
            secure: $request->isSecure(),
            httpOnly: true,
            raw: false,
            sameSite: 'lax'
        ));
    }
}
