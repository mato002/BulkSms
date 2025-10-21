# WhatsApp Messages Not Sending - Troubleshooting Guide

## Quick Diagnostics Checklist

Run through this checklist to identify why WhatsApp messages are not sending:

### 1. ✅ Check WhatsApp Configuration

Navigate to **WhatsApp → Index** page and verify:
- [ ] WhatsApp channel shows as "Connected" (green badge)
- [ ] Provider is configured (UltraMsg or WhatsApp Cloud API)
- [ ] Credentials are properly saved

### 2. ✅ Test Connection

1. Go to `/whatsapp`
2. Click "Test Connection" button
3. Expected result: ✅ Success message with phone number details
4. If failed: Note the error message

### 3. ✅ Check Database Configuration

Run this command to check if WhatsApp channel exists:
```bash
php artisan tinker
```
Then execute:
```php
$channel = App\Models\Channel::where('name', 'whatsapp')->first();
if ($channel) {
    dump([
        'Provider' => $channel->provider,
        'Active' => $channel->active,
        'Has Credentials' => !empty($channel->credentials)
    ]);
} else {
    echo "❌ No WhatsApp channel configured!";
}
```

---

## Common Issues & Solutions

### Issue 1: "WhatsApp channel not configured"

**Symptoms:**
- Error message: "WhatsApp channel not configured. Please configure it first."
- No configuration shown on WhatsApp page

**Solution:**
1. Navigate to `/whatsapp/configure`
2. Choose a provider:
   - **UltraMsg** (Easier, faster setup - Recommended)
   - **WhatsApp Cloud API** (Official Meta API)
3. Enter credentials and save

---

### Issue 2: SSL Certificate Verification Errors (Windows/XAMPP)

**Symptoms:**
- Error: "cURL error 60: SSL certificate problem"
- Connection test fails with SSL errors

**Solution:**
The code already includes SSL bypass for development. If still failing:

1. Download CA certificate bundle:
   ```
   https://curl.se/ca/cacert.pem
   ```

2. Save to `C:\xampp\php\extras\ssl\cacert.pem`

3. Update `php.ini`:
   ```ini
   curl.cainfo = "C:\xampp\php\extras\ssl\cacert.pem"
   openssl.cafile = "C:\xampp\php\extras\ssl\cacert.pem"
   ```

4. Restart Apache

**Note:** For production, remove `withOptions(['verify' => false])` from the code.

---

### Issue 3: Invalid Phone Number Format

**Symptoms:**
- Message fails with "Invalid phone number"
- No error but message doesn't send

**Solution:**
Phone numbers must include country code:

✅ **Correct formats:**
- `+254712345678` (with +)
- `254712345678` (without +)
- `0712345678` (will be auto-converted to +254712345678)

❌ **Incorrect formats:**
- `712345678` (missing leading 0 or country code)
- `+254 712 345 678` (spaces - will be auto-removed)

---

### Issue 4: UltraMsg Instance Not Active

**Symptoms:**
- Test connection shows: "Instance not active" or "Account suspended"
- Messages fail silently

**Solution:**
1. Log in to [UltraMsg Dashboard](https://ultramsg.com)
2. Check instance status
3. Verify:
   - [ ] Instance is CONNECTED (green status)
   - [ ] WhatsApp is scanned and active
   - [ ] Account has sufficient credits/balance
4. If disconnected, scan QR code again

---

### Issue 5: WhatsApp Cloud API Token Expired

**Symptoms:**
- Error: "Invalid OAuth access token"
- Connection test fails with authentication error

**Solution:**
1. Go to [Meta Business Suite](https://business.facebook.com/)
2. Navigate to **System Users** → Select your user
3. Generate new access token with required permissions:
   - `whatsapp_business_management`
   - `whatsapp_business_messaging`
4. Update credentials in `/whatsapp/configure`

---

### Issue 6: Template Messages Not Working

**Symptoms:**
- Regular messages work, but template messages fail
- Error: "Template not found" or "Template not approved"

**Solution:**
1. Go to `/whatsapp`
2. Click "Sync Templates from WhatsApp"
3. Verify templates show status "Approved" (green badge)
4. Only use approved templates
5. Ensure template variables match the template definition

---

### Issue 7: Message Stuck in "Sending" Status

**Symptoms:**
- Message saved to database but never sends
- Status remains "sending" indefinitely

**Solution:**
Check Laravel logs for errors:
```bash
tail -f storage/logs/laravel.log
```

Look for patterns like:
- `WhatsApp API Error`
- `UltraMsg API Error`
- `Connection timeout`

Fix based on specific error message.

---

### Issue 8: Recipient Number Not Receiving Messages

**Symptoms:**
- Send shows success
- No error messages
- Recipient doesn't receive message

**Possible Causes:**
1. **Recipient hasn't opted in** (WhatsApp Cloud API requirement)
   - Recipient must message you first, OR
   - Use approved template messages

2. **Recipient's phone not registered on WhatsApp**
   - Verify number is active on WhatsApp

3. **Number not in E.164 format**
   - Use: `+[country code][number]`
   - Example: `+254712345678`

---

## Testing Procedure

### Step 1: Basic Configuration Test
```bash
# Go to WhatsApp page
/whatsapp

# Click "Test Connection"
# Expected: ✅ Success with phone number info
```

### Step 2: Send Test Message
```javascript
// Use the Send Test Message modal
Recipient: +254712345678 (your own number)
Message: "Test from BulkSms system"

// Click Send
// Check your WhatsApp for message
```

### Step 3: Check Message Status
```php
// In tinker
App\Models\Message::where('channel', 'whatsapp')
    ->latest()
    ->first()
    ->only(['status', 'error_message', 'sent_at']);
```

---

## Provider-Specific Setup

### UltraMsg Setup (Recommended for Quick Start)

1. **Create Account**
   - Visit: https://ultramsg.com
   - Sign up for free trial

2. **Create Instance**
   - Click "Create Instance"
   - Select country code
   - Name your instance

3. **Connect WhatsApp**
   - Scan QR code with WhatsApp mobile app
   - Wait for "Connected" status

4. **Get Credentials**
   - Go to **Instance Settings**
   - Copy:
     - `Instance ID` (format: instance12345)
     - `Token` (long alphanumeric string)

5. **Configure in BulkSms**
   ```
   /whatsapp/configure
   
   Provider: UltraMsg
   Instance ID: instance12345
   Token: [paste your token]
   
   Click Save
   ```

6. **Test**
   - Click "Test Connection"
   - Should show: ✅ Connected

---

### WhatsApp Cloud API Setup (Official Meta API)

1. **Meta Business Account**
   - Visit: https://business.facebook.com
   - Create business account

2. **Create WhatsApp Business App**
   - Go to [Meta Developers](https://developers.facebook.com)
   - Create app → Business → WhatsApp

3. **Get Credentials**
   - **Phone Number ID**: In WhatsApp → API Setup
   - **Access Token**: In WhatsApp → API Setup → Temporary token
   - **Business Account ID**: In WhatsApp → Settings

4. **Configure in BulkSms**
   ```
   /whatsapp/configure
   
   Provider: WhatsApp Cloud API
   Phone Number ID: [from Meta]
   Access Token: [from Meta]
   Business Account ID: [from Meta]
   Webhook Verify Token: [create your own secret]
   
   Click Save
   ```

5. **Set up Webhook** (Optional - for receiving messages)
   ```
   Callback URL: https://yourdomain.com/webhook/whatsapp
   Verify Token: [same as above]
   ```

---

## Advanced Debugging

### Enable Detailed Logging

Add to `config/logging.php`:
```php
'whatsapp' => [
    'driver' => 'single',
    'path' => storage_path('logs/whatsapp.log'),
    'level' => 'debug',
],
```

Update WhatsApp senders to use this channel:
```php
Log::channel('whatsapp')->info('WhatsApp API Request', $data);
```

### Check API Response

For UltraMsg, test directly:
```bash
curl "https://api.ultramsg.com/YOUR_INSTANCE/messages/chat" \
  -X POST \
  -d "token=YOUR_TOKEN" \
  -d "to=+254712345678" \
  -d "body=Test message"
```

For Cloud API:
```bash
curl https://graph.facebook.com/v21.0/YOUR_PHONE_NUMBER_ID/messages \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "messaging_product": "whatsapp",
    "to": "254712345678",
    "type": "text",
    "text": {
      "body": "Test message"
    }
  }'
```

---

## Quick Fixes

### Clear Config Cache
```bash
php artisan config:clear
php artisan cache:clear
```

### Reset WhatsApp Configuration
```php
// In tinker
$channel = App\Models\Channel::where('name', 'whatsapp')->first();
$channel->delete();

// Then reconfigure via /whatsapp/configure
```

### Check User Balance (if applicable)
```php
// In tinker
$user = Auth::user();
echo "Balance: " . $user->balance;
```

---

## Support & Documentation

- **UltraMsg Docs**: https://docs.ultramsg.com/
- **WhatsApp Cloud API Docs**: https://developers.facebook.com/docs/whatsapp
- **Laravel Logs**: `storage/logs/laravel.log`
- **Server Requirements**: PHP 8.1+, cURL enabled, OpenSSL enabled

---

## Need More Help?

If messages still aren't sending after following this guide:

1. **Check Laravel logs**:
   ```bash
   tail -100 storage/logs/laravel.log
   ```

2. **Look for specific error messages**
3. **Test with curl** (see Advanced Debugging)
4. **Verify provider account status**
5. **Check network/firewall settings**

**Common Final Checks:**
- [ ] Apache/Nginx is running
- [ ] Internet connection is active
- [ ] PHP extensions enabled: `curl`, `openssl`, `mbstring`
- [ ] Provider account is active and funded (if paid)
- [ ] Phone numbers have country codes
- [ ] Recipient numbers are valid WhatsApp users

---

**Last Updated:** October 2025
**Version:** 2.0

