# PCIP System Integration with FORTRESS Sender

## Overview

This guide will help you configure the PCIP system (located in `C:\xampp\htdocs\PCIP`) to send SMS through our Bulk SMS platform using the FORTRESS sender.

---

## Step 1: Generate FORTRESS API Credentials

Run this command to set up FORTRESS:

```bash
cd C:\xampp\htdocs\bulk-sms-laravel
php setup_fortress_api.php
```

This will:
- ✅ Create/find FORTRESS client
- ✅ Generate unique API key
- ✅ Configure SMS channel
- ✅ Add test balance
- ✅ Save configuration to file

---

## Step 2: Get API Credentials

### Option A: From Generated File

Check the file: `FORTRESS_PCIP_CONFIG.txt` (created by setup script)

### Option B: Manual Query

```bash
php artisan tinker

>>> $fortress = App\Models\Client::where('sender_id', 'FORTRESS')->first();
>>> echo "Client ID: " . $fortress->id;
>>> echo "API Key: " . $fortress->api_key;
>>> echo "Balance: KSH " . $fortress->balance;
```

---

## Step 3: Configure PCIP System

### Required Settings for PCIP:

```
API Base URL:  http://localhost/api
Client ID:     [Get from Step 2]
API Key:       [Get from Step 2]
Sender Name:   FORTRESS
```

### Where to Add in PCIP:

1. Navigate to PCIP configuration file (usually `config.php` or `settings.php`)
2. Add these constants/variables:

```php
// SMS API Configuration
define('SMS_API_URL', 'http://localhost/api');
define('SMS_CLIENT_ID', '1'); // Replace with actual ID
define('SMS_API_KEY', 'your_api_key_here'); // Replace with actual key
define('SMS_SENDER', 'FORTRESS');
```

---

## Step 4: Integration Code for PCIP

### Create SMS Helper Function

Add this to PCIP's utility or helper file:

```php
<?php
/**
 * Send SMS via Bulk SMS API
 * 
 * @param string $recipient Phone number (254XXXXXXXXX)
 * @param string $message SMS message content
 * @return array Response with success status
 */
function sendSMS($recipient, $message) {
    $apiUrl = SMS_API_URL . '/' . SMS_CLIENT_ID . '/messages/send';
    
    $data = [
        'channel' => 'sms',
        'recipient' => $recipient,
        'body' => $message,
        'sender' => SMS_SENDER
    ];
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . SMS_API_KEY,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local development
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode === 200 || $httpCode === 201) {
        $result = json_decode($response, true);
        return [
            'success' => true,
            'message_id' => $result['data']['id'] ?? null,
            'cost' => $result['data']['cost'] ?? 0,
            'response' => $result
        ];
    } else {
        return [
            'success' => false,
            'error' => $response ? json_decode($response, true) : $error,
            'http_code' => $httpCode
        ];
    }
}

/**
 * Check SMS balance
 * 
 * @return array|null Balance information or null on error
 */
function checkSMSBalance() {
    $apiUrl = SMS_API_URL . '/' . SMS_CLIENT_ID . '/client/balance';
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . SMS_API_KEY,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return null;
}
```

---

## Step 5: Usage Examples in PCIP

### Example 1: Send SMS Notification

```php
// In your PCIP notification function
function notifyClient($phone, $message) {
    // Format phone number if needed
    if (substr($phone, 0, 1) === '0') {
        $phone = '254' . substr($phone, 1);
    }
    
    // Send SMS
    $result = sendSMS($phone, $message);
    
    if ($result['success']) {
        // Log success
        logActivity("SMS sent to $phone - Message ID: " . $result['message_id']);
        return true;
    } else {
        // Log error
        logError("SMS failed to $phone - Error: " . json_encode($result['error']));
        return false;
    }
}
```

### Example 2: Send Payment Confirmation

```php
// After successful payment in PCIP
function sendPaymentConfirmation($clientPhone, $amount, $reference) {
    $message = "Payment of KSH $amount received. Ref: $reference. Thank you!";
    
    $result = sendSMS($clientPhone, $message);
    
    if ($result['success']) {
        // Update database that SMS was sent
        updatePaymentRecord($reference, ['sms_sent' => 1, 'sms_id' => $result['message_id']]);
    }
    
    return $result['success'];
}
```

### Example 3: Check Balance Before Sending

```php
function sendSMSWithCheck($phone, $message) {
    // Check balance first
    $balance = checkSMSBalance();
    
    if (!$balance || $balance['data']['units'] < 1) {
        logError("Insufficient SMS balance");
        return false;
    }
    
    // Balance OK, send SMS
    return sendSMS($phone, $message);
}
```

---

## Step 6: Test the Integration

### Test Script for PCIP

Create `test_sms.php` in PCIP directory:

```php
<?php
require_once 'config.php'; // Your PCIP config
require_once 'sms_helper.php'; // The SMS functions above

// Test 1: Check Balance
echo "Testing SMS Balance...\n";
$balance = checkSMSBalance();
if ($balance) {
    echo "✅ Balance: KSH " . $balance['data']['balance'] . "\n";
    echo "✅ Units: " . $balance['data']['units'] . "\n";
} else {
    echo "❌ Failed to check balance\n";
}

// Test 2: Send Test SMS
echo "\nTesting SMS Send...\n";
$testPhone = '254712345678'; // Replace with your test number
$testMessage = 'Test SMS from PCIP system via FORTRESS';

$result = sendSMS($testPhone, $testMessage);

if ($result['success']) {
    echo "✅ SMS sent successfully!\n";
    echo "   Message ID: " . $result['message_id'] . "\n";
    echo "   Cost: KSH " . $result['cost'] . "\n";
} else {
    echo "❌ SMS failed!\n";
    echo "   Error: " . json_encode($result['error']) . "\n";
}
```

Run the test:
```bash
cd C:\xampp\htdocs\PCIP
php test_sms.php
```

---

## Step 7: Monitor and Manage

### View SMS History

```bash
cd C:\xampp\htdocs\bulk-sms-laravel

# View recent messages from FORTRESS
php artisan tinker

>>> $fortress = App\Models\Client::where('sender_id', 'FORTRESS')->first();
>>> $messages = App\Models\Message::where('client_id', $fortress->id)->latest()->take(10)->get();
>>> $messages->each(function($m) {
...   echo $m->recipient . ' - ' . $m->status . ' - ' . $m->created_at . "\n";
... });
```

### Add Balance

```bash
php artisan tinker

>>> $fortress = App\Models\Client::where('sender_id', 'FORTRESS')->first();
>>> $fortress->balance += 1000; // Add KSH 1000
>>> $fortress->save();
>>> echo "New balance: KSH " . $fortress->balance;
```

### Check Statistics

Access the web dashboard:
```
http://localhost:8000/login
Email: admin@fortress.co.ke
Password: fortress123
```

---

## API Reference

### 1. Send SMS

```
POST http://localhost/api/{client_id}/messages/send
Headers:
  X-API-Key: {your_api_key}
  Content-Type: application/json
Body:
  {
    "channel": "sms",
    "recipient": "254712345678",
    "body": "Your message",
    "sender": "FORTRESS"
  }
```

**Response (Success):**
```json
{
  "status": "success",
  "message": "Message queued for sending",
  "data": {
    "id": 123,
    "client_id": 1,
    "channel": "sms",
    "recipient": "254712345678",
    "status": "sent",
    "cost": 1.00
  }
}
```

**Response (Error):**
```json
{
  "status": "error",
  "message": "Insufficient balance",
  "errors": []
}
```

### 2. Check Balance

```
GET http://localhost/api/{client_id}/client/balance
Headers:
  X-API-Key: {your_api_key}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "balance": 1000.00,
    "units": 1000.00,
    "price_per_unit": 1.00,
    "currency": "KSH"
  }
}
```

### 3. SMS History

```
GET http://localhost/api/{client_id}/sms/history?page=1&per_page=50
Headers:
  X-API-Key: {your_api_key}
```

---

## Troubleshooting

### Issue: "Invalid API key"

**Solution:**
1. Verify API key is correct
2. Check that FORTRESS client is active:
```bash
php artisan tinker
>>> App\Models\Client::where('sender_id', 'FORTRESS')->first()->status;
```

### Issue: "Insufficient balance"

**Solution:**
```bash
php artisan tinker
>>> $fortress = App\Models\Client::where('sender_id', 'FORTRESS')->first();
>>> $fortress->balance = 1000;
>>> $fortress->save();
```

### Issue: SMS not sending

**Check:**
1. API key is in header: `X-API-Key`
2. Client ID in URL matches API key owner
3. Phone number format: `254XXXXXXXXX`
4. Channel credentials configured
5. Laravel application is running

**View logs:**
```bash
tail -f C:\xampp\htdocs\bulk-sms-laravel\storage\logs\laravel.log
```

---

## Security Best Practices

1. **Store API key securely**
   - Use environment variables
   - Never commit to version control
   - Never expose in client-side code

2. **Validate phone numbers**
   - Check format before sending
   - Remove duplicates
   - Sanitize inputs

3. **Monitor usage**
   - Check balance regularly
   - Set up low balance alerts
   - Review SMS history

4. **Error handling**
   - Log all SMS attempts
   - Retry failed messages
   - Alert on repeated failures

---

## Quick Reference

| Task | Command/URL |
|------|-------------|
| Setup FORTRESS | `php setup_fortress_api.php` |
| Check credentials | `php artisan tinker` then query Client |
| Test API | `curl -X POST http://localhost/api/1/messages/send -H "X-API-Key: key"` |
| Add balance | Update via tinker or admin dashboard |
| View history | Login to `http://localhost:8000` |
| Check logs | `storage/logs/laravel.log` |

---

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify FORTRESS client exists and is active
3. Test API with cURL before integrating
4. Contact system administrator

---

**Status:** Ready for PCIP Integration ✅

**Next:** Run `php setup_fortress_api.php` to get started!

