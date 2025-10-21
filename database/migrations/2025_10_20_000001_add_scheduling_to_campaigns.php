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
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('campaigns', 'is_scheduled')) {
                $table->boolean('is_scheduled')->default(false)->after('scheduled_at');
            }
            if (!Schema::hasColumn('campaigns', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('is_scheduled');
            }
            if (!Schema::hasColumn('campaigns', 'recurrence')) {
                $table->string('recurrence')->nullable()->after('processed_at'); // daily, weekly, monthly
            }
            if (!Schema::hasColumn('campaigns', 'recurrence_settings')) {
                $table->json('recurrence_settings')->nullable()->after('recurrence');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'scheduled_at', 
                'is_scheduled', 
                'processed_at',
                'recurrence',
                'recurrence_settings'
            ]);
        });
    }
};

