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
            // Add price per unit column (cost of 1 SMS/WhatsApp unit in KSH)
            if (!Schema::hasColumn('clients', 'price_per_unit')) {
                $table->decimal('price_per_unit', 10, 4)->default(1.00)->after('balance');
            }
            
            // Add company_name column (keep sender_id for backward compatibility)
            if (!Schema::hasColumn('clients', 'company_name')) {
                $table->string('company_name')->nullable()->after('sender_id');
            }
        });
        
        // Copy sender_id values to company_name
        DB::statement('UPDATE clients SET company_name = sender_id WHERE company_name IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'price_per_unit')) {
                $table->dropColumn('price_per_unit');
            }
            
            if (Schema::hasColumn('clients', 'company_name')) {
                $table->dropColumn('company_name');
            }
        });
    }
};

