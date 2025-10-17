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
        Schema::create('short_links', function (Blueprint $table) {
            $table->id();
            $table->string('code', 4)->unique(); // Ultra-short 4-char code
            $table->unsignedBigInteger('message_id'); // Links to messages table
            $table->unsignedInteger('clicks')->default(0); // Track clicks
            $table->timestamp('last_clicked_at')->nullable(); // Last click time
            $table->timestamp('expires_at')->nullable(); // Optional expiration
            $table->timestamps();

            // Indexes for performance
            $table->index('code');
            $table->index('message_id');
            
            // Foreign key
            $table->foreign('message_id')
                  ->references('id')
                  ->on('messages')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_links');
    }
};

