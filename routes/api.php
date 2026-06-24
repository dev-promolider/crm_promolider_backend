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
    // Aquí irán las rutas que requieran autenticación en el futuro
});
