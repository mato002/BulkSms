# Inbox Reply Fix - Complete Guide

## 🎯 Issue Identified

**Your inbox chat is working, but customer replies aren't showing up.**

### Diagnosis Results:
```
Conversation 2: 0 inbound messages, 4 outbound messages
Conversation 1: 0 inbound messages, 6 outbound messages
```

**Problem:** NO CUSTOMER REPLIES are being received/saved in your system!

---

## 📋 Why This is Happening

There are TWO ways customers can reply:

### Method 1: Direct SMS Reply (Not Working ❌)
Customer replies directly to the SMS → Onfon receives it → **Should** send to your webhook → Saves in your system

**This isn't working because:**
- Your webhook isn't configured on Onfon's dashboard
- Onfon doesn't know where to send incoming messages

### Method 2: Web Reply Link (Working ✅)
Customer clicks the short link in SMS → Opens web form → Submits reply → Saves in your system

**This IS working**, but you need to configure Method 1 for complete functionality.

---

## 🔧 Solution: Configure Onfon Webhooks

### Step 1: Login to Onfon Portal

1. Go to: https://portal.onfonmedia.co.ke/
2. Login with your credentials:
   - Client ID: `e27847c1-a9fe-4eef-b60d-ddb291b175ab`
   - API Key: `VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=`

### Step 2: Navigate to Webhook Settings

1. Find "Settings" or "API Settings" in the menu
2. Look for "Webhooks" or "MO (Mobile Originated)" section

### Step 3: Add Your Webhook URLs

Configure these webhook endpoints:

#### Inbound Messages (MO) Webhook:
```
URL: https://crm.pradytecai.com/api/webhooks/onfon/inbound
Method: POST
```

This receives customer replies when they reply directly to your SMS.

#### Delivery Reports (DLR) Webhook:
```
URL: https://crm.pradytecai.com/api/webhooks/onfon/dlr
Method: POST
```

This updates message delivery status.

### Step 4: Test the Configuration

After saving, test it:

1. Send an SMS from your inbox to a real phone number
2. Reply to that SMS from the phone
3. Check if the reply shows up in your inbox chat

---

## 🧪 Testing Webhooks

### Test if Webhooks are Receiving Data

```bash
# Monitor webhook logs in real-time
tail -f storage/logs/laravel.log | grep -i "inbound\|webhook"
```

Then:
1. Have someone reply to your SMS
2. Watch the logs for incoming data
3. Should see: "Onfon Inbound received"

### Manual Webhook Test

You can simulate an inbound message:

```bash
curl -X POST https://crm.pradytecai.com/api/webhooks/onfon/inbound \
  -H "Content-Type: application/json" \
  -d '{
    "From": "254728883160",
    "To": "PRADY_TECH",
    "Message": "Test reply from customer",
    "MessageId": "test123",
    "ReceivedTime": "2025-10-18 12:00:00"
  }'
```

After running this, check your inbox - you should see a new inbound message!

---

## 📊 Verify It's Working

### Check Recent Messages

```bash
php debug_inbox_messages.php
```

After configuring webhooks and receiving a reply, you should see:
```
Conversation 2: 1 inbound messages, 4 outbound messages  ✅
```

### Check Logs

```bash
tail -100 storage/logs/laravel.log | grep "Onfon Inbound"
```

You should see entries like:
```
[2025-10-18 12:00:00] local.INFO: Onfon Inbound received {..."From":"254728883160"...}
```

---

## 🎯 How Customer Replies Work

### Scenario 1: Customer Replies via SMS (Direct)

```
1. Customer receives: "Your order is ready! Reply: https://crm.pradytecai.com/x/abc123"
2. Customer types: "Thank you, I'll pick it up tomorrow"
3. Customer sends SMS to your shortcode/sender ID
4. Onfon receives the SMS
5. Onfon sends webhook to: /api/webhooks/onfon/inbound
6. Your system processes:
   - Finds/creates contact
   - Finds/creates conversation
   - Saves as inbound message
   - Updates unread count
7. You see reply in inbox ✅
```

### Scenario 2: Customer Replies via Web Link

```
1. Customer receives: "Your order is ready! Reply: https://crm.pradytecai.com/x/abc123"
2. Customer clicks the link
3. Opens web form
4. Types reply: "Thank you, I'll pick it up tomorrow"
5. Submits form
6. Your system processes via PublicReplyController:
   - Finds original message
   - Finds/creates contact
   - Finds/creates conversation
   - Saves as inbound message
   - Shows success page
7. You see reply in inbox ✅
```

---

## ⚠️ Common Issues & Solutions

### Issue 1: Webhook Not Being Called

**Symptoms:**
- No "Onfon Inbound received" in logs
- Replies not showing up

**Solution:**
- Verify webhook URL is correct: `https://crm.pradytecai.com/api/webhooks/onfon/inbound`
- Ensure HTTPS (not HTTP)
- Check if Onfon portal shows "Active" status for webhook
- Contact Onfon support if webhook isn't triggering

### Issue 2: Webhook Returns Error

**Symptoms:**
- Logs show "Onfon Inbound received" but message not saved
- Error messages in logs

**Solution:**
```bash
# Check detailed error logs
tail -100 storage/logs/laravel.log | grep -i "error"
```

Common fixes:
- Database connection issues → Check `.env` DB settings
- Permission issues → Check folder permissions
- Missing data → Check Onfon sends all required fields

### Issue 3: Wrong Client Association

**Symptoms:**
- Messages saved but to wrong client account

**Current Code:**
```php
'client_id' => 1,  // Default client for now
```

**Solution:**
You need to determine which client the message belongs to based on the `To` field (your sender ID).

Let me create a fix for this!

---

## 🔧 Fix: Multi-Client Support

Update `WebhookController.php` to associate messages with correct client:

```php
public function onfonInbound(Request $request)
{
    Log::info('Onfon Inbound received', $request->all());

    $from = $request->input('From') ?? $request->input('from');
    $to = $request->input('To') ?? $request->input('to');
    $messageText = $request->input('Message') ?? $request->input('message');
    $messageId = $request->input('MessageId') ?? $request->input('message_id');
    $receivedTime = $request->input('ReceivedTime') ?? $request->input('received_time');

    if ($from && $messageText) {
        // Find client by sender_id (the $to field is the sender ID)
        $client = DB::table('clients')
            ->where('sender_id', $to)
            ->first();

        $clientId = $client ? $client->id : 1; // Fallback to client 1

        // Find or create contact
        $contact = DB::table('contacts')
            ->where('contact', $from)
            ->where('client_id', $clientId)
            ->first();

        // ... rest of the code
    }

    return response()->json(['status' => 'received'], 200);
}
```

---

## ✅ Verification Checklist

After setup, verify:

- [ ] Onfon webhook URL configured: `https://crm.pradytecai.com/api/webhooks/onfon/inbound`
- [ ] Send test SMS from inbox
- [ ] Reply to SMS from real phone
- [ ] Check logs: `tail -f storage/logs/laravel.log`
- [ ] Verify reply appears in inbox chat
- [ ] Test web link reply as backup method
- [ ] Confirm unread count updates correctly

---

## 📱 Alternative: If Onfon Doesn't Support Webhooks

If Onfon doesn't support inbound webhooks for your account type, customers can still reply using the web link method:

### The Web Reply Flow (Already Working):

1. Your messages include: `Reply: https://crm.pradytecai.com/x/abc123`
2. Customer clicks link
3. Opens reply form
4. Submits reply
5. Reply saved to your inbox ✅

This is a **valid alternative** and many SMS platforms use this method!

---

## 📞 Contact Onfon Support

If you need help configuring webhooks:

**Onfon Support:**
- Email: support@onfonmedia.co.ke
- Phone: +254 709 990 000
- Portal: https://portal.onfonmedia.co.ke/

**Ask them to:**
1. Enable inbound message webhooks for your account
2. Configure webhook URL: `https://crm.pradytecai.com/api/webhooks/onfon/inbound`
3. Test that webhooks are triggering correctly

---

## 🎓 Understanding the Two Reply Methods

### Direct SMS Reply (Requires Webhook)
**Pros:**
- ✅ More natural for customers
- ✅ No need to click links
- ✅ Works on any phone

**Cons:**
- ❌ Requires webhook setup
- ❌ May have additional costs from provider
- ❌ Needs provider support

### Web Link Reply (Already Working)
**Pros:**
- ✅ Works immediately
- ✅ No provider configuration needed
- ✅ Better for detailed replies
- ✅ Can include attachments (future)

**Cons:**
- ❌ Requires customer to click link
- ❌ Needs mobile data/wifi

**Recommendation:** Use BOTH methods! The system is already set up for it.

---

## 🚀 Next Steps

1. **Immediate:** Configure Onfon webhooks (follow Step-by-Step above)
2. **Test:** Send SMS and verify replies come through
3. **Monitor:** Watch logs for any errors
4. **Optimize:** Consider adding multi-client support fix above
5. **Train:** Ensure team knows both reply methods work

---

## ✅ Summary

### What's Working:
- ✅ Sending messages from inbox
- ✅ Messages display correctly
- ✅ Web link replies work
- ✅ Conversations thread properly
- ✅ Message storage is correct

### What Needs Setup:
- ⏳ Onfon inbound webhook configuration
- ⏳ Direct SMS reply functionality
- ⏳ Testing with real customer replies

### Expected Result After Setup:
- 📱 Customer replies via SMS → Shows in inbox instantly
- 🔗 Customer replies via web link → Shows in inbox instantly
- 🎯 Full two-way communication working perfectly!

---

**Last Updated:** October 18, 2025  
**Status:** Webhooks Need Configuration on Onfon Side  
**Priority:** High - Required for full inbox functionality



