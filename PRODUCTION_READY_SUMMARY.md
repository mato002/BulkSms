# ✅ Production Ready - BulkSms CRM & PCIP Integration

## 🌐 Production Domain
**BulkSms CRM:** https://crm.pradytecai.com

---

## 📋 PCIP Configuration (Already Done!)

### File Updated: `C:\xampp\htdocs\pcip\app\Services\FortressSmsService.php`
✅ Configured to use production settings from .env

### Add to PCIP `.env` file:
```env
FORTRESS_SMS_API_URL=https://crm.pradytecai.com/api/2/messages/send
FORTRESS_SMS_API_KEY=USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh
FORTRESS_SMS_CLIENT_ID=2
FORTRESS_SMS_SENDER_ID=FORTRESS
FORTRESS_SMS_ENABLED=true
```

### After adding to .env, run:
```bash
cd C:\xampp\htdocs\pcip
php artisan config:clear
```

---

## 🚀 Production API Endpoints

### All Clients Can Call:

| Client | API Endpoint | API Key |
|--------|--------------|---------|
| **FORTRESS** | https://crm.pradytecai.com/api/2/messages/send | USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh |
| PRADY_TECH | https://crm.pradytecai.com/api/1/messages/send | ea55cb72-a734-48b2-87a6-8d0ea1d397de |
| FALLEY-MED | https://crm.pradytecai.com/api/3/messages/send | 6d3d9896-6d4a-42dc-9bd6-675fd2f4e3f4 |
| LOGIC-LINK | https://crm.pradytecai.com/api/4/messages/send | 45cd60bb-ff46-41ce-9920-d25306315c1b |
| BriskCredit | https://crm.pradytecai.com/api/5/messages/send | fa40c0fc-516b-4c21-8a5d-bf7fbe740a4e |

---

## 🔌 How External Systems Call Your API

### From Any System (PHP Example):
```php
<?php

$ch = curl_init('https://crm.pradytecai.com/api/2/messages/send');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'client_id' => 2,
    'channel' => 'sms',
    'recipient' => '254728883160',
    'sender' => 'FORTRESS',
    'body' => 'Your message here'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh',
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

if ($result['status'] === 'sent') {
    echo "SMS sent! Message ID: " . $result['id'];
}
```

### From JavaScript:
```javascript
fetch('https://crm.pradytecai.com/api/2/messages/send', {
    method: 'POST',
    headers: {
        'X-API-Key': 'USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        client_id: 2,
        channel: 'sms',
        recipient: '254728883160',
        sender: 'FORTRESS',
        body: 'Your message'
    })
})
.then(response => response.json())
.then(data => console.log('SMS sent:', data));
```

### From Python:
```python
import requests

response = requests.post(
    'https://crm.pradytecai.com/api/2/messages/send',
    headers={
        'X-API-Key': 'USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh',
        'Content-Type': 'application/json'
    },
    json={
        'client_id': 2,
        'channel': 'sms',
        'recipient': '254728883160',
        'sender': 'FORTRESS',
        'body': 'Your message'
    }
)

print(response.json())
```

---

## 🔒 BulkSms CRM Production Checklist

### On Production Server (crm.pradytecai.com):

- [ ] **SSL Certificate installed** (HTTPS working)
- [ ] **Database configured** with production credentials
- [ ] **`.env` file updated** with production settings
- [ ] **APP_ENV=production** in .env
- [ ] **APP_DEBUG=false** in .env
- [ ] **APP_URL=https://crm.pradytecai.com** in .env
- [ ] **Run migrations:** `php artisan migrate`
- [ ] **Clear caches:** 
  ```bash
  php artisan config:clear
  php artisan cache:clear
  php artisan route:clear
  php artisan view:clear
  ```
- [ ] **Optimize:** `php artisan optimize`
- [ ] **Test API endpoint** works from external call

### Test Production API:
```bash
curl -X POST https://crm.pradytecai.com/api/2/messages/send \
  -H "X-API-Key: USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 2,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "FORTRESS",
    "body": "Production test from external system"
  }'
```

Expected response:
```json
{
    "id": 7,
    "status": "sent",
    "provider_message_id": "xxxxx-xxxxx-xxxxx"
}
```

---

## 📊 Architecture Overview

```
┌─────────────────────┐
│  External Systems   │
│  - PCIP             │
│  - Other websites   │
│  - Mobile apps      │
└──────────┬──────────┘
           │
           │ HTTPS API Calls
           │ https://crm.pradytecai.com/api/*/messages/send
           │
           ▼
┌──────────────────────────────────────┐
│   BulkSms CRM (Your Server)          │
│   Domain: crm.pradytecai.com         │
│                                      │
│   - Validates API Key                │
│   - Checks Client Balance            │
│   - Tracks Usage                     │
│   - Logs Messages                    │
└──────────┬───────────────────────────┘
           │
           │ Calls Onfon API
           │ (Using YOUR credentials)
           │
           ▼
┌──────────────────────────────────────┐
│   Onfon Media API                    │
│   Your Account: PRADY_TECH           │
│                                      │
│   - Processes SMS                    │
│   - Delivers to Network              │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│   Recipient Phone                    │
│   Receives SMS with sender ID        │
│   (FORTRESS, PRADY_TECH, etc.)       │
└──────────────────────────────────────┘
```

---

## 🎯 What's Ready

✅ **BulkSms CRM System** - Ready to deploy to crm.pradytecai.com  
✅ **5 Clients Configured** - FORTRESS, PRADY_TECH, FALLEY-MED, LOGIC-LINK, BriskCredit  
✅ **API Tested** - Working locally (tested with Message IDs: 4, 5, 6, 7)  
✅ **PCIP Integration** - FortressSmsService configured for production  
✅ **Documentation** - Complete API docs and examples  
✅ **Multi-Platform Support** - PHP, JavaScript, Python examples provided  

---

## 📞 Next Steps

1. **Deploy BulkSms CRM to production server** (crm.pradytecai.com)
2. **Configure production .env file** on server
3. **Run database migrations** on production
4. **Test API endpoint** from external system
5. **Update PCIP .env** with production credentials
6. **Share API credentials** with other systems that need to call your API

---

## 🔐 Security Notes

- ✅ API keys are unique per client
- ✅ Clients can only use their assigned sender ID
- ✅ Balance tracking prevents overuse
- ✅ All calls logged for audit
- ✅ HTTPS encrypts all API traffic
- ⚠️ Keep API keys secret
- ⚠️ Monitor API usage regularly

---

**Status:** ✅ Ready for Production Deployment

**Domain:** https://crm.pradytecai.com  
**Primary Use Case:** PCIP → BulkSms CRM → Onfon → SMS Delivery

