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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // campaign_completed, message_failed, system_alert, etc.
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable(); // bootstrap icon class
            $table->string('color')->default('primary'); // primary, success, danger, warning, info
            $table->string('link')->nullable(); // URL to related resource
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable(); // extra data
            $table->timestamps();
            
            $table->index(['client_id', 'is_read', 'created_at']);
            $table->index(['user_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
