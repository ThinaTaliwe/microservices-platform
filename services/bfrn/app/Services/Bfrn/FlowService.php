<?php

namespace App\Services\Bfrn;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class FlowService
{
    private function baseUrl(): string
    {
        $base = rtrim(config('services.siya.base_url', env('SIYA_API_BASE', 'http://siya-app:8000')), '/');
        $prefix = trim(config('services.siya.prefix', env('SIYA_API_PREFIX', '')), '/');

        return $prefix ? "{$base}/{$prefix}" : $base;
    }

    private function apiUrl(string $path): string
    {
        return rtrim($this->baseUrl(), '/') . '/' . ltrim($path, '/');
    }


    private function activeBuId(): int
    {
        $activeBuId = (int) session('active_bu_id');

        if ($activeBuId <= 0) {
            throw new RuntimeException('No active business unit selected.');
        }

        return $activeBuId;
    }

    private function assertShipmentBelongsToActiveBu(array $shipment): void
    {
        if ((int) ($shipment['bu'] ?? 0) !== $this->activeBuId()) {
            throw new RuntimeException('You do not have access to this shipment for the selected business unit.');
        }
    }


    private function get(string $path): array
    {
        $response = Http::timeout(30)
            ->acceptJson()
            ->get($this->apiUrl($path));

        if (!$response->successful()) {
            throw new RuntimeException(
                'Siya API failed on GET ' . $path . ': ' . $response->body()
            );
        }

        return $response->json();
    }

    private function post(string $path, array $payload): array
    {
        $response = Http::timeout(30)
            ->acceptJson()
            ->asJson()
            ->post($this->apiUrl($path), $payload);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Siya API failed on POST ' . $path . ': ' . $response->body()
            );
        }

        return $response->json();
    }


    private function patch(string $path, array $payload): array
    {
        $response = Http::timeout(30)
            ->acceptJson()
            ->asJson()
            ->patch($this->apiUrl($path), $payload);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Siya API failed on PATCH ' . $path . ': ' . $response->body()
            );
        }

        return $response->json();
    }


    private function delete(string $path): void
    {
        $response = Http::timeout(30)
            ->acceptJson()
            ->delete($this->apiUrl($path));

        if (!$response->successful() && $response->status() !== 204) {
            throw new RuntimeException(
                'Siya API failed on DELETE ' . $path . ': ' . $response->body()
            );
        }
    }

    public function createCompleteFlow(array $data): array
    {
        $buId = $this->activeBuId();
        $shipmentTypeId = (int) $data['shipment_type_id'];
        $modeOfTransportId = (int) $data['mode_of_transport_id'];
        $items = $data['items'] ?? [
            [
                'item_id' => $data['item_id'] ?? 1,
                'quantity' => $data['quantity'] ?? '1.000000',
            ],
        ];

        $items = array_values(array_map(
            static fn (array $item): array => [
                'item' => (int) $item['item_id'],
                'quantity' => $item['quantity'],
            ],
            $items
        ));

        $name = trim($data['name']);
        $description = trim($data['description'] ?? '');

        $reference = 'UI-FLOW-' . now()->format('YmdHis');
        $timestamp = now()->toIso8601String();

        $instructionPayload = [
            'bu' => $buId,
            'instruction_type' => 1,
            'instruction_reference' => $reference . '-INSTRUCTION',
            'instruction_detail' => $description ?: 'Created from BFRN Operations UI.',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];

        if (!empty($data['from_address_id'])) {
            $instructionPayload['from_address'] = (int) $data['from_address_id'];
        }

        if (!empty($data['to_address_id'])) {
            $instructionPayload['to_address'] = (int) $data['to_address_id'];
        }

        $instruction = $this->post('/api/shipments/shipment-instructions/', $instructionPayload);

        $instructionItems = [];

        foreach ($items as $item) {
            $instructionItems[] = $this->post('/api/shipments/shipment-instruction-items/', [
                'shipment_instruction' => $instruction['id'],
                'item' => $item['item'],
                'quantity' => $item['quantity'],
            ]);
        }

        $loading = $this->post('/api/loading/loadings/', [
            'bu' => $buId,
            'parent_loading' => null,
            'loading_reference' => $reference . '-LOADING',
            'loading_start_time' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'items' => $items,
        ]);

        $movement = $this->post('/api/movement/movements/', [
            'bu' => $buId,
            'parent_movement' => null,
            'movement_reference' => $reference . '-MOVEMENT',
            'movement_start_time' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $movementItems = [];

        foreach ($items as $item) {
            $movementItems[] = $this->post('/api/movement/movement-items/', [
                'movement' => $movement['id'],
                'item' => $item['item'],
                'quantity' => $item['quantity'],
            ]);
        }

        $offloading = $this->post('/api/movement/offloadings/', [
            'bu' => $buId,
            'parent_offloading' => null,
            'offloading_reference' => $reference . '-OFFLOADING',
            'offloading_start_time' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $offloadingItems = [];

        foreach ($items as $item) {
            $offloadingItems[] = $this->post('/api/movement/offloading-items/', [
                'offloading' => $offloading['id'],
                'item' => $item['item'],
                'quantity' => $item['quantity'],
            ]);
        }

        $storage = $this->post('/api/storage/storage/', [
            'bu' => $buId,
            'parent_storage' => null,
            'storage_start_time' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'storage_end_time' => null,
            'storage_refence' => $reference . '-STORAGE',
        ]);

        $storageItems = [];

        foreach ($items as $item) {
            $storageItems[] = $this->post('/api/storage/storage-items/', [
                'storage' => $storage['id'],
                'item' => $item['item'],
                'quantity' => $item['quantity'],
            ]);
        }

        $shipment = $this->post('/api/shipments/shipments/', [
            'shipment_type' => $shipmentTypeId,
            'mode_of_transport' => $modeOfTransportId,
            'bu' => $buId,
            'shipment_instruction' => $instruction['id'],
            'name' => $name,
            'description' => $description,
            'loading' => $loading['id'],
            'movement' => $movement['id'],
            'offloading' => $offloading['id'],
            'storage' => $storage['id'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $shipmentItems = [];

        foreach ($items as $item) {
            $shipmentItems[] = $this->post('/api/shipments/shipment-items/', [
                'shipment' => $shipment['id'],
                'item' => $item['item'],
                'quantity' => $item['quantity'],
            ]);
        }

        return [
            'reference' => $reference,
            'instruction' => $instruction,
            'loading' => $loading,
            'movement' => $movement,
            'offloading' => $offloading,
            'storage' => $storage,
            'shipment' => $shipment,
            'instruction_items' => $instructionItems,
            'movement_items' => $movementItems,
            'offloading_items' => $offloadingItems,
            'storage_items' => $storageItems,
            'shipment_items' => $shipmentItems,
            'shipment_item' => $shipmentItems[0] ?? null,
        ];
    }


    /**
     * Synchronize one operational item collection using a minimal diff.
     *
     * Existing records are preserved where possible. Only changed quantities
     * are patched, new items are created, and removed items are deleted.
     */
    private function synchronizeOperationalItems(
        string $endpoint,
        string $parentField,
        int $parentId,
        array $submittedItems
    ): array {
        if ($parentId <= 0) {
            return [];
        }

        $payload = $this->get($endpoint);
        $existingRows = $payload['results'] ?? $payload ?? [];

        $existingByItemId = [];

        foreach ($existingRows as $existingRow) {
            if (
                !is_array($existingRow)
                || (int) ($existingRow[$parentField] ?? 0) !== $parentId
                || empty($existingRow['id'])
                || empty($existingRow['item'])
            ) {
                continue;
            }

            $itemId = (int) $existingRow['item'];

            /*
             * Duplicate rows should not normally exist. When they do, retain
             * the first record as canonical and delete the duplicates.
             */
            if (isset($existingByItemId[$itemId])) {
                $this->delete(
                    rtrim($endpoint, '/') . '/' . (int) $existingRow['id'] . '/'
                );

                continue;
            }

            $existingByItemId[$itemId] = $existingRow;
        }

        $synchronizedRows = [];
        $submittedItemIds = [];

        foreach ($submittedItems as $submittedItem) {
            $itemId = (int) $submittedItem['item'];
            $quantity = $submittedItem['quantity'];

            $submittedItemIds[$itemId] = true;

            if (!isset($existingByItemId[$itemId])) {
                $synchronizedRows[] = $this->post($endpoint, [
                    $parentField => $parentId,
                    'item' => $itemId,
                    'quantity' => $quantity,
                ]);

                continue;
            }

            $existingRow = $existingByItemId[$itemId];

            $existingQuantity = number_format(
                (float) ($existingRow['quantity'] ?? 0),
                6,
                '.',
                ''
            );

            $submittedQuantity = number_format(
                (float) $quantity,
                6,
                '.',
                ''
            );

            if ($existingQuantity !== $submittedQuantity) {
                $existingRow = $this->patch(
                    rtrim($endpoint, '/') . '/' . (int) $existingRow['id'] . '/',
                    [
                        $parentField => $parentId,
                        'item' => $itemId,
                        'quantity' => $quantity,
                    ]
                );
            }

            $synchronizedRows[] = $existingRow;
        }

        foreach ($existingByItemId as $itemId => $existingRow) {
            if (isset($submittedItemIds[$itemId])) {
                continue;
            }

            $this->delete(
                rtrim($endpoint, '/') . '/' . (int) $existingRow['id'] . '/'
            );
        }

        return $synchronizedRows;
    }

    /**
     * Keep shipment, instruction, loading, movement, offloading and storage
     * item collections synchronized.
     */
    private function synchronizeShipmentCargo(array $shipment, array $submittedItems): array
    {
        $items = array_values(array_map(
            static fn (array $item): array => [
                'item' => (int) $item['item_id'],
                'quantity' => $item['quantity'],
            ],
            $submittedItems
        ));

        $collections = [
            'instruction_items' => $this->synchronizeOperationalItems(
                '/api/shipments/shipment-instruction-items/',
                'shipment_instruction',
                (int) ($shipment['shipment_instruction'] ?? 0),
                $items
            ),
            'loading_items' => $this->synchronizeOperationalItems(
                '/api/loading/loading-items/',
                'loading',
                (int) ($shipment['loading'] ?? 0),
                $items
            ),
            'movement_items' => $this->synchronizeOperationalItems(
                '/api/movement/movement-items/',
                'movement',
                (int) ($shipment['movement'] ?? 0),
                $items
            ),
            'offloading_items' => $this->synchronizeOperationalItems(
                '/api/movement/offloading-items/',
                'offloading',
                (int) ($shipment['offloading'] ?? 0),
                $items
            ),
            'storage_items' => $this->synchronizeOperationalItems(
                '/api/storage/storage-items/',
                'storage',
                (int) ($shipment['storage'] ?? 0),
                $items
            ),
            'shipment_items' => $this->synchronizeOperationalItems(
                '/api/shipments/shipment-items/',
                'shipment',
                (int) ($shipment['id'] ?? 0),
                $items
            ),
        ];

        return $collections;
    }

    public function updateShipment(int $shipmentId, array $data): array
    {
        $existingShipment = $this->get("/api/shipments/shipments/{$shipmentId}/");
        $this->assertShipmentBelongsToActiveBu($existingShipment);

        $payload = [
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'bu' => $this->activeBuId(),
            'shipment_type' => (int) $data['shipment_type_id'],
            'mode_of_transport' => (int) $data['mode_of_transport_id'],
            'updated_at' => now()->toIso8601String(),
        ];

        $shipment = $this->patch("/api/shipments/shipments/{$shipmentId}/", $payload);

        $instructionId = (int) ($shipment['shipment_instruction'] ?? 0);

        if ($instructionId > 0) {
            $instructionPayload = [
                'updated_at' => now()->toIso8601String(),
            ];

            if (!empty($data['from_address_id'])) {
                $instructionPayload['from_address'] = (int) $data['from_address_id'];
            }

            if (!empty($data['to_address_id'])) {
                $instructionPayload['to_address'] = (int) $data['to_address_id'];
            }

            if (count($instructionPayload) > 1) {
                $this->patch("/api/shipments/shipment-instructions/{$instructionId}/", $instructionPayload);
            }
        }

        $cargoCollections = $this->synchronizeShipmentCargo(
            $shipment,
            $data['items']
        );

        $shipment['shipment_items'] = $cargoCollections['shipment_items'];
        $shipment['cargo_collections'] = $cargoCollections;

        return $shipment;
    }

    public function deleteShipment(int $shipmentId): void
    {
        $shipment = $this->get("/api/shipments/shipments/{$shipmentId}/");
        $this->assertShipmentBelongsToActiveBu($shipment);

        $itemsPayload = $this->get('/api/shipments/shipment-items/');
        $items = $itemsPayload['results'] ?? $itemsPayload ?? [];

        foreach ($items as $item) {
            if ((int) ($item['shipment'] ?? 0) === $shipmentId && !empty($item['id'])) {
                $this->delete("/api/shipments/shipment-items/{$item['id']}/");
            }
        }

        $this->delete("/api/shipments/shipments/{$shipmentId}/");
    }

    public function createChildFlow(int $parentShipmentId, array $data): array
    {
        $parent = $this->get("/api/shipments/shipments/{$parentShipmentId}/");
        $this->assertShipmentBelongsToActiveBu($parent);

        $parentInstruction = [];
        $parentInstructionId = (int) ($parent['shipment_instruction'] ?? 0);

        if ($parentInstructionId > 0) {
            $parentInstruction = $this->get("/api/shipments/shipment-instructions/{$parentInstructionId}/");
        }

        $itemsPayload = $this->get('/api/shipments/shipment-items/');
        $items = $itemsPayload['results'] ?? $itemsPayload ?? [];

        $parentItem = collect($items)->first(function ($item) use ($parentShipmentId) {
            return (int) ($item['shipment'] ?? 0) === $parentShipmentId;
        }) ?? [];

        $data['bu_id'] = $this->activeBuId();
        $data['shipment_type_id'] = $data['shipment_type_id'] ?? ($parent['shipment_type'] ?? null);
        $data['mode_of_transport_id'] = $data['mode_of_transport_id'] ?? ($parent['mode_of_transport'] ?? null);
        $data['from_address_id'] = $data['from_address_id'] ?? ($parentInstruction['from_address'] ?? null);
        $data['to_address_id'] = $data['to_address_id'] ?? ($parentInstruction['to_address'] ?? null);
        $data['item_id'] = $data['item_id'] ?? ($parentItem['item'] ?? null);
        $data['quantity'] = $data['quantity'] ?? ($parentItem['quantity'] ?? '1.000000');
        $data['name'] = trim($data['name'] ?? '') ?: trim(($parent['name'] ?? 'Parent Shipment') . ' - Child');
        $data['description'] = trim($data['description'] ?? '') ?: ($parent['description'] ?? 'Created from parent shipment details.');

        foreach (['bu_id', 'shipment_type_id', 'mode_of_transport_id', 'item_id'] as $requiredField) {
            if (empty($data[$requiredField])) {
                throw new RuntimeException("Unable to create child shipment. Missing inherited field: {$requiredField}");
            }
        }

        logger()->info('BFRN Child Shipment Request', [
            'parent_shipment_id' => $parentShipmentId,
            'inherited_payload' => $data,
        ]);

        try {

            $result = $this->createCompleteFlow($data);

        } catch (\Throwable $e) {

            logger()->error('BFRN Child Shipment Failed', [
                'parent_shipment_id' => $parentShipmentId,
                'inherited_payload' => $data,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }

        $childShipmentId = (int) ($result['shipment']['id'] ?? 0);

        if ($childShipmentId <= 0) {
            throw new RuntimeException('Child shipment was created but no shipment ID was returned.');
        }

        $relationship = $this->post('/api/shipments/shipment-has-shipment/', [
            'parent_shipment' => $parentShipmentId,
            'child_shipment' => $childShipmentId,
            'name' => ($data['relationship_name'] ?? 'Parent -> Child'),
            'description' => ($data['relationship_description'] ?? 'Child shipment created from BFRN Operations UI.'),
            'code' => 'UI-CHILD-' . now()->format('YmdHis'),
        ]);

        $result['relationship'] = $relationship;

        return $result;
    }

    public function updateOperationalStage(int $shipmentId, string $stage, string $action): array
    {
        $allowedStages = ['loading', 'movement', 'offloading', 'storage'];
        $allowedActions = ['start', 'end'];

        if (!in_array($stage, $allowedStages, true)) {
            throw new RuntimeException('Invalid operational stage.');
        }

        if (!in_array($action, $allowedActions, true)) {
            throw new RuntimeException('Invalid operational action.');
        }

        $shipment = $this->get("/api/shipments/shipments/{$shipmentId}/");
        $this->assertShipmentBelongsToActiveBu($shipment);

        $sequence = [
            'loading' => null,
            'movement' => 'loading',
            'offloading' => 'movement',
            'storage' => 'offloading',
        ];

        $previousStage = $sequence[$stage];

        if ($action === 'start') {
            if (!empty($shipment["{$stage}_started"])) {
                throw new RuntimeException(ucfirst($stage) . ' has already started.');
            }

            if ($previousStage && empty($shipment["{$previousStage}_ended"])) {
                throw new RuntimeException(
                    'You must finish ' . $previousStage . ' before starting ' . $stage . '.'
                );
            }
        }

        if ($action === 'end') {
            if (empty($shipment["{$stage}_started"])) {
                throw new RuntimeException(
                    'You must start ' . $stage . ' before finishing it.'
                );
            }

            if (!empty($shipment["{$stage}_ended"])) {
                throw new RuntimeException(ucfirst($stage) . ' has already finished.');
            }
        }

        $field = $stage . '_' . ($action === 'start' ? 'started' : 'ended');

        return $this->patch("/api/shipments/shipments/{$shipmentId}/", [
            $field => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);
    }
}
