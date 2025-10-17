# API Call Flow - Who Calls What?

## 🎯 Quick Answer

**CURRENTLY:** Tenants call **YOUR SYSTEM'S API**, which then calls **ONFON API**

```
Tenant (PRADY_TECH) 
    ↓
YOUR SYSTEM API (http://localhost/api/...)
    ↓
ONFON API (https://api.onfonmedia.co.ke/...)
```

**NOT THIS:** ~~Tenant → Onfon API directly~~ ❌

---

## 📊 Complete Step-by-Step Flow

### Scenario: PRADY_TECH Sends SMS to 254728883160

```
┌─────────────────────────────────────────────────────────────────┐
│  STEP 1: Tenant Sends Request to YOUR SYSTEM                   │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│  POST http://localhost/api/1/messages/send                     │
│  Headers:                                                       │
│    X-API-Key: abc123xyz789 (PRADY_TECH's key)                 │
│  Body:                                                          │
│    {                                                           │
│      "channel": "sms",                                         │
│      "recipient": "254728883160",                              │
│      "body": "Hello from PRADY_TECH",                          │
│      "sender": "PRADY_TECH"                                    │
│    }                                                           │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 2: YOUR SYSTEM - ApiAuth Middleware                      │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│  File: app/Http/Middleware/ApiAuth.php                         │
│                                                                 │
│  1. Extract API key: "abc123xyz789"                            │
│  2. Query YOUR database:                                       │
│     SELECT * FROM clients WHERE api_key = 'abc123xyz789'       │
│  3. Found: Client ID 1 (PRADY_TECH) ✅                         │
│  4. Authenticate tenant                                        │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 3: YOUR SYSTEM - CompanyAuth Middleware                  │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│  File: app/Http/Middleware/CompanyAuth.php                     │
│                                                                 │
│  1. URL company_id: 1                                          │
│  2. Authenticated client ID: 1                                 │
│  3. Match? YES ✅                                               │
│  4. Authorize access                                           │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 4: YOUR SYSTEM - MessageController                       │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│  File: app/Http/Controllers/Api/MessageController.php          │
│                                                                 │
│  1. Get authenticated client (PRADY_TECH)                      │
│  2. Validate request data                                      │
│  3. Check balance: KSH 1000.00 ✅                              │
│  4. Create OutboundMessage object                              │
│  5. Pass to MessageDispatcher                                  │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 5: YOUR SYSTEM - MessageDispatcher                       │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│  File: app/Services/Messaging/MessageDispatcher.php            │
│                                                                 │
│  1. Load channel config for Client 1, channel 'sms'            │
│  2. Query YOUR database:                                       │
│     SELECT * FROM channels                                     │
│     WHERE client_id = 1 AND name = 'sms'                       │
│  3. Found channel with provider = 'onfon' ✅                   │
│  4. Extract Onfon credentials from channel config              │
│  5. Instantiate OnfonSmsSender with credentials                │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 6: YOUR SYSTEM - OnfonSmsSender Prepares Request         │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│  File: app/Services/Messaging/Drivers/Sms/OnfonSmsSender.php  │
│                                                                 │
│  1. Get Onfon credentials from channel config:                 │
│     - api_key: "VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=" │
│     - client_id: "e27847c1-a9fe-4eef-b60d-ddb291b175ab"       │
│     - access_key_header: "8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB"   │
│  2. Build Onfon payload:                                       │
│     {                                                          │
│       "ApiKey": "VKft5j+GOeSXYSlk...",                        │
│       "ClientId": "e27847c1-a9fe...",                         │
│       "SenderId": "PRADY_TECH",                               │
│       "MessageParameters": [{                                 │
│         "Number": "254728883160",                             │
│         "Text": "Hello from PRADY_TECH"                       │
│       }]                                                      │
│     }                                                          │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 7: YOUR SYSTEM Calls ONFON API                           │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│  POST https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS         │
│  Headers:                                                       │
│    Accept: application/json                                    │
│    Content-Type: application/json; charset=utf-8              │
│    AccessKey: 8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB                │
│  Body:                                                          │
│    {                                                           │
│      "ApiKey": "VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=",│
│      "ClientId": "e27847c1-a9fe-4eef-b60d-ddb291b175ab",      │
│      "IsUnicode": 1,                                           │
│      "IsFlash": 1,                                             │
│      "SenderId": "PRADY_TECH",                                 │
│      "MessageParameters": [                                    │
│        {                                                       │
│          "Number": "254728883160",                             │
│          "Text": "Hello from PRADY_TECH"                       │
│        }                                                       │
│      ]                                                         │
│    }                                                           │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 8: ONFON API Processes and Sends SMS                     │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│  1. Onfon receives request                                     │
│  2. Validates credentials                                      │
│  3. Sends SMS to mobile network                                │
│  4. Returns response:                                          │
│     {                                                          │
│       "ErrorCode": 0,                                          │
│       "ErrorMessage": "Success",                               │
│       "Data": [                                                │
│         {                                                      │
│           "MessageId": "onfon-msg-12345",                      │
│           "MessageErrorCode": 0,                               │
│           "Number": "254728883160"                             │
│         }                                                      │
│       ]                                                        │
│     }                                                          │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 9: YOUR SYSTEM Processes Onfon Response                  │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│  File: app/Services/Messaging/Drivers/Sms/OnfonSmsSender.php  │
│                                                                 │
│  1. Check ErrorCode = 0 ✅                                     │
│  2. Extract MessageId = "onfon-msg-12345"                      │
│  3. Return MessageId to MessageDispatcher                      │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 10: YOUR SYSTEM Saves Message to Database                │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│  File: app/Services/Messaging/MessageDispatcher.php            │
│                                                                 │
│  1. Create message record in YOUR database:                    │
│     INSERT INTO messages (                                     │
│       client_id,      -- 1 (PRADY_TECH)                        │
│       channel,        -- 'sms'                                 │
│       provider,       -- 'onfon'                               │
│       sender,         -- 'PRADY_TECH'                          │
│       recipient,      -- '254728883160'                        │
│       body,           -- 'Hello from PRADY_TECH'               │
│       status,         -- 'sent'                                │
│       provider_message_id, -- 'onfon-msg-12345'                │
│       cost,           -- 1.00                                  │
│       sent_at         -- NOW()                                 │
│     )                                                          │
│  2. Deduct balance from client:                                │
│     UPDATE clients SET balance = balance - 1.00                │
│     WHERE id = 1                                               │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 11: YOUR SYSTEM Returns Response to Tenant               │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│  HTTP 200 OK                                                    │
│  {                                                             │
│    "status": "success",                                        │
│    "message": "Message queued for sending",                    │
│    "data": {                                                   │
│      "id": 123,                                                │
│      "client_id": 1,                                           │
│      "channel": "sms",                                         │
│      "recipient": "254728883160",                              │
│      "sender": "PRADY_TECH",                                   │
│      "status": "sent",                                         │
│      "cost": 1.00,                                             │
│      "provider_message_id": "onfon-msg-12345",                 │
│      "sent_at": "2025-10-10 15:30:00"                          │
│    }                                                           │
│  }                                                             │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 12: SMS Delivered to Phone                               │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━│
│                                                                 │
│  📱 Phone 254728883160 receives:                               │
│                                                                 │
│  From: PRADY_TECH                                              │
│  Message: Hello from PRADY_TECH                                │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔑 Key Points

### 1. **Tenant Never Calls Onfon Directly**
```
❌ WRONG: Tenant → Onfon API
✅ RIGHT: Tenant → Your System → Onfon API
```

### 2. **Your System is the Middleman**
```
Tenant's API Key → Your System → Onfon API Key
```

**Tenant knows:** Their own API key (`abc123xyz789`)  
**Tenant doesn't know:** Onfon API key (kept in YOUR database)

### 3. **Onfon Credentials Stored in YOUR Database**

**Table:** `channels`
```sql
SELECT credentials 
FROM channels 
WHERE client_id = 1 AND name = 'sms';

-- Returns:
{
  "api_key": "VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=",
  "client_id": "e27847c1-a9fe-4eef-b60d-ddb291b175ab",
  "access_key_header": "8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB",
  "default_sender": "PRADY_TECH"
}
```

**This means:**
- ✅ You control Onfon credentials
- ✅ Tenants never see Onfon credentials
- ✅ You can change Onfon credentials without affecting tenants
- ✅ You can switch providers without tenant knowing

---

## 📊 Visual Flow Diagram

```
┌──────────────┐
│   TENANT     │
│ (PRADY_TECH) │
└──────┬───────┘
       │ POST /api/1/messages/send
       │ X-API-Key: abc123xyz789
       │ Body: {recipient, message}
       ▼
┌────────────────────────────────────────┐
│      YOUR SYSTEM (Laravel)             │
│                                        │
│  ┌──────────────────────────────────┐ │
│  │ 1. ApiAuth Middleware            │ │
│  │    - Validate API key            │ │
│  │    - Load client from DB         │ │
│  └──────────────────────────────────┘ │
│              ↓                         │
│  ┌──────────────────────────────────┐ │
│  │ 2. CompanyAuth Middleware        │ │
│  │    - Verify client_id match      │ │
│  └──────────────────────────────────┘ │
│              ↓                         │
│  ┌──────────────────────────────────┐ │
│  │ 3. MessageController             │ │
│  │    - Validate request            │ │
│  │    - Check balance               │ │
│  └──────────────────────────────────┘ │
│              ↓                         │
│  ┌──────────────────────────────────┐ │
│  │ 4. MessageDispatcher             │ │
│  │    - Load channel config         │ │
│  │    - Get Onfon credentials       │ │
│  └──────────────────────────────────┘ │
│              ↓                         │
│  ┌──────────────────────────────────┐ │
│  │ 5. OnfonSmsSender                │ │
│  │    - Build Onfon request         │ │
│  │    - Call Onfon API              │ │
│  └──────────────────────────────────┘ │
│              ↓                         │
│  ┌──────────────────────────────────┐ │
│  │ 6. Save to Database              │ │
│  │    - Store message record        │ │
│  │    - Deduct balance              │ │
│  └──────────────────────────────────┘ │
└────────┬───────────────────────────────┘
         │
         │ POST https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS
         │ AccessKey: 8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB
         │ Body: {ApiKey, ClientId, MessageParameters}
         ▼
┌──────────────────────────┐
│     ONFON API            │
│ (SMS Gateway Provider)   │
└──────────┬───────────────┘
           │
           │ Send SMS via Mobile Network
           ▼
    ┌─────────────┐
    │ 📱 Phone    │
    │ 254728883160│
    └─────────────┘
```

---

## 🎯 Why This Architecture?

### Benefits:

1. **Security** ✅
   - Tenants never see Onfon credentials
   - You control all provider access
   - Can revoke tenant access anytime

2. **Flexibility** ✅
   - Change Onfon credentials without affecting tenants
   - Switch to different provider (Twilio, etc.) transparently
   - Add multiple providers per tenant

3. **Billing Control** ✅
   - Track exact usage per tenant
   - Deduct from tenant balance
   - Prevent overdraft

4. **Monitoring** ✅
   - Log all messages
   - Track delivery status
   - Generate usage reports

5. **Multi-Tenancy** ✅
   - Complete data isolation
   - Each tenant has own balance
   - Each tenant can have different pricing

---

## 📋 Code Evidence

### Tenant's Request (What PRADY_TECH sends)

```bash
curl -X POST http://localhost/api/1/messages/send \
  -H "X-API-Key: abc123xyz789" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "sms",
    "recipient": "254728883160",
    "body": "Test message",
    "sender": "PRADY_TECH"
  }'
```

**Notice:** No Onfon credentials in tenant's request!

---

### Your System's Request to Onfon (What happens internally)

**File:** `app/Services/Messaging/Drivers/Sms/OnfonSmsSender.php`

```php
$url = 'https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS';

$payload = [
    'ApiKey' => $this->credentials['api_key'],    // From YOUR database
    'ClientId' => $this->credentials['client_id'], // From YOUR database
    'IsUnicode' => 1,
    'IsFlash' => 1,
    'SenderId' => $message->sender,
    'MessageParameters' => [
        [
            'Number' => $message->recipient,
            'Text' => $message->body,
        ],
    ],
];

$resp = Http::timeout(20)
    ->withOptions(['verify' => false])
    ->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json; charset=utf-8',
        'AccessKey' => $this->credentials['access_key_header'], // From YOUR database
    ])
    ->post($url, $payload);
```

**Notice:** All Onfon credentials come from YOUR database's `channels` table!

---

## 🔍 Where Onfon Credentials Come From

### Database Query

```sql
SELECT credentials 
FROM channels 
WHERE client_id = 1 
  AND name = 'sms' 
  AND active = 1;
```

### Result (JSON stored in database)

```json
{
  "api_key": "VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=",
  "client_id": "e27847c1-a9fe-4eef-b60d-ddb291b175ab",
  "access_key_header": "8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB",
  "default_sender": "PRADY_TECH"
}
```

### This Gets Passed to OnfonSmsSender

```php
// MessageDispatcher.php
$credentials = json_decode($channelConfig->credentials, true);
$sender = new OnfonSmsSender($credentials);
$sender->send($message);
```

---

## ✅ Summary

| Aspect | Who Calls What |
|--------|----------------|
| **Tenant's Request** | Tenant → YOUR System API |
| **Authentication** | Your System validates tenant's API key |
| **Provider Call** | Your System → Onfon API |
| **Credentials Used** | Onfon credentials from YOUR database |
| **Response** | Your System → Tenant |
| **Tenant Knows** | Only their API key |
| **Tenant Doesn't Know** | Onfon credentials, Onfon API URLs |

---

## 🎯 Current Architecture Summary

```
TENANTS:
- Call YOUR system's API
- Use their unique API key
- Never see or know Onfon credentials
- Get responses from YOUR system

YOUR SYSTEM:
- Receives requests from tenants
- Authenticates using tenant's API key
- Retrieves Onfon credentials from database
- Calls Onfon API on tenant's behalf
- Saves message records
- Deducts balance
- Returns response to tenant

ONFON:
- Only receives calls from YOUR system
- Never called directly by tenants
- Doesn't know about your tenants
```

**This is a proper multi-tenant SaaS architecture!** ✅

---

For more details see:
- `TENANT_IDENTIFICATION_SYSTEM.md` - How tenant authentication works
- `TENANT_DATABASE_STORAGE.md` - How tenants are stored
- `OnfonSmsSender.php` - Actual code that calls Onfon

