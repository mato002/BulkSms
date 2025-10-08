# 🚀 UltraMsg WhatsApp - Quick Setup Guide

## Step 1: Get UltraMsg Credentials

1. **Visit**: https://ultramsg.com
2. **Sign Up** or **Login**
3. **Create a New Instance**:
   - Click "Create Instance"
   - Choose a plan (they have free trial)
   - Wait for instance to be created

4. **Get Your Credentials**:
   - **Instance ID**: Found in your dashboard (e.g., `instance143390`)
   - **Token**: Click "Show Token" in your instance settings

## Step 2: Connect Your WhatsApp

1. In UltraMsg dashboard, click **"Connect"**
2. **Scan QR Code** with your WhatsApp mobile app:
   - Open WhatsApp on your phone
   - Go to Settings → Linked Devices
   - Click "Link a Device"
   - Scan the QR code shown in UltraMsg

3. **Wait for "Connected"** status ✅

## Step 3: Configure in Laravel App

### Option A: Via Web Interface (Recommended)

1. Go to `/whatsapp/configure` in your browser
2. Select **UltraMsg** as provider
3. Enter:
   - **Instance ID**: Your instance ID (e.g., `instance143390`)
   - **Token**: Your UltraMsg API token
4. Click **Save Configuration**
5. Click **Test Connection** to verify

### Option B: Via Database (Quick)

Run this command in your terminal:

```bash
php artisan tinker
```

Then paste:

```php
DB::table('channels')->updateOrInsert(
    ['client_id' => 1, 'name' => 'whatsapp'],
    [
        'provider' => 'ultramsg',
        'credentials' => json_encode([
            'instance_id' => 'YOUR_INSTANCE_ID',  // Replace with your instance ID
            'token' => 'YOUR_TOKEN'                // Replace with your token
        ]),
        'active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]
);
```

## Step 4: Test It

### Via Web Interface:
1. Go to `/whatsapp`
2. Click "Send Test Message"
3. Enter phone number: `+254728883160` (with country code)
4. Type a message
5. Click Send

### Via Test Script:
```bash
php test_whatsapp.php
```

## Troubleshooting

### "Instance ID: NOT SET"
- You haven't configured credentials yet
- Follow Step 3 above

### "SSL Certificate Error"
- Already fixed! The code now handles Windows SSL issues

### "Message not delivered"
- Check if WhatsApp is connected in UltraMsg dashboard
- Make sure phone number has country code (e.g., `+254` for Kenya)
- Verify the recipient has WhatsApp installed

### "Failed to send"
- Check UltraMsg dashboard for instance status
- Verify your token is correct
- Check if you have credits/balance in UltraMsg

## Important Notes

✅ **Phone Number Format**: Always use international format (`+254728883160`)
✅ **WhatsApp Must Be Connected**: Check UltraMsg dashboard
✅ **Inbox Integration**: Messages now automatically appear in `/inbox`
✅ **Free Tier Limits**: UltraMsg free tier has message limits

## Quick Commands

```bash
# Test WhatsApp
php test_whatsapp.php

# Check configuration
php artisan tinker --execute="DB::table('channels')->where('name','whatsapp')->first();"

# View recent messages
php artisan tinker --execute="DB::table('messages')->where('channel','whatsapp')->latest()->take(5)->get();"

# Check logs
tail -50 storage/logs/laravel.log
```

## Need Help?

1. Check UltraMsg dashboard: https://ultramsg.com
2. View API docs: https://docs.ultramsg.com
3. Check Laravel logs: `storage/logs/laravel.log`

