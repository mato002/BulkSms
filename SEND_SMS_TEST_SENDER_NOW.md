# Send SMS from TEST_SENDER to 254728883160 - Quick Guide

## ✅ Easiest Method: Start a Conversation from a Contact

###Step 1: Check Setup
```bash
php check_test_sender.php
```

This will ensure:
- ✅ TEST_SENDER client exists
- ✅ User account created (test@sender.com)
- ✅ Balance added
- ✅ SMS channel configured

### Step 2: Start Server
```bash
php artisan serve
```

Leave this running.

### Step 3: Login

**Open in browser:** `http://localhost:8000/login`

**Credentials:**
- Email: `test@sender.com`  
- Password: `password123`

### Step 4: Create the Contact (if not exists)

**Go to:** `http://localhost:8000/contacts`

1. Click **"Add Contact"** button
2. Fill in:
   - **Name:** Test Client
   - **Phone:** 254728883160  
   - **Email:** (optional)
3. Click **"Save"**

### Step 5: Start a Conversation with the Contact

From the Contacts page:
1. Find the contact "Test Client" (254728883160)
2. Click on the contact
3. Look for **"Send Message"** or **"Start Conversation"** button
4. Click it

OR use this direct URL:
```
http://localhost:8000/inbox/start/{{contact_id}}
```

### Step 6: Send the Message

In the chat interface:
1. Type your message: "Test from TEST_SENDER at [time]"
2. Press **Enter** or click **"Send"**
3. Wait for confirmation

### Step 7: Verify

**Check the phone 254728883160** - should receive the SMS!

---

## 🔄 Alternative: Use Campaigns (Bulk Send)

If the inbox method doesn't work, try sending via Campaign:

### Step 1-3: Same as above

### Step 4: Create Campaign

**Go to:** `http://localhost:8000/campaigns`

1. Click **"Create Campaign"**
2. Fill in:
   - **Name:** Test Campaign
   - **Message:** Test from TEST_SENDER
   - **Sender ID:** TEST_SENDER
   - **Channel:** SMS
3. Click **"Next"** or **"Save"**

### Step 5: Add Recipients

1. Select or add contact: 254728883160
2. Or manually enter the number

### Step 6: Send

1. Review the campaign
2. Click **"Send Campaign"**
3. Confirm

---

## 🚀 Fastest: Direct PHP Script

If you want to skip the web interface:

```bash
php test_sender_sms.php
```

This will:
1. Setup TEST_SENDER
2. Send SMS via API to 254728883160
3. Show results immediately

---

## 📱 What You Should See

### On Web:
- ✅ Success message: "Message sent successfully"
- ✅ Conversation appears in Inbox
- ✅ Message shows as "sent" or "delivered"

### On Phone (254728883160):
- ✅ SMS received from TEST_SENDER
- ✅ Contains your test message

### In Dashboard:
- ✅ Balance decreased by ~1 unit
- ✅ Message count increased

---

## 🔧 Troubleshooting

### Can't find "Send Message" button?

**Option A:** Go to Inbox and look for "New Conversation"

**Option B:** Try this URL structure:
```
http://localhost:8000/inbox/start/{contact_id}
```
Replace `{contact_id}` with the actual ID of the contact.

**Option C:** Use campaigns instead (see alternative method above)

### Login fails?

Reset password:
```bash
php artisan tinker --execute="$user = App\Models\User::where('email', 'test@sender.com')->first(); $user->password = Hash::make('password123'); $user->save(); echo 'Password: password123';"
```

### No balance?

Add balance:
```bash
php artisan tinker --execute="$client = App\Models\Client::where('sender_id', 'TEST_SENDER')->first(); $client->balance = 100; $client->save(); echo 'Added KSH 100';"
```

### SMS not sending?

Check logs:
```bash
tail -f storage/logs/laravel.log
```

---

## 🎯 Recommended Flow

**For simplest test:**
1. Run: `php check_test_sender.php`
2. Run: `php test_sender_sms.php`
3. Check phone 254728883160

**For web CRM test:**
1. Run: `php check_test_sender.php`
2. Start server: `php artisan serve`
3. Login: http://localhost:8000/login
4. Go to Contacts: http://localhost:8000/contacts
5. Create contact: 254728883160
6. Click contact → Send Message
7. Type message → Send

---

## 📞 Expected Result

**Within 30 seconds**, phone 254728883160 should receive:
```
Test from TEST_SENDER at [time]
```

**Sender shown as:** TEST_SENDER

---

Ready to test? Choose your method and let's send that SMS! 🚀

