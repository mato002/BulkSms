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
            // Add company_name if it doesn't exist
            if (!Schema::hasColumn('clients', 'company_name')) {
                $table->string('company_name')->nullable()->after('sender_id');
            }
            
            // Add price_per_unit if it doesn't exist
            if (!Schema::hasColumn('clients', 'price_per_unit')) {
                $table->decimal('price_per_unit', 10, 4)->default(1.00)->after('balance');
            }
            
            // Add Onfon-specific fields
            if (!Schema::hasColumn('clients', 'onfon_balance')) {
                $table->decimal('onfon_balance', 10, 2)->nullable()->after('balance')
                    ->comment('Last synced balance from Onfon Media');
            }
            
            if (!Schema::hasColumn('clients', 'onfon_last_sync')) {
                $table->timestamp('onfon_last_sync')->nullable()
                    ->comment('Last time balance was synced from Onfon');
            }
            
            if (!Schema::hasColumn('clients', 'auto_sync_balance')) {
                $table->boolean('auto_sync_balance')->default(false)
                    ->comment('Automatically sync balance before sending');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'price_per_unit',
                'onfon_balance',
                'onfon_last_sync',
                'auto_sync_balance'
            ]);
        });
    }
};

