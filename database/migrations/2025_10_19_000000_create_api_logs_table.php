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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('api_key', 100)->nullable();
            $table->string('endpoint', 255);
            $table->string('method', 10);
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            $table->integer('response_status')->nullable();
            $table->json('response_body')->nullable();
            $table->integer('response_time_ms')->nullable(); // Response time in milliseconds
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('client_id');
            $table->index('endpoint');
            $table->index('method');
            $table->index('success');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};

