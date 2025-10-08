<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\Messaging\Contracts\MessageSender;
use App\Services\Messaging\Drivers\Sms\TwilioSmsSender;
use App\Services\Messaging\Drivers\Sms\OnfonSmsSender;
use App\Services\Messaging\Drivers\WhatsApp\CloudWhatsAppSender;
use App\Services\Messaging\Drivers\WhatsApp\UltraMessageSender;
use App\Services\Messaging\Drivers\Email\SmtpEmailSender;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind named providers for resolver usage
        $this->app->bind(MessageSender::class.'@twilio', function ($app, $params) {
            return new TwilioSmsSender($params['credentials'] ?? []);
        });
        $this->app->bind(MessageSender::class.'@whatsapp_cloud', function ($app, $params) {
            return new CloudWhatsAppSender($params['credentials'] ?? []);
        });
        $this->app->bind(MessageSender::class.'@ultramsg', function ($app, $params) {
            return new UltraMessageSender($params['credentials'] ?? []);
        });
        $this->app->bind(MessageSender::class.'@smtp', function ($app, $params) {
            return new SmtpEmailSender($params['credentials'] ?? []);
        });
        $this->app->bind(MessageSender::class.'@onfon', function ($app, $params) {
            return new OnfonSmsSender($params['credentials'] ?? []);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MySQL
        Schema::defaultStringLength(191);
    }
}
