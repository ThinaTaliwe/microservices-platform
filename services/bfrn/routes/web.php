<?php


use App\Http\Controllers\ShipmentWebController;
use App\Http\Controllers\Api\SiyaProxyController;
use App\Http\Controllers\ApiHealthController;
use App\Http\Controllers\Bfrn\OperationsFlowController;
use App\Http\Controllers\Bfrn\OperationsDashboardController;
use App\Http\Controllers\Bfrn\AuthController;
use App\Http\Controllers\Bfrn\GatewayLoginController;
use App\Http\Controllers\Bfrn\UserAdministrationController;
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

// // // //         Route::put('/shipments/{id}', [SiyaProxyController::class, 'shipmentsUpdate']);
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


Route::get('/login', function () {
    return redirect('/bfrn/login');
})->name('login');

// All BFRN routes under /bfrn/*
Route::prefix('bfrn')->name('bfrn.')->group(function () {

    Route::get('/gateway-login', GatewayLoginController::class)
        ->name('gateway-login');

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->middleware('guest')
        ->name('auth.login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('guest')
        ->name('auth.login.post');

    Route::get('/logout', function () {
        return redirect('/bfrn/operations/dashboard');
    })->middleware('auth');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('auth.logout');

    Route::get('/select-business-unit', [AuthController::class, 'showBusinessUnitSelect'])
        ->middleware('auth')
        ->name('auth.bu.select');

    Route::post('/select-business-unit', [AuthController::class, 'selectBusinessUnit'])
        ->middleware('auth')
        ->name('auth.bu.store');

    Route::middleware(['auth', \App\Http\Middleware\EnsureActiveBusinessUnit::class, \App\Http\Middleware\EnsureBfrnSystemAccess::class])->group(function () {

    Route::get('/APImap', function () {
        return view('ShipmentSA');
    })->name('shipmentSA');

    Route::get('/APIdata', function () {
        return view('ShipmentDiagram');
    })->name('diagram');

    Route::get('/api', function () {
        return view('ShipmentAPI');
    })->name('api');
    


    
    Route::get('/operations/dashboard', [OperationsDashboardController::class, 'index'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':dash,read')
        ->name('operations.dashboard');

    Route::get('/operations/dashboard.', function () {
        return redirect()->route('bfrn.operations.dashboard');
    });


    Route::get('/operations/flows', [OperationsFlowController::class, 'index'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,read')
        ->name('operations.flows.index');

    Route::post('/operations/flows', [OperationsFlowController::class, 'store'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,edit')
        ->name('operations.flows.store');

    Route::post('/operations/flows/{parentId}/children', [OperationsFlowController::class, 'storeChild'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,edit')
        ->name('operations.flows.children.store');


    Route::patch('/operations/flows/{id}', [OperationsFlowController::class, 'update'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,edit')
        ->name('operations.flows.update');

    Route::patch('/operations/flows/{id}/stage', [OperationsFlowController::class, 'updateStage'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,edit')
        ->name('operations.flows.stage.update');


    Route::delete('/operations/flows/{id}', [OperationsFlowController::class, 'destroy'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,delete')
        ->name('operations.flows.destroy');



    


    Route::post('/administration/users/{id}/permissions', [UserAdministrationController::class, 'updatePermissions'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':useradmin,edit')
        ->name('administration.users.permissions');


    Route::post('/administration/users/{id}/business-units', [UserAdministrationController::class, 'updateBusinessUnits'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':useradmin,edit')
        ->name('administration.users.business-units');



    Route::post('/administration/users/{id}/disable', [UserAdministrationController::class, 'disableUser'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':useradmin,edit')
        ->name('administration.users.disable');

    Route::post('/administration/users/{id}/enable', [UserAdministrationController::class, 'enableUser'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':useradmin,edit')
        ->name('administration.users.enable');


    Route::post('/administration/users/{id}/reset-password', [UserAdministrationController::class, 'resetPassword'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':useradmin,edit')
        ->name('administration.users.reset-password');



    Route::get('/administration/users/create', [UserAdministrationController::class, 'create'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':useradmin,edit')
        ->name('administration.users.create');

    Route::post('/administration/users', [UserAdministrationController::class, 'store'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':useradmin,edit')
        ->name('administration.users.store');


    Route::get('/administration/users/{id}', [UserAdministrationController::class, 'show'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':useradmin,read')
        ->name('administration.users.show');



    Route::get('/administration/audits/{id}', [UserAdministrationController::class, 'auditShow'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':useradmin,read')
        ->name('administration.audits.show');


    Route::get('/administration/audits', [UserAdministrationController::class, 'audits'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':useradmin,read')
        ->name('administration.audits.index');


    Route::get('/administration/users', [UserAdministrationController::class, 'index'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':useradmin,read')
        ->name('administration.users.index');

    // API Health Dashboard
    Route::get('/api-health', [ApiHealthController::class, 'page'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':apihealth,read')
        ->name('api-health.page');
    Route::get('/api-health/latest', [ApiHealthController::class, 'latest'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':apihealth,read')
        ->name('api-health.latest');
    Route::post('/api-health/run', [ApiHealthController::class, 'run'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':apihealth,edit')
        ->name('api-health.run');
    Route::get('/api-health/history', [ApiHealthController::class, 'history'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':apihealth,read')
        ->name('api-health.history');
    Route::get('/api-health/report', [ApiHealthController::class, 'report'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':apihealth,read')
        ->name('api-health.report');
    Route::get('/api-health/log', [ApiHealthController::class, 'log'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':apihealth,read')
        ->name('api-health.log');

    // UI Pages
    Route::get('/', function () {
        return view('ShipmentIndex');
    })->name('index');

    Route::get('/create', [ShipmentWebController::class, 'create'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,edit')
        ->name('create');
    
    
    Route::get('/shipcreate', [ShipmentWebController::class, 'create'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,edit')
        ->name('shipments.create');
    Route::post('/shipments', [ShipmentWebController::class, 'store'])
        ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,edit')
        ->name('shipments.store');

    // API Proxy Routes
    Route::prefix('api')->group(function () {
        Route::get('/bu', [SiyaProxyController::class, 'buIndex'])->name('api.bu.index');

        Route::get('/addresses', [SiyaProxyController::class, 'addressesIndex'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':addr,read')
            ->name('api.addresses.index');
        Route::post('/addresses', [SiyaProxyController::class, 'addressesStore'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':addr,edit')
            ->name('api.addresses.store');
        Route::get('/addresses/{id}', [SiyaProxyController::class, 'addressesShow'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':addr,read')
            ->name('api.addresses.show');
        Route::put('/addresses/{id}', [SiyaProxyController::class, 'addressesUpdate'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':addr,edit')
            ->name('api.addresses.update');
        Route::get('/addresses/address-types', [SiyaProxyController::class, 'addressTypesIndex'])->name('api.address-types.index');

        Route::get('/shipments/shipment-types', [SiyaProxyController::class, 'shipmentTypesIndex'])->name('api.shipment-types.index');

        Route::get('/shipments/shipment-instructions/{id}', [SiyaProxyController::class, 'shipmentInstructionShow'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,read')
            ->name('api.shipment-instructions.show');


        Route::get('/shipments/relationships/parent-child', [SiyaProxyController::class, 'shipmentRelationships'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,read')
            ->name('api.shipments.relationships.parent-child');
        Route::delete('/shipments/relationships/parent-child/{id}', [SiyaProxyController::class, 'shipmentRelationshipDestroy'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,delete')
            ->name('api.shipments.relationships.destroy');

        Route::get('/shipments/relationships/previous', [SiyaProxyController::class, 'previousShipmentRelationships'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,read')
            ->name('api.shipments.relationships.previous');


        Route::get('/lookups/modes-of-transport', [SiyaProxyController::class, 'modesOfTransport'])->name('api.lookups.modes-of-transport');
        Route::get('/lookups/items', [SiyaProxyController::class, 'items'])->name('api.lookups.items');

        Route::post('/shipments/shipment-instructions', [SiyaProxyController::class, 'shipmentInstructionsStore'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,edit')
            ->name('api.shipment-instructions.store');

        Route::get('/shipments/shipment-items', [SiyaProxyController::class, 'shipmentItemsIndex'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,read')
            ->name('api.shipments.items.index');
        Route::get('/shipments', [SiyaProxyController::class, 'shipmentsIndex'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,read')
            ->name('api.shipments.index');
        Route::post('/shipments', [SiyaProxyController::class, 'shipmentsStore'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,edit')
            ->name('api.shipments.store');
        Route::get('/shipments/{id}', [SiyaProxyController::class, 'shipmentsShow'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':ship,read')
            ->name('api.shipments.show');

        Route::get('/shipments/{id}/documents', [SiyaProxyController::class, 'shipmentDocumentsIndex'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':docs,read')
            ->name('api.shipments.documents.index');
        Route::post('/shipments/{id}/documents', [SiyaProxyController::class, 'shipmentDocumentsStore'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':docs,edit')
            ->name('api.shipments.documents.store');
        Route::delete('/shipments/{id}/documents', [SiyaProxyController::class, 'shipmentDocumentsDestroyByQuery'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':docs,delete')
            ->name('api.shipments.documents.destroy-query');
        Route::delete('/shipments/{id}/documents/{filename}', [SiyaProxyController::class, 'shipmentDocumentsDestroy'])
            ->middleware(\App\Http\Middleware\EnsureBfrnComponentPermission::class . ':docs,delete')
            ->name('api.shipments.documents.destroy');


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
});


// Route::get('/', function () {
//     return view('ShipmentIndex');
// });

// Route::get('/create', [ShipmentWebController::class, 'create']);


// The GET route to show the form
// Route::get('/shipcreate', [ShipmentWebController::class, 'create']);


// The POST route to save the data
// Disabled: unsafe duplicate outside /bfrn middleware group.
// Route::post('/shipments', [ShipmentWebController::class, 'store'])->name('shipments.store');

// home page
// Route::get('/', [App\Http\Controllers\ShipmentWebController::class, 'create']);
