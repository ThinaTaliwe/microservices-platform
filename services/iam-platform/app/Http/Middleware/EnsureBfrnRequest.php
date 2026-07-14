<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBfrnRequest
{
    private const MAX_CLOCK_SKEW_SECONDS = 300;

    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) env('BFRN_IAM_SHARED_SECRET');

        if ($secret === '') {
            return response()->json([
                'message' => 'BFRN IAM shared secret is not configured.',
            ], 500);
        }

        $timestamp = (string) $request->header('X-BFRN-Timestamp');
        $signature = (string) $request->header('X-BFRN-Signature');

        if ($timestamp === '' || $signature === '' || !ctype_digit($timestamp)) {
            return response()->json([
                'message' => 'Missing or invalid BFRN request signature.',
            ], 401);
        }

        if (abs(time() - (int) $timestamp) > self::MAX_CLOCK_SKEW_SECONDS) {
            return response()->json([
                'message' => 'BFRN request timestamp has expired.',
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
                'message' => 'Unauthorized BFRN request.',
            ], 401);
        }

        return $next($request);
    }
}
