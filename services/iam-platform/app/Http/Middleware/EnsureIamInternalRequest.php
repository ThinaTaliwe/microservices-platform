<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureIamInternalRequest
{
    private const MAX_CLOCK_SKEW_SECONDS = 300;

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $secret = trim(
            (string) config(
                'services.iam_internal.shared_secret'
            )
        );

        if ($secret === '') {
            return $this->failure(
                500,
                'IAM_INTERNAL_SECRET_NOT_CONFIGURED',
                'IAM internal API authentication is not configured.'
            );
        }

        $timestamp = trim(
            (string) $request->header('X-IAM-Timestamp')
        );

        $signature = strtolower(trim(
            (string) $request->header('X-IAM-Signature')
        ));

        if (
            $timestamp === ''
            || !ctype_digit($timestamp)
            || !preg_match('/^[a-f0-9]{64}$/', $signature)
        ) {
            return $this->failure(
                401,
                'IAM_SIGNATURE_REQUIRED',
                'A valid IAM request signature is required.'
            );
        }

        if (
            abs(time() - (int) $timestamp)
            > self::MAX_CLOCK_SKEW_SECONDS
        ) {
            return $this->failure(
                401,
                'IAM_SIGNATURE_EXPIRED',
                'The IAM request timestamp has expired.'
            );
        }

        $canonical = implode("\n", [
            $timestamp,
            strtoupper($request->method()),
            '/' . ltrim($request->path(), '/'),
            hash('sha256', $request->getContent()),
        ]);

        $expectedSignature = hash_hmac(
            'sha256',
            $canonical,
            $secret
        );

        if (!hash_equals($expectedSignature, $signature)) {
            return $this->failure(
                401,
                'IAM_SIGNATURE_INVALID',
                'The IAM request signature is invalid.'
            );
        }

        return $next($request);
    }

    private function failure(
        int $status,
        string $code,
        string $message
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'errors' => [],
        ], $status)->withHeaders([
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
        ]);
    }
}
