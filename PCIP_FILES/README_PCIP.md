# PCIP Integration Files

## 📁 Files Included

1. **BulkSmsHelper.php** - Main SMS helper class
2. **test_integration.php** - Test script to verify integration
3. **examples.php** - Real-world usage examples

## 🚀 Quick Setup

### Step 1: Copy Files to PCIP

Copy these files to your PCIP website:

```
PCIP_Website/
├── includes/
│   └── BulkSmsHelper.php
├── tests/
│   └── test_integration.php
└── examples/
    └── examples.php
```

### Step 2: Test the Integration

Run the test script from command line:

```bash
cd /path/to/pcip
php tests/test_integration.php
```

Or access via browser:
```
http://your-pcip-domain.com/tests/test_integration.php
```

### Step 3: Implement in Your Code

```php
<?php
require_once 'includes/BulkSmsHelper.php';

$sms = new BulkSmsHelper();

// Send SMS
$result = $sms->sendSms('0728883160', 'Hello from PCIP!');

if ($result['success']) {
    echo "SMS sent!";
} else {
    echo "Error: " . $result['error'];
}
```

## 📖 Usage Examples

### Send Welcome SMS After Registration

```php
require_once 'includes/BulkSmsHelper.php';

function onUserRegistration($userData) {
    $sms = new BulkSmsHelper();
    
    $message = "Welcome {$userData['name']}! Your PCIP account is ready.";
    $sms->sendSms($userData['phone'], $message);
}
```

### Send Payment Confirmation

```php
function onPaymentReceived($payment) {
    $sms = new BulkSmsHelper();
    
    $message = "Payment of KSH {$payment['amount']} received. Ref: {$payment['reference']}";
    $sms->sendSms($payment['customer_phone'], $message);
}
```

### Send OTP

```php
function sendLoginOtp($phone) {
    $sms = new BulkSmsHelper();
    
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + 300; // 5 minutes
    
    $message = "Your PCIP login code: $otp (valid for 5 minutes)";
    return $sms->sendSms($phone, $message);
}
```

## 🔐 API Credentials

**Already configured in BulkSmsHelper.php:**

- API URL: `http://127.0.0.1:8000/api/2/messages/send`
- API Key: `USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh`
- Client ID: `2`
- Sender ID: `FORTRESS`

## 📊 Monitoring

### Check Balance

```php
$sms = new BulkSmsHelper();
$balance = $sms->checkBalance();

echo "Balance: KSH " . $balance['balance'];
```

### Set Up Low Balance Alert

```php
// Run this as a cron job (daily)
$sms = new BulkSmsHelper();
$balance = $sms->checkBalance();

if ($balance['balance'] < 50) {
    // Alert admin
    $sms->sendSms('admin-phone', 'Low SMS balance: ' . $balance['balance']);
}
```

## ⚠️ Important Notes

1. **Phone Number Format:** Accepts any format (0728883160, 728883160, 254728883160)
2. **Message Length:** Keep under 160 characters for single SMS
3. **Rate Limiting:** Small delay (0.1s) added in bulk sending
4. **Error Handling:** Always check `$result['success']` before proceeding
5. **Security:** Never expose API key in frontend JavaScript

## 🧪 Testing Checklist

- [ ] Test balance check
- [ ] Send test SMS to your number
- [ ] Test invalid phone number
- [ ] Test with empty message
- [ ] Test bulk sending
- [ ] Verify message appears in BulkSms CRM dashboard

## 📞 Support

For issues or questions:
- Check BulkSms CRM dashboard for message status
- Verify balance is sufficient
- Check server connectivity
- Review error messages in result array

## 🔄 Update Log

- **v1.0** - Initial PCIP integration
  - Basic SMS sending
  - Balance checking
  - Bulk SMS support
  - Phone number formatting
  - Error handling


