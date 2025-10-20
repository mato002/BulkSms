<?php

echo "\n";
echo "=================================================\n";
echo "    CHECKING PCIP ON HOSTINGER\n";
echo "=================================================\n\n";

// Your PCIP domain (update if different)
$pcipUrl = 'https://your-pcip-domain.com';  // UPDATE THIS!

echo "Checking PCIP installation...\n\n";

// Test 1: Check if site is accessible
echo "1. Testing main site...\n";
$mainSite = @file_get_contents($pcipUrl, false, stream_context_create([
    'http' => [
        'timeout' => 10,
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]));

if ($mainSite !== false) {
    echo "   ✅ Site is accessible\n";
} else {
    echo "   ❌ Cannot access site\n";
}

// Test 2: Check if FortressSmsService exists
echo "\n2. Checking if FortressSmsService.php exists...\n";
$serviceUrl = $pcipUrl . '/app/Services/FortressSmsService.php';
$headers = @get_headers($serviceUrl);

if ($headers && strpos($headers[0], '200') !== false) {
    echo "   ⚠️  File is publicly accessible (might be security issue)\n";
} else {
    echo "   ✅ File not directly accessible (good for security)\n";
}

// Test 3: Check Laravel installation
echo "\n3. Checking Laravel...\n";
$laravelCheck = @file_get_contents($pcipUrl . '/public', false, stream_context_create([
    'http' => ['timeout' => 5, 'ignore_errors' => true],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]));

if ($laravelCheck !== false) {
    if (strpos($laravelCheck, 'Laravel') !== false) {
        echo "   ✅ Laravel detected\n";
    } else {
        echo "   ⚠️  Response received but Laravel not detected\n";
    }
}

echo "\n=================================================\n";
echo "    MANUAL VERIFICATION STEPS\n";
echo "=================================================\n\n";

echo "To verify your upload, check these:\n\n";

echo "1. FTP/File Manager Check:\n";
echo "   - Login to Hostinger control panel\n";
echo "   - Go to File Manager\n";
echo "   - Navigate to: public_html/pcip/ (or your folder)\n";
echo "   - Verify these files exist:\n";
echo "     • app/Services/FortressSmsService.php\n";
echo "     • config/services.php\n";
echo "     • .env file\n\n";

echo "2. Check .env Configuration:\n";
echo "   - Open .env file\n";
echo "   - Verify these lines exist:\n";
echo "     FORTRESS_SMS_API_URL=https://crm.pradytecai.com/api/2/messages/send\n";
echo "     FORTRESS_SMS_API_KEY=USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh\n";
echo "     FORTRESS_SMS_ENABLED=true\n\n";

echo "3. Test SMS Functionality:\n";
echo "   - Create a test script on your PCIP site\n";
echo "   - Or use Laravel Tinker via SSH:\n";
echo "     php artisan tinker\n";
echo "     >>> (new \\App\\Services\\FortressSmsService())->send('254728883160', 'Test');\n\n";

echo "=================================================\n";
echo "\nWould you like me to:\n";
echo "1. Create a test endpoint to verify?\n";
echo "2. Check via SSH (provide SSH credentials)?\n";
echo "3. Guide you through Hostinger File Manager?\n\n";



