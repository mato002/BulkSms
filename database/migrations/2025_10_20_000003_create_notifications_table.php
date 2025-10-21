<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type'); // LowBalanceNotification, FailedDeliveryNotification, etc.
                $table->morphs('notifiable'); // User or Client
                $table->text('data'); // JSON data
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                
                $table->index(['notifiable_type', 'notifiable_id']);
            });
        }

        if (!Schema::hasTable('notification_settings')) {
            Schema::create('notification_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                
                // Alert types
                $table->boolean('low_balance_enabled')->default(true);
                $table->decimal('low_balance_threshold', 10, 2)->default(100);
                
                $table->boolean('failed_delivery_enabled')->default(true);
                $table->integer('failed_delivery_threshold')->default(5); // alert after X failures
                
                $table->boolean('daily_summary_enabled')->default(false);
                $table->time('daily_summary_time')->default('09:00:00');
                
                $table->boolean('weekly_summary_enabled')->default(false);
                $table->string('weekly_summary_day')->default('monday');
                
                $table->boolean('campaign_complete_enabled')->default(true);
                $table->boolean('large_campaign_warning_enabled')->default(true);
                $table->integer('large_campaign_threshold')->default(1000);
                
                // Notification channels
                $table->boolean('notify_via_email')->default(true);
                $table->boolean('notify_via_sms')->default(false);
                $table->boolean('notify_via_browser')->default(true);
                
                $table->timestamps();
                
                $table->index('client_id');
                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
        Schema::dropIfExists('notifications');
    }
};

