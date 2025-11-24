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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('transaction_type')->default('topup'); // topup, refund, deduction
            $table->string('payment_method'); // mpesa, stripe, bank_transfer, manual
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('status')->default('pending'); // pending, completed, failed, canceled
            $table->string('reference')->unique(); // Unique transaction reference
            $table->string('external_reference')->nullable(); // M-Pesa receipt, Stripe payment intent, etc.
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            $table->json('payment_details')->nullable(); // Payment method specific details
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['client_id', 'status']);
            $table->index(['payment_method', 'status']);
            $table->index('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
