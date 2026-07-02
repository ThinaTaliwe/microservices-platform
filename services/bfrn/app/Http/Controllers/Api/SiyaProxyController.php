<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\BfrnAudit;
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

    private function activeBuId(): int
    {
        $activeBuId = (int) session('active_bu_id');

        if ($activeBuId <= 0) {
            abort(403, 'No active business unit selected.');
        }

        return $activeBuId;
    }

    private function addressBelongsToActiveBu(array $address): bool
    {
        return (int) ($address['bu'] ?? $address['bu_id'] ?? 0) === $this->activeBuId();
    }

    private function filteredActiveBuResponse($res)
    {
        $json = $res->json();

        if (!is_array($json)) {
            return $this->passthrough($res);
        }

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

    private function assertShipmentBelongsToActiveBu(Request $request, int $shipmentId)
    {
        $shipmentUrl = $this->url("/api/shipments/shipments/{$shipmentId}/");
        $shipmentRes = $this->client($request)->get($shipmentUrl);

        if (!$shipmentRes->successful()) {
            return response()->json(['message' => 'Shipment not found.'], $shipmentRes->status());
        }

        $shipment = $shipmentRes->json() ?? [];

        if ((int) ($shipment['bu'] ?? 0) !== $this->activeBuId()) {
            return response()->json(['message' => 'You do not have access to this shipment for the selected business unit.'], 403);
        }

        return null;
    }

    private function filteredShipmentRelationshipResponse(Request $request, $res)
    {
        $json = $res->json();

        if (!is_array($json)) {
            return $this->passthrough($res);
        }

        $filter = function ($row) use ($request) {
            if (!is_array($row)) {
                return false;
            }

            foreach (['parent_shipment', 'child_shipment', 'shipment', 'previous_shipment'] as $field) {
                if (!empty($row[$field])) {
                    if ($this->assertShipmentBelongsToActiveBu($request, (int) $row[$field]) === null) {
                        return true;
                    }
                }
            }

            return false;
        };

        if (isset($json['results']) && is_array($json['results'])) {
            $json['results'] = collect($json['results'])->filter($filter)->values()->all();
            return response()->json($json, $res->status());
        }

        $json = collect($json)->filter($filter)->values()->all();
        return response()->json($json, $res->status());
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
    public function buIndex(Request $request)
    {
        $url = $this->url('/api/bu/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    private function normalizeAddressCoordinates(array $payload): array
    {
        foreach (['latitude', 'longitude'] as $field) {
            if (!array_key_exists($field, $payload)) {
                $payload[$field] = null;
                continue;
            }

            $value = trim((string) $payload[$field]);

            $payload[$field] = $value === '' ? null : $value;
        }

        return $payload;
    }

    public function addressesIndex(Request $request)
    {
        $url = $this->url('/api/addresses/');
        $query = array_merge($request->query(), [
            'bu' => $this->activeBuId(),
            'bu_id' => $this->activeBuId(),
        ]);

        $res = $this->client($request)->get($url, $query);

        $json = $res->json();

        if (is_array($json)) {
            if (isset($json['results']) && is_array($json['results'])) {
                $json['results'] = collect($json['results'])
                    ->filter(fn ($address) => $this->addressBelongsToActiveBu($address))
                    ->values()
                    ->all();

                return response()->json($json, $res->status());
            }

            $json = collect($json)
                ->filter(fn ($address) => is_array($address) && $this->addressBelongsToActiveBu($address))
                ->values()
                ->all();

            return response()->json($json, $res->status());
        }

        return $this->passthrough($res);
    }

    public function addressesStore(Request $request)
    {
        $url = $this->url('/api/addresses/');

        $payload = $this->normalizeAddressCoordinates($request->all());
        $timestamp = now()->toIso8601String();

        $payload['bu'] = $this->activeBuId();
        $payload['bu_id'] = $this->activeBuId();
        $payload['adress_type'] = $payload['adress_type'] ?? 1;
        $payload['created_at'] = $payload['created_at'] ?? $timestamp;
        $payload['updated_at'] = $payload['updated_at'] ?? $timestamp;

        $res = $this->client($request)->post($url, $payload);

        if ($res->successful()) {
            $body = $res->json();

            BfrnAudit::log(
                'address_created',
                'bfrn_address',
                (int) ($body['id'] ?? 0),
                [],
                [
                    'input' => $payload,
                    'result' => $body,
                    'active_bu_id' => session('active_bu_id'),
                ],
                ['bfrn', 'operations', 'addresses']
            );
        }

        return $this->passthrough($res);
    }


    public function addressesShow(Request $request, $id)
    {
        $url = $this->url("/api/addresses/{$id}/");
        $res = $this->client($request)->get($url, $request->query());

        if ($res->successful() && !$this->addressBelongsToActiveBu($res->json() ?? [])) {
            return response()->json(['message' => 'You do not have access to this address for the selected business unit.'], 403);
        }

        return $this->passthrough($res);
    }

    public function addressesUpdate(Request $request, $id)
    {
        $url = $this->url("/api/addresses/{$id}/");

        $payload = $this->normalizeAddressCoordinates($request->all());

        $payload['adress_type'] = $payload['adress_type']
            ?? $payload['address_type']
            ?? 1;

        unset($payload['address_type']);

        $existing = $this->client($request)->get($url, $request->query());

        if ($existing->successful() && !$this->addressBelongsToActiveBu($existing->json() ?? [])) {
            return response()->json(['message' => 'You do not have access to this address for the selected business unit.'], 403);
        }

        $payload['bu'] = $this->activeBuId();
        $payload['bu_id'] = $this->activeBuId();
        $payload['updated_at'] = $payload['updated_at'] ?? now()->toIso8601String();

        $res = $this->client($request)->put($url, $payload);

        if ($res->successful()) {
            BfrnAudit::log(
                'address_updated',
                'bfrn_address',
                (int) $id,
                [],
                [
                    'input' => $payload,
                    'result' => $res->json(),
                    'active_bu_id' => session('active_bu_id'),
                ],
                ['bfrn', 'operations', 'addresses']
            );
        }

        return $this->passthrough($res);
    }

    public function addressTypesIndex(Request $request)
    {
        $url = $this->url('/api/addresses/address-types/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function shipmentTypesIndex(Request $request)
    {
        $url = $this->url('/api/shipments/shipment-types/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function shipmentInstructionsStore(Request $request)
    {
        $url = $this->url('/api/shipments/shipment-instructions/');
        $res = $this->client($request)->post($url, $request->all());
        return $this->passthrough($res);
    }

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
        $query = array_merge($request->query(), [
            'bu' => $this->activeBuId(),
            'bu_id' => $this->activeBuId(),
        ]);

        $res = $this->client($request)->get($url, $query);
        return $this->filteredActiveBuResponse($res);
    }

    public function loadingItems(Request $request)
    {
        $url = $this->url('/api/loading/loading-items/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->filteredParentItemResponse($request, $res, 'loading', '/api/loading/loadings/');
    }

    public function movements(Request $request)
    {
        $url = $this->url('/api/movement/movements/');
        $query = array_merge($request->query(), [
            'bu' => $this->activeBuId(),
            'bu_id' => $this->activeBuId(),
        ]);

        $res = $this->client($request)->get($url, $query);
        return $this->filteredActiveBuResponse($res);
    }

    public function movementItems(Request $request)
    {
        $url = $this->url('/api/movement/movement-items/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->filteredParentItemResponse($request, $res, 'movement', '/api/movement/movements/');
    }

    public function offloadings(Request $request)
    {
        $url = $this->url('/api/movement/offloadings/');
        $query = array_merge($request->query(), [
            'bu' => $this->activeBuId(),
            'bu_id' => $this->activeBuId(),
        ]);

        $res = $this->client($request)->get($url, $query);
        return $this->filteredActiveBuResponse($res);
    }

    public function offloadingItems(Request $request)
    {
        $url = $this->url('/api/movement/offloading-items/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->filteredParentItemResponse($request, $res, 'offloading', '/api/movement/offloadings/');
    }

    public function storage(Request $request)
    {
        $url = $this->url('/api/storage/storage/');
        $query = array_merge($request->query(), [
            'bu' => $this->activeBuId(),
            'bu_id' => $this->activeBuId(),
        ]);

        $res = $this->client($request)->get($url, $query);
        return $this->filteredActiveBuResponse($res);
    }

    public function storageItems(Request $request)
    {
        $url = $this->url('/api/storage/storage-items/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->filteredParentItemResponse($request, $res, 'storage', '/api/storage/storage/');
    }

    private function parentRecordBelongsToActiveBu(Request $request, string $parentEndpoint, int $parentId): bool
    {
        $url = $this->url(rtrim($parentEndpoint, '/') . "/{$parentId}/");
        $res = $this->client($request)->get($url);

        if (!$res->successful()) {
            return false;
        }

        $parent = $res->json() ?? [];

        return (int) ($parent['bu'] ?? $parent['bu_id'] ?? 0) === $this->activeBuId();
    }

    private function filteredParentItemResponse(Request $request, $res, string $parentField, string $parentEndpoint)
    {
        $json = $res->json();

        if (!is_array($json)) {
            return $this->passthrough($res);
        }

        $filter = function ($row) use ($request, $parentField, $parentEndpoint) {
            return is_array($row)
                && !empty($row[$parentField])
                && $this->parentRecordBelongsToActiveBu($request, $parentEndpoint, (int) $row[$parentField]);
        };

        if (isset($json['results']) && is_array($json['results'])) {
            $json['results'] = collect($json['results'])->filter($filter)->values()->all();
            return response()->json($json, $res->status());
        }

        return response()->json(collect($json)->filter($filter)->values()->all(), $res->status());
    }

    public function modesOfTransport(Request $request)
    {
        $url = $this->url('/api/lookups/modes-of-transport/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function items(Request $request)
    {
        $url = $this->url('/api/lookups/items/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function shipmentDocumentsIndex(Request $request, $shipmentId)
    {
        if ($blocked = $this->assertShipmentBelongsToActiveBu($request, (int) $shipmentId)) {
            return $blocked;
        }

        $url = $this->url("/api/shipments/shipments/{$shipmentId}/documents/");
        $res = $this->client($request)->get($url, $request->query());
        return $this->passthrough($res);
    }

    public function shipmentDocumentsStore(Request $request, $shipmentId)
    {
        if ($blocked = $this->assertShipmentBelongsToActiveBu($request, (int) $shipmentId)) {
            return $blocked;
        }

        $url = $this->url("/api/shipments/shipments/{$shipmentId}/documents/");

        $http = $this->client($request);

        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded.'], 422);
        }

        $file = $request->file('file');

        $res = $http->attach(
            'file',
            file_get_contents($file->getRealPath()),
            $file->getClientOriginalName()
        )->post($url);

        if ($res->successful()) {
            BfrnAudit::log(
                'document_uploaded',
                'bfrn_shipment_document',
                (int) $shipmentId,
                [],
                [
                    'shipment_id' => (int) $shipmentId,
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'result' => $res->json(),
                    'active_bu_id' => session('active_bu_id'),
                ],
                ['bfrn', 'operations', 'documents']
            );
        }

        return $this->passthrough($res);
    }

    public function shipmentDocumentsDestroy(Request $request, $shipmentId, $filename)
    {
        if ($blocked = $this->assertShipmentBelongsToActiveBu($request, (int) $shipmentId)) {
            return $blocked;
        }

        $encodedFilename = implode('/', array_map('rawurlencode', explode('/', $filename)));

        $url = $this->url("/api/shipments/shipments/{$shipmentId}/documents/{$encodedFilename}");

        $res = $this->client($request)->delete($url);

        if ($res->successful()) {
            BfrnAudit::log(
                'document_deleted',
                'bfrn_shipment_document',
                (int) $shipmentId,
                [],
                [
                    'shipment_id' => (int) $shipmentId,
                    'filename' => $filename,
                    'active_bu_id' => session('active_bu_id'),
                ],
                ['bfrn', 'operations', 'documents']
            );
        }

        return $this->passthrough($res);
    }

    public function shipmentRelationships(Request $request)
    {
        $url = $this->url('/api/shipments/shipment-has-shipment/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->filteredShipmentRelationshipResponse($request, $res);
    }

    public function previousShipmentRelationships(Request $request)
    {
        $url = $this->url('/api/shipments/shipment-has-previous-shipments/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->filteredShipmentRelationshipResponse($request, $res);
    }

    public function shipmentInstructionShow(Request $request, $id)
    {
        $url = $this->url("/api/shipments/shipment-instructions/{$id}/");
        $res = $this->client($request)->get($url, $request->query());

        if ($res->successful()) {
            $instruction = $res->json() ?? [];
            $instructionBu = (int) ($instruction['bu'] ?? $instruction['bu_id'] ?? 0);

            if ($instructionBu !== $this->activeBuId()) {
                return response()->json(['message' => 'You do not have access to this shipment instruction for the selected business unit.'], 403);
            }
        }

        return $this->passthrough($res);
    }

    public function shipmentRelationshipsIndex(Request $request)
    {
        $url = $this->url('/api/shipments/shipment-has-shipment/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->filteredShipmentRelationshipResponse($request, $res);
    }

    public function shipmentDocumentsDestroyByQuery(Request $request, $shipmentId)
    {
        if ($blocked = $this->assertShipmentBelongsToActiveBu($request, (int) $shipmentId)) {
            return $blocked;
        }

        $filename = $request->query('filename');

        if (!$filename) {
            return response()->json(['message' => 'Filename is required.'], 422);
        }

        $encodedFilename = implode('/', array_map('rawurlencode', explode('/', $filename)));

        $url = $this->url("/api/shipments/shipments/{$shipmentId}/documents/{$encodedFilename}");

        $res = $this->client($request)->delete($url);

        if ($res->successful()) {
            BfrnAudit::log(
                'document_deleted',
                'bfrn_shipment_document',
                (int) $shipmentId,
                [],
                [
                    'shipment_id' => (int) $shipmentId,
                    'filename' => $filename,
                    'active_bu_id' => session('active_bu_id'),
                ],
                ['bfrn', 'operations', 'documents']
            );
        }

        return $this->passthrough($res);
    }

    public function shipmentRelationshipDestroy(Request $request, $id)
    {
        $showUrl = $this->url("/api/shipments/shipment-has-shipment/{$id}/");
        $relationshipRes = $this->client($request)->get($showUrl);

        if ($relationshipRes->successful()) {
            $relationship = $relationshipRes->json() ?? [];

            foreach (['parent_shipment', 'child_shipment'] as $field) {
                if (!empty($relationship[$field]) && $this->assertShipmentBelongsToActiveBu($request, (int) $relationship[$field]) === null) {
                    $url = $this->url("/api/shipments/shipment-has-shipment/{$id}/");
                    $res = $this->client($request)->delete($url);
                    return $this->passthrough($res);
                }
            }

            return response()->json(['message' => 'You do not have access to this shipment relationship for the selected business unit.'], 403);
        }

        return $this->passthrough($relationshipRes);
    }

    public function shipmentItemsIndex(Request $request)
    {
        $url = $this->url('/api/shipments/shipment-items/');
        $res = $this->client($request)->get($url, $request->query());
        return $this->filteredParentItemResponse($request, $res, 'shipment', '/api/shipments/shipments/');
    }
}
