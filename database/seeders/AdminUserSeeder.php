<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $existing = DB::table('users')->where('email', 'admin@bulksms.local')->first();
        
        if ($existing) {
            return; // Already exists
        }

        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@bulksms.local',
            'password' => Hash::make('password'),
            'client_id' => 1,
            'role' => 'admin',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

