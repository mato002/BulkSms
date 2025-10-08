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
        Schema::table('contacts', function (Blueprint $table) {
            $table->timestamp('last_message_at')->nullable()->after('custom_fields');
            $table->integer('total_messages')->default(0)->after('last_message_at');
            $table->integer('unread_messages')->default(0)->after('total_messages');
            $table->text('notes')->nullable()->after('unread_messages');
            $table->json('tags')->nullable()->after('notes');
            $table->boolean('opted_in')->default(true)->after('tags');
            
            $table->index(['last_message_at']);
            $table->index(['opted_in']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['last_message_at', 'total_messages', 'unread_messages', 'notes', 'tags', 'opted_in']);
        });
    }
};
