<?php

use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\TwilioStudioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Webhooks WhatsApp/Twilio (pas d'authentification requise)
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'receiveMessage'])
    ->name('api.whatsapp.webhook');

Route::post('/webhook/whatsapp/status', [WhatsAppWebhookController::class, 'statusCallback'])
    ->name('api.whatsapp.status');

// Endpoints pour Twilio Studio Flow CAN 2025
Route::prefix('can')->group(function () {
    Route::post('/scan', [TwilioStudioController::class, 'scan'])->name('api.can.scan');
    Route::post('/optin', [TwilioStudioController::class, 'optin'])->name('api.can.optin');
    Route::post('/inscription', [TwilioStudioController::class, 'inscription'])->name('api.can.inscription');
    Route::post('/refus', [TwilioStudioController::class, 'refus'])->name('api.can.refus');
    Route::post('/stop', [TwilioStudioController::class, 'stop'])->name('api.can.stop');
    Route::post('/abandon', [TwilioStudioController::class, 'abandon'])->name('api.can.abandon');
    Route::post('/timeout', [TwilioStudioController::class, 'timeout'])->name('api.can.timeout');
    Route::post('/error', [TwilioStudioController::class, 'error'])->name('api.can.error');
});

// Routes API authentifiées (pour future app mobile par exemple)
Route::middleware('auth:sanctum')->group(function () {
    // API utilisateur
    // Route::get('/user', function (Request $request) {
    //     return $request->user();
    // });
});
