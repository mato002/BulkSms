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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['credit', 'debit', 'refund'])->default('credit');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50)->nullable(); // mpesa, bank, manual, stripe
            $table->string('payment_phone', 20)->nullable(); // M-Pesa phone number
            $table->string('transaction_ref', 100)->nullable(); // Internal reference
            $table->string('mpesa_receipt', 100)->nullable(); // M-Pesa receipt code
            $table->string('checkout_request_id', 100)->nullable(); // M-Pesa checkout request ID
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['client_id', 'status']);
            $table->index(['client_id', 'created_at']);
            $table->index('transaction_ref');
            $table->index('checkout_request_id');
            $table->index('mpesa_receipt');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};

