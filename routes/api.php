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

    // ==========================================
    // Módulo: Preregistro (Gestión Patrocinador)
    // ==========================================
    Route::group(['prefix' => 'registration/preregistro'], function () {
        Route::post('config', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'saveConfig'])->name('registration.preregistro.save_config');
        Route::get('referrals', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'referrals'])->name('registration.preregistro.referrals');
    });

    // ==========================================
    // Módulo: Registro (Dashboard Patrocinador)
    // ==========================================
    Route::group(['prefix' => 'dashboard/registro'], function () {
        Route::get('link', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistroDashboardController::class, 'getActiveLink'])->name('dashboard.registro.get_link');
        Route::post('link', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistroDashboardController::class, 'generateLink'])->name('dashboard.registro.generate_link');
        Route::delete('link/{id}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistroDashboardController::class, 'suspendLink'])->name('dashboard.registro.suspend_link');
        Route::get('directs', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistroDashboardController::class, 'getDirects'])->name('dashboard.registro.get_directs');
    });
});

// ==========================================
// Módulo: Registro y Preregistro
// ==========================================
Route::group(['prefix' => 'registration'], function () {
    // Preregistro (Landing pública)
    Route::get('preregistro/retorno/{token}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'retorno'])->name('registration.preregistro.retorno');
    Route::post('preregistro/openpay', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'openpay'])->name('registration.preregistro.openpay');
    
    // Webhook Openpay
    Route::post('preregistro/webhook/openpay', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'openpayWebhook'])->name('registration.preregistro.webhook.openpay');

    Route::get('preregistro/check-duplicate', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'checkDuplicate'])->name('registration.preregistro.check_duplicate');
    Route::get('preregistro/config/{username}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'config'])->name('registration.preregistro.config');
    Route::post('preregistro/radar', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'radar'])->name('registration.preregistro.radar');
    
    // Rutas públicas de integración n8n
    Route::post('preregistro/resend-link', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'resendLink'])->name('registration.preregistro.resend_link');
    Route::get('preregistro/check-payment/{email}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'checkPaymentStatus'])->name('registration.preregistro.check_payment');

    // Mover ruta con comodín al final para evitar colisiones
    Route::post('preregistro/{username}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'store'])->name('registration.preregistro.store');

    // Registro Principal
    Route::get('sponsor-link/{id}/{code}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistrationController::class, 'validateSponsorLink'])->name('registration.sponsor_link');
    Route::get('form-data', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistrationController::class, 'getFormData'])->name('registration.form_data');
    Route::post('check-availability', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistrationController::class, 'checkAvailability'])->name('registration.check_availability');
    Route::post('create', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistrationController::class, 'create'])->name('registration.create');

    // Registro a Productos (Cursos, Ebooks, Masterclass)
    Route::post('ebook', [\Promolider\Infrastructure\Registration\In\Http\Controllers\EbookRegistrationController::class, 'register'])->name('registration.ebook');
    Route::post('ebook/{ebookId}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\EbookRegistrationController::class, 'register'])->name('registration.ebook.specific');
    Route::post('minicourse', [\Promolider\Infrastructure\Registration\In\Http\Controllers\MinicourseRegistrationController::class, 'register'])->name('registration.minicourse');
    Route::post('masterclass', [\Promolider\Infrastructure\Registration\In\Http\Controllers\MasterclassRegistrationController::class, 'register'])->name('registration.masterclass');
});

