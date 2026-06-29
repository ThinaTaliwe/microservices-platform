<?php

namespace App\Http\Controllers\Bfrn;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class OperationsDashboardController extends Controller
{
    public function index()
    {
        $shipments = [];
        $shipmentCount = 0;
        $documentCount = 0;
        $businessUnits = [];

        $buMap = [];
        $modeMap = [];

        try {

            $activeBuId = (int) session('active_bu_id');
            $base = rtrim(config('services.siya.base_url'), '/');

            /*
            |--------------------------------------------------------------------------
            | Business Units
            |--------------------------------------------------------------------------
            */
            try {
                $response = Http::acceptJson()
                    ->get($base . '/api/bu/');

                if ($response->successful()) {

                    $rows = $response->json();
                    $rows = $rows['results'] ?? $rows;

                    foreach ($rows as $row) {
                        $buMap[$row['id']] =
                            $row['bu_name']
                            ?? $row['name']
                            ?? $row['short_code']
                            ?? $row['description']
                            ?? ('BU #' . $row['id']);
                    }
                }
            } catch (\Throwable $e) {
            }

            /*
            |--------------------------------------------------------------------------
            | Modes Of Transport
            |--------------------------------------------------------------------------
            */
            try {
                $response = Http::acceptJson()
                    ->get($base . '/api/lookups/modes-of-transport/');

                if ($response->successful()) {

                    $rows = $response->json();
                    $rows = $rows['results'] ?? $rows;

                    foreach ($rows as $row) {
                        $modeMap[$row['id']] =
                            $row['name']
                            ?? ('Mode #' . $row['id']);
                    }
                }
            } catch (\Throwable $e) {
            }

            /*
            |--------------------------------------------------------------------------
            | Shipments
            |--------------------------------------------------------------------------
            */
            $shipmentsResponse = Http::acceptJson()
                ->get($base . '/api/shipments/shipments/');

            if ($shipmentsResponse->successful()) {

                $shipments = $shipmentsResponse->json();
                $shipments = $shipments['results'] ?? $shipments;

                $shipments = collect($shipments)
                    ->filter(fn ($shipment) => (int) ($shipment['bu'] ?? 0) === $activeBuId)
                    ->values()
                    ->all();

                $shipmentCount = count($shipments);

                foreach ($shipments as &$shipment) {

                    if (!empty($shipment['bu'])) {
                        $businessUnits[$shipment['bu']] = true;
                    }

                    $shipment['bu_name'] =
                        $buMap[$shipment['bu']] ??
                        ('BU #' . ($shipment['bu'] ?? ''));

                    $shipment['mode_name'] =
                        $modeMap[$shipment['mode_of_transport']] ??
                        ('Mode #' . ($shipment['mode_of_transport'] ?? ''));

                    try {

                        $docs = Http::acceptJson()->get(
                            $base . '/api/shipments/shipments/' .
                            $shipment['id'] .
                            '/documents/'
                        );

                        if ($docs->successful()) {
                            $payload = $docs->json();
                            $documentCount += count($payload['documents'] ?? []);
                        }

                    } catch (\Throwable $e) {
                    }
                }
            }

        } catch (\Throwable $e) {
        }

        return view('bfrn.operations.dashboard.index', [
            'shipmentCount'     => $shipmentCount,
            'documentCount'     => $documentCount,
            'businessUnitCount' => count($businessUnits),
            'latestShipments'   => collect($shipments)->take(10),
        ]);
    }
}
