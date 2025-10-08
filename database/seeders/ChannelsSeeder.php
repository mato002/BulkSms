<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChannelsSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('channels')) {
            return;
        }

        $now = now();
        $credentials = [
            'base_url' => 'https://api.onfonmedia.co.ke',
            'client_code' => 'e27847c1-a9fe-4eef-b60d-ddb291b175ab',
            'access_key' => 'VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=',
            'default_sender' => 'PRADY_TECH',
        ];

        $existing = DB::table('channels')
            ->where('client_id', 1)
            ->where('name', 'sms')
            ->first();

        if ($existing) {
            DB::table('channels')
                ->where('id', $existing->id)
                ->update([
                    'provider' => 'onfon',
                    'credentials' => json_encode($credentials),
                    'active' => 1,
                    'updated_at' => $now,
                ]);
        } else {
            DB::table('channels')->insert([
                'client_id' => 1,
                'name' => 'sms',
                'provider' => 'onfon',
                'credentials' => json_encode($credentials),
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}



