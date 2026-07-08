<?php

namespace App\Http\Controllers\Bfrn;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OperationsDashboardController extends Controller
{
    public function index()
    {
        $activeBuId = (int) session('active_bu_id');

        $shipmentCount = DB::table('shipment')
            ->where('bu_id', $activeBuId)
            ->count();

        $latestShipments = DB::table('shipment as s')
            ->leftJoin('bu as b', 'b.id', '=', 's.bu_id')
            ->leftJoin('mode_of_transport as m', 'm.id', '=', 's.mode_of_transport_id')
            ->where('s.bu_id', $activeBuId)
            ->orderByDesc('s.id')
            ->limit(10)
            ->get([
                's.id',
                's.name',
                DB::raw("COALESCE(b.bu_name, b.short_code, CONCAT('BU #', b.id)) as bu_name"),
                DB::raw("COALESCE(m.name, CONCAT('Mode #', m.id)) as mode_name"),
            ]);

        return view('bfrn.operations.dashboard.index', [
            'shipmentCount' => $shipmentCount,
            'documentCount' => 0,
            'businessUnitCount' => $shipmentCount > 0 ? 1 : 0,
            'latestShipments' => $latestShipments,
        ]);
    }

    public function mapData()
    {
        $activeBuId = (int) session('active_bu_id');

        if ($activeBuId <= 0) {
            return response()->json([
                'mapped_shipments' => [],
                'missing_shipments' => [],
                'mapped_count' => 0,
                'missing_count' => 0,
                'total_count' => 0,
            ]);
        }

        return response()->json(
            Cache::remember("bfrn_dashboard_fast_map_bu_{$activeBuId}", 60, function () use ($activeBuId) {
                $rows = DB::table('shipment as s')
                    ->join('shipment_instruction as i', 'i.id', '=', 's.shipment_instruction_id')
                    ->leftJoin('address as origin', 'origin.id', '=', 'i.from_address_id')
                    ->leftJoin('address as destination', 'destination.id', '=', 'i.to_address_id')
                    ->leftJoin('mode_of_transport as m', 'm.id', '=', 's.mode_of_transport_id')
                    ->where('s.bu_id', $activeBuId)
                    ->orderByDesc('s.id')
                    ->limit(1000)
                    ->get([
                        's.id',
                        's.name',
                        DB::raw("COALESCE(m.name, '') as mode_name"),
                        'origin.name as origin_name',
                        'origin.latitude as origin_latitude',
                        'origin.longitude as origin_longitude',
                        'destination.name as destination_name',
                        'destination.latitude as destination_latitude',
                        'destination.longitude as destination_longitude',
                    ]);

                $mapped = [];
                $missing = [];

                foreach ($rows as $row) {
                    $hasOrigin = $row->origin_latitude !== null && $row->origin_longitude !== null;
                    $hasDestination = $row->destination_latitude !== null && $row->destination_longitude !== null;

                    if ($hasOrigin || $hasDestination) {
                        $mapped[] = [
                            'id' => $row->id,
                            'name' => $row->name ?: ('Shipment #' . $row->id),
                            'mode' => $row->mode_name,
                            'from' => $hasOrigin ? [
                                'name' => $row->origin_name ?: 'Origin',
                                'latitude' => (float) $row->origin_latitude,
                                'longitude' => (float) $row->origin_longitude,
                            ] : null,
                            'to' => $hasDestination ? [
                                'name' => $row->destination_name ?: 'Destination',
                                'latitude' => (float) $row->destination_latitude,
                                'longitude' => (float) $row->destination_longitude,
                            ] : null,
                        ];
                    } else {
                        $missing[] = [
                            'id' => $row->id,
                            'name' => $row->name ?: ('Shipment #' . $row->id),
                        ];
                    }
                }

                return [
                    'mapped_shipments' => $mapped,
                    'missing_shipments' => array_slice($missing, 0, 20),
                    'mapped_count' => count($mapped),
                    'missing_count' => count($missing),
                    'total_count' => count($rows),
                ];
            })
        );
    }
}
