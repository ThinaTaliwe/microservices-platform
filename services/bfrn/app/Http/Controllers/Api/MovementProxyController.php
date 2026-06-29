<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

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

    private function activeBuId(): int
    {
        $activeBuId = (int) session('active_bu_id');

        if ($activeBuId <= 0) {
            abort(403, 'No active business unit selected.');
        }

        return $activeBuId;
    }

    private function getActiveBuJson(string $path)
    {
        $url = rtrim($this->baseUrl(), '/') . '/' . ltrim($path, '/');

        $res = Http::timeout(15)
            ->acceptJson()
            ->get($url, [
                'bu' => $this->activeBuId(),
                'bu_id' => $this->activeBuId(),
            ]);

        $json = $res->json();

        if (is_array($json)) {
            if (isset($json['results']) && is_array($json['results'])) {
                $json['results'] = collect($json['results'])
                    ->filter(fn ($row) => is_array($row) && (int) ($row['bu'] ?? $row['bu_id'] ?? 0) === $this->activeBuId())
                    ->values()
                    ->all();

                return response()->json($json, $res->status());
            }

            $json = collect($json)
                ->filter(fn ($row) => is_array($row) && (int) ($row['bu'] ?? $row['bu_id'] ?? 0) === $this->activeBuId())
                ->values()
                ->all();

            return response()->json($json, $res->status());
        }

        return response()->json($json, $res->status());
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
        return $this->getActiveBuJson('/api/movement/movements/');
    }

    public function movementItems()
    {
        return $this->getJson('/api/movement/movement-items/');
    }

    public function offloadings()
    {
        return $this->getActiveBuJson('/api/movement/offloadings/');
    }

    public function offloadingItems()
    {
        return $this->getJson('/api/movement/offloading-items/');
    }
}