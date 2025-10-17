# 🔍 EXACT API CALL LOCATIONS MAP

**Generated:** October 13, 2025  
**Purpose:** Locate every external API call in the system with exact file paths and line numbers

---

## 📨 SMS API CALLS

### 1. ✅ **MODERN APPROACH** (Recommended - Currently Used)

#### **Onfon Media SMS API** 
**File:** `app/Services/Messaging/Drivers/Sms/OnfonSmsSender.php`

**API Endpoint:** `https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS`

**Exact Call Location:**
```php
Line 26: $url = 'https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS';

Line 44-51: 
$resp = Http::timeout(20)
    ->withOptions(['verify' => false])
    ->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json; charset=utf-8',
        'AccessKey' => $accessKeyHeader,
    ])
    ->post($url, $payload);
```

**Payload Structure:**
```php
Line 28-40:
$payload = [
    'ApiKey' => $apiKey,                    // From channel credentials
    'ClientId' => $clientId,                // From channel credentials
    'IsUnicode' => 1,
    'IsFlash' => 1,
    'SenderId' => $message->sender,         // e.g., 'PRADY_TECH'
    'MessageParameters' => [
        [
            'Number' => $message->recipient,  // e.g., '254712345678'
            'Text' => $message->body,         // Message content
        ],
    ],
];
```

**How It's Called:**
```
MessageDispatcher (Line 40) 
  → Resolves OnfonSmsSender
  → Calls send() method (Line 15)
  → Makes HTTP POST to Onfon API (Line 44-51)
```

**Response Handling:**
```php
Line 67-86: Parse response
- Extract MessageId from Data array
- Check ErrorCode (must be 0 or '000')
- Check MessageErrorCode
- Return provider message ID
```

---

#### **Twilio SMS API** (Not Yet Implemented)
**File:** `app/Services/Messaging/Drivers/Sms/TwilioSmsSender.php`

**Status:** ⚠️ **STUB ONLY**

**Line 16-17:**
```php
// TODO: integrate Twilio SDK. For now, stub a provider message id.
return 'twilio_'.bin2hex(random_bytes(6));
```

**To Implement:**
- Add Twilio SDK dependency
- Implement actual API call
- Use Twilio credentials from channel config

---

### 2. ⚠️ **LEGACY APPROACH** (Being Phased Out)

#### **SmsService - Multi-Gateway Support**
**File:** `app/Services/SmsService.php`

This service has **three different gateways**:

#### **Gateway 1: Mobitech Technologies**

**API Endpoint:** From `config/sms.php` → `SMS_GATEWAY_URL`  
Default: `http://bulksms.mobitechtechnologies.com/api/sendsms`

**Call Location:**
```php
Method: callMobitechGateway() (Line 110-135)

Line 120: 
$response = Http::timeout(30)->post($this->gatewayUrl, $data);
```

**Payload:**
```php
Line 112-118:
$data = [
    'api_key' => $this->apiKey,      // From config
    'username' => $this->username,    // From config
    'sender_id' => $senderId,
    'message' => $message,
    'phone' => $recipient
];
```

**When Used:**
- Default for sender IDs not in Onfon or Moja lists
- Configured in `config/sms.php`

---

#### **Gateway 2: Onfon Media (Legacy Path)**

**API Endpoint:** From `config/sms.php` → `ONFON_API_URL`  
Default: `https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS`

**Call Location:**
```php
Method: callOnfonGateway() (Line 140-173)

Line 156: 
$response = Http::timeout(30)->post(config('sms.onfon_url'), $data);
```

**Payload:**
```php
Line 142-154:
$data = [
    'ApiKey' => config('sms.onfon_api_key'),
    'ClientId' => config('sms.onfon_client_id'),
    'IsUnicode' => 1,
    'IsFlash' => 1,
    'SenderId' => $senderId,
    'MessageParameters' => [
        [
            'Number' => $recipient,
            'Text' => $message
        ]
    ]
];
```

**When Used:**
- If sender_id is in `config/sms.php` → `onfon_senders` array
- Sender IDs: FALLEY-MED, MWANGAZACLG, LOGIC-LINK, FORTRESS, etc. (27 senders)

---

#### **Gateway 3: MojaSMS**

**API Endpoint:** From `config/sms.php` → `MOJA_API_URL`  
Default: `https://prady-api-p1.mojasms.dev/api/campaign`

**Call Location:**
```php
Method: callMojaGateway() (Line 178-203)

Line 186: 
$response = Http::timeout(30)->post(config('sms.moja_url'), $data);
```

**Payload:**
```php
Line 180-184:
$data = [
    'from' => $senderId,
    'to' => $recipient,
    'message' => $message
];
```

**When Used:**
- If sender_id is in `config/sms.php` → `moja_senders` array
- Sender IDs: PIXEL_LTD, NJORO CLUB, MWEGUNI, NJORODAYSEC

---

#### **Gateway Selection Logic**
**File:** `app/Services/SmsService.php`

```php
Method: callSmsGateway() (Line 87-105)

Line 91-94: 
if (in_array($senderId, config('sms.onfon_senders', []))) {
    return $this->callOnfonGateway($recipient, $message, $senderId);
} elseif (in_array($senderId, config('sms.moja_senders', []))) {
    return $this->callMojaGateway($recipient, $message, $senderId);
} else {
    return $this->callMobitechGateway($recipient, $message, $senderId);
}
```

---

## 📱 WHATSAPP API CALLS

### 1. **UltraMsg WhatsApp API**
**File:** `app/Services/Messaging/Drivers/WhatsApp/UltraMessageSender.php`

**API Endpoint:** `https://api.ultramsg.com/{instanceId}/{endpoint}`

**Base URL Construction:**
```php
Line 20: $this->baseUrl = "https://api.ultramsg.com/{$this->instanceId}";
```

**Exact Call Location:**
```php
Method: send() (Line 23-92)

Line 49-51:
$response = $httpClient->post("{$this->baseUrl}/{$endpoint}", array_merge($payload, [
    'token' => $this->token
]));
```

**Endpoints Used:**
```php
Method: getEndpoint() (Line 97-125)

Possible endpoints:
- messages/chat          (default text messages)
- messages/image         (image with caption)
- messages/video         (video with caption)
- messages/audio         (audio file)
- messages/voice         (voice note)
- messages/document      (PDF, DOC, etc.)
- messages/sticker       (sticker)
- messages/contact       (vCard)
- messages/location      (GPS location)
```

**Payload Examples:**

**Text Message:**
```php
Line 132-220: buildPayload()
{
    'to' => '254712345678',
    'body' => 'Your message here',
    'preview_url' => true,  // If URL detected
    'token' => 'xxx'
}
```

**Image Message:**
```php
{
    'to' => '254712345678',
    'image' => 'https://example.com/image.jpg',
    'caption' => 'Image caption',
    'token' => 'xxx'
}
```

**Authentication:**
- Token appended to every request (Line 49-51)
- From channel credentials: `instance_id` + `token`

**Response Handling:**
```php
Line 73-74: Check if sent successfully
if (isset($result['sent']) && ($result['sent'] === 'true' || $result['sent'] === true)) {
    return $result['id'] ?? 'ultra_' . uniqid();
}
```

---

### 2. **WhatsApp Cloud API (Meta)**
**File:** `app/Services/Messaging/Drivers/WhatsApp/CloudWhatsAppSender.php`

**API Endpoint:** `https://graph.facebook.com/{apiVersion}/{phoneNumberId}/messages`

**Base URL Construction:**
```php
Line 22: $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}";
```

**Exact Call Location:**
```php
Method: send() (Line 25-66)

Line 39-42:
$response = Http::withToken($this->accessToken)
    ->timeout(30)
    ->withOptions(['verify' => false])
    ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", $payload);
```

**Payload Structure:**
```php
Line 71-105: buildPayload()

Base payload:
{
    'messaging_product': 'whatsapp',
    'recipient_type': 'individual',
    'to': '254712345678',
    'type': 'text',        // or 'template', 'interactive', 'image', etc.
    ...
}
```

**Message Types Supported:**

**Text:**
```php
Line 96-102:
'type' => 'text',
'text' => [
    'preview_url' => true,
    'body' => $message->body
]
```

**Template:**
```php
Line 79-83:
'type' => 'template',
'template' => [
    'name' => 'template_name',
    'language' => ['code' => 'en'],
    'components' => [...]
]
```

**Interactive (Buttons/List):**
```php
Line 84-88:
'type' => 'interactive',
'interactive' => [
    'type' => 'button' | 'list',
    'body' => ['text' => 'message'],
    'action' => [...]
]
```

**Media:**
```php
Line 89-94:
'type' => 'image' | 'video' | 'document',
'image' => [
    'id' => 'media_id',  // OR
    'link' => 'https://example.com/image.jpg',
    'caption' => 'Caption text'
]
```

**Authentication:**
- Bearer token in Authorization header (Line 39)
- Access token from channel credentials

**Response:**
```php
Line 56-57:
$result = $response->json();
return $result['messages'][0]['id'] ?? 'wacloud_' . bin2hex(random_bytes(6));
```

---

## 📧 EMAIL API CALLS

### **SMTP Email Sender** (Not Yet Implemented)
**File:** `app/Services/Messaging/Drivers/Email/SmtpEmailSender.php`

**Status:** ⚠️ **STUB ONLY**

```php
Line 14-18:
public function send(OutboundMessage $message): string
{
    // TODO: integrate with mailer or provider API. For now, stub id.
    return 'smtp_'.bin2hex(random_bytes(6));
}
```

**To Implement:**
- Use Laravel Mail facade
- Configure SMTP settings
- Send actual emails

---

## 💰 WALLET & BALANCE API CALLS

### **Onfon Wallet Service**
**File:** `app/Services/OnfonWalletService.php`

This service makes **3 different API calls** to Onfon:

#### **1. Get Balance**

**Endpoint:** `https://api.onfonmedia.co.ke/v1/balance/GetBalance`

**Call Locations:**
```php
Method: getBalance() (Line 14-72)

Line 26-36:
$response = Http::timeout(30)
    ->withOptions(['verify' => false])
    ->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'AccessKey' => $credentials['access_key_header'],
    ])
    ->post('https://api.onfonmedia.co.ke/v1/balance/GetBalance', [
        'ApiKey' => $credentials['api_key'],
        'ClientId' => $credentials['client_id'],
    ]);
```

**Also called from:**
```php
Method: testConnection() (Line 113-160)
Line 125-136: Same call to test credentials
```

**Response:**
```php
Line 42-47:
{
    "Balance": 1234.56,   // KSH balance
    "Units": 1234,        // SMS units
    "Currency": "KES"
}
```

---

#### **2. Sync Balance**

**Method:** `syncBalance()` (Line 77-108)  
**What it does:**
- Calls `getBalance()` (which makes the API call above)
- Compares with stored balance
- Updates client record
- Logs the difference

**No direct API call** - uses `getBalance()` internally

---

#### **3. Get Transaction History**

**Endpoint:** `https://api.onfonmedia.co.ke/v1/reports/GetTransactionHistory`

**Call Location:**
```php
Method: getTransactionHistory() (Line 165-214)

Line 180-192:
$response = Http::timeout(30)
    ->withOptions(['verify' => false])
    ->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'AccessKey' => $credentials['access_key_header'],
    ])
    ->post('https://api.onfonmedia.co.ke/v1/reports/GetTransactionHistory', [
        'ApiKey' => $credentials['api_key'],
        'ClientId' => $credentials['client_id'],
        'FromDate' => $fromDate,  // 'Y-m-d' format
        'ToDate' => $toDate,
    ]);
```

**Response:**
```php
Line 195-200:
{
    "Transactions": [
        {
            "Date": "2025-10-13",
            "Amount": 100.00,
            "Type": "Debit",
            "Description": "SMS sent"
        }
    ]
}
```

---

## 💳 M-PESA API CALLS

### **M-Pesa Service**
**File:** `app/Services/MpesaService.php`

#### **1. Generate OAuth Token**

**Endpoint:** Sandbox/Production Auth URL from `config/mpesa.php`
- Sandbox: `https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials`
- Production: `https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials`

**Call Location:**
```php
Method: getAccessToken() (Line 37-67)

Line 44-49:
$response = Http::withBasicAuth(
        $this->consumerKey,
        $this->consumerSecret
    )
    ->timeout(30)
    ->get($this->urls['auth']);
```

**Response:**
```php
Line 51-52:
{
    "access_token": "xxx",
    "expires_in": "3599"
}
```

---

#### **2. STK Push (Payment Request)**

**Endpoint:** Sandbox/Production STK Push URL
- Sandbox: `https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest`
- Production: `https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest`

**Call Location:**
```php
Method: stkPush() (Line 76-182)

Line 136-140:
$response = Http::withToken($accessToken)
    ->timeout(60)
    ->withOptions(['verify' => false])
    ->post($this->urls['stk_push'], $payload);
```

**Payload:**
```php
Line 120-135:
{
    "BusinessShortCode": "174379",
    "Password": "base64_encoded_password",
    "Timestamp": "20251013103045",
    "TransactionType": "CustomerPayBillOnline",
    "Amount": "100",
    "PartyA": "254712345678",
    "PartyB": "174379",
    "PhoneNumber": "254712345678",
    "CallBackURL": "https://your-domain.com/api/webhooks/mpesa/callback",
    "AccountReference": "WalletTopup",
    "TransactionDesc": "Wallet top-up"
}
```

**Response:**
```php
Line 142-145:
{
    "MerchantRequestID": "xxx",
    "CheckoutRequestID": "yyy",
    "ResponseCode": "0",
    "ResponseDescription": "Success"
}
```

---

#### **3. STK Query (Check Status)**

**Endpoint:** Sandbox/Production STK Query URL
- Sandbox: `https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query`
- Production: `https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query`

**Call Location:**
```php
Method: queryTransactionStatus() (Line 191-253)

Line 209-213:
$response = Http::withToken($accessToken)
    ->timeout(30)
    ->withOptions(['verify' => false])
    ->post($this->urls['stk_query'], $payload);
```

**Payload:**
```php
Line 203-208:
{
    "BusinessShortCode": "174379",
    "Password": "base64_encoded_password",
    "Timestamp": "20251013103045",
    "CheckoutRequestID": "ws_CO_13102025103045123456"
}
```

---

## 🔗 WEBHOOK DELIVERIES (Outbound)

### **Webhook Service**
**File:** `app/Services/WebhookService.php`

**Endpoint:** Client-configured webhook URL (from `clients.webhook_url`)

**Call Location:**
```php
Method: dispatch() (Line 20-103)

Line 71-76:
$response = Http::timeout(10)
    ->withHeaders([
        'Content-Type' => 'application/json',
        'X-Webhook-Signature' => $signature,
    ])
    ->post($client->webhook_url, $payload);
```

**Payload:**
```php
Line 57-61:
{
    "event": "message.sent",
    "timestamp": "2025-10-13T10:30:45Z",
    "data": {
        "message_id": 123,
        "recipient": "254712345678",
        "status": "sent",
        ...
    },
    "signature": "sha256_hash"
}
```

**When Called:**
- After message sent
- After message delivered
- After message failed
- Low balance alert
- Campaign completed

---

## 🎯 FLOW SUMMARY

### **How to Trace an SMS Send:**

1. **Entry Point:** User sends campaign or API call
   - Web: `CampaignController@send()` → Line 188
   - API: `MessageController@send()` → Line 16

2. **Message Dispatcher:** `MessageDispatcher@dispatch()`
   - File: `app/Services/Messaging/MessageDispatcher.php`
   - Line 22-107

3. **Provider Selection:**
   - Loads channel config from database (Line 24-28)
   - Resolves driver (OnfonSmsSender, UltraMessageSender, etc.) (Line 40-42)

4. **API Call:**
   - **If Onfon SMS:**
     - File: `app/Services/Messaging/Drivers/Sms/OnfonSmsSender.php`
     - Line 44-51: HTTP POST to Onfon API
   
   - **If UltraMsg WhatsApp:**
     - File: `app/Services/Messaging/Drivers/WhatsApp/UltraMessageSender.php`
     - Line 49-51: HTTP POST to UltraMsg API
   
   - **If WhatsApp Cloud:**
     - File: `app/Services/Messaging/Drivers/WhatsApp/CloudWhatsAppSender.php`
     - Line 39-42: HTTP POST to Facebook Graph API

5. **Response Handling:**
   - Provider returns message ID
   - MessageDispatcher updates message status (Line 89-98)
   - Creates/updates conversation (Line 103)

---

## 📊 QUICK REFERENCE TABLE

| Service | File | Method | Line | API Endpoint |
|---------|------|--------|------|--------------|
| **Onfon SMS** | OnfonSmsSender.php | send() | 44-51 | api.onfonmedia.co.ke/v1/sms/SendBulkSMS |
| **Onfon Balance** | OnfonWalletService.php | getBalance() | 26-36 | api.onfonmedia.co.ke/v1/balance/GetBalance |
| **Onfon Transactions** | OnfonWalletService.php | getTransactionHistory() | 180-192 | api.onfonmedia.co.ke/v1/reports/GetTransactionHistory |
| **UltraMsg WhatsApp** | UltraMessageSender.php | send() | 49-51 | api.ultramsg.com/{instance}/messages/* |
| **WhatsApp Cloud** | CloudWhatsAppSender.php | send() | 39-42 | graph.facebook.com/{version}/{phone}/messages |
| **M-Pesa Auth** | MpesaService.php | getAccessToken() | 44-49 | safaricom.co.ke/oauth/v1/generate |
| **M-Pesa STK** | MpesaService.php | stkPush() | 136-140 | safaricom.co.ke/mpesa/stkpush/v1/processrequest |
| **M-Pesa Query** | MpesaService.php | queryTransactionStatus() | 209-213 | safaricom.co.ke/mpesa/stkpushquery/v1/query |
| **Webhooks** | WebhookService.php | dispatch() | 71-76 | {client.webhook_url} |

---

## 🔍 HOW TO ADD LOGGING TO API CALLS

If you want to trace API calls during execution, add logging:

```php
// Before API call
Log::info('Making API call', [
    'provider' => 'onfon',
    'endpoint' => $url,
    'recipient' => $message->recipient
]);

// After API call
Log::info('API response', [
    'status' => $response->status(),
    'body' => $response->json()
]);
```

Check logs: `storage/logs/laravel.log`

---

## ✅ VERIFICATION CHECKLIST

To verify where SMS is being sent:

- [ ] Check if using **modern approach** (MessageDispatcher) or **legacy** (SmsService)
- [ ] Identify which provider: Onfon, Mobitech, or Moja
- [ ] Check channel configuration in database (`channels` table)
- [ ] Verify credentials are set correctly
- [ ] Check Laravel logs for HTTP requests
- [ ] Test with `dd()` or `Log::info()` in the send methods

---

**Last Updated:** October 13, 2025  
**Complete and Accurate as of this date**


