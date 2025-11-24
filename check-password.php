<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Hash;

$hash = '$2y$12$W/6Y2zLLet1fJoU5GQWHP.UcZS/eGHBMfdJpGHuGkBOk8jxe34ZgW';

$passwords = [
    'password',
    'Password123',
    'Fortress123',
    'Fortress@123',
    'Fortress@2024',
    'Fortress@2025',
    'BulkSMS@123',
    'Secret123',
];

foreach ($passwords as $pwd) {
    if (Hash::check($pwd, $hash)) {
        echo "Match found: {$pwd}\n";
        exit(0);
    }
}

echo "No matches found\n";
