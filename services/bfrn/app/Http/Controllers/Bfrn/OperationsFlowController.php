<?php

namespace App\Http\Controllers\Bfrn;

use App\Http\Controllers\Controller;
use App\Services\Bfrn\FlowService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use App\Support\BfrnAudit;

class OperationsFlowController extends Controller
{
    private function apiBase(): string
    {
        $base = rtrim(config('services.siya.base_url', env('SIYA_API_BASE', 'http://siya-app:8000')), '/');
        $prefix = trim(config('services.siya.prefix', env('SIYA_API_PREFIX', '')), '/');

        return $prefix ? "{$base}/{$prefix}" : $base;
    }



    private function stageStatus(array $shipment, string $stage): string
    {
        if ($stage === 'instruction') {
            return !empty($shipment['shipment_instruction']) ? 'Linked' : 'Missing';
        }

        $idField = $stage;
        $startedField = "{$stage}_started";
        $endedField = "{$stage}_ended";

        if (empty($shipment[$idField])) {
            return 'Missing';
        }

        if (!empty($shipment[$endedField])) {
            return 'Ended';
        }

        if (!empty($shipment[$startedField])) {
            return 'Started';
        }

        return 'Linked';
    }

    private function shipmentStatus(array $shipment): string
    {
        if (!empty($shipment['storage_ended'])) {
            return 'Completed';
        }

        if (!empty($shipment['storage_started'])) {
            return 'Stored';
        }

        if (!empty($shipment['offloading_started'])) {
            return 'Offloading';
        }

        if (!empty($shipment['movement_started'])) {
            return 'In Transit';
        }

        if (!empty($shipment['loading_started'])) {
            return 'Loading';
        }

        return 'Draft';
    }

    private function fetchAll(string $path): array
    {
        $records = [];
        $page = 1;

        while (true) {
            $response = Http::timeout(20)
                ->acceptJson()
                ->get($this->apiBase() . $path, ['page' => $page]);

            if (!$response->successful()) {
                throw new \RuntimeException('API returned status ' . $response->status() . ' for ' . $path);
            }

            $payload = $response->json();

            if (isset($payload['results'])) {
                $records = array_merge($records, $payload['results']);

                if (empty($payload['next'])) {
                    break;
                }

                $page++;
                continue;
            }

            return is_array($payload) ? $payload : [];
        }

        return $records;
    }

    public function index(Request $request)
    {
        $error = null;
        $perPage = 10;
        $page = max((int) $request->query('page', 1), 1);

        try {
            $activeBuId = (int) session('active_bu_id');

            $allShipments = collect($this->fetchAll('/api/shipments/shipments/'))
                ->filter(fn ($shipment) => (int) ($shipment['bu'] ?? 0) === $activeBuId)
                ->values()
                ->all();

            $instructions = $this->fetchAll('/api/shipments/shipment-instructions/');
            $relationships = $this->fetchAll('/api/shipments/shipment-has-shipment/');

            $instructionMap = collect($instructions)
                ->keyBy('id')
                ->all();

            $allShipments = collect($allShipments)
                ->map(function ($shipment) use ($instructionMap) {
                    $instructionId = $shipment['shipment_instruction'] ?? null;
                    $instruction = $instructionMap[$instructionId] ?? null;

                    $shipment['shipment_reference'] = $instruction['instruction_reference'] ?? ('SHIP-' . str_pad((string) ($shipment['id'] ?? 0), 6, '0', STR_PAD_LEFT));
                    $shipment['derived_status'] = $this->shipmentStatus($shipment);
                    $shipment['stage_statuses'] = [
                        'instruction' => $this->stageStatus($shipment, 'instruction'),
                        'loading' => $this->stageStatus($shipment, 'loading'),
                        'movement' => $this->stageStatus($shipment, 'movement'),
                        'offloading' => $this->stageStatus($shipment, 'offloading'),
                        'storage' => $this->stageStatus($shipment, 'storage'),
                    ];

                    return $shipment;
                })
                ->all();

            $childShipmentIds = collect($relationships)
                ->pluck('child_shipment')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $parentShipments = collect($allShipments)
                ->reject(fn ($shipment) => in_array((int) ($shipment['id'] ?? 0), $childShipmentIds, true))
                ->values()
                ->all();

            usort($parentShipments, function ($a, $b) {
                $aName = $a['name'] ?? '';
                $bName = $b['name'] ?? '';

                if (str_contains($aName, 'BFRN Demo')) {
                    return -1;
                }

                if (str_contains($bName, 'BFRN Demo')) {
                    return 1;
                }

                return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
            });

            $shipments = new LengthAwarePaginator(
                array_slice($parentShipments, ($page - 1) * $perPage, $perPage),
                count($parentShipments),
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        } catch (\Throwable $e) {
            $shipments = new LengthAwarePaginator([], 0, $perPage, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            $error = 'Unable to load parent shipments: ' . $e->getMessage();
        }

        return view('bfrn.operations.flows.index', [
            'shipments' => $shipments,
            'error' => $error,
        ]);
    }

    public function store(Request $request, FlowService $flowService)
    {
        $validated = $request->validate([
            'bu_id' => ['required', 'integer'],
            'shipment_type_id' => ['required', 'integer'],
            'mode_of_transport_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:45'],
            'description' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.000001'],
            'from_address_id' => ['nullable', 'integer'],
            'to_address_id' => ['nullable', 'integer'],
        ]);

        $validated['bu_id'] = (int) session('active_bu_id');

        try {
            $result = $flowService->createCompleteFlow($validated);

            BfrnAudit::log(
                'shipment_created',
                'bfrn_shipment',
                (int) ($result['shipment']['id'] ?? $result['id'] ?? 0),
                [],
                [
                    'input' => $validated,
                    'result' => $result,
                    'active_bu_id' => session('active_bu_id'),
                ],
                ['bfrn', 'operations', 'shipments']
            );

            return response()->json([
                'message' => 'Created successfully.',
                'data' => $result,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Unable to create shipment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id, FlowService $flowService)
    {
        $validated = $request->validate([
            'bu_id' => ['required', 'integer'],
            'shipment_type_id' => ['required', 'integer'],
            'mode_of_transport_id' => ['required', 'integer'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.item_id' => ['required', 'integer', 'distinct'],

            'items.*.quantity' => ['required', 'numeric', 'min:0.000001'],
            'name' => ['required', 'string', 'max:45'],
            'description' => ['nullable', 'string', 'max:255'],
            'from_address_id' => ['nullable', 'integer'],
            'to_address_id' => ['nullable', 'integer'],
        ]);

        $validated['bu_id'] = (int) session('active_bu_id');

        try {
            $result = $flowService->updateShipment($id, $validated);

            BfrnAudit::log(
                'shipment_updated',
                'bfrn_shipment',
                (int) $id,
                [],
                [
                    'input' => $validated,
                    'result' => $result,
                    'active_bu_id' => session('active_bu_id'),
                ],
                ['bfrn', 'operations', 'shipments']
            );

            return response()->json([
                'message' => 'Flow updated successfully.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Unable to update flow.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id, FlowService $flowService)
    {
        try {
            $flowService->deleteShipment($id);

            BfrnAudit::log(
                'shipment_deleted',
                'bfrn_shipment',
                (int) $id,
                [],
                [
                    'active_bu_id' => session('active_bu_id'),
                ],
                ['bfrn', 'operations', 'shipments']
            );

            return response()->json([
                'message' => 'Flow deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Unable to delete flow.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeChild(Request $request, int $parentId, FlowService $flowService)
    {
        $validated = $request->validate([
            'bu_id' => ['nullable', 'integer'],
            'shipment_type_id' => ['nullable', 'integer'],
            'mode_of_transport_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:45'],
            'description' => ['nullable', 'string', 'max:255'],
            'item_id' => ['nullable', 'integer'],
            'quantity' => ['required', 'numeric', 'min:0.000001'],
            'relationship_name' => ['nullable', 'string', 'max:255'],
            'relationship_description' => ['nullable', 'string', 'max:255'],
            'from_address_id' => ['nullable', 'integer'],
            'to_address_id' => ['nullable', 'integer'],
        ]);

        $validated['bu_id'] = (int) session('active_bu_id');

        try {
            $result = $flowService->createChildFlow($parentId, $validated);

            BfrnAudit::log(
                'child_shipment_created',
                'bfrn_shipment',
                (int) ($result['shipment']['id'] ?? $result['id'] ?? $parentId),
                [],
                [
                    'parent_id' => $parentId,
                    'input' => $validated,
                    'result' => $result,
                    'active_bu_id' => session('active_bu_id'),
                ],
                ['bfrn', 'operations', 'shipments']
            );

            return response()->json([
                'message' => 'Child shipment created successfully.',
                'data' => $result,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Unable to create child shipment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStage(Request $request, int $id, FlowService $flowService)
    {
        $validated = $request->validate([
            'stage' => ['required', 'string', 'in:loading,movement,offloading,storage'],
            'action' => ['required', 'string', 'in:start,end'],
        ]);

        try {
            $result = $flowService->updateOperationalStage(
                $id,
                $validated['stage'],
                $validated['action']
            );

            BfrnAudit::log(
                'shipment_stage_updated',
                'bfrn_shipment',
                (int) $id,
                [],
                [
                    'stage' => $validated['stage'],
                    'action' => $validated['action'],
                    'result' => $result,
                    'active_bu_id' => session('active_bu_id'),
                ],
                ['bfrn', 'operations', 'shipments']
            );

            return response()->json([
                'message' => 'Operational stage updated successfully.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Unable to update operational stage.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
