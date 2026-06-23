<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API (Arquitectura Hexagonal)
|--------------------------------------------------------------------------
| Aquí solo registramos las rutas que ya han sido migradas a la nueva
| estructura. Todo el monolito ha sido descartado de este repositorio.
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
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Módulo: Dashboard
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('/topbar-stats', [\Promolider\Infrastructure\Dashboard\In\Http\Controllers\DashboardController::class, 'topbarStats']);
        Route::get('/widgets', [\Promolider\Infrastructure\Dashboard\In\Http\Controllers\DashboardController::class, 'dashboardWidgets']);
        Route::get('/unilevel-tree', [\Promolider\Infrastructure\Dashboard\In\Http\Controllers\DashboardController::class, 'unilevelTree']);
    });

});