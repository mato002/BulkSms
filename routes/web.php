<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\PublicReplyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminSettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public API Documentation (no authentication required)
Route::get('/api-documentation', function() {
    return view('api-documentation');
})->name('api.documentation');

// Public Pages (no authentication required)
Route::get('/documentation', function() {
    return view('pages.documentation');
})->name('documentation');

Route::get('/support', function() {
    return view('pages.support');
})->name('support');

Route::get('/privacy-policy', function() {
    return view('pages.privacy-policy');
})->name('privacy-policy');

Route::get('/terms-of-service', function() {
    return view('pages.terms-of-service');
})->name('terms-of-service');

// Tenant Registration Routes (for new businesses to sign up)
Route::get('/register', [App\Http\Controllers\TenantRegistrationController::class, 'showRegistration'])->name('tenant.register');
Route::post('/register', [App\Http\Controllers\TenantRegistrationController::class, 'register'])->name('tenant.register.submit');
Route::get('/registration-success', [App\Http\Controllers\TenantRegistrationController::class, 'showSuccess'])->name('tenant.registration.success');

// Short URL Redirect (no authentication required) - Ultra short!
Route::get('/x/{code}', [App\Http\Controllers\ShortLinkController::class, 'redirect'])->name('short.redirect');

// Public Reply Routes (no authentication required)
Route::get('/reply/{token}', [PublicReplyController::class, 'showReplyForm'])->name('public.reply');
Route::post('/reply/{token}', [PublicReplyController::class, 'submitReply'])->name('public.reply.submit');

// WhatsApp Webhook Routes (no authentication required)
Route::get('/webhook/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handle'])->name('whatsapp.webhook.handle');

// Authentication Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Password Reset Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Manual password reset page (for EmailJS flow)
Route::get('/reset-password-manual', function(Request $request) {
    return view('auth.manual-reset', [
        'email' => $request->get('email', ''),
        'token' => $request->get('token', 'manual-reset')
    ]);
})->name('password.manual');

// Manual password reset handler (bypasses Laravel's token system)
Route::post('/reset-password-manual', function(Request $request) {
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // Find user by email
    $user = \App\Models\User::where('email', $request->email)->first();
    
    if (!$user) {
        // Log failed password reset attempt for admin notification
        \Illuminate\Support\Facades\Log::warning('Failed Password Reset Attempt', [
            'attempted_email' => $request->email,
            'attempt_time' => now()->format('Y-m-d H:i:s'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reason' => 'Email not found in system'
        ]);
        
        return back()->withErrors(['email' => 'User not found.']);
    }

    // Update password directly
    $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
    $user->save();

    // Log password reset for admin notification
    \Illuminate\Support\Facades\Log::info('Password Reset Success', [
        'user_email' => $user->email,
        'user_name' => $user->name,
        'reset_time' => now()->format('Y-m-d H:i:s'),
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent()
    ]);

    return redirect('/login')->with('status', 'Password reset successfully! You can now login with your new password.');
})->name('password.manual.update');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Landing Page (for guests) / Dashboard (for authenticated users)
Route::get('/', function() {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

// Admin notification page (for monitoring password resets) - ADMIN ONLY
Route::get('/admin/security-logs', function() {
    $logFile = storage_path('logs/laravel.log');
    $logs = [];
    
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $lines = explode("\n", $logContent);
        
        // Get last 100 lines and filter for password reset related logs
        $recentLines = array_slice($lines, -100);
        foreach ($recentLines as $line) {
            if (strpos($line, 'Password Reset') !== false || strpos($line, 'Failed Password Reset') !== false) {
                $logs[] = $line;
            }
        }
        $logs = array_reverse($logs); // Most recent first
    }
    
    return view('admin.security-logs', compact('logs'));
})->name('admin.security-logs')->middleware(['auth', 'admin']);

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function() {
        $user = auth()->user();
        // Redirect tenants to their dashboard, admins to main dashboard
        if ($user->client_id && $user->client_id !== 1) {
            return redirect()->route('tenant.dashboard');
        }
        return app(DashboardController::class)->index();
    })->name('dashboard');

    // Search (Available to all authenticated users)
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'showResults'])->name('search.results');
    Route::get('/api/search', [App\Http\Controllers\SearchController::class, 'search'])->name('search.api');

    // ADMIN ONLY ROUTES - Require admin role
    Route::middleware('admin')->group(function () {
        // API Monitor (Admin only)
        Route::get('/api-monitor', [App\Http\Controllers\ApiMonitorController::class, 'index'])->name('api-monitor.index');
        Route::get('/api-monitor/{id}', [App\Http\Controllers\ApiMonitorController::class, 'show'])->name('api-monitor.show');
        Route::get('/api-monitor-stats', [App\Http\Controllers\ApiMonitorController::class, 'statistics'])->name('api-monitor.statistics');
        Route::get('/api-monitor-activity', [App\Http\Controllers\ApiMonitorController::class, 'activity'])->name('api-monitor.activity');
        Route::post('/api-monitor/cleanup', [App\Http\Controllers\ApiMonitorController::class, 'cleanup'])->name('api-monitor.cleanup');
    });

    // Shared routes (for both admin and tenants)
    Route::post('contacts/bulk-action', [ContactController::class, 'bulkAction'])->name('contacts.bulk-action');
    Route::resource('contacts', ContactController::class);
    Route::post('contacts/import', [ContactController::class, 'import'])->name('contacts.import');

    Route::resource('templates', TemplateController::class);

    Route::resource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');

    // Tags Management (Shared - all authenticated users)
    Route::prefix('tags')->name('tags.')->group(function () {
        Route::get('/', [App\Http\Controllers\TagController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\TagController::class, 'store'])->name('store');
        Route::put('/{tag}', [App\Http\Controllers\TagController::class, 'update'])->name('update');
        Route::delete('/{tag}', [App\Http\Controllers\TagController::class, 'destroy'])->name('destroy');
        Route::get('/{tag}/contacts', [App\Http\Controllers\TagController::class, 'getContacts'])->name('contacts');
        Route::post('/contact/{contact}/add', [App\Http\Controllers\TagController::class, 'addToContact'])->name('addToContact');
        Route::delete('/contact/{contact}/remove/{tag}', [App\Http\Controllers\TagController::class, 'removeFromContact'])->name('removeFromContact');
    });

    // Notifications (Shared - all authenticated users)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/settings', [App\Http\Controllers\NotificationController::class, 'settings'])->name('settings');
        Route::put('/settings', [App\Http\Controllers\NotificationController::class, 'updateSettings'])->name('update-settings');
        Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/list', [App\Http\Controllers\NotificationController::class, 'getNotifications'])->name('list');
        Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'delete'])->name('delete');
    });

    // Messages (Shared - all authenticated users)
    Route::resource('messages', MessageController::class)->only(['index', 'show']);
    Route::get('/messages-all', [MessageController::class, 'allMessages'])->name('messages.all');

    // Inbox (Conversations/Chat) - Shared
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::get('/inbox/start/{contact}', [InboxController::class, 'startWithContact'])->name('inbox.start');
    Route::get('/inbox/{conversation}', [InboxController::class, 'show'])->name('inbox.show');
    Route::post('/inbox/{conversation}/reply', [InboxController::class, 'reply'])->name('inbox.reply');
    Route::post('/inbox/{conversation}/status', [InboxController::class, 'updateStatus'])->name('inbox.updateStatus');

    // Analytics (Shared)
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::put('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');

    // ADMIN ONLY: Settings & Wallet Management
    Route::middleware('admin')->group(function () {
        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/client', [SettingsController::class, 'updateClient'])->name('settings.client.update');
        Route::post('/settings/channel/create', [SettingsController::class, 'createChannel'])->name('settings.channel.create');
        Route::post('/settings/channel/{channel}', [SettingsController::class, 'updateChannel'])->name('settings.channel.update');
        Route::post('/settings/regenerate-api-key', [SettingsController::class, 'regenerateApiKey'])->name('settings.regenerate-api-key');
        
        // Admin Settings (consolidated into main settings)
        Route::post('/settings/admin', [SettingsController::class, 'updateAdminSettings'])->name('settings.admin.update');
        Route::post('/settings/phone/add', [SettingsController::class, 'addPhoneNumber'])->name('settings.phone.add');
        Route::get('/settings/phone/{id}/toggle', [SettingsController::class, 'togglePhoneNumber'])->name('settings.phone.toggle');
        Route::delete('/settings/phone/{id}/delete', [SettingsController::class, 'deletePhoneNumber'])->name('settings.phone.delete');

        // Wallet & Top-up Routes
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [App\Http\Controllers\WalletController::class, 'index'])->name('index');
            Route::get('/topup', [App\Http\Controllers\WalletController::class, 'topup'])->name('topup');
            Route::post('/topup', [App\Http\Controllers\WalletController::class, 'initiateTopup'])->name('topup.initiate');
            Route::get('/status/{transactionRef}', [App\Http\Controllers\WalletController::class, 'status'])->name('status');
            Route::get('/export', [App\Http\Controllers\WalletController::class, 'exportTransactions'])->name('export');
            
            // Onfon Balance Management
            Route::post('/onfon/sync', [App\Http\Controllers\WalletController::class, 'syncOnfonBalance'])->name('onfon.sync');
            Route::get('/onfon/balance', [App\Http\Controllers\WalletController::class, 'getOnfonBalance'])->name('onfon.balance');
        });
    });

// Public API endpoints (accessible without wallet prefix) for dashboard real-time updates
Route::prefix('api')->name('api.')->group(function () {
    Route::post('/onfon/balance/refresh', [App\Http\Controllers\WalletController::class, 'refreshSystemBalance'])->name('onfon.balance.refresh');
});

    // ADMIN ONLY: Client switching & API Documentation
    Route::middleware('admin')->group(function () {
        // Client switching for debugging (temporary)
        Route::get('/switch-client/{clientId}', function($clientId) {
            session(['client_id' => $clientId]);
            return redirect()->back()->with('success', 'Switched to client ' . $clientId);
        })->name('switch.client');

        // API Documentation & Developer Portal
        Route::get('/api-docs', [App\Http\Controllers\ApiDocumentationController::class, 'index'])->name('api.docs');

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
            Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'delete'])->name('delete');
        });

        // Channel Routes (Admin only)
        Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
            Route::get('/', [WhatsAppController::class, 'index'])->name('index');
            Route::get('/configure', [WhatsAppController::class, 'configure'])->name('configure');
            Route::post('/configure', [WhatsAppController::class, 'saveConfiguration'])->name('configure.save');
            Route::post('/test-connection', [WhatsAppController::class, 'testConnection'])->name('test');
            Route::post('/send', [WhatsAppController::class, 'sendMessage'])->name('send');
            Route::post('/send-interactive', [WhatsAppController::class, 'sendInteractive'])->name('send.interactive');
            Route::post('/upload-media', [WhatsAppController::class, 'uploadMedia'])->name('upload.media');
            Route::post('/fetch-templates', [WhatsAppController::class, 'fetchTemplates'])->name('templates.fetch');
        });

        Route::prefix('sms')->name('sms.')->group(function () {
            Route::get('/', [App\Http\Controllers\SmsController::class, 'index'])->name('index');
            Route::post('/test-connection', [App\Http\Controllers\SmsController::class, 'testConnection'])->name('test');
            Route::post('/send', [App\Http\Controllers\SmsController::class, 'sendMessage'])->name('send');
        });

        Route::prefix('email')->name('email.')->group(function () {
            Route::get('/', [App\Http\Controllers\EmailController::class, 'index'])->name('index');
            Route::post('/test-connection', [App\Http\Controllers\EmailController::class, 'testConnection'])->name('test');
            Route::post('/send', [App\Http\Controllers\EmailController::class, 'sendMessage'])->name('send');
        });
    });

    // Tenant Dashboard Routes (Authenticated tenants only). Apply tenant.active to gate features until activation
    Route::prefix('tenant')->name('tenant.')->middleware(['tenant.active'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\TenantDashboardController::class, 'index'])->name('dashboard');
               Route::get('/onboarding', [App\Http\Controllers\TenantDashboardController::class, 'onboarding'])->name('onboarding');
        Route::get('/profile', [App\Http\Controllers\TenantDashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [App\Http\Controllers\TenantDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::get('/billing', [App\Http\Controllers\TenantDashboardController::class, 'billing'])->name('billing');
        Route::get('/payment', [App\Http\Controllers\PaymentController::class, 'index'])->name('payment');
        
        // Payment processing routes
        Route::post('/payments/mpesa/initiate', [App\Http\Controllers\PaymentController::class, 'initiateMpesaPayment'])->name('payments.mpesa.initiate');
        Route::post('/payments/stripe/create-intent', [App\Http\Controllers\PaymentController::class, 'createStripePaymentIntent'])->name('payments.stripe.create-intent');
        Route::get('/payments/stripe/publishable-key', [App\Http\Controllers\PaymentController::class, 'getStripePublishableKey'])->name('payments.stripe.publishable-key');
        Route::get('/payments/status/{transactionId}', [App\Http\Controllers\PaymentController::class, 'checkPaymentStatus'])->name('payments.status');
        Route::get('/payments/transactions', [App\Http\Controllers\PaymentController::class, 'getTransactionHistory'])->name('payments.transactions');
        Route::get('/api-docs', [App\Http\Controllers\TenantDashboardController::class, 'apiDocs'])->name('api-docs');
        Route::get('/notifications', [App\Http\Controllers\TenantDashboardController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/{id}/read', [App\Http\Controllers\TenantDashboardController::class, 'markNotificationAsRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [App\Http\Controllers\TenantDashboardController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::delete('/notifications/{id}', [App\Http\Controllers\TenantDashboardController::class, 'deleteNotification'])->name('notifications.delete');
        Route::delete('/notifications/clear-all', [App\Http\Controllers\TenantDashboardController::class, 'clearAllNotifications'])->name('notifications.clear-all');
        Route::get('/notifications/unread-count', [App\Http\Controllers\TenantDashboardController::class, 'getUnreadCount'])->name('notifications.unread-count');
    });

    // Admin Sender/Tenant Management Routes (Admin only)
    Route::prefix('admin/senders')->name('admin.senders.')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/create', [AdminController::class, 'create'])->name('create');
        Route::post('/', [AdminController::class, 'store'])->name('store');
        Route::get('/{id}', [AdminController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AdminController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/regenerate-api-key', [AdminController::class, 'regenerateApiKey'])->name('regenerate-api-key');
        Route::post('/{id}/update-balance', [AdminController::class, 'updateBalance'])->name('update-balance');
        Route::patch('/{id}/toggle-status', [AdminController::class, 'toggleStatus'])->name('toggle-status');
        
        // Onfon Wallet Management Routes
        Route::post('/{id}/onfon-credentials', [AdminController::class, 'updateOnfonCredentials'])->name('onfon-credentials');
        Route::post('/{id}/sync-onfon-balance', [AdminController::class, 'syncOnfonBalance'])->name('sync-onfon-balance');
        Route::get('/{id}/onfon-balance', [AdminController::class, 'getOnfonBalance'])->name('onfon-balance');
        Route::post('/{id}/test-onfon', [AdminController::class, 'testOnfonConnection'])->name('test-onfon');
        Route::get('/{id}/onfon-transactions', [AdminController::class, 'getOnfonTransactions'])->name('onfon-transactions');
    });

    // Admin User Management Routes (Admin only)
    Route::prefix('admin/admins')->name('admin.admins.')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'admins'])->name('index');
        Route::get('/create', [AdminController::class, 'createAdmin'])->name('create');
        Route::post('/', [AdminController::class, 'storeAdmin'])->name('store');
        Route::get('/{id}/edit', [AdminController::class, 'editAdmin'])->name('edit');
        Route::put('/{id}', [AdminController::class, 'updateAdmin'])->name('update');
        Route::delete('/{id}', [AdminController::class, 'destroyAdmin'])->name('destroy');
    });

    // ADMIN ONLY: Senders Page
    Route::middleware('admin')->get('/senders', function() {
        return view('senders.index');
    })->name('senders.index');
});


