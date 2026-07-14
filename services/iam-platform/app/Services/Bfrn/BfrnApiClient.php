<?php

namespace App\Services\Bfrn;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class BfrnApiClient
{
    public function findUserByEmail(string $email): ?object
    {
        $response = $this->request(
            'POST',
            '/api/bfrn/internal/auth/user/find',
            ['email' => strtolower(trim($email))],
            10
        );

        if (!$response->successful()) {
            return null;
        }

        $payload = $response->json();

        return ($payload['found'] ?? false)
            ? (object) ($payload['user'] ?? [])
            : null;
    }

    public function listBusinessUnits(): Collection
    {
        $response = $this->request(
            'GET',
            '/api/bfrn/internal/auth/business-units',
            [],
            10
        );

        if (!$response->successful()) {
            return collect();
        }

        return collect($response->json('business_units') ?? [])
            ->map(fn (array $businessUnit) => (object) $businessUnit);
    }

    public function provisionUser(string $email, ?int $businessUnitId = null): ?object
    {
        $response = $this->request(
            'POST',
            '/api/bfrn/internal/auth/user/provision',
            [
                'email' => strtolower(trim($email)),
                'business_unit_id' => $businessUnitId,
            ],
            15
        );

        if (!$response->successful()) {
            return null;
        }

        return (object) $response->json();
    }

    private function request(
        string $method,
        string $path,
        array $payload = [],
        int $timeout = 10
    ): Response {
        $method = strtoupper($method);
        $path = '/' . ltrim($path, '/');
        $timestamp = (string) time();

        $body = $method === 'GET'
            ? ''
            : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $canonical = implode("\n", [
            $timestamp,
            $method,
            $path,
            hash('sha256', $body),
        ]);

        $signature = hash_hmac(
            'sha256',
            $canonical,
            (string) env('IAM_PLATFORM_SHARED_SECRET')
        );

        $request = Http::timeout($timeout)
            ->acceptJson()
            ->withHeaders([
                'X-IAM-Timestamp' => $timestamp,
                'X-IAM-Signature' => $signature,
            ]);

        if ($method === 'GET') {
            return $request->get($this->url($path));
        }

        return $request
            ->withBody($body, 'application/json')
            ->send($method, $this->url($path));
    }

    private function url(string $path): string
    {
        return rtrim((string) env('BFRN_INTERNAL_URL'), '/')
            . '/'
            . ltrim($path, '/');
    }
}
