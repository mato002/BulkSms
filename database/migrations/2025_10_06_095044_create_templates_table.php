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
        if (Schema::hasTable('templates')) {
            return;
        }
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('channel'); // sms | whatsapp | email
            $table->string('category')->nullable(); // e.g., transactional, marketing
            $table->string('subject')->nullable(); // email/whatsapp header
            $table->longText('body'); // may include placeholders like {{name}}
            $table->json('variables')->nullable();
            $table->boolean('approved')->default(false); // for whatsapp approvals
            $table->timestamps();

            $table->unique(['client_id', 'name', 'channel']);
            $table->index(['client_id', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
