<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Commercant\AuthController as CommercantAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

// Route publique - Scan QR Code (tracking + redirect WhatsApp)
Route::get('/qr/{code}', [\App\Http\Controllers\Admin\QrCodeController::class, 'scan'])
    ->name('qr.scan');

// Routes Admin - Login
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Routes protégées par le middleware admin
    Route::middleware(['admin'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('admin.dashboard');
        Route::get('/dashboard/export/detailed-stats', [\App\Http\Controllers\Admin\DashboardController::class, 'exportDetailedStats'])
            ->name('admin.dashboard.export.detailed-stats');

        // Routes Villages
        Route::resource('villages', \App\Http\Controllers\Admin\VillageController::class)
            ->names([
                'index'   => 'admin.villages.index',
                'create'  => 'admin.villages.create',
                'store'   => 'admin.villages.store',
                'show'    => 'admin.villages.show',
                'edit'    => 'admin.villages.edit',
                'update'  => 'admin.villages.update',
                'destroy' => 'admin.villages.destroy',
            ]);

        // Routes Partenaires
        Route::resource('partners', \App\Http\Controllers\Admin\PartnerController::class)
            ->names([
                'index'   => 'admin.partners.index',
                'create'  => 'admin.partners.create',
                'store'   => 'admin.partners.store',
                'show'    => 'admin.partners.show',
                'edit'    => 'admin.partners.edit',
                'update'  => 'admin.partners.update',
                'destroy' => 'admin.partners.destroy',
            ]);

        // Routes Matchs
        Route::resource('matches', \App\Http\Controllers\Admin\MatchController::class)
            ->names([
                'index'   => 'admin.matches.index',
                'create'  => 'admin.matches.create',
                'store'   => 'admin.matches.store',
                'show'    => 'admin.matches.show',
                'edit'    => 'admin.matches.edit',
                'update'  => 'admin.matches.update',
                'destroy' => 'admin.matches.destroy',
            ]);

        // Routes Users (Joueurs)
        Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
        Route::get('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');
        Route::delete('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');

        // Routes Prizes (Lots)
        Route::resource('prizes', \App\Http\Controllers\Admin\PrizeController::class)
            ->names([
                'index'   => 'admin.prizes.index',
                'create'  => 'admin.prizes.create',
                'store'   => 'admin.prizes.store',
                'show'    => 'admin.prizes.show',
                'edit'    => 'admin.prizes.edit',
                'update'  => 'admin.prizes.update',
                'destroy' => 'admin.prizes.destroy',
            ]);

        // Routes QR Codes
        Route::resource('qrcodes', \App\Http\Controllers\Admin\QrCodeController::class)
            ->names([
                'index'   => 'admin.qrcodes.index',
                'create'  => 'admin.qrcodes.create',
                'store'   => 'admin.qrcodes.store',
                'show'    => 'admin.qrcodes.show',
                'edit'    => 'admin.qrcodes.edit',
                'update'  => 'admin.qrcodes.update',
                'destroy' => 'admin.qrcodes.destroy',
            ]);

        // Route de téléchargement
        Route::get('qrcodes/{qrcode}/download', [\App\Http\Controllers\Admin\QrCodeController::class, 'download'])
            ->name('admin.qrcodes.download');

        // Routes Pronostics
        Route::get('pronostics/stats', [\App\Http\Controllers\Admin\PronosticController::class, 'stats'])
            ->name('admin.pronostics.stats');
        Route::get('pronostics/export/winners', [\App\Http\Controllers\Admin\PronosticController::class, 'exportAllWinners'])
            ->name('admin.pronostics.export.winners');
        Route::get('pronostics', [\App\Http\Controllers\Admin\PronosticController::class, 'index'])
            ->name('admin.pronostics.index');
        Route::get('pronostics/{pronostic}', [\App\Http\Controllers\Admin\PronosticController::class, 'show'])
            ->name('admin.pronostics.show');
        Route::delete('pronostics/{pronostic}', [\App\Http\Controllers\Admin\PronosticController::class, 'destroy'])
            ->name('admin.pronostics.destroy');
        Route::get('matches/{match}/pronostics', [\App\Http\Controllers\Admin\PronosticController::class, 'byMatch'])
            ->name('admin.pronostics.by-match');
        Route::get('matches/{match}/pronostics/export', [\App\Http\Controllers\Admin\PronosticController::class, 'exportWinners'])
            ->name('admin.pronostics.export.match-winners');

        Route::post('/matches/{match}/evaluate-pronostics', [\App\Http\Controllers\Admin\PronosticController::class, 'evaluateMatch'])
            ->name('admin.matches.evaluate');
        Route::post('/pronostics/reevaluate-all', [\App\Http\Controllers\Admin\PronosticController::class, 'reevaluateAll'])
            ->name('admin.pronostics.reevaluate-all');

        // Routes Templates de Messages
        Route::resource('templates', \App\Http\Controllers\Admin\MessageTemplateController::class)
            ->names([
                'index'   => 'admin.templates.index',
                'create'  => 'admin.templates.create',
                'store'   => 'admin.templates.store',
                'show'    => 'admin.templates.show',
                'edit'    => 'admin.templates.edit',
                'update'  => 'admin.templates.update',
                'destroy' => 'admin.templates.destroy',
            ]);
        Route::post('templates/{template}/duplicate', [\App\Http\Controllers\Admin\MessageTemplateController::class, 'duplicate'])
            ->name('admin.templates.duplicate');
        Route::get('templates/{template}/preview', [\App\Http\Controllers\Admin\MessageTemplateController::class, 'preview'])
            ->name('admin.templates.preview');

        // Routes Campagnes
        Route::resource('campaigns', \App\Http\Controllers\Admin\CampaignController::class)
            ->names([
                'index'   => 'admin.campaigns.index',
                'create'  => 'admin.campaigns.create',
                'store'   => 'admin.campaigns.store',
                'show'    => 'admin.campaigns.show',
                'edit'    => 'admin.campaigns.edit',
                'update'  => 'admin.campaigns.update',
                'destroy' => 'admin.campaigns.destroy',
            ]);
        Route::get('campaigns/{campaign}/confirm-send', [\App\Http\Controllers\Admin\CampaignController::class, 'confirmSend'])
            ->name('admin.campaigns.confirm-send');
        Route::post('campaigns/{campaign}/send', [\App\Http\Controllers\Admin\CampaignController::class, 'send'])
            ->name('admin.campaigns.send');
        Route::post('campaigns/{campaign}/test', [\App\Http\Controllers\Admin\CampaignController::class, 'test'])
            ->name('admin.campaigns.test');

        // Routes Classement
        Route::get('leaderboard', [\App\Http\Controllers\Admin\LeaderboardController::class, 'index'])
            ->name('admin.leaderboard');
        Route::get('leaderboard/village/{village}', [\App\Http\Controllers\Admin\LeaderboardController::class, 'village'])
            ->name('admin.leaderboard.village');

        // Routes Analytics
        Route::get('analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])
            ->name('admin.analytics');
        Route::get('analytics/export/users', [\App\Http\Controllers\Admin\AnalyticsController::class, 'exportUsers'])
            ->name('admin.analytics.export.users');
        Route::get('analytics/export/pronostics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'exportPronostics'])
            ->name('admin.analytics.export.pronostics');

        // Routes Quiz Questions
        Route::resource('quiz/questions', \App\Http\Controllers\Admin\QuizQuestionController::class)
            ->names([
                'index'   => 'admin.quiz.questions.index',
                'create'  => 'admin.quiz.questions.create',
                'store'   => 'admin.quiz.questions.store',
                'show'    => 'admin.quiz.questions.show',
                'edit'    => 'admin.quiz.questions.edit',
                'update'  => 'admin.quiz.questions.update',
                'destroy' => 'admin.quiz.questions.destroy',
            ]);
        Route::post('quiz/questions/{question}/toggle', [\App\Http\Controllers\Admin\QuizQuestionController::class, 'toggleActive'])
            ->name('admin.quiz.questions.toggle');
        Route::post('quiz/questions/reorder', [\App\Http\Controllers\Admin\QuizQuestionController::class, 'reorder'])
            ->name('admin.quiz.questions.reorder');

        // Routes Quiz Answers
        Route::get('quiz/answers', [\App\Http\Controllers\Admin\QuizAnswerController::class, 'index'])
            ->name('admin.quiz.answers.index');
        Route::get('quiz/answers/{question}', [\App\Http\Controllers\Admin\QuizAnswerController::class, 'show'])
            ->name('admin.quiz.answers.show');
        Route::get('quiz/leaderboard', [\App\Http\Controllers\Admin\QuizAnswerController::class, 'leaderboard'])
            ->name('admin.quiz.leaderboard');
        Route::get('quiz/export', [\App\Http\Controllers\Admin\QuizAnswerController::class, 'export'])
            ->name('admin.quiz.export');

        // ── La Clé des Châteaux ───────────────────────────────────
        Route::prefix('lck')->group(function () {
            // Commandes
            Route::get('orders', [\App\Http\Controllers\Admin\LckOrderController::class, 'index'])
                ->name('admin.lck.orders.index');
            Route::get('orders/{ref}', [\App\Http\Controllers\Admin\LckOrderController::class, 'show'])
                ->name('admin.lck.orders.show');
            Route::post('orders/{ref}/status', [\App\Http\Controllers\Admin\LckOrderController::class, 'updateStatus'])
                ->name('admin.lck.orders.status');
            Route::delete('orders/{ref}', [\App\Http\Controllers\Admin\LckOrderController::class, 'destroy'])
                ->name('admin.lck.orders.destroy');
            Route::get('reports', [\App\Http\Controllers\Admin\LckReportController::class, 'index'])
                ->name('admin.lck.reports');

            // Produits
            Route::get('products', [\App\Http\Controllers\Admin\LckProductController::class, 'index'])
                ->name('admin.lck.products.index');
            Route::get('products/create', [\App\Http\Controllers\Admin\LckProductController::class, 'create'])
                ->name('admin.lck.products.create');
            Route::post('products', [\App\Http\Controllers\Admin\LckProductController::class, 'store'])
                ->name('admin.lck.products.store');
            Route::get('products/{product}/edit', [\App\Http\Controllers\Admin\LckProductController::class, 'edit'])
                ->name('admin.lck.products.edit');
            Route::put('products/{product}', [\App\Http\Controllers\Admin\LckProductController::class, 'update'])
                ->name('admin.lck.products.update');
            Route::delete('products/{product}', [\App\Http\Controllers\Admin\LckProductController::class, 'destroy'])
                ->name('admin.lck.products.destroy');
            Route::post('products/{product}/toggle', [\App\Http\Controllers\Admin\LckProductController::class, 'toggleAvailability'])
                ->name('admin.lck.products.toggle');

            // Commercantes
            Route::get('commercants', [\App\Http\Controllers\Admin\LckCommerçantController::class, 'index'])
                ->name('admin.lck.commercants.index');
            Route::get('commercants/create', [\App\Http\Controllers\Admin\LckCommerçantController::class, 'create'])
                ->name('admin.lck.commercants.create');
            Route::post('commercants', [\App\Http\Controllers\Admin\LckCommerçantController::class, 'store'])
                ->name('admin.lck.commercants.store');
            Route::get('commercants/{commercant}/edit', [\App\Http\Controllers\Admin\LckCommerçantController::class, 'edit'])
                ->name('admin.lck.commercants.edit');
            Route::put('commercants/{commercant}', [\App\Http\Controllers\Admin\LckCommerçantController::class, 'update'])
                ->name('admin.lck.commercants.update');
            Route::delete('commercants/{commercant}', [\App\Http\Controllers\Admin\LckCommerçantController::class, 'destroy'])
                ->name('admin.lck.commercants.destroy');
            Route::post('commercants/{commercant}/toggle', [\App\Http\Controllers\Admin\LckCommerçantController::class, 'toggleActive'])
                ->name('admin.lck.commercants.toggle');
        });
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Panel Commercante — La Clé des Châteaux
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('commercant')->group(function () {
    // Auth (public)
    Route::get('/login', [CommercantAuthController::class, 'showLogin'])->name('commercant.login');
    Route::post('/login', [CommercantAuthController::class, 'login'])->name('commercant.login.post');
    Route::post('/logout', [CommercantAuthController::class, 'logout'])->name('commercant.logout');

    // Routes protégées par le middleware commercant
    Route::middleware(['commercant'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Commercant\DashboardController::class, 'index'])
            ->name('commercant.dashboard');

        // Commandes
        Route::get('/orders', [\App\Http\Controllers\Commercant\OrderController::class, 'index'])
            ->name('commercant.orders.index');
        Route::get('/orders/export', [\App\Http\Controllers\Commercant\OrderController::class, 'export'])
            ->name('commercant.orders.export');
        Route::get('/orders/{ref}', [\App\Http\Controllers\Commercant\OrderController::class, 'show'])
            ->name('commercant.orders.show');
        Route::post('/orders/{ref}/status', [\App\Http\Controllers\Commercant\OrderController::class, 'updateStatus'])
            ->name('commercant.orders.status');
        Route::delete('/orders/{ref}', [\App\Http\Controllers\Commercant\OrderController::class, 'destroy'])
            ->name('commercant.orders.destroy');
        Route::get('/orders/{ref}/print', [\App\Http\Controllers\Commercant\OrderController::class, 'printOrder'])
            ->name('commercant.orders.print');
        Route::post('/orders/{ref}/claim', [\App\Http\Controllers\Commercant\OrderController::class, 'claim'])
            ->name('commercant.orders.claim');
        Route::get('/pending-count', [\App\Http\Controllers\Commercant\OrderController::class, 'pendingCount'])
            ->name('commercant.pending-count');

        // Catalogue (caviste seulement)
        Route::get('/products', [\App\Http\Controllers\Commercant\ProductController::class, 'index'])
            ->name('commercant.products.index');
        Route::post('/products/{id}/stock', [\App\Http\Controllers\Commercant\ProductController::class, 'updateStock'])
            ->name('commercant.products.stock');
        Route::post('/products/{id}/toggle', [\App\Http\Controllers\Commercant\ProductController::class, 'toggle'])
            ->name('commercant.products.toggle');
    });
});
