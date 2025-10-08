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
        Schema::table('messages', function (Blueprint $table) {
            $table->string('direction')->default('outbound')->after('channel'); // inbound|outbound
            $table->foreignId('conversation_id')->nullable()->after('client_id')->constrained('conversations')->nullOnDelete();
            $table->boolean('is_read')->default(false)->after('status');
            $table->timestamp('read_at')->nullable()->after('is_read');
            
            $table->index(['conversation_id', 'created_at']);
            $table->index(['direction', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['conversation_id']);
            $table->dropColumn(['direction', 'conversation_id', 'is_read', 'read_at']);
        });
    }
};
