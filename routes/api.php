<?php

use App\Http\Controllers\Api\LckController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\TwilioStudioController;
use App\Http\Controllers\Api\TwilioWebhookController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
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

// Webhooks Twilio pour les campagnes
Route::post('/webhook/twilio/status', [TwilioWebhookController::class, 'statusCallback'])
    ->name('api.twilio.status-callback');

Route::post('/webhook/twilio/incoming', [TwilioWebhookController::class, 'incomingMessage'])
    ->name('api.twilio.incoming');

// Endpoints pour Twilio Studio Flow CAN 2025
// Protégés par une clé partagée (middleware api.key, fail-open tant que CAN_API_KEY est vide)
Route::prefix('can')->middleware('api.key')->group(function () {
    // Tracking & Inscription
    Route::post('/scan', [TwilioStudioController::class, 'scan'])->name('api.can.scan');
    Route::post('/optin', [TwilioStudioController::class, 'optin'])->name('api.can.optin');
    Route::post('/inscription', [TwilioStudioController::class, 'inscription'])->name('api.can.inscription');
    Route::post('/refus', [TwilioStudioController::class, 'refus'])->name('api.can.refus');
    Route::post('/stop', [TwilioStudioController::class, 'stop'])->name('api.can.stop');
    Route::post('/abandon', [TwilioStudioController::class, 'abandon'])->name('api.can.abandon');
    Route::post('/timeout', [TwilioStudioController::class, 'timeout'])->name('api.can.timeout');
    Route::post('/error', [TwilioStudioController::class, 'error'])->name('api.can.error');
    Route::post('/reactivate', [TwilioStudioController::class, 'reactivate'])->name('api.can.reactivate');
    Route::post('/log', [TwilioStudioController::class, 'log'])->name('api.can.log');

    // Nouvelles API pour le flow interactif
    Route::post('/check-user', [TwilioStudioController::class, 'checkUser'])->name('api.can.check-user');
    Route::get('/villages', [TwilioStudioController::class, 'getVillages'])->name('api.can.villages');

    // Matchs
    Route::get('/matches/today', [TwilioStudioController::class, 'getMatchesToday'])->name('api.can.matches.today');
    Route::get('/matches/upcoming', [TwilioStudioController::class, 'getUpcomingMatches'])->name('api.can.matches.upcoming');
    Route::get('/matches/formatted', [TwilioStudioController::class, 'getMatchesFormatted'])->name('api.can.matches.formatted');
    Route::get('/matches/{id}', [TwilioStudioController::class, 'getMatch'])->name('api.can.matches.show');

    // Pronostics
    Route::post('/check-pronostic', [TwilioStudioController::class, 'checkPronostic'])->name('api.can.check-pronostic'); // ✅ NOUVELLE ROUTE
    Route::post('/user-pronostics', [TwilioStudioController::class, 'getUserPronostics'])->name('api.can.user-pronostics'); // ✅ NOUVELLE ROUTE
    Route::post('/pronostic', [TwilioStudioController::class, 'savePronostic'])
        ->middleware('force.json')
        ->name('api.can.pronostic');
    Route::get('/pronostic/test', [TwilioStudioController::class, 'testPronostic'])->name('api.can.pronostic.test');

    // Quiz Routes
    Route::prefix('quiz')->group(function () {
        Route::post('/check-user', [QuizController::class, 'checkUser'])->name('api.can.quiz.check-user');
        Route::get('/questions', [QuizController::class, 'getQuestions'])->name('api.can.quiz.questions');
        Route::get('/questions/formatted', [QuizController::class, 'getQuestionsFormatted'])->name('api.can.quiz.questions.formatted');
        Route::post('/check-answer', [QuizController::class, 'checkAnswer'])->name('api.can.quiz.check-answer');
        Route::post('/answer', [QuizController::class, 'saveAnswer'])
            ->middleware('force.json')
            ->name('api.can.quiz.answer');
        Route::post('/history', [QuizController::class, 'getHistory'])->name('api.can.quiz.history');
        Route::get('/leaderboard', [QuizController::class, 'getLeaderboard'])->name('api.can.quiz.leaderboard');
    });

    // Autres endpoints
    Route::post('/unsubscribe', [TwilioStudioController::class, 'unsubscribe'])->name('api.can.unsubscribe');
    Route::get('/partners', [TwilioStudioController::class, 'getPartners'])->name('api.can.partners');
    Route::get('/prizes', [TwilioStudioController::class, 'getPrizes'])->name('api.can.prizes');
});

// ─────────────────────────────────────────────────────────────────────────────
// Endpoints La Clé des Châteaux — appelés par WordPress + Twilio Studio
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('lck')->group(function () {

    // Catalogue (WordPress + bot Twilio)
    Route::get('/categories', [LckController::class, 'getCategories'])->name('api.lck.categories');
    Route::get('/products', [LckController::class, 'getProducts'])->name('api.lck.products');

    // Tunnel panier → commande (WordPress → WhatsApp → Twilio)
    Route::post('/cart/create', [LckController::class, 'createCart'])->name('api.lck.cart.create');
    Route::get('/cart/{token}', [LckController::class, 'getCart'])->name('api.lck.cart.show');

    // Confirmation / annulation depuis Twilio Studio
    Route::post('/order/confirm', [LckController::class, 'confirmOrder'])->name('api.lck.order.confirm');
    Route::post('/order/cancel', [LckController::class, 'cancelOrder'])->name('api.lck.order.cancel');

    // Mise à jour statut depuis le dashboard commercante
    Route::put('/order/{ref}/status', [LckController::class, 'updateOrderStatus'])->name('api.lck.order.status');

    // Consultation + annulation depuis WhatsApp (Twilio Studio)
    Route::get('/order/{ref}/status-check', [LckController::class, 'checkOrderStatus'])->name('api.lck.order.status-check');
    Route::post('/order/{ref}/cancel-by-customer', [LckController::class, 'cancelByCustomer'])->name('api.lck.order.cancel-customer');

    // Disponibilité commercant (envoyé par Twilio quand le vendeur tape ONLINE/OFFLINE)
    Route::post('/commercant/checkin', [\App\Http\Controllers\Api\LckController::class, 'commercantCheckin'])->name('api.lck.commercant.checkin');

    // Paiement — initiation, webhook, vérification
    Route::post('/payment/initiate', [\App\Http\Controllers\Api\LckPaymentController::class, 'initiate'])->name('api.lck.payment.initiate');
    Route::post('/payment/callback', [\App\Http\Controllers\Api\LckPaymentController::class, 'callback'])->name('api.lck.payment.callback');
    Route::post('/payment/verify',   [\App\Http\Controllers\Api\LckPaymentController::class, 'verify'])->name('api.lck.payment.verify');
});

// Routes API authentifiées (pour future app mobile par exemple)
Route::middleware('auth:sanctum')->group(function () {
    // API utilisateur
    // Route::get('/user', function (Request $request) {
    //     return $request->user();
    // });
});