<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminSetting;

class AdminSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Low balance threshold setting
        AdminSetting::set(
            'low_balance_threshold',
            1000,
            'number',
            'Minimum Onfon balance units before sending low balance alert'
        );

        // Admin phone number for alerts
        AdminSetting::set(
            'admin_phone',
            '254722295194',
            'string',
            'Phone number to receive low balance SMS alerts'
        );

        // Auto-refresh interval (in minutes)
        AdminSetting::set(
            'balance_refresh_interval',
            60,
            'number',
            'How often to refresh Onfon balance (in minutes)'
        );
    }
}
