# API Documentation - CORRECTED VERSION

## 🎯 The Problem with Current Documentation

The landing page API docs have **outdated endpoints and parameters**. Here's what's actually working:

---

## ✅ CORRECT API Endpoints

### Authentication
All requests require:
```
X-API-KEY: your-api-key-here
```

Get your API key from Settings → Profile or your account manager.

---

### Method 1: Unified Messages API (RECOMMENDED) ✅

**Endpoint:**
```
POST /api/{company_id}/messages/send
```

**Parameters:**
```json
{
  "client_id": 1,
  "channel": "sms",
  "recipient": "254712345678",
  "sender": "YOUR-SENDER-ID",
  "body": "Your message here"
}
```

**Example (cURL):**
```bash
curl -X POST https://crm.pradytecai.com/api/1/messages/send \
  -H "X-API-KEY: your-api-key-here" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "channel": "sms",
    "recipient": "254712345678",
    "sender": "PRADY_TECH",
    "body": "Hello! This is a test message."
  }'
```

**Example (PHP):**
```php
$ch = curl_init('https://crm.pradytecai.com/api/1/messages/send');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-KEY: your-api-key-here',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'client_id' => 1,
    'channel' => 'sms',
    'recipient' => '254712345678',
    'sender' => 'PRADY_TECH',
    'body' => 'Hello! This is a test message.'
]));

$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

print_r($result);
```

**Success Response:**
```json
{
  "id": 123,
  "status": "sent",
  "provider_message_id": "MSG-ABC123"
}
```

---

### Method 2: SMS-Only API ⚠️ (Has Issues)

**Endpoint:**
```
POST /api/{company_id}/sms/send
```

**IMPORTANT:** This endpoint expects `recipients` (plural/array), not `recipient` (singular)!

**Parameters:**
```json
{
  "recipients": ["254712345678", "254723456789"],
  "message": "Your message here",
  "sender_id": "YOUR-SENDER-ID"
}
```

**Example (cURL):**
```bash
curl -X POST https://crm.pradytecai.com/api/1/sms/send \
  -H "X-API-KEY: your-api-key-here" \
  -H "Content-Type: application/json" \
  -d '{
    "recipients": ["254712345678"],
    "message": "Hello! This is a test message.",
    "sender_id": "PRADY_TECH"
  }'
```

---

## 🔍 Why Your API Calls Are Failing

### Issue 1: Wrong Parameters
**Doc says:**
```json
{
  "recipient": "254712345678",  ← Singular
  "message": "...",
  "sender": "..."
}
```

**API expects:**
```json
{
  "recipients": ["254712345678"],  ← Plural, Array!
  "message": "...",
  "sender_id": "..."  ← Note: sender_id not sender
}
```

### Issue 2: Wrong Endpoint Structure
**Doc says:**
```
/api/1/sms/send
```

**Should be:**
```
/api/{company_id}/messages/send  ← Recommended
OR
/api/{company_id}/sms/send       ← If using bulk SMS endpoint
```

### Issue 3: Missing/Wrong Headers
**Docs may show:**
```
X-API-Key: ...
```

**Should be:**
```
X-API-KEY: ...  ← All caps KEY
```

---

## ✅ WORKING EXAMPLES

### Send Single SMS (Unified API - BEST)

```bash
curl -X POST https://crm.pradytecai.com/api/1/messages/send \
  -H "X-API-KEY: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "PRADY_TECH",
    "body": "Test message from API"
  }'
```

### Send Bulk SMS

```bash
curl -X POST https://crm.pradytecai.com/api/1/sms/send \
  -H "X-API-KEY: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  -H "Content-Type: application/json" \
  -d '{
    "recipients": ["254728883160", "254722123456"],
    "message": "Bulk SMS test",
    "sender_id": "PRADY_TECH"
  }'
```

### Send WhatsApp Message

```bash
curl -X POST https://crm.pradytecai.com/api/1/messages/send \
  -H "X-API-KEY: your-api-key-here" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "channel": "whatsapp",
    "recipient": "254728883160",
    "sender": "YOUR-WHATSAPP-NUMBER",
    "body": "Hello from WhatsApp!"
  }'
```

---

## 📋 All Available Endpoints

### Messages
- `POST /api/{company_id}/messages/send` - Send any message (SMS/WhatsApp/Email)

### SMS
- `POST /api/{company_id}/sms/send` - Send bulk SMS
- `GET /api/{company_id}/sms/status/{id}` - Check SMS status
- `GET /api/{company_id}/sms/history` - Get SMS history
- `GET /api/{company_id}/sms/statistics` - Get statistics

### Client
- `GET /api/{company_id}/client/profile` - Get profile
- `GET /api/{company_id}/client/balance` - Get balance
- `GET /api/{company_id}/client/statistics` - Get stats

### Contacts
- `GET /api/{company_id}/contacts` - List contacts
- `POST /api/{company_id}/contacts` - Create contact
- `PUT /api/{company_id}/contacts/{id}` - Update contact
- `DELETE /api/{company_id}/contacts/{id}` - Delete contact
- `POST /api/{company_id}/contacts/bulk-import` - Import CSV

### Campaigns
- `GET /api/{company_id}/campaigns` - List campaigns
- `POST /api/{company_id}/campaigns` - Create campaign
- `POST /api/{company_id}/campaigns/{id}/send` - Send campaign
- `GET /api/{company_id}/campaigns/{id}/statistics` - Campaign stats

### Wallet
- `GET /api/{company_id}/wallet/balance` - Get balance
- `POST /api/{company_id}/wallet/sync` - Sync with Onfon
- `GET /api/{company_id}/wallet/transactions` - List transactions
- `POST /api/{company_id}/wallet/topup` - Initiate top-up

### Analytics
- `GET /api/{company_id}/analytics/summary` - Summary
- `GET /api/{company_id}/analytics/daily` - Daily stats
- `GET /api/{company_id}/analytics/monthly` - Monthly stats
- `GET /api/{company_id}/analytics/by-channel` - By channel
- `GET /api/{company_id}/analytics/wallet` - Wallet analytics

---

## 🧪 Test Your API

### Quick Test (Local/Development)

If on localhost, test without auth:
```bash
curl -X POST http://localhost/BulkSms/api/_test/messages/send \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "PRADY_TECH",
    "body": "Test message"
  }'
```

### Test API Key

```bash
curl -H "X-API-KEY: your-key-here" \
  https://crm.pradytecai.com/api/1/client/profile
```

Should return your client profile if API key is valid.

---

## ❌ Common Errors & Solutions

### Error: "Unauthenticated"
**Cause:** Missing or invalid API key
**Solution:** Add header: `X-API-KEY: your-key-here`

### Error: "The recipients field is required"
**Cause:** Using `/sms/send` with `recipient` (singular)
**Solution:** Use `recipients` (array) or switch to `/messages/send`

### Error: "The client id field is required"
**Cause:** Using `/messages/send` without `client_id`
**Solution:** Add `"client_id": 1` to your payload

### Error: 404 Not Found
**Cause:** Wrong endpoint URL
**Solution:** Make sure using `/api/{company_id}/...` not just `/api/...`

### Error: "Insufficient balance"
**Cause:** No balance in account
**Solution:** Top up via wallet or M-Pesa

---

## 📝 RECOMMENDED: Use Unified Messages API

**Why?**
- ✅ Simpler parameters
- ✅ Works for SMS, WhatsApp, Email
- ✅ Single `recipient` (not array)
- ✅ More intuitive
- ✅ Better maintained

**Example:**
```json
{
  "client_id": 1,
  "channel": "sms",
  "recipient": "254712345678",
  "sender": "YOUR-SENDER-ID",
  "body": "Your message"
}
```

That's it! Clean and simple.

---

## 🔧 Fix Needed: Update Landing Page Docs

The API documentation view needs to be updated with:

1. **Correct endpoint:** `/api/{company_id}/messages/send`
2. **Correct parameters:** Show both `/messages/send` and `/sms/send`
3. **Clarify arrays:** Make it clear `/sms/send` uses `recipients` array
4. **Update examples:** All code examples should work

---

## 📚 Where to Find Your API Key

1. Login to: https://crm.pradytecai.com
2. Go to: Settings → Profile
3. Look for: API Key section
4. Copy your key

Default test key: `bae377bc-0282-4fc9-a2a1-e338b18da77a`

---

## ✅ Summary

**WRONG (From Current Docs):**
```bash
POST /api/1/sms/send
{
  "recipient": "254...",      ← Wrong
  "message": "...",
  "sender": "..."
}
```

**CORRECT (What Actually Works):**
```bash
POST /api/1/messages/send
{
  "client_id": 1,
  "channel": "sms",
  "recipient": "254...",      ← Correct
  "sender": "...",
  "body": "..."                ← Note: "body" not "message"
}
```

**OR for bulk:**
```bash
POST /api/1/sms/send
{
  "recipients": ["254..."],    ← Array!
  "message": "...",
  "sender_id": "..."           ← Note: "sender_id"
}
```

---

**Last Updated:** October 18, 2025  
**Status:** Tested and Working  
**Action Needed:** Update landing page API documentation view


