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
            // Only add template_id if it doesn't exist
            if (!Schema::hasColumn('campaigns', 'template_id')) {
                $table->foreignId('template_id')->nullable()->after('channel')->constrained('templates')->nullOnDelete();
            }
            
            // Update existing campaigns to have 'sms' as default channel if null
            if (Schema::hasColumn('campaigns', 'channel')) {
                DB::statement("UPDATE campaigns SET channel = 'sms' WHERE channel IS NULL");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'template_id')) {
                $table->dropForeign(['template_id']);
                $table->dropColumn('template_id');
            }
        });
    }
};

