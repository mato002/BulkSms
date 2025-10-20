<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "=================================================\n";
echo "    M-PESA CONFIGURATION CHECK\n";
echo "=================================================\n\n";

$configs = [
    'Environment' => config('mpesa.env'),
    'Consumer Key' => config('mpesa.consumer_key') ? substr(config('mpesa.consumer_key'), 0, 30) . '...' : 'NOT SET',
    'Consumer Secret' => config('mpesa.consumer_secret') ? substr(config('mpesa.consumer_secret'), 0, 30) . '...' : 'NOT SET',
    'Passkey' => config('mpesa.passkey') ? substr(config('mpesa.passkey'), 0, 30) . '...' : 'NOT SET',
    'Shortcode' => config('mpesa.shortcode'),
    'Transaction Type' => config('mpesa.transaction_type'),
    'Callback URL' => config('mpesa.callback_url'),
    'Timeout URL' => config('mpesa.timeout_url'),
];

foreach ($configs as $key => $value) {
    printf("%-20s: %s\n", $key, $value);
}

echo "\n";
echo "=================================================\n";
echo "    ENVIRONMENT STATUS\n";
echo "=================================================\n\n";

if (config('mpesa.env') === 'sandbox') {
    echo "⚠️  SANDBOX MODE\n\n";
    echo "Sandbox limitations:\n";
    echo "- STK push prompts DO NOT go to real phones\n";
    echo "- Only works with M-Pesa Sandbox app\n";
    echo "- For testing purposes only\n\n";
    echo "To receive prompts on real phone:\n";
    echo "1. Get production credentials from Safaricom\n";
    echo "2. Update .env with: MPESA_ENV=production\n";
    echo "3. Add production keys, secret, passkey, shortcode\n";
} else {
    echo "✅ PRODUCTION MODE\n\n";
    echo "Production is active.\n";
    echo "STK push prompts should go to real phones.\n\n";
    
    // Check if credentials look like production or still sandbox defaults
    if (config('mpesa.shortcode') === '174379') {
        echo "⚠️  WARNING: Still using sandbox shortcode (174379)\n";
        echo "   Update to your production shortcode!\n";
    }
}

echo "\n=================================================\n";

// Check if .env file exists
if (file_exists(__DIR__ . '/.env')) {
    echo "\n✅ .env file exists\n";
    
    // Read .env and check for MPESA_ entries
    $envContent = file_get_contents(__DIR__ . '/.env');
    $mpesaLines = array_filter(
        explode("\n", $envContent),
        function($line) {
            return strpos($line, 'MPESA_') === 0;
        }
    );
    
    if (!empty($mpesaLines)) {
        echo "\nM-Pesa settings in .env:\n";
        echo str_repeat('-', 50) . "\n";
        foreach ($mpesaLines as $line) {
            // Mask sensitive values
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                if (in_array($key, ['MPESA_CONSUMER_KEY', 'MPESA_CONSUMER_SECRET', 'MPESA_PASSKEY'])) {
                    $value = substr($value, 0, 20) . '...';
                }
                echo "$key=$value\n";
            }
        }
    } else {
        echo "\n⚠️  No MPESA_ settings found in .env\n";
        echo "   Using defaults from config/mpesa.php\n";
    }
} else {
    echo "\n⚠️  .env file not found!\n";
}

echo "\n=================================================\n\n";



