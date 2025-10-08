# 🔐 System Credentials & Access

## Web Login

**URL**: http://127.0.0.1:8000/login

**Default Admin Account:**
- Email: `admin@bulksms.local`
- Password: `password`

## API Access

**Client 1 API Key**: `bae377bc-0282-4fc9-a2a1-e338b18da77a`

**Usage Example:**
```bash
curl -X POST http://127.0.0.1:8000/api/1/messages/send \
  -H "X-API-KEY: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "PRADY_TECH",
    "body": "Test message"
  }'
```

## Onfon SMS Credentials

**Configured in Settings → Channel Providers**

- **API Key**: `VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=`
- **Client ID**: `e27847c1-a9fe-4eef-b60d-ddb291b175ab`
- **Access Key Header**: `8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB`
- **Default Sender**: `PRADY_TECH`
- **Status**: ✅ Active and tested

## Webhook URLs

Configure these in your Onfon portal to enable two-way communication:

**Onfon Inbound (MO)**: `http://your-domain.com/api/webhooks/onfon/inbound` ⭐ **NEW - For receiving replies**  
**Onfon DLR (MT Status)**: `http://your-domain.com/api/webhooks/onfon/dlr`  
**WhatsApp**: `http://your-domain.com/api/webhooks/whatsapp`  
**Email**: `http://your-domain.com/api/webhooks/email`

**For Local Testing**: Use ngrok or similar tunnel service

## Database Access

**Connection:** MySQL/MariaDB  
**Database:** `bulk_sms_laravel`  
**User:** `root`  
**Password:** (empty)  
**Port:** 3306

## Important Pages

- **Dashboard**: http://127.0.0.1:8000
- **Settings**: http://127.0.0.1:8000/settings (View/Edit all credentials)
- **Contacts**: http://127.0.0.1:8000/contacts
- **Templates**: http://127.0.0.1:8000/templates
- **Campaigns**: http://127.0.0.1:8000/campaigns
- **Messages**: http://127.0.0.1:8000/messages

## Security Notes

⚠️ **Before Production:**
1. Change the admin password
2. Regenerate API keys from Settings page
3. Use strong, unique passwords
4. Enable HTTPS
5. Restrict database access
6. Update Onfon webhook URL to public domain

## How to Find Credentials

### Via Web UI:
1. Login at http://127.0.0.1:8000/login
2. Navigate to **Settings** in the sidebar
3. All credentials are displayed there:
   - Client API Key (with copy button)
   - Onfon channel configuration
   - Webhook URLs
   - System information

### Via Database:
```sql
-- Get client API key
SELECT api_key FROM clients WHERE id = 1;

-- Get channel credentials  
SELECT name, provider, credentials FROM channels WHERE client_id = 1;
```

## Test Status

✅ SMS sent and delivered successfully  
✅ Login system working  
✅ All pages accessible  
✅ API endpoints functional  

**Last Test:**
- Message to: 254728883160
- Status: Delivered
- Provider Message ID: 84157deb-75d0-408c-9cf3-23b22e7d198f

