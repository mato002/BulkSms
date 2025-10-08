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
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('phone', 20)->nullable()->after('avatar');
            $table->text('bio')->nullable()->after('phone');
            $table->string('timezone', 50)->nullable()->after('bio');
            $table->string('language', 10)->default('en')->after('timezone');
            $table->json('preferences')->nullable()->after('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'phone', 'bio', 'timezone', 'language', 'preferences']);
        });
    }
};
