<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SmsController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Bulk SMS API is running',
        'timestamp' => now()
    ]);
});

// API routes with authentication
Route::middleware(['api.auth'])->group(function () {
    
    // Company-specific routes
    Route::prefix('{company_id}')->middleware(['company.auth', 'tier.rate.limit'])->group(function () {
        
        // SMS endpoints
        Route::prefix('sms')->group(function () {
            Route::post('/send', [SmsController::class, 'send']);
            Route::get('/status/{id}', [SmsController::class, 'status']);
            Route::get('/history', [SmsController::class, 'history']);
            Route::get('/statistics', [SmsController::class, 'statistics']);
        });

        // Unified messaging endpoint (sms/whatsapp/email)
        Route::post('/messages/send', [MessageController::class, 'send']);
        
        // Contact endpoints
        Route::prefix('contacts')->group(function () {
            Route::get('/', [ContactController::class, 'index']);
            Route::post('/', [ContactController::class, 'store']);
            Route::put('/{id}', [ContactController::class, 'update']);
            Route::delete('/{id}', [ContactController::class, 'destroy']);
            Route::post('/bulk-import', [ContactController::class, 'bulkImport']);
        });
        
        // Campaign endpoints
        Route::prefix('campaigns')->group(function () {
            Route::get('/', [CampaignController::class, 'index']);
            Route::post('/', [CampaignController::class, 'store']);
            Route::put('/{id}', [CampaignController::class, 'update']);
            Route::delete('/{id}', [CampaignController::class, 'destroy']);
            Route::post('/{id}/send', [CampaignController::class, 'send']);
            Route::get('/{id}/statistics', [CampaignController::class, 'statistics']);
        });
        
        // Client endpoints
        Route::prefix('client')->group(function () {
            Route::get('/profile', [ClientController::class, 'profile']);
            Route::get('/balance', [ClientController::class, 'balance']);
            Route::get('/statistics', [ClientController::class, 'statistics']);
        });

        // Analytics endpoints
        Route::prefix('analytics')->group(function () {
            Route::get('/summary', [\App\Http\Controllers\Api\AnalyticsController::class, 'summary']);
            Route::get('/daily', [\App\Http\Controllers\Api\AnalyticsController::class, 'daily']);
            Route::get('/monthly', [\App\Http\Controllers\Api\AnalyticsController::class, 'monthly']);
            Route::get('/by-channel', [\App\Http\Controllers\Api\AnalyticsController::class, 'byChannel']);
            Route::get('/wallet', [\App\Http\Controllers\Api\AnalyticsController::class, 'wallet']);
        });

        // Wallet endpoints (Onfon integration)
        Route::prefix('wallet')->group(function () {
            Route::get('/balance', [\App\Http\Controllers\Api\WalletController::class, 'balance']);
            Route::post('/sync', [\App\Http\Controllers\Api\WalletController::class, 'sync']);
            Route::post('/test-connection', [\App\Http\Controllers\Api\WalletController::class, 'testConnection']);
            Route::get('/transactions', [\App\Http\Controllers\Api\TopupController::class, 'getTransactions']);
            Route::get('/transactions/export', [\App\Http\Controllers\Api\TopupController::class, 'exportTransactionsCSV']);
            Route::post('/check-sufficient', [\App\Http\Controllers\Api\TopupController::class, 'checkSufficientBalance']);
            
            // Top-up endpoints
            Route::post('/topup', [\App\Http\Controllers\Api\TopupController::class, 'initiateTopup']);
            Route::get('/topup/{transaction_id}', [\App\Http\Controllers\Api\TopupController::class, 'checkTopupStatus']);
        });
    });
});

// Webhooks (no auth - providers will call these)
Route::post('/webhooks/onfon/inbound', [WebhookController::class, 'onfonInbound']); // Inbound SMS
Route::post('/webhooks/onfon/dlr', [WebhookController::class, 'onfonDlr']); // Delivery reports
Route::post('/webhooks/whatsapp', [WebhookController::class, 'whatsappWebhook']);
Route::post('/webhooks/email', [WebhookController::class, 'emailWebhook']);

// M-Pesa Webhooks
Route::post('/webhooks/mpesa/callback', [\App\Http\Controllers\MpesaWebhookController::class, 'callback']);
Route::post('/webhooks/mpesa/timeout', [\App\Http\Controllers\MpesaWebhookController::class, 'timeout']);

// Local test route (no auth) to validate send flow quickly
if (app()->environment('local')) {
    Route::post('/_test/messages/send', [MessageController::class, 'send'])
        ->withoutMiddleware(['api.auth', 'company.auth']);
}

// Admin routes (for managing all companies)
Route::middleware(['api.auth', 'admin'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/clients', [ClientController::class, 'index']);
        Route::post('/clients', [ClientController::class, 'store']);
        Route::put('/clients/{id}', [ClientController::class, 'update']);
        Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
        Route::get('/statistics', [ClientController::class, 'adminStatistics']);
    });
});
