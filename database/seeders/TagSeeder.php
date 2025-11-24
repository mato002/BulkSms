<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientId = DB::table('clients')->value('id') ?? 1;

        $tags = [
            [
                'client_id' => $clientId,
                'name' => 'VIP',
                'slug' => Str::slug('VIP'),
                'color' => '#FFD700', // Gold
                'description' => 'Very Important Persons - Priority customers',
                'contacts_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Active',
                'slug' => Str::slug('Active'),
                'color' => '#28a745', // Green
                'description' => 'Active customers who regularly engage',
                'contacts_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Inactive',
                'slug' => Str::slug('Inactive'),
                'color' => '#6c757d', // Gray
                'description' => 'Inactive customers who haven\'t engaged recently',
                'contacts_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Prospect',
                'slug' => Str::slug('Prospect'),
                'color' => '#17a2b8', // Cyan
                'description' => 'Potential customers to follow up with',
                'contacts_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Support',
                'slug' => Str::slug('Support'),
                'color' => '#dc3545', // Red
                'description' => 'Customers who need support or assistance',
                'contacts_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Newsletter',
                'slug' => Str::slug('Newsletter'),
                'color' => '#007bff', // Blue
                'description' => 'Subscribers to newsletter and updates',
                'contacts_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Promotion',
                'slug' => Str::slug('Promotion'),
                'color' => '#ffc107', // Yellow
                'description' => 'Customers interested in promotions and offers',
                'contacts_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Only insert tags that don't already exist
        foreach ($tags as $tag) {
            $exists = DB::table('tags')
                ->where('client_id', $tag['client_id'])
                ->where('slug', $tag['slug'])
                ->exists();

            if (!$exists) {
                DB::table('tags')->insert($tag);
            }
        }

        $this->command->info('Successfully seeded ' . count($tags) . ' tags!');
    }
}




