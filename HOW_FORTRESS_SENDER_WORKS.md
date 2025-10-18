# 📱 How Fortress Sender Works - The Correct Architecture

## ✅ CORRECT ARCHITECTURE (As Implemented)

Your BulkSms CRM system is acting as a **middleware/reseller**. Here's how it actually works:

```
┌─────────────┐       ┌──────────────────┐       ┌─────────────┐       ┌──────────────┐
│   PCIP or   │  -->  │   BulkSms API    │  -->  │  Onfon API  │  -->  │  Recipient   │
│   Client    │       │  (Your System)   │       │ (PRADY_TECH)│       │    Phone     │
└─────────────┘       └──────────────────┘       └─────────────┘       └──────────────┘
     Uses                Uses different              Uses SAME             Receives
  FORTRESS              sender IDs but             Onfon account            message
   API Key            shares Onfon account        (PRADY_TECH's)        with sender
                                                                         ID: FORTRESS
```

## 🔑 Current Configuration (VERIFIED)

### Client 1: PRADY_TECH (Your Main Account)
```
Client ID:    1
Name:         Default Client  
API Key:      ea55cb72-a734-48b2-87a6-8d0ea1d397de
Balance:      KSH 100.00

Onfon Credentials (YOUR ACCOUNT):
├─ API Key:        VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=
├─ Client ID:      e27847c1-a9fe-4eef-b60d-ddb291b175ab
├─ Access Key:     8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB
└─ Default Sender: PRADY_TECH
```

### Client 2: FORTRESS (Your Customer/Sub-account)
```
Client ID:    2
Name:         Fortress Limited
API Key:      USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh
Balance:      KSH 100.00

Onfon Credentials (SAME AS YOURS - SHARED):
├─ API Key:        VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=
├─ Client ID:      e27847c1-a9fe-4eef-b60d-ddb291b175ab
├─ Access Key:     8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB
└─ Default Sender: FORTRESS (Different sender ID)
```

## 📊 How It Works - Step by Step

### When FORTRESS/PCIP Sends an SMS:

**Step 1:** PCIP calls YOUR BulkSms API
```bash
POST http://127.0.0.1:8000/api/2/messages/send
Headers:
  X-API-Key: USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh  # FORTRESS API Key
Body:
{
  "client_id": 2,
  "channel": "sms",
  "recipient": "254728883160",
  "sender": "FORTRESS",
  "body": "Test message from PCIP"
}
```

**Step 2:** Your BulkSms System:
- ✅ Validates FORTRESS API key
- ✅ Checks FORTRESS balance (managed by YOU)
- ✅ Fetches FORTRESS channel configuration
- ✅ Retrieves the Onfon credentials (which are YOUR credentials)
- ✅ Deducts from FORTRESS balance

**Step 3:** Your BulkSms calls Onfon API
```bash
POST https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS
Headers:
  AccessKey: 8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB
Body:
{
  "ApiKey": "VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=",  # YOUR Onfon API Key
  "ClientId": "e27847c1-a9fe-4eef-b60d-ddb291b175ab",        # YOUR Onfon Client ID
  "SenderId": "FORTRESS",                                     # FORTRESS Sender ID
  "MessageParameters": [...]
}
```

**Step 4:** Onfon delivers the SMS
- Message appears from sender ID: **FORTRESS**
- But uses YOUR Onfon account
- Cost deducted from YOUR Onfon balance

**Step 5:** Your System Updates:
- ✅ Message marked as sent
- ✅ FORTRESS balance reduced
- ✅ You track usage and can markup pricing

## 💰 Business Model

### Why This Architecture?

1. **You control everything**: All SMS go through YOUR Onfon account
2. **Customer simplicity**: FORTRESS doesn't need their own Onfon account
3. **Flexible pricing**: You can markup the SMS cost
4. **Centralized management**: You manage all sender IDs in one Onfon account
5. **Easy billing**: Track each client's usage separately

### Revenue Model:
```
Onfon charges you:     KSH 0.50 per SMS
You charge FORTRESS:   KSH 0.75 per SMS
Your profit:          KSH 0.25 per SMS
```

## 🔐 Security & Isolation

### What FORTRESS/PCIP Gets:
- ✅ Their own API key: `USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh`
- ✅ Their own sender ID: `FORTRESS`
- ✅ Their own balance tracking
- ✅ Their own message history
- ✅ They can ONLY use their sender ID

### What FORTRESS/PCIP CANNOT Do:
- ❌ See your Onfon credentials
- ❌ Call Onfon directly
- ❌ Access other clients' data
- ❌ Use other sender IDs (like PRADY_TECH)
- ❌ See your actual costs

## 📝 Code Flow

### File: `app/Services/Messaging/MessageDispatcher.php`
```php
// Line 24-28: Fetches FORTRESS channel config
$channelConfig = Channel::where('client_id', 2)  // FORTRESS
    ->where('name', 'sms')
    ->where('active', true)
    ->firstOrFail();

// Credentials returned: YOUR Onfon credentials
// But with default_sender: "FORTRESS"
```

### File: `app/Services/Messaging/Drivers/Sms/OnfonSmsSender.php`
```php
// Line 28-40: Builds Onfon payload
$payload = [
    'ApiKey' => 'VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=',  // YOUR key
    'ClientId' => 'e27847c1-a9fe-4eef-b60d-ddb291b175ab',        // YOUR client ID
    'SenderId' => 'FORTRESS',                                     // THEIR sender ID
    'MessageParameters' => [...]
];

// Line 51: Sends to Onfon using YOUR credentials
$resp = Http::timeout(20)->post($url, $payload);
```

## ✅ Current Status

### ✓ Working Correctly:
- Both PRADY_TECH and FORTRESS use the SAME Onfon account
- Each client has different API keys for YOUR system
- Each client can use different sender IDs
- Balance tracking is separate per client
- All SMS are routed through YOUR Onfon account

### ✓ This is Exactly What You Want:
Your BulkSms system is a **multi-tenant SMS gateway** where:
- You own the Onfon account (PRADY_TECH)
- You provide API access to multiple clients
- Clients don't need their own Onfon accounts
- You manage all sender IDs centrally
- You can markup pricing and profit

## 🎯 Summary

**FORTRESS does NOT call Onfon directly.** ✅

The flow is:
1. FORTRESS → Your BulkSms API
2. Your BulkSms API → Onfon (using your credentials)
3. Onfon → Recipient

**Your system is working correctly as a middleware/reseller platform!** 🎉

---

## 📞 For PCIP Integration

When integrating with PCIP, give them:
- **API URL**: `http://127.0.0.1:8000/api/2/messages/send`
- **API Key**: `USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh`
- **Client ID**: `2`
- **Sender**: `FORTRESS`

They will NEVER know or see your Onfon credentials. They only interact with YOUR API.


