<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SiyaProxyController extends Controller
{
    private function baseUrl(): string
    {
        $base = rtrim(config('services.siya.base_url', env('SIYA_API_BASE', 'http://siya-app:8000')), '/');
        $prefix = trim(config('services.siya.prefix', env('SIYA_API_PREFIX', '')), '/');
        return $prefix ? "{$base}/{$prefix}" : $base;
    }

    private function client(Request $request)
    {
        $http = Http::timeout(20)->acceptJson();

        // Forward auth if you later add a token
        $token = config('services.siya.token');
        if ($token) {
            $http = $http->withToken($token);
        }

        // Forward some headers (optional)
        $forward = [];
        if ($request->headers->get('X-Request-Id')) {
            $forward['X-Request-Id'] = $request->headers->get('X-Request-Id');
            $http = $http->withHeaders($forward);
        }

        return $http;
    }

    private function url(string $path): string
    {
        return rtrim($this->baseUrl(), '/') . '/' . ltrim($path, '/');
    }

    private function passthrough($res)
    {
        // Return JSON if possible; otherwise return raw
        $body = $res->body();
        $json = null;

        try { $json = $res->json(); } catch (\Throwable $e) {}

        return response()->json(
            $json ?? ['raw' => $body],
            $res->status()
        );
    }

    // -------- Shipments (CRUD) --------
    public function shipmentsIndex(Request $request)
    {
        // Siya endpoint example: /api/shipments/shipments/
        $url = $this->url('/api/shipments/shipments/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function shipmentsShow(Request $request, $id)
    {
        $url = $this->url("/api/shipments/shipments/{$id}/");
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function shipmentsStore(Request $request)
    {
        $url = $this->url('/api/shipments/shipments/');
        $res = $this->client($request)->post($url, $request->all());
        return $this->passthrough($res);
    }

    public function shipmentsUpdate(Request $request, $id)
    {
        $url = $this->url("/api/shipments/shipments/{$id}/");
        $res = $this->client($request)->put($url, $request->all());
        return $this->passthrough($res);
    }

    public function shipmentsDestroy(Request $request, $id)
    {
        $url = $this->url("/api/shipments/shipments/{$id}/");
        $res = $this->client($request)->delete($url);
        return $this->passthrough($res);
    }

    // -------- Operations (list endpoints) --------
    public function loadings(Request $request)
    {
        $url = $this->url('/api/loading/loadings/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function loadingItems(Request $request)
    {
        $url = $this->url('/api/loading/loading-items/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function movements(Request $request)
    {
        $url = $this->url('/api/movement/movements/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function movementItems(Request $request)
    {
        $url = $this->url('/api/movement/movement-items/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function offloadings(Request $request)
    {
        $url = $this->url('/api/movement/offloadings/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function offloadingItems(Request $request)
    {
        $url = $this->url('/api/movement/offloading-items/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function storage(Request $request)
    {
        $url = $this->url('/api/storage/storage/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function storageItems(Request $request)
    {
        $url = $this->url('/api/storage/storage-items/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }
}
