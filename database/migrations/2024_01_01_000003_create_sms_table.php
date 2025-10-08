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
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('recipient');
            $table->text('message');
            $table->string('sender_id');
            $table->string('status')->default('pending');
            $table->string('message_id')->nullable();
            $table->decimal('cost', 8, 2)->default(0.75);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
            
            $table->index(['client_id', 'status']);
            $table->index(['sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms');
    }
};
