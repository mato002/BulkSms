<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContactsSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('contacts')) {
            return;
        }

        // Clear existing contacts to reseed fresh data
        // Disable foreign key checks temporarily for MySQL
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('contacts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ensure we have at least one client
        $clientId = DB::table('clients')->value('id');
        if (!$clientId) {
            $this->command->error('No clients found. Please run ClientsSeeder first.');
            return;
        }

        $contacts = [
            [
                'client_id' => $clientId,
                'name' => 'John Doe',
                'contact' => '+254712345678',
                'department' => 'Sales',
                'custom_fields' => json_encode([
                    'position' => 'Sales Manager',
                    'location' => 'Nairobi',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(2),
                'total_messages' => 15,
                'unread_messages' => 2,
                'notes' => 'Key client contact. Prefers morning communication.',
                'tags' => json_encode(['VIP', 'Sales', 'Active']),
                'opted_in' => true,
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subDays(2),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Jane Smith',
                'contact' => '+254723456789',
                'department' => 'Marketing',
                'custom_fields' => json_encode([
                    'position' => 'Marketing Director',
                    'location' => 'Mombasa',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(5),
                'total_messages' => 8,
                'unread_messages' => 0,
                'notes' => 'Interested in monthly newsletters.',
                'tags' => json_encode(['Marketing', 'Newsletter']),
                'opted_in' => true,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subDays(5),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Michael Johnson',
                'contact' => '+254734567890',
                'department' => 'IT',
                'custom_fields' => json_encode([
                    'position' => 'IT Manager',
                    'location' => 'Nairobi',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subHours(3),
                'total_messages' => 25,
                'unread_messages' => 1,
                'notes' => 'Technical contact for system updates.',
                'tags' => json_encode(['Technical', 'IT', 'Support']),
                'opted_in' => true,
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subHours(3),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Sarah Williams',
                'contact' => '+254745678901',
                'department' => 'HR',
                'custom_fields' => json_encode([
                    'position' => 'HR Officer',
                    'location' => 'Kisumu',
                    'preferred_language' => 'Swahili'
                ]),
                'last_message_at' => now()->subDays(1),
                'total_messages' => 12,
                'unread_messages' => 3,
                'notes' => 'Handles employee communications.',
                'tags' => json_encode(['HR', 'Employee Relations']),
                'opted_in' => true,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subDays(1),
            ],
            [
                'client_id' => $clientId,
                'name' => 'David Brown',
                'contact' => '+254756789012',
                'department' => 'Finance',
                'custom_fields' => json_encode([
                    'position' => 'Finance Director',
                    'location' => 'Nairobi',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(7),
                'total_messages' => 5,
                'unread_messages' => 0,
                'notes' => 'Monthly billing contact.',
                'tags' => json_encode(['Finance', 'Billing']),
                'opted_in' => true,
                'created_at' => now()->subMonths(5),
                'updated_at' => now()->subDays(7),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Emily Davis',
                'contact' => '+254767890123',
                'department' => 'Sales',
                'custom_fields' => json_encode([
                    'position' => 'Sales Representative',
                    'location' => 'Eldoret',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(3),
                'total_messages' => 18,
                'unread_messages' => 0,
                'notes' => 'Very responsive. Prefers SMS over email.',
                'tags' => json_encode(['Sales', 'Active', 'SMS Preferred']),
                'opted_in' => true,
                'created_at' => now()->subMonths(1),
                'updated_at' => now()->subDays(3),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Robert Wilson',
                'contact' => '+254778901234',
                'department' => 'Operations',
                'custom_fields' => json_encode([
                    'position' => 'Operations Manager',
                    'location' => 'Nakuru',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(4),
                'total_messages' => 10,
                'unread_messages' => 1,
                'notes' => 'Coordinates logistics and operations.',
                'tags' => json_encode(['Operations', 'Logistics']),
                'opted_in' => true,
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subDays(4),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Lisa Anderson',
                'contact' => '+254789012345',
                'department' => 'Customer Service',
                'custom_fields' => json_encode([
                    'position' => 'Customer Service Lead',
                    'location' => 'Nairobi',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subHours(6),
                'total_messages' => 32,
                'unread_messages' => 5,
                'notes' => 'Main point of contact for customer issues.',
                'tags' => json_encode(['Customer Service', 'Support', 'Active']),
                'opted_in' => true,
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subHours(6),
            ],
            [
                'client_id' => $clientId,
                'name' => 'James Taylor',
                'contact' => '+254790123456',
                'department' => 'IT',
                'custom_fields' => json_encode([
                    'position' => 'System Administrator',
                    'location' => 'Nairobi',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(10),
                'total_messages' => 7,
                'unread_messages' => 0,
                'notes' => 'Handles server and network issues.',
                'tags' => json_encode(['IT', 'Technical', 'Infrastructure']),
                'opted_in' => true,
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subDays(10),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Mary Martinez',
                'contact' => '+254701234567',
                'department' => 'Marketing',
                'custom_fields' => json_encode([
                    'position' => 'Social Media Manager',
                    'location' => 'Mombasa',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(1),
                'total_messages' => 14,
                'unread_messages' => 2,
                'notes' => 'Manages social media campaigns.',
                'tags' => json_encode(['Marketing', 'Social Media', 'Active']),
                'opted_in' => true,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subDays(1),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Thomas Garcia',
                'contact' => '+254712345670',
                'department' => 'Sales',
                'custom_fields' => json_encode([
                    'position' => 'Regional Sales Manager',
                    'location' => 'Kisumu',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(6),
                'total_messages' => 11,
                'unread_messages' => 0,
                'notes' => 'Covers Western Kenya region.',
                'tags' => json_encode(['Sales', 'Regional']),
                'opted_in' => true,
                'created_at' => now()->subMonths(5),
                'updated_at' => now()->subDays(6),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Patricia Rodriguez',
                'contact' => '+254723456780',
                'department' => 'HR',
                'custom_fields' => json_encode([
                    'position' => 'Recruitment Manager',
                    'location' => 'Nairobi',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(8),
                'total_messages' => 9,
                'unread_messages' => 0,
                'notes' => 'Handles recruitment and onboarding.',
                'tags' => json_encode(['HR', 'Recruitment']),
                'opted_in' => true,
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subDays(8),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Christopher Lee',
                'contact' => '+254734567891',
                'department' => 'Finance',
                'custom_fields' => json_encode([
                    'position' => 'Accountant',
                    'location' => 'Nairobi',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(12),
                'total_messages' => 6,
                'unread_messages' => 0,
                'notes' => 'Handles accounts payable.',
                'tags' => json_encode(['Finance', 'Accounting']),
                'opted_in' => true,
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subDays(12),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Nancy White',
                'contact' => '+254745678902',
                'department' => 'Operations',
                'custom_fields' => json_encode([
                    'position' => 'Logistics Coordinator',
                    'location' => 'Thika',
                    'preferred_language' => 'Swahili'
                ]),
                'last_message_at' => now()->subDays(2),
                'total_messages' => 13,
                'unread_messages' => 1,
                'notes' => 'Coordinates deliveries and shipments.',
                'tags' => json_encode(['Operations', 'Logistics', 'Active']),
                'opted_in' => true,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Daniel Harris',
                'contact' => '+254756789013',
                'department' => 'Sales',
                'custom_fields' => json_encode([
                    'position' => 'Account Executive',
                    'location' => 'Nairobi',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subHours(12),
                'total_messages' => 20,
                'unread_messages' => 4,
                'notes' => 'High performer. Key accounts handler.',
                'tags' => json_encode(['Sales', 'VIP', 'High Performer']),
                'opted_in' => true,
                'created_at' => now()->subMonths(7),
                'updated_at' => now()->subHours(12),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Karen Clark',
                'contact' => '+254767890124',
                'department' => 'Marketing',
                'custom_fields' => json_encode([
                    'position' => 'Content Writer',
                    'location' => 'Remote',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => null,
                'total_messages' => 0,
                'unread_messages' => 0,
                'notes' => 'Recently added. No messages yet.',
                'tags' => json_encode(['Marketing', 'New']),
                'opted_in' => true,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Steven Lewis',
                'contact' => '+254778901235',
                'department' => 'Customer Service',
                'custom_fields' => json_encode([
                    'position' => 'Support Agent',
                    'location' => 'Mombasa',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(3),
                'total_messages' => 16,
                'unread_messages' => 0,
                'notes' => 'Handles tier 1 support tickets.',
                'tags' => json_encode(['Customer Service', 'Support']),
                'opted_in' => true,
                'created_at' => now()->subMonths(1),
                'updated_at' => now()->subDays(3),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Betty Robinson',
                'contact' => '+254789012346',
                'department' => 'Finance',
                'custom_fields' => json_encode([
                    'position' => 'Financial Analyst',
                    'location' => 'Nairobi',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(15),
                'total_messages' => 4,
                'unread_messages' => 0,
                'notes' => 'Quarterly reports contact.',
                'tags' => json_encode(['Finance', 'Analysis']),
                'opted_in' => true,
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subDays(15),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Paul Walker',
                'contact' => '+254790123457',
                'department' => 'IT',
                'custom_fields' => json_encode([
                    'position' => 'DevOps Engineer',
                    'location' => 'Nairobi',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(1),
                'total_messages' => 19,
                'unread_messages' => 2,
                'notes' => 'Manages CI/CD pipelines.',
                'tags' => json_encode(['IT', 'DevOps', 'Technical']),
                'opted_in' => true,
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subDays(1),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Sandra Hall',
                'contact' => '+254701234568',
                'department' => 'Sales',
                'custom_fields' => json_encode([
                    'position' => 'Sales Coordinator',
                    'location' => 'Eldoret',
                    'preferred_language' => 'English'
                ]),
                'last_message_at' => now()->subDays(9),
                'total_messages' => 8,
                'unread_messages' => 0,
                'notes' => 'Coordinates sales team activities.',
                'tags' => json_encode(['Sales', 'Coordination']),
                'opted_in' => true,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subDays(9),
            ],
        ];

        DB::table('contacts')->insert($contacts);

        $this->command->info('Successfully seeded ' . count($contacts) . ' contacts!');
    }
}

