<?php

namespace App\Http\Controllers\ApiTest;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class VerificationApiTestController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $call = $this->callInternal(
            'POST',
            '/api/internal/verification/challenges',
            [
                'email' => strtolower(trim($data['email'])),
                'device_name' => 'Browser API Test',
                'timezone' => 'Africa/Johannesburg',
                'platform' => 'Browser',
            ]
        );

        $uuid = $call['json']['data']['challenge_id'] ?? null;
        $token = $call['json']['data']['api_token'] ?? null;

        if (is_string($uuid) && is_string($token)) {
            $request->session()->put(
                $this->tokenKey($uuid),
                $token
            );
        }

        return $this->browserResponse(
            $call,
            is_string($uuid)
                && $request->session()->has(
                    $this->tokenKey($uuid)
                )
        );
    }

    public function show(
        Request $request,
        string $challengeUuid
    ): JsonResponse {
        return $this->browserResponse(
            $this->callInternal(
                'GET',
                $this->challengePath($challengeUuid)
            ),
            $request->session()->has(
                $this->tokenKey($challengeUuid)
            )
        );
    }

    public function verifyOtp(
        Request $request,
        string $challengeUuid
    ): JsonResponse {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $call = $this->callInternal(
            'POST',
            $this->challengePath($challengeUuid) . '/verify',
            ['code' => $data['code']]
        );

        if ($call['status'] >= 200 && $call['status'] < 300) {
            $request->session()->forget(
                $this->tokenKey($challengeUuid)
            );
        }

        return $this->browserResponse($call, false);
    }

    public function verifyToken(
        Request $request,
        string $challengeUuid
    ): JsonResponse {
        $token = $request->session()->get(
            $this->tokenKey($challengeUuid)
        );

        if (!is_string($token) || $token === '') {
            return response()->json([
                'http_status' => 409,
                'response' => [
                    'success' => false,
                    'status' => 409,
                    'code' => 'API_TEST_TOKEN_NOT_AVAILABLE',
                    'message' =>
                        'Generate a fresh challenge in this browser.',
                    'errors' => [],
                ],
                'test_state' => [
                    'api_token_available' => false,
                ],
            ], 409);
        }

        $call = $this->callInternal(
            'POST',
            $this->challengePath($challengeUuid) . '/verify',
            ['api_token' => $token]
        );

        if ($call['status'] >= 200 && $call['status'] < 300) {
            $request->session()->forget(
                $this->tokenKey($challengeUuid)
            );
        }

        return $this->browserResponse($call, false);
    }

    public function resend(
        Request $request,
        string $challengeUuid
    ): JsonResponse {
        $call = $this->callInternal(
            'POST',
            $this->challengePath($challengeUuid) . '/resend'
        );

        $newUuid = $call['json']['data']['challenge_id'] ?? null;
        $newToken = $call['json']['data']['api_token'] ?? null;

        $request->session()->forget(
            $this->tokenKey($challengeUuid)
        );

        if (is_string($newUuid) && is_string($newToken)) {
            $request->session()->put(
                $this->tokenKey($newUuid),
                $newToken
            );
        }

        return $this->browserResponse(
            $call,
            is_string($newUuid)
                && $request->session()->has(
                    $this->tokenKey($newUuid)
                )
        );
    }

    public function revoke(
        Request $request,
        string $challengeUuid
    ): JsonResponse {
        $call = $this->callInternal(
            'DELETE',
            $this->challengePath($challengeUuid)
        );

        $request->session()->forget(
            $this->tokenKey($challengeUuid)
        );

        return $this->browserResponse($call, false);
    }

    private function callInternal(
        string $method,
        string $path,
        ?array $payload = null
    ): array {
        try {
            $method = strtoupper($method);

            $body = $payload === null
                ? ''
                : json_encode(
                    $payload,
                    JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
                );

            $timestamp = (string) time();
            $secret = trim((string) config(
                'services.iam_internal.shared_secret'
            ));

            if ($secret === '') {
                throw new \RuntimeException(
                    'IAM internal shared secret is not configured.'
                );
            }

            $canonical = implode("\n", [
                $timestamp,
                $method,
                $path,
                hash('sha256', $body),
            ]);

            $signature = hash_hmac(
                'sha256',
                $canonical,
                $secret
            );

            $internalRequest = Request::create(
                $path,
                $method,
                [],
                [],
                [],
                [
                    'HTTP_ACCEPT' => 'application/json',
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_X_IAM_TIMESTAMP' => $timestamp,
                    'HTTP_X_IAM_SIGNATURE' => $signature,
                ],
                $body
            );

            $response = app('router')->dispatch(
                $internalRequest
            );

            $json = json_decode(
                (string) $response->getContent(),
                true
            );

            return [
                'method' => $method,
                'path' => $path,
                'status' => $response->getStatusCode(),
                'json' => is_array($json)
                    ? $json
                    : ['raw_response' => $response->getContent()],
            ];
        } catch (Throwable $exception) {
            return [
                'method' => strtoupper($method),
                'path' => $path,
                'status' => 500,
                'json' => [
                    'success' => false,
                    'status' => 500,
                    'code' => 'API_TEST_REQUEST_FAILED',
                    'message' => $exception->getMessage(),
                    'errors' => [],
                ],
            ];
        }
    }

    private function browserResponse(
        array $call,
        bool $tokenAvailable
    ): JsonResponse {
        return response()
            ->json([
                'request' => [
                    'method' => $call['method'],
                    'url' => request()->getSchemeAndHttpHost()
                        . $call['path'],
                ],
                'http_status' => $call['status'],
                'response' => $this->sanitize(
                    $call['json']
                ),
                'test_state' => [
                    'api_token_available' =>
                        $tokenAvailable,
                ],
            ], $call['status'])
            ->header('Cache-Control', 'no-store');
    }

    private function sanitize(
        mixed $value,
        string $key = ''
    ): mixed {
        if (
            in_array($key, [
                'api_token',
                'token',
                'handoff_token',
            ], true)
        ) {
            return empty($value) ? null : '[present]';
        }

        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $childKey => $childValue) {
            $value[$childKey] = $this->sanitize(
                $childValue,
                (string) $childKey
            );
        }

        return $value;
    }

    private function challengePath(string $uuid): string
    {
        return '/api/internal/verification/challenges/'
            . $uuid;
    }

    private function tokenKey(string $uuid): string
    {
        return 'api_test.verification_tokens.' . $uuid;
    }
}
