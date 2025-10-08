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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->string('channel'); // sms|whatsapp|email
            $table->string('contact_identifier'); // phone number or email
            $table->text('last_message_preview')->nullable();
            $table->string('last_message_direction')->nullable(); // inbound|outbound
            $table->timestamp('last_message_at')->nullable();
            $table->integer('unread_count')->default(0);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('open'); // open|resolved|archived
            $table->timestamps();

            $table->unique(['client_id', 'contact_id', 'channel']);
            $table->index(['client_id', 'status', 'last_message_at']);
            $table->index(['assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
