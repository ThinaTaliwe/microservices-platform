<?php

namespace App\Services\Iam;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class IamHandoffClient
{
    public function consume(string $token): ?object
    {
        $response = $this->request(
            'POST',
            '/api/internal/handoff/consume',
            ['token' => $token]
        );

        if (!$response->successful()) {
            return null;
        }

        $payload = $response->json();

        if (!($payload['consumed'] ?? false)) {
            return null;
        }

        return isset($payload['handoff'])
            ? (object) $payload['handoff']
            : null;
    }

    public function validateSession(
        int $sessionId,
        int $bfrnUserId
    ): bool {
        $response = $this->request(
            'POST',
            '/api/internal/session/validate',
            [
                'session_id' => $sessionId,
                'bfrn_user_id' => $bfrnUserId,
            ]
        );

        return $response->successful()
            && (bool) $response->json('valid', false);
    }


    private function request(string $method, string $path, array $payload): Response
    {
        $method = strtoupper($method);
        $path = '/' . ltrim($path, '/');
        $timestamp = (string) time();

        $body = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );

        $canonical = implode("\n", [
            $timestamp,
            $method,
            $path,
            hash('sha256', $body),
        ]);

        $signature = hash_hmac(
            'sha256',
            $canonical,
            (string) env('BFRN_IAM_SHARED_SECRET')
        );

        return Http::timeout(10)
            ->acceptJson()
            ->withHeaders([
                'X-BFRN-Timestamp' => $timestamp,
                'X-BFRN-Signature' => $signature,
            ])
            ->withBody($body, 'application/json')
            ->send($method, $this->url($path));
    }

    private function url(string $path): string
    {
        return rtrim((string) env('IAM_PLATFORM_INTERNAL_URL'), '/')
            . '/'
            . ltrim($path, '/');
    }
}
