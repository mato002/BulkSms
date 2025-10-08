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
        if (Schema::hasTable('messages')) {
            return; // table already exists
        }
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('templates')->nullOnDelete();
            $table->string('channel'); // sms | whatsapp | email
            $table->string('provider')->nullable(); // e.g., twilio, infobip, smtp
            $table->string('sender')->nullable();
            $table->string('recipient'); // phone or email
            $table->string('subject')->nullable(); // email only
            $table->longText('body');
            $table->string('status')->default('queued'); // queued|sending|sent|delivered|failed
            $table->string('provider_message_id')->nullable();
            $table->decimal('cost', 10, 4)->default(0);
            $table->json('metadata')->nullable(); // arbitrary extra data
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('error_code')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'channel', 'status']);
            $table->index(['recipient']);
            $table->index(['scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
