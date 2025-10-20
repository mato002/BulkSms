# Setup ngrok for Local Testing - Web Link Replies

## 🎯 Goal
Test web link replies on your local machine by making it accessible via ngrok.

---

## 📋 Prerequisites

1. **ngrok installed** - Download from: https://ngrok.com/download
2. **XAMPP running** - Apache and MySQL should be started
3. **Your local app** - Running on http://localhost/BulkSms

---

## 🚀 Step-by-Step Setup

### Step 1: Start ngrok

Open a **new terminal/command prompt** and run:

```bash
ngrok http 80
```

Or if your app is on a different port:
```bash
ngrok http localhost:80
```

You should see something like:
```
Session Status    online
Account           Your Name
Version           3.x.x
Region            United States (us)
Forwarding        https://abc123.ngrok-free.app -> http://localhost:80
```

**Copy the https URL** (e.g., `https://abc123.ngrok-free.app`)

---

### Step 2: Update .env File

Edit your local `.env` file:

```bash
# Change this:
APP_URL=http://localhost

# To your ngrok URL:
APP_URL=https://abc123.ngrok-free.app
```

**Important:** Use the HTTPS URL from ngrok, not HTTP!

---

### Step 3: Clear Config Cache

```bash
php artisan config:clear
```

---

### Step 4: Test It!

1. **Send a message from inbox:**
   - Go to: http://localhost/BulkSms/inbox
   - Open a conversation
   - Send a test message

2. **Check the message:**
   - The SMS should contain: `Reply: https://abc123.ngrok-free.app/x/xyz123`

3. **Click the link:**
   - Opens on any device (phone, tablet, another computer)
   - Should show reply form

4. **Submit a reply:**
   - Fill out the form
   - Submit
   - Check your inbox - reply should appear! ✅

---

## 🧪 Quick Test Command

Run this to test webhook locally:

```bash
php test_webhook_reply.php
```

Then check: http://localhost/BulkSms/inbox/1

---

## ⚠️ Important Notes

### ngrok URL Changes
- **Free ngrok:** URL changes every time you restart ngrok
- **Need to update .env** each time you restart
- **Paid ngrok:** Can get a permanent URL

### Alternative: Use ngrok in .env Directly

Create a `.env.ngrok` file:
```env
APP_NAME=BulkSMSPlatform
APP_ENV=local
APP_KEY=base64:yKKkeh9b6J2NJ6OpnwgInEXK2DQyg34BmQ258BSlg6k=
APP_DEBUG=true
APP_URL=https://YOUR-NGROK-URL.ngrok-free.app
# ... rest of your settings
```

Switch between environments:
```bash
# Use ngrok
cp .env.ngrok .env
php artisan config:clear

# Back to localhost
cp .env.local .env
php artisan config:clear
```

---

## 🎯 Testing Workflow

### 1. Start Everything:
```bash
# Terminal 1: Start XAMPP (or just ensure it's running)
# Services should be green

# Terminal 2: Start ngrok
ngrok http 80

# Copy the ngrok URL shown
```

### 2. Update Configuration:
```bash
# Terminal 3: Update .env
# Change APP_URL to ngrok URL

# Clear cache
php artisan config:clear
```

### 3. Test:
```bash
# Send message from inbox
# Check message has ngrok URL
# Click link from phone
# Submit reply
# Verify appears in inbox
```

---

## 🔧 Troubleshooting

### Issue 1: "Invalid Host" Error

**Symptom:** ngrok shows "Invalid Host" when you visit the URL

**Solution:** Add this to `.env`:
```env
NGROK_SKIP_HOST_CHECK=true
```

Or click "Visit Site" on the ngrok warning page.

### Issue 2: URL Still Shows Localhost

**Symptom:** Messages still have localhost URLs

**Solution:**
```bash
# Make sure you cleared cache
php artisan config:clear

# Check current config
php artisan tinker
>>> config('app.url')
# Should show ngrok URL, not localhost
```

### Issue 3: ngrok URL Not Working

**Symptom:** Can't access the ngrok URL

**Solution:**
- Make sure XAMPP Apache is running
- Check if http://localhost/BulkSms works first
- Verify ngrok shows "online" status
- Try the ngrok URL in incognito mode

### Issue 4: Reply Not Saving

**Symptom:** Can submit reply but doesn't appear in inbox

**Solution:**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Test webhook directly
php test_webhook_reply.php

# Check database
php debug_inbox_messages.php
```

---

## 📱 Testing from Mobile Device

### Method 1: Click Link from SMS
1. Send message from inbox
2. Send that message to a real phone
3. Click the link on the phone
4. Should open reply form
5. Submit and check inbox

### Method 2: Share Link Directly
1. Copy the short URL from message
2. Send via WhatsApp/Email to your phone
3. Click and test

### Method 3: QR Code
1. Generate QR code for the short URL
2. Scan with phone camera
3. Test reply form

---

## 🎓 Understanding the Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    LOCAL TESTING FLOW                       │
└─────────────────────────────────────────────────────────────┘

1. XAMPP (localhost:80)
   ↓
2. ngrok Tunnel (https://abc123.ngrok-free.app)
   ↓
3. Internet (accessible from anywhere)
   ↓
4. Customer's Phone
   ↓
5. Clicks Link
   ↓
6. ngrok forwards to localhost
   ↓
7. Your Laravel app handles reply
   ↓
8. Saves to local database
   ↓
9. Shows in your inbox!
```

---

## 💡 Pro Tips

### Tip 1: Keep ngrok Running
- Don't close the ngrok terminal
- URL stays active as long as ngrok runs
- If you close it, you need a new URL

### Tip 2: Use Paid ngrok for Serious Testing
- Get permanent URL
- No need to update .env constantly
- More reliable for testing
- Cost: ~$8/month

### Tip 3: Test Both Reply Methods
```bash
# Method 1: Web link reply (works now with ngrok)
# Customer clicks link → submits form → reply saves

# Method 2: Direct SMS reply (needs Onfon webhook)
# Customer replies via SMS → Onfon webhook → reply saves
```

### Tip 4: Monitor in Real-Time
```bash
# Terminal 4: Watch logs
tail -f storage/logs/laravel.log | grep -i "reply\|inbound"

# When customer submits reply, you'll see it here
```

---

## 🚀 Quick Start Commands

```bash
# 1. Start ngrok
ngrok http 80

# 2. Copy the https URL, then update .env
# APP_URL=https://YOUR-NGROK-URL.ngrok-free.app

# 3. Clear cache
php artisan config:clear

# 4. Test
php test_webhook_reply.php

# 5. Check inbox
# http://localhost/BulkSms/inbox
```

---

## ✅ Verification Checklist

After setup:
- [ ] ngrok running and shows "online"
- [ ] .env has ngrok URL (not localhost)
- [ ] Config cleared: `php artisan config:clear`
- [ ] Test message sent from inbox
- [ ] Message contains ngrok URL (not localhost)
- [ ] Link opens on phone
- [ ] Reply form displays correctly
- [ ] Can submit reply
- [ ] Reply appears in inbox

---

## 📊 Compare: Before vs After

### Before ngrok:
```
Message: "Your order is ready! Reply: http://localhost/x/abc"
Customer clicks: ❌ Doesn't work (localhost not accessible)
```

### After ngrok:
```
Message: "Your order is ready! Reply: https://abc123.ngrok-free.app/x/abc"
Customer clicks: ✅ Works! Opens reply form
Customer submits: ✅ Saves to inbox
```

---

## 🎯 When to Use What

### Use ngrok (Local Testing):
- ✅ Testing web link replies
- ✅ Testing webhooks locally
- ✅ Demo to clients
- ✅ Development and debugging

### Use Hostinger (Production):
- ✅ Real customer messages
- ✅ Permanent URL
- ✅ Better performance
- ✅ Professional setup

---

## 📞 Need Help?

### ngrok Issues:
- Docs: https://ngrok.com/docs
- Dashboard: https://dashboard.ngrok.com/

### App Issues:
```bash
# Check logs
tail -100 storage/logs/laravel.log

# Test webhook
php test_webhook_reply.php

# Debug messages
php debug_inbox_messages.php
```

---

**Last Updated:** October 18, 2025  
**Status:** Ready for Testing  
**Time to Setup:** 5 minutes



