# 🌐 Production API Endpoints - BulkSms CRM

**Production Domain:** `crm.pradytecai.com`

---

## 📡 API Endpoints

### FORTRESS (Client ID: 2)
```
POST https://crm.pradytecai.com/api/2/messages/send
GET  https://crm.pradytecai.com/api/2/client/balance
```

### PRADY_TECH (Client ID: 1)
```
POST https://crm.pradytecai.com/api/1/messages/send
GET  https://crm.pradytecai.com/api/1/client/balance
```

### FALLEY-MED (Client ID: 3)
```
POST https://crm.pradytecai.com/api/3/messages/send
GET  https://crm.pradytecai.com/api/3/client/balance
```

### LOGIC-LINK (Client ID: 4)
```
POST https://crm.pradytecai.com/api/4/messages/send
GET  https://crm.pradytecai.com/api/4/client/balance
```

### BriskCredit (Client ID: 5)
```
POST https://crm.pradytecai.com/api/5/messages/send
GET  https://crm.pradytecai.com/api/5/client/balance
```

---

## 🔑 API Keys

| Client | API Key | Endpoint |
|--------|---------|----------|
| FORTRESS | `USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh` | https://crm.pradytecai.com/api/2/messages/send |
| PRADY_TECH | `ea55cb72-a734-48b2-87a6-8d0ea1d397de` | https://crm.pradytecai.com/api/1/messages/send |
| FALLEY-MED | `6d3d9896-6d4a-42dc-9bd6-675fd2f4e3f4` | https://crm.pradytecai.com/api/3/messages/send |
| LOGIC-LINK | `45cd60bb-ff46-41ce-9920-d25306315c1b` | https://crm.pradytecai.com/api/4/messages/send |
| BriskCredit | `fa40c0fc-516b-4c21-8a5d-bf7fbe740a4e` | https://crm.pradytecai.com/api/5/messages/send |

---

## 🚀 Production Request Example

### cURL
```bash
curl -X POST https://crm.pradytecai.com/api/2/messages/send \
  -H "X-API-Key: USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 2,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "FORTRESS",
    "body": "Your message here"
  }'
```

### PHP (Laravel)
```php
use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'X-API-Key' => 'USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh'
])->post('https://crm.pradytecai.com/api/2/messages/send', [
    'client_id' => 2,
    'channel' => 'sms',
    'recipient' => '254728883160',
    'sender' => 'FORTRESS',
    'body' => 'Your message'
]);
```

### JavaScript/Node.js
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
});
```

---

## ✅ PCIP Configuration Updated

**File:** `C:\xampp\htdocs\pcip\app\Services\FortressSmsService.php`

Now using: `https://crm.pradytecai.com/api/2/messages/send`

✅ Ready for production!

---

## 🔒 Security Checklist for Production

- [ ] Ensure HTTPS is enabled on crm.pradytecai.com
- [ ] Valid SSL certificate installed
- [ ] API rate limiting configured
- [ ] CORS configured if needed
- [ ] Firewall rules allow API access
- [ ] Monitor API usage
- [ ] Set up error logging

---

## 📊 Test Production Endpoint

```bash
curl https://crm.pradytecai.com/api/2/client/balance \
  -H "X-API-Key: USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh"
```

Expected response:
```json
{
    "client_id": 2,
    "client_name": "Fortress Limited",
    "balance": 100.00
}
```

---

**Production URL:** https://crm.pradytecai.com  
**Status:** ✅ Ready for external systems to call

