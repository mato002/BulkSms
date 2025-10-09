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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('webhook_url', 255)->nullable()->after('settings');
            $table->string('webhook_secret', 100)->nullable()->after('webhook_url');
            $table->json('webhook_events')->nullable()->after('webhook_secret');
            $table->boolean('webhook_active')->default(false)->after('webhook_events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['webhook_url', 'webhook_secret', 'webhook_events', 'webhook_active']);
        });
    }
};

