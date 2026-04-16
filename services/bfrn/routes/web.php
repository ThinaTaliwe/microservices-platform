<?php


use App\Http\Controllers\ShipmentWebController;
use App\Http\Controllers\Api\SiyaProxyController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/api/proxy/movement', function () {
    $base = rtrim(env('SIYA_API_BASE', 'http://siya-app:8000'), '/');
    $url  = $base . '/siya/api/movement/';

    try {
        $res = Http::timeout(15)->acceptJson()->get($url);

        return response()->json($res->json(), $res->status());

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to reach Siya API',
            'url' => $url,
            'details' => $e->getMessage(),
        ], 504);
    }
});

// Route::prefix('bfrn')->group(function () {

//     Route::get('/shipcreate', [\App\Http\Controllers\ShipmentWebController::class, 'create'])
//         ->name('shipments.create');

//     Route::post('/shipments', [\App\Http\Controllers\ShipmentWebController::class, 'store'])
//         ->name('shipments.store');

    

//     Route::prefix('api')->group(function () {

//         Route::get('/shipments', [SiyaProxyController::class, 'shipmentsIndex']);
//         Route::post('/shipments', [SiyaProxyController::class, 'shipmentsStore']);
//         Route::get('/shipments/{id}', [SiyaProxyController::class, 'shipmentsShow']);
//         Route::put('/shipments/{id}', [SiyaProxyController::class, 'shipmentsUpdate']);
//         Route::delete('/shipments/{id}', [SiyaProxyController::class, 'shipmentsDestroy']);

//         Route::get('/loading/loadings', [SiyaProxyController::class, 'loadings']);
//         Route::get('/loading/loading-items', [SiyaProxyController::class, 'loadingItems']);

//         Route::get('/movement/movements', [SiyaProxyController::class, 'movements']);
//         Route::get('/movement/movement-items', [SiyaProxyController::class, 'movementItems']);

//         Route::get('/offloading/offloadings', [SiyaProxyController::class, 'offloadings']);
//         Route::get('/offloading/offloading-items', [SiyaProxyController::class, 'offloadingItems']);

//         Route::get('/storage/storage', [SiyaProxyController::class, 'storage']);
//         Route::get('/storage/storage-items', [SiyaProxyController::class, 'storageItems']);
//     });
// });

Route::get('/bfrn/api/freehub/ports', function () {
    $p = base_path('resources/js/datasets/json/ports.json');
    if (!file_exists($p)) return response()->json(['error'=>'ports.json missing'], 404);
    return response()->file($p, ['Content-Type' => 'application/json']);
});

Route::get('/bfrn/api/freehub/lines', function () {
    $p = base_path('resources/js/datasets/json/shipping_lines.json');
    if (!file_exists($p)) return response()->json(['error'=>'shipping_lines.json missing'], 404);
    return response()->file($p, ['Content-Type' => 'application/json']);
});

Route::get('/bfrn/api/freehub/agents', function () {
    $p = base_path('resources/js/datasets/json/clearing_agents.json');
    if (!file_exists($p)) return response()->json(['error'=>'clearing_agents.json missing'], 404);
    return response()->file($p, ['Content-Type' => 'application/json']);
});


Route::get('/bfrn/api/freehub/cma/events', function () {
    $key = env('CMA_KEYID');
    if (!$key) return response()->json(['error' => 'CMA_KEYID missing in env'], 500);

    $eventType = request('eventType', 'EQUIPMENT');
    $limit = (int) request('limit', 10);

    $equipmentReference = request('equipmentReference');
    $carrierBookingReference = request('carrierBookingReference');

    if (!$equipmentReference && !$carrierBookingReference) {
        return response()->json(['error' => 'equipmentReference or carrierBookingReference required'], 400);
    }

    $query = ['eventType' => $eventType, 'limit' => $limit];
    if ($equipmentReference) $query['equipmentReference'] = $equipmentReference;
    if ($carrierBookingReference) $query['carrierBookingReference'] = $carrierBookingReference;

    $url = 'https://apis.cma-cgm.net/operation/trackandtrace/v1/events';

    $res = Http::timeout(20)
        ->acceptJson()
        ->withHeaders(['keyId' => $key])
        ->get($url, $query);

    return response()->json($res->json(), $res->status());
});

// All BFRN routes under /bfrn/*
Route::prefix('bfrn')->name('bfrn.')->group(function () {

    Route::get('/APImap', function () {
        return view('ShipmentSA');
    })->name('shipmentSA');

    Route::get('/APIdata', function () {
        return view('ShipmentDiagram');
    })->name('diagram');

    Route::get('/api', function () {
        return view('ShipmentAPI');
    })->name('api');
    
    // UI Pages
    Route::get('/', function () {
        return view('ShipmentIndex');
    })->name('index');

    Route::get('/create', [ShipmentWebController::class, 'create'])->name('create');
    
    
    Route::get('/shipcreate', [ShipmentWebController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentWebController::class, 'store'])->name('shipments.store');

    // API Proxy Routes
    Route::prefix('api')->group(function () {
        Route::get('/bu', [SiyaProxyController::class, 'buIndex'])->name('api.bu.index');

        Route::get('/addresses', [SiyaProxyController::class, 'addressesIndex'])->name('api.addresses.index');
        Route::post('/addresses', [SiyaProxyController::class, 'addressesStore'])->name('api.addresses.store');
        Route::get('/addresses/address-types', [SiyaProxyController::class, 'addressTypesIndex'])->name('api.address-types.index');

        Route::get('/shipments/shipment-types', [SiyaProxyController::class, 'shipmentTypesIndex'])->name('api.shipment-types.index');
        Route::post('/shipments/shipment-instructions', [SiyaProxyController::class, 'shipmentInstructionsStore'])->name('api.shipment-instructions.store');

        Route::get('/shipments', [SiyaProxyController::class, 'shipmentsIndex'])->name('api.shipments.index');
        Route::post('/shipments', [SiyaProxyController::class, 'shipmentsStore'])->name('api.shipments.store');
        Route::get('/shipments/{id}', [SiyaProxyController::class, 'shipmentsShow'])->name('api.shipments.show');
        Route::get('/shipments', [SiyaProxyController::class, 'shipmentsIndex']);
        Route::post('/shipments', [SiyaProxyController::class, 'shipmentsStore']);
        Route::get('/shipments/{id}', [SiyaProxyController::class, 'shipmentsShow']);

        Route::get('/loading/loadings', [SiyaProxyController::class, 'loadings']);
        Route::get('/loading/loading-items', [SiyaProxyController::class, 'loadingItems']);

        Route::get('/movement/movements', [SiyaProxyController::class, 'movements']);
        Route::get('/movement/movement-items', [SiyaProxyController::class, 'movementItems']);

        Route::get('/offloading/offloadings', [SiyaProxyController::class, 'offloadings']);
        Route::get('/offloading/offloading-items', [SiyaProxyController::class, 'offloadingItems']);

        Route::get('/storage/storage', [SiyaProxyController::class, 'storage']);
        Route::get('/storage/storage-items', [SiyaProxyController::class, 'storageItems']);
    });
});


// Route::get('/', function () {
//     return view('ShipmentIndex');
// });

// Route::get('/create', [ShipmentWebController::class, 'create']);


// The GET route to show the form
// Route::get('/shipcreate', [ShipmentWebController::class, 'create']);


// The POST route to save the data
Route::post('/shipments', [ShipmentWebController::class, 'store'])->name('shipments.store');

// home page
// Route::get('/', [App\Http\Controllers\ShipmentWebController::class, 'create']);
