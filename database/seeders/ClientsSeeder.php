<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class ClientsSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('clients')) {
            return;
        }

        $exists = DB::table('clients')->where('id', 1)->first();
        if ($exists) {
            return;
        }

        DB::table('clients')->insert([
            'id' => 1,
            'name' => 'Default Client',
            'contact' => 'admin@example.com',
            'sender_id' => 'PRADY_TECH',
            'balance' => 0,
            'api_key' => Str::uuid()->toString(),
            'status' => true,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}



