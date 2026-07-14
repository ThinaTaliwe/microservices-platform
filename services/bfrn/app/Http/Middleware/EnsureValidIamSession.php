<?php

namespace App\Http\Middleware;

use App\Services\Iam\IamHandoffClient;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidIamSession
{
    private const VALIDATION_CACHE_SECONDS = 120;

    public function __construct(
        private readonly IamHandoffClient $iamClient
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $iamSessionId = (int) $request->session()->get(
            'iam_session_id',
            0
        );

        /*
         * Local BFRN password sessions currently have no IAM session.
         * They remain supported until IAM-only authentication is enforced.
         */
        if ($iamSessionId < 1) {
            return $next($request);
        }

        $validatedAt = (int) $request->session()->get(
            'iam_session_validated_at',
            0
        );

        if (
            $validatedAt > 0
            && (time() - $validatedAt) < self::VALIDATION_CACHE_SECONDS
        ) {
            return $next($request);
        }

        try {
            $valid = $this->iamClient->validateSession(
                $iamSessionId,
                (int) Auth::id()
            );
        } catch (ConnectionException $exception) {
            abort(
                503,
                'IAM session validation is temporarily unavailable.'
            );
        } catch (\Throwable $exception) {
            report($exception);

            abort(
                503,
                'IAM session validation could not be completed.'
            );
        }

        if (!$valid) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $loginUrl = rtrim(
                (string) env(
                    'IAM_PLATFORM_PUBLIC_URL',
                    'http://192.168.1.9:8092'
                ),
                '/'
            ) . '/login?reason=session-ended';

            return redirect()->away($loginUrl);
        }

        $request->session()->put(
            'iam_session_validated_at',
            now()->timestamp
        );

        $request->session()->save();

        return $next($request);
    }
}
