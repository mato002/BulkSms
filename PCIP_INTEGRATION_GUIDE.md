# 🔗 PCIP Integration with BulkSms CRM - Complete Guide

**For:** PCIP Website  
**Sender:** FORTRESS  
**Generated:** October 17, 2025

---

## 📋 Overview

This guide shows you how to integrate your PCIP website with the BulkSms CRM system to send SMS messages using the FORTRESS sender ID.

### What You'll Achieve:
- ✅ Send SMS from PCIP website
- ✅ Check message status
- ✅ View balance
- ✅ Track sent messages
- ✅ Handle errors gracefully

---

## 🔑 Your API Credentials

```
API URL:     http://127.0.0.1:8000/api/2/messages/send
API Key:     USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh
Client ID:   2
Sender ID:   FORTRESS
Balance URL: http://127.0.0.1:8000/api/2/client/balance
```

**⚠️ IMPORTANT:** Store these credentials securely (use environment variables, not hardcoded).

---

## 🚀 Quick Start - PHP Integration

### Step 1: Create SMS Helper Class

Create a file: `includes/BulkSmsHelper.php`

```php
<?php

class BulkSmsHelper
{
    private $apiUrl;
    private $apiKey;
    private $clientId;
    private $senderId;
    
    public function __construct()
    {
        // Load from environment or config
        $this->apiUrl = 'http://127.0.0.1:8000/api/2/messages/send';
        $this->apiKey = 'USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh';
        $this->clientId = 2;
        $this->senderId = 'FORTRESS';
    }
    
    /**
     * Send SMS message
     * 
     * @param string $recipient Phone number (254XXXXXXXXX format)
     * @param string $message Message content
     * @return array Response with status
     */
    public function sendSms($recipient, $message)
    {
        // Format phone number if needed
        $recipient = $this->formatPhoneNumber($recipient);
        
        $data = [
            'client_id' => $this->clientId,
            'channel' => 'sms',
            'recipient' => $recipient,
            'sender' => $this->senderId,
            'body' => $message
        ];
        
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'Connection error: ' . $error
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'message_id' => $responseData['id'] ?? null,
                'status' => $responseData['status'] ?? 'unknown',
                'provider_message_id' => $responseData['provider_message_id'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Unknown error',
                'errors' => $responseData['errors'] ?? []
            ];
        }
    }
    
    /**
     * Check account balance
     * 
     * @return array Balance information
     */
    public function checkBalance()
    {
        $balanceUrl = 'http://127.0.0.1:8000/api/2/client/balance';
        
        $ch = curl_init($balanceUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    /**
     * Format phone number to international format
     * 
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    private function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 0, replace with 254
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }
        
        // If doesn't start with 254, add it
        if (substr($phone, 0, 3) !== '254') {
            $phone = '254' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Send bulk SMS to multiple recipients
     * 
     * @param array $recipients Array of phone numbers
     * @param string $message Message content
     * @return array Results for each recipient
     */
    public function sendBulkSms($recipients, $message)
    {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $results[] = [
                'recipient' => $recipient,
                'result' => $this->sendSms($recipient, $message)
            ];
        }
        
        return $results;
    }
}
```

---

## 💡 Usage Examples

### Example 1: Send Single SMS

```php
<?php
require_once 'includes/BulkSmsHelper.php';

$sms = new BulkSmsHelper();

// Send SMS
$result = $sms->sendSms('0728883160', 'Hello from PCIP via FORTRESS!');

if ($result['success']) {
    echo "✅ SMS sent successfully!";
    echo "Message ID: " . $result['message_id'];
} else {
    echo "❌ Failed to send SMS: " . $result['error'];
}
```

### Example 2: Send SMS After User Registration

```php
<?php
require_once 'includes/BulkSmsHelper.php';

// After user registration
function sendWelcomeSms($userPhone, $userName)
{
    $sms = new BulkSmsHelper();
    
    $message = "Welcome to PCIP, $userName! Your account has been created successfully. Thank you for joining us.";
    
    $result = $sms->sendSms($userPhone, $message);
    
    if ($result['success']) {
        // Log success
        error_log("Welcome SMS sent to $userPhone");
        return true;
    } else {
        // Log error
        error_log("Failed to send welcome SMS: " . $result['error']);
        return false;
    }
}

// Usage
sendWelcomeSms('0728883160', 'John Doe');
```

### Example 3: Send Payment Notification

```php
<?php
require_once 'includes/BulkSmsHelper.php';

function sendPaymentNotification($phone, $amount, $reference)
{
    $sms = new BulkSmsHelper();
    
    $message = "Payment of KSH $amount received. Ref: $reference. Thank you for your payment!";
    
    return $sms->sendSms($phone, $message);
}

// Usage
$result = sendPaymentNotification('0728883160', '5000', 'PAY-12345');
```

### Example 4: Send OTP/Verification Code

```php
<?php
require_once 'includes/BulkSmsHelper.php';

function sendOtp($phone)
{
    $sms = new BulkSmsHelper();
    
    // Generate OTP
    $otp = rand(100000, 999999);
    
    // Store OTP in session or database
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_phone'] = $phone;
    $_SESSION['otp_expiry'] = time() + 300; // 5 minutes
    
    $message = "Your PCIP verification code is: $otp. Valid for 5 minutes.";
    
    $result = $sms->sendSms($phone, $message);
    
    return [
        'sent' => $result['success'],
        'otp' => $otp
    ];
}

// Usage
$otpResult = sendOtp('0728883160');
if ($otpResult['sent']) {
    echo "OTP sent successfully!";
}
```

### Example 5: Check Balance Before Sending

```php
<?php
require_once 'includes/BulkSmsHelper.php';

$sms = new BulkSmsHelper();

// Check balance first
$balance = $sms->checkBalance();

if ($balance && $balance['balance'] > 0) {
    echo "Current balance: KSH " . $balance['balance'];
    
    // Send SMS
    $result = $sms->sendSms('0728883160', 'Test message');
    
    if ($result['success']) {
        echo "SMS sent!";
    }
} else {
    echo "Insufficient balance. Please contact administrator.";
}
```

### Example 6: Send Bulk SMS

```php
<?php
require_once 'includes/BulkSmsHelper.php';

$sms = new BulkSmsHelper();

$recipients = [
    '0728883160',
    '0712345678',
    '0723456789'
];

$message = "Important announcement from PCIP...";

$results = $sms->sendBulkSms($recipients, $message);

foreach ($results as $result) {
    if ($result['result']['success']) {
        echo "✅ Sent to " . $result['recipient'] . "\n";
    } else {
        echo "❌ Failed for " . $result['recipient'] . ": " . $result['result']['error'] . "\n";
    }
}
```

---

## 🔧 Configuration File Setup

### Create: `config/sms.php`

```php
<?php

return [
    'fortress' => [
        'api_url' => getenv('FORTRESS_API_URL') ?: 'http://127.0.0.1:8000/api/2/messages/send',
        'api_key' => getenv('FORTRESS_API_KEY') ?: 'USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh',
        'client_id' => 2,
        'sender_id' => 'FORTRESS',
        'balance_url' => getenv('FORTRESS_BALANCE_URL') ?: 'http://127.0.0.1:8000/api/2/client/balance',
    ]
];
```

### Environment Variables (.env file)

```env
# BulkSms CRM - FORTRESS API
FORTRESS_API_URL=http://127.0.0.1:8000/api/2/messages/send
FORTRESS_API_KEY=USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh
FORTRESS_BALANCE_URL=http://127.0.0.1:8000/api/2/client/balance
```

---

## 🌐 JavaScript/AJAX Integration

### For Web Forms

```javascript
// Send SMS via AJAX from PCIP frontend

async function sendSmsFromPCIP(phone, message) {
    try {
        const response = await fetch('/api/send-sms.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                phone: phone,
                message: message
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('✅ SMS sent successfully!');
            return true;
        } else {
            console.error('❌ Failed:', result.error);
            return false;
        }
    } catch (error) {
        console.error('❌ Error:', error);
        return false;
    }
}

// Usage
sendSmsFromPCIP('0728883160', 'Hello from PCIP!');
```

### Backend Handler: `api/send-sms.php`

```php
<?php
header('Content-Type: application/json');
require_once '../includes/BulkSmsHelper.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

$phone = $input['phone'] ?? '';
$message = $input['message'] ?? '';

if (empty($phone) || empty($message)) {
    echo json_encode([
        'success' => false,
        'error' => 'Phone and message are required'
    ]);
    exit;
}

$sms = new BulkSmsHelper();
$result = $sms->sendSms($phone, $message);

echo json_encode($result);
```

---

## 🧪 Testing the Integration

### Create: `test_pcip_integration.php`

```php
<?php
require_once 'includes/BulkSmsHelper.php';

echo "=== TESTING PCIP INTEGRATION ===\n\n";

$sms = new BulkSmsHelper();

// Test 1: Check Balance
echo "Test 1: Checking balance...\n";
$balance = $sms->checkBalance();
if ($balance) {
    echo "✅ Balance: KSH " . $balance['balance'] . "\n";
} else {
    echo "❌ Failed to check balance\n";
}
echo "\n";

// Test 2: Send Test SMS
echo "Test 2: Sending test SMS...\n";
$result = $sms->sendSms('0728883160', 'Test SMS from PCIP - ' . date('H:i:s'));

if ($result['success']) {
    echo "✅ SMS sent successfully!\n";
    echo "   Message ID: " . $result['message_id'] . "\n";
    echo "   Status: " . $result['status'] . "\n";
} else {
    echo "❌ Failed to send SMS\n";
    echo "   Error: " . $result['error'] . "\n";
}
echo "\n";

// Test 3: Phone Number Formatting
echo "Test 3: Testing phone number formatting...\n";
$testNumbers = ['0728883160', '728883160', '254728883160'];
foreach ($testNumbers as $number) {
    $formatted = $sms->formatPhoneNumber($number);
    echo "   $number → $formatted\n";
}
echo "\n";

echo "=== TESTING COMPLETE ===\n";
```

---

## 📊 Database Logging (Optional but Recommended)

### Create SMS Log Table in PCIP Database

```sql
CREATE TABLE sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    message_id INT NULL,
    provider_message_id VARCHAR(100) NULL,
    error_message TEXT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipient (recipient),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
);
```

### Enhanced Helper with Logging

```php
public function sendSmsWithLogging($recipient, $message)
{
    // Send SMS
    $result = $this->sendSms($recipient, $message);
    
    // Log to database
    $pdo = $this->getDatabaseConnection(); // Your DB connection
    
    $stmt = $pdo->prepare("
        INSERT INTO sms_logs 
        (recipient, message, status, message_id, provider_message_id, error_message)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $recipient,
        $message,
        $result['success'] ? 'sent' : 'failed',
        $result['message_id'] ?? null,
        $result['provider_message_id'] ?? null,
        $result['error'] ?? null
    ]);
    
    return $result;
}
```

---

## ⚠️ Error Handling Best Practices

```php
try {
    $sms = new BulkSmsHelper();
    $result = $sms->sendSms($phone, $message);
    
    if ($result['success']) {
        // Success - update your records
        logSuccess("SMS sent to $phone");
    } else {
        // Failed - log error and notify admin
        logError("SMS failed: " . $result['error']);
        
        // Maybe retry later or notify admin
        if (strpos($result['error'], 'balance') !== false) {
            notifyAdmin('Low SMS balance!');
        }
    }
} catch (Exception $e) {
    // Critical error - log and handle
    logCritical("SMS system error: " . $e->getMessage());
}
```

---

## 🔒 Security Best Practices

1. **Never expose API key in frontend code**
   - Always call from backend PHP
   - Use server-side processing

2. **Validate phone numbers**
   - Check format before sending
   - Sanitize user input

3. **Rate limiting**
   - Limit SMS per user/IP
   - Prevent spam/abuse

4. **Use HTTPS in production**
   - Change API URL to HTTPS
   - Secure all communications

5. **Store credentials securely**
   - Use environment variables
   - Never commit to Git

---

## 📞 Support & Troubleshooting

### Common Issues

**Problem:** "Connection refused"
- **Solution:** Check if BulkSms CRM server is running

**Problem:** "Invalid API key"
- **Solution:** Verify API key is correct: `USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh`

**Problem:** "Insufficient balance"
- **Solution:** Contact administrator to top up FORTRESS account

**Problem:** "Invalid phone number"
- **Solution:** Ensure format is 254XXXXXXXXX (Kenyan numbers)

---

## ✅ Quick Checklist

- [ ] Copy `BulkSmsHelper.php` to your PCIP project
- [ ] Configure API credentials
- [ ] Test with `test_pcip_integration.php`
- [ ] Implement in your use cases (registration, payments, etc.)
- [ ] Set up error logging
- [ ] Test in production
- [ ] Monitor balance regularly

---

**Ready to integrate! Start with the test script and then implement in your PCIP workflows.** 🚀
