<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clients')) {
            return;
        }

        DB::table('clients')->orderBy('id')->chunk(100, function ($clients) {
            foreach ($clients as $client) {
                $settings = json_decode($client->settings ?? '{}', true);
                if (!is_array($settings)) {
                    $settings = [];
                }

                $settings['append_reply_link'] = false;
                $settings['append_stop_footer'] = false;

                DB::table('clients')
                    ->where('id', $client->id)
                    ->update(['settings' => json_encode($settings)]);
            }
        });
    }

    public function down(): void
    {
        // No rollback — re-enabling reply links is done per client in admin.
    }
};
