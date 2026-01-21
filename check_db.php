<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $pdo = DB::connection()->getPdo();
    echo "Database connection: SUCCESS\n";
    
    $tables = DB::select('SHOW TABLES');
    echo "\nTables in database:\n";
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        echo "- $tableName\n";
    }
    
    if (count($tables) == 0) {
        echo "\nNo tables found. Running migrations...\n";
        Artisan::call('migrate');
        echo Artisan::output();
    }
} catch (\Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}


