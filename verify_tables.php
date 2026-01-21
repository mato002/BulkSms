<?php

$host = '127.0.0.1';
$db   = 'bulk_sms_laravel';
$user = 'root';
$pass = '';

$output = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $output .= "✓ Database connection successful!\n\n";
    
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $output .= "Tables created (" . count($tables) . " total):\n";
    $output .= str_repeat('=', 50) . "\n";
    foreach ($tables as $table) {
        $output .= "  ✓ $table\n";
    }
    
    if (count($tables) > 0) {
        $output .= "\n" . str_repeat('=', 50) . "\n";
        $output .= "✓ Migrations completed successfully!\n";
    } else {
        $output .= "\n⚠ No tables found. Migrations may not have run.\n";
    }
    
} catch (PDOException $e) {
    $output .= "✗ Error: " . $e->getMessage() . "\n";
}

file_put_contents('migration_verification.txt', $output);
echo $output;

