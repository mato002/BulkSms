# Testing SMS Send via Web CRM - TEST_SENDER

## Overview
This guide will help you test sending an SMS from TEST_SENDER to 254728883160 using the web CRM interface.

---

## Prerequisites Check

Run this script to ensure TEST_SENDER is ready:
```bash
php check_test_sender.php
```

This will:
- ✅ Verify TEST_SENDER client exists
- ✅ Create user account if needed (test@sender.com / password123)
- ✅ Configure SMS channel
- ✅ Add balance if low

---

## Step-by-Step Guide

### Step 1: Start the Laravel Server

```bash
php artisan serve
```

You should see:
```
Starting Laravel development server: http://127.0.0.1:8000
```

### Step 2: Open the Web Application

Open your browser and go to:
```
http://localhost:8000
```

or
```
http://127.0.0.1:8000
```

### Step 3: Login

1. Go to: `http://localhost:8000/login`
2. Enter credentials:
   - **Email:** `test@sender.com`
   - **Password:** `password123` (or the one you set)

### Step 4: Navigate to Inbox/Messages

You have **3 options** to send a message:

#### Option A: Via Inbox
1. Click **"Inbox"** in the sidebar
2. Click **"New Message"** or **"Start Conversation"**
3. Select or enter recipient: `254728883160`

#### Option B: Via Contacts  
1. Click **"Contacts"** in the sidebar
2. Find or create contact with number `254728883160`
3. Click **"Send Message"** or **"Start Conversation"**

#### Option C: Via Campaigns (Bulk Send)
1. Click **"Campaigns"** in the sidebar
2. Create a new campaign
3. Add recipient `254728883160`
4. Send the campaign

---

## Sending the Test Message

### Using the Inbox (Recommended)

1. **Go to Inbox:**
   ```
   http://localhost:8000/inbox
   ```

2. **Start a New Conversation:**
   - Click "New Message" or similar button
   - Enter recipient: `254728883160`

3. **Compose Message:**
   - Type: "Test message from TEST_SENDER via CRM at [current time]"
   - Sender should auto-select as TEST_SENDER

4. **Send:**
   - Click "Send" button
   - Watch for success notification

### Creating a Contact First (Alternative)

If the contact doesn't exist:

1. **Go to Contacts:**
   ```
   http://localhost:8000/contacts
   ```

2. **Add Contact:**
   - Click "Add Contact"
   - Name: Test Contact
   - Phone: 254728883160
   - Save

3. **Send Message:**
   - Click on the contact
   - Click "Send Message" or "Start Chat"
   - Type your message
   - Send

---

## What to Check After Sending

### 1. Success Message
You should see a notification like:
- ✅ "Message sent successfully"
- ✅ "Message queued for sending"

### 2. Check Message Status

#### Via Inbox:
```
http://localhost:8000/inbox
```
- You should see the conversation
- Message status should show as "sent" or "delivered"

#### Via Messages:
```
http://localhost:8000/messages
```
- Recent messages should appear here
- Check status column

### 3. Check Balance Deduction

Go to Dashboard or Settings to verify:
- Balance should decrease by ~1 unit
- Transaction should appear in wallet/balance history

### 4. Verify in Database (Optional)

```bash
php artisan tinker --execute="$msg = App\Models\Message::latest()->first(); echo 'ID: ' . $msg->id . PHP_EOL; echo 'To: ' . $msg->recipient . PHP_EOL; echo 'Status: ' . $msg->status . PHP_EOL; echo 'Body: ' . $msg->body . PHP_EOL;"
```

---

## Troubleshooting

### Issue: Can't Login

**Check user exists:**
```bash
php artisan tinker --execute="$user = App\Models\User::where('email', 'test@sender.com')->first(); if($user) { echo 'User exists'; } else { echo 'User not found - run check_test_sender.php'; }"
```

**Reset password:**
```bash
php artisan tinker --execute="$user = App\Models\User::where('email', 'test@sender.com')->first(); $user->password = Hash::make('password123'); $user->save(); echo 'Password reset to: password123';"
```

### Issue: No Balance

**Add balance:**
```bash
php artisan tinker --execute="$client = App\Models\Client::where('sender_id', 'TEST_SENDER')->first(); $client->balance = 100; $client->save(); echo 'Balance added: KSH 100';"
```

### Issue: SMS Not Sending

**Check channel configuration:**
```bash
php artisan tinker --execute="$client = App\Models\Client::where('sender_id', 'TEST_SENDER')->first(); $channel = $client->smsChannel; if($channel) { echo 'Provider: ' . $channel->provider . PHP_EOL; echo 'Active: ' . ($channel->active ? 'Yes' : 'No') . PHP_EOL; } else { echo 'No channel - run check_test_sender.php'; }"
```

### Issue: Page Not Found

**Check routes:**
```bash
php artisan route:list | grep inbox
php artisan route:list | grep messages
php artisan route:list | grep contacts
```

---

## Expected Result

After sending the SMS, you should see:

1. ✅ **Web Interface:** Success notification
2. ✅ **Phone 254728883160:** Receives SMS from TEST_SENDER
3. ✅ **Inbox:** Conversation appears with status "sent" or "delivered"
4. ✅ **Balance:** Decreased by cost of 1 SMS
5. ✅ **Database:** Message record created with all details

---

## Quick Commands Reference

### Start Server:
```bash
php artisan serve
```

### Check Setup:
```bash
php check_test_sender.php
```

### View Recent Messages:
```bash
php artisan tinker --execute="App\Models\Message::latest()->take(5)->get()->each(function($m) { echo $m->recipient . ' - ' . $m->status . ' - ' . $m->created_at . PHP_EOL; });"
```

### Check Balance:
```bash
php artisan tinker --execute="$c = App\Models\Client::where('sender_id', 'TEST_SENDER')->first(); echo 'Balance: KSH ' . $c->balance . PHP_EOL;"
```

---

## Alternative: Direct PHP Test

If web interface has issues, you can test directly:

```bash
php test_sender_sms.php
```

This will:
- Setup TEST_SENDER
- Send SMS via API
- Show all results

---

## URLs Quick Reference

| Page | URL |
|------|-----|
| Login | http://localhost:8000/login |
| Dashboard | http://localhost:8000/ |
| Inbox | http://localhost:8000/inbox |
| Contacts | http://localhost:8000/contacts |
| Messages | http://localhost:8000/messages |
| Campaigns | http://localhost:8000/campaigns |
| Settings | http://localhost:8000/settings |

---

## Next Steps

1. ✅ Run `php check_test_sender.php`
2. ✅ Start server: `php artisan serve`
3. ✅ Login: http://localhost:8000/login
4. ✅ Go to Inbox: http://localhost:8000/inbox
5. ✅ Send message to 254728883160
6. ✅ Check phone for SMS delivery

---

**Ready to test? Start the server and login!** 🚀

