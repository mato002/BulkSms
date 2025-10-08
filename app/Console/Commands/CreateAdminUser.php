<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {email?} {password?}';
    protected $description = 'Create an admin user';

    public function handle()
    {
        $email = $this->argument('email') ?? 'admin@bulksms.local';
        $password = $this->argument('password') ?? 'password';

        // Check if user already exists
        $existing = User::where('email', $email)->first();
        if ($existing) {
            $this->info('User already exists. Updating role to admin...');
            $existing->role = 'admin';
            $existing->save();
            $this->info("User {$email} is now an admin!");
            return 0;
        }

        // Create new admin user
        User::create([
            'name' => 'Admin User',
            'email' => $email,
            'password' => Hash::make($password),
            'client_id' => 1,
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->info("Admin user created successfully!");
        $this->info("Email: {$email}");
        $this->info("Password: {$password}");
        
        return 0;
    }
}

