<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIamPlatformRequest
{
    private const MAX_CLOCK_SKEW_SECONDS = 300;

    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) env('IAM_PLATFORM_SHARED_SECRET');

        if ($secret === '') {
            return response()->json([
                'message' => 'IAM Platform shared secret is not configured.',
            ], 500);
        }

        $timestamp = (string) $request->header('X-IAM-Timestamp');
        $signature = (string) $request->header('X-IAM-Signature');

        if ($timestamp === '' || $signature === '' || !ctype_digit($timestamp)) {
            return response()->json([
                'message' => 'Missing or invalid IAM request signature.',
            ], 401);
        }

        if (abs(time() - (int) $timestamp) > self::MAX_CLOCK_SKEW_SECONDS) {
            return response()->json([
                'message' => 'IAM request timestamp has expired.',
            ], 401);
        }

        $canonical = implode("\n", [
            $timestamp,
            strtoupper($request->method()),
            '/' . ltrim($request->path(), '/'),
            hash('sha256', $request->getContent()),
        ]);

        $expectedSignature = hash_hmac('sha256', $canonical, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json([
                'message' => 'Unauthorized internal IAM request.',
            ], 401);
        }

        return $next($request);
    }
}
