# 🔐 System Credentials & Access

## Web Login

**URL**: http://127.0.0.1:8000/login

**Default Admin Account:**
- Email: `admin@bulksms.local`
- Password: `password`

## API Access

### Production Domain
**URL**: https://crm.pradytecai.com

### All API Clients

| Client ID | Name | Sender ID | API Key |
|-----------|------|-----------|---------|
| 1 | PRADY_TECH | PRADY_TECH | `ea55cb72-a734-48b2-87a6-8d0ea1d397de` |
| 2 | FORTRESS | FORTRESS | `USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh` |
| 3 | FALLEY-MED | FALLEY-MED | `6d3d9896-6d4a-42dc-9bd6-675fd2f4e3f4` |
| 4 | LOGIC-LINK | LOGIC-LINK | `45cd60bb-ff46-41ce-9920-d25306315c1b` |
| 5 | BriskCredit | BriskCredit | `fa40c0fc-516b-4c21-8a5d-bf7fbe740a4e` |

**Production Usage Example (FORTRESS):**
```bash
curl -X POST https://crm.pradytecai.com/api/2/messages/send \
  -H "X-API-Key: USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 2,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "FORTRESS",
    "body": "Test message from production"
  }'
```

**Local Testing Example (PRADY_TECH):**
```bash
curl -X POST http://127.0.0.1:8000/api/1/messages/send \
  -H "X-API-Key: ea55cb72-a734-48b2-87a6-8d0ea1d397de" \
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

**Onfon Inbound (MO)**: `https://crm.pradytecai.com/api/webhooks/onfon/inbound` ⭐ **For receiving replies**  
**Onfon DLR (MT Status)**: `https://crm.pradytecai.com/api/webhooks/onfon/dlr`  
**WhatsApp**: `https://crm.pradytecai.com/api/webhooks/whatsapp`  
**Email**: `https://crm.pradytecai.com/api/webhooks/email`

**For Local Testing**: Use ngrok or similar tunnel service

## Database Access

**Connection:** MySQL/MariaDB  
**Database:** `bulk_sms_laravel`  
**User:** `root`  
**Password:** (empty)  
**Port:** 3306

## Important Pages

### Production (crm.pradytecai.com)
- **Dashboard**: https://crm.pradytecai.com
- **Settings**: https://crm.pradytecai.com/settings (View/Edit all credentials)
- **Contacts**: https://crm.pradytecai.com/contacts
- **Templates**: https://crm.pradytecai.com/templates
- **Campaigns**: https://crm.pradytecai.com/campaigns
- **Messages**: https://crm.pradytecai.com/messages

### Local Development
- **Dashboard**: http://127.0.0.1:8000
- **Settings**: http://127.0.0.1:8000/settings
- **Contacts**: http://127.0.0.1:8000/contacts

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

