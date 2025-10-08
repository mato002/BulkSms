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
        if (Schema::hasTable('channels')) {
            return;
        }
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name'); // sms | whatsapp | email
            $table->string('provider'); // e.g., twilio, whatsapp_cloud, smtp
            $table->json('credentials'); // encrypted json of keys/secrets
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['client_id', 'name', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
