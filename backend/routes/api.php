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

    Route::get('/blockchain/contract', [AutoChainController::class, 'contractInfo']);
    Route::post('/blockchain/sync-roles', [AutoChainController::class, 'syncRoles']);
    Route::post('/blockchain/txs', [AutoChainController::class, 'syncTx']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users', [AutoChainController::class, 'adminUsers']);
        Route::post('/users/{user}/roles', [AutoChainController::class, 'adminUpdateUserRoles']);
        Route::post('/contract/config', [AutoChainController::class, 'adminSaveContractConfig']);
    });

    Route::middleware('role:manager|admin')->prefix('manager')->group(function () {
        Route::get('/fleet', [AutoChainController::class, 'managerFleet']);
        Route::post('/vehicles', [AutoChainController::class, 'storeVehicle']);
        Route::post('/vehicles/{vehicle}/assign', [AutoChainController::class, 'assignVehicle']);
        Route::get('/fuel-summary', [AutoChainController::class, 'managerFuelSummary']);
    });

    Route::middleware('role:driver|manager|admin')->prefix('driver')->group(function () {
        Route::get('/assignments', [AutoChainController::class, 'driverAssignments']);
        Route::post('/vehicles/{vehicle}/mileage', [AutoChainController::class, 'recordMileage']);
        Route::post('/vehicles/{vehicle}/checkin', [AutoChainController::class, 'driverCheckin']);
    });

    Route::middleware('role:garage|admin')->prefix('garage')->group(function () {
        Route::get('/maintenances', [AutoChainController::class, 'garageMaintenances']);
        Route::post('/vehicles/{vehicle}/maintenance', [AutoChainController::class, 'recordMaintenance']);
    });

    Route::get('/vehicles', [AutoChainController::class, 'vehicles'])->middleware('role:manager|admin|driver|garage|auditor');
    Route::get('/vehicles/{vehicle}', [AutoChainController::class, 'showVehicle'])->middleware('role:manager|admin|driver|garage|auditor');
    Route::patch('/vehicles/{vehicle}', [AutoChainController::class, 'updateVehicle'])->middleware('role:manager|admin');
    Route::post('/vehicles/{vehicle}/documents', [AutoChainController::class, 'uploadDocument'])->middleware('role:manager|admin');
    Route::post('/vehicles/{vehicle}/fuel', [AutoChainController::class, 'storeFuel'])->middleware('role:driver|manager|admin');
    Route::get('/vehicles/{vehicle}/timeline', [AutoChainController::class, 'timeline'])->middleware('role:manager|admin|driver|garage|auditor');

    Route::get('/timeline', [AutoChainController::class, 'timeline'])->middleware('role:manager|admin|driver|garage|auditor');
    Route::get('/alerts', [AutoChainController::class, 'alerts'])->middleware('role:manager|admin|driver|garage|auditor');
    Route::post('/alerts/generate', [AutoChainController::class, 'generateAlerts'])->middleware('role:manager|admin');
});
