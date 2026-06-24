<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Arquitectura Hexagonal)
|--------------------------------------------------------------------------
| Aquí solo registramos las rutas que ya han sido migradas a la nueva
| estructura.
|
*/

// ==========================================
// Módulo: Autenticación
// ==========================================
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [\Promolider\Infrastructure\Auth\In\Http\Controllers\AuthController::class, 'login'])->name('auth.login');
});

// ==========================================
// Rutas Protegidas (Requieren Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    // ==========================================
    // Módulo: Dashboard
    // ==========================================
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('topbar-stats', [\Promolider\Infrastructure\Dashboard\In\Http\Controllers\DashboardController::class, 'topbarStats'])->name('dashboard.stats');
        Route::get('widgets', [\Promolider\Infrastructure\Dashboard\In\Http\Controllers\DashboardController::class, 'dashboardWidgets'])->name('dashboard.widgets');
        Route::get('unilevel-tree', [\Promolider\Infrastructure\Dashboard\In\Http\Controllers\DashboardController::class, 'unilevelTree'])->name('dashboard.unilevel_tree');
        Route::get('binary-tree', [\Promolider\Infrastructure\Dashboard\In\Http\Controllers\DashboardController::class, 'binaryTree'])->name('dashboard.binary_tree');
    });

    // ==========================================
    // Módulo: Perfil
    // ==========================================
    Route::get('profile/info', function (\Illuminate\Http\Request $request) {
        return response()->json(['user' => $request->user()]);
    });
});
