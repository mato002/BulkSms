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
            // Add Onfon wallet fields
            if (!Schema::hasColumn('clients', 'onfon_balance')) {
                $table->decimal('onfon_balance', 10, 2)->default(0)->after('price_per_unit');
            }
            
            if (!Schema::hasColumn('clients', 'onfon_last_sync')) {
                $table->timestamp('onfon_last_sync')->nullable()->after('onfon_balance');
            }
            
            if (!Schema::hasColumn('clients', 'auto_sync_balance')) {
                $table->boolean('auto_sync_balance')->default(false)->after('onfon_last_sync');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'onfon_balance')) {
                $table->dropColumn('onfon_balance');
            }
            
            if (Schema::hasColumn('clients', 'onfon_last_sync')) {
                $table->dropColumn('onfon_last_sync');
            }
            
            if (Schema::hasColumn('clients', 'auto_sync_balance')) {
                $table->dropColumn('auto_sync_balance');
            }
        });
    }
};


