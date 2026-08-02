<?php

use App\Http\Controllers\Api\AutoChainController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'AutoChain API is running',
        'status' => 'ok',
    ]);
});

Route::post('/login', [AutoChainController::class, 'login']);
Route::get('/public/vehicles/{vehicle}/history', [AutoChainController::class, 'publicHistory']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AutoChainController::class, 'me']);
    Route::post('/wallet', [AutoChainController::class, 'linkWallet']);
    Route::get('/dashboard', [AutoChainController::class, 'dashboard']);

    Route::get('/vehicles', [AutoChainController::class, 'vehicles'])->middleware('role:manager|admin|driver|garage|auditor');
    Route::post('/vehicles', [AutoChainController::class, 'storeVehicle'])->middleware('role:manager|admin');
    Route::get('/vehicles/{vehicle}', [AutoChainController::class, 'showVehicle'])->middleware('role:manager|admin|driver|garage|auditor');
    Route::patch('/vehicles/{vehicle}', [AutoChainController::class, 'updateVehicle'])->middleware('role:manager|admin');
    Route::post('/vehicles/{vehicle}/assign', [AutoChainController::class, 'assignVehicle'])->middleware('role:manager|admin');
    Route::post('/vehicles/{vehicle}/mileage', [AutoChainController::class, 'recordMileage'])->middleware('role:driver|manager|admin');
    Route::post('/vehicles/{vehicle}/maintenance', [AutoChainController::class, 'recordMaintenance'])->middleware('role:garage|admin');
    Route::post('/vehicles/{vehicle}/documents', [AutoChainController::class, 'uploadDocument'])->middleware('role:manager|admin');
    Route::post('/vehicles/{vehicle}/fuel', [AutoChainController::class, 'storeFuel'])->middleware('role:driver|manager|admin');
    Route::get('/vehicles/{vehicle}/timeline', [AutoChainController::class, 'timeline'])->middleware('role:manager|admin|driver|garage|auditor');

    Route::get('/timeline', [AutoChainController::class, 'timeline'])->middleware('role:manager|admin|driver|garage|auditor');
    Route::get('/alerts', [AutoChainController::class, 'alerts'])->middleware('role:manager|admin|driver|garage|auditor');
    Route::post('/alerts/generate', [AutoChainController::class, 'generateAlerts'])->middleware('role:manager|admin');
    // Allow authenticated users to submit blockchain txs (pending/confirmed)
    Route::post('/blockchain/txs', [AutoChainController::class, 'syncTx']);
    // Contract info should be available to authenticated users (no role required)
    Route::get('/blockchain/contract', [AutoChainController::class, 'contractInfo']);
    // Allow authenticated users to trigger role sync (it will create roles as needed)
    Route::post('/blockchain/sync-roles', [AutoChainController::class, 'syncRoles']);
});
