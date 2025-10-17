# Quick API Setup for PRADY_TECH

## TL;DR - Get PRADY_TECH sending SMS in 5 minutes

### Step 1: Generate API Key (30 seconds)
```bash
php generate_api_credentials.php
```

Copy the API Key from the output.

### Step 2: Add Balance (1 minute)
```sql
UPDATE clients 
SET balance = 1000.00, price_per_unit = 1.00 
WHERE sender_id = 'PRADY_TECH';
```

### Step 3: Test It (30 seconds)
```bash
php test_sender_api.php
```

### Step 4: Share with PRADY_TECH (1 minute)

Send them these 3 things:
1. **API Key** (from Step 1)
2. **Client ID** (usually `1`)
3. **API Endpoint** (`http://your-domain.com/api/1/messages/send`)

### Step 5: They Start Sending (2 minutes)

PRADY_TECH can now send SMS like this:

```bash
curl -X POST http://your-domain.com/api/1/messages/send \
  -H "X-API-Key: their_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "sms",
    "recipient": "254712345678",
    "body": "Hello!",
    "sender": "PRADY_TECH"
  }'
```

---

## What Changed?

### Before:
```
PRADY_TECH → Onfon API (direct)
```
- They manage Onfon credentials
- No usage tracking
- No billing control

### After:
```
PRADY_TECH → Your API → Onfon API
```
- They use your API key
- Full usage tracking
- Proper billing
- Better security

---

## Testing Commands

### Check if API is running:
```bash
curl http://localhost/api/health
```

### Check balance:
```bash
curl -H "X-API-Key: YOUR_KEY" \
  http://localhost/api/1/client/balance
```

### Send test SMS:
```bash
curl -X POST http://localhost/api/1/messages/send \
  -H "X-API-Key: YOUR_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "sms",
    "recipient": "254700000000",
    "body": "Test from API",
    "sender": "PRADY_TECH"
  }'
```

---

## Files Created

✅ `generate_api_credentials.php` - Generate credentials  
✅ `test_sender_api.php` - Test the API  
✅ `SENDER_API_DOCUMENTATION.md` - Full docs  
✅ `SETUP_PRADY_TECH_API.md` - Detailed setup guide  
✅ `PRADY_TECH_API_CREDENTIALS.txt` - Auto-generated credentials  

---

## Troubleshooting

**Problem:** Script doesn't work  
**Fix:** Check database connection and run migrations

**Problem:** 401 Unauthorized  
**Fix:** Check API key and header name (`X-API-Key`)

**Problem:** Insufficient balance  
**Fix:** Add balance to the client

**Problem:** SMS not sending  
**Fix:** Check Onfon credentials in channel config

---

## Support

For full documentation, see:
- `SENDER_API_DOCUMENTATION.md` - Complete API reference
- `SETUP_PRADY_TECH_API.md` - Detailed setup guide

---

**Ready to go? Run:** `php generate_api_credentials.php`

