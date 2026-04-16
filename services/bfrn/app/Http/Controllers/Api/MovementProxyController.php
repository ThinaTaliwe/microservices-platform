<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class MovementProxyController extends Controller
{
    private function baseUrl(): string
    {
        $base = rtrim(config('services.siya.base_url', env('SIYA_API_BASE', 'http://siya-app:8000')), '/');

        // We’ll support both styles:
        // - http://siya-app:8000/api/movement/
        // - http://siya-app:8000/siya/api/movement/
        // by allowing an optional prefix env.
        $prefix = trim(config('services.siya.prefix', env('SIYA_API_PREFIX', '')), '/');

        return $prefix ? "{$base}/{$prefix}" : $base;
    }

    private function getJson(string $path)
    {
        $url = rtrim($this->baseUrl(), '/') . '/' . ltrim($path, '/');

        $res = Http::timeout(15)
            ->acceptJson()
            ->get($url);

        return response()->json($res->json(), $res->status());
    }

    // GET /bfrn/api/movement
    public function index()
    {
        // directory json
        return $this->getJson('/api/movement/');
    }

    // GET /bfrn/api/movement/movements
    public function movements()
    {
        return $this->getJson('/api/movement/movements/');
    }

    public function movementItems()
    {
        return $this->getJson('/api/movement/movement-items/');
    }

    public function offloadings()
    {
        return $this->getJson('/api/movement/offloadings/');
    }

    public function offloadingItems()
    {
        return $this->getJson('/api/movement/offloading-items/');
    }
}