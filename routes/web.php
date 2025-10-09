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

// Public Reply Routes (no authentication required)
Route::get('/reply/{token}', [PublicReplyController::class, 'showReplyForm'])->name('public.reply.form');
Route::post('/reply/{token}', [PublicReplyController::class, 'submitReply'])->name('public.reply.submit');

// WhatsApp Webhook Routes (no authentication required)
Route::get('/webhook/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handle'])->name('whatsapp.webhook.handle');

// Authentication Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
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

// Admin notification page (for monitoring password resets)
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
})->name('admin.security-logs')->middleware('auth');

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Search
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'showResults'])->name('search.results');
    Route::get('/api/search', [App\Http\Controllers\SearchController::class, 'search'])->name('search.api');

    Route::resource('contacts', ContactController::class);
    Route::post('contacts/import', [ContactController::class, 'import'])->name('contacts.import');

    Route::resource('templates', TemplateController::class);

    Route::resource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');

    Route::resource('messages', MessageController::class)->only(['index', 'show']);

    // Inbox (Conversations/Chat)
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::get('/inbox/start/{contact}', [InboxController::class, 'startWithContact'])->name('inbox.start');
    Route::get('/inbox/{conversation}', [InboxController::class, 'show'])->name('inbox.show');
    Route::post('/inbox/{conversation}/reply', [InboxController::class, 'reply'])->name('inbox.reply');
    Route::post('/inbox/{conversation}/status', [InboxController::class, 'updateStatus'])->name('inbox.updateStatus');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::put('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/client', [SettingsController::class, 'updateClient'])->name('settings.client.update');
    Route::post('/settings/channel/{channel}', [SettingsController::class, 'updateChannel'])->name('settings.channel.update');
    Route::post('/settings/regenerate-api-key', [SettingsController::class, 'regenerateApiKey'])->name('settings.regenerate-api-key');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });

    // WhatsApp Routes
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

    // Senders Page (For all users)
    Route::get('/senders', function() {
        return view('senders.index');
    })->name('senders.index');
});


