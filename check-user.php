<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$email = $argv[1] ?? 'admin@fortress.co.ke';

$user = User::where('email', $email)->first();

if (!$user) {
    echo "User not found\n";
    exit(1);
}

echo "User: {$user->name}\n";

echo "Email: {$user->email}\n";

echo "Password hash: {$user->password}\n";

echo "Created at: {$user->created_at}\n";






