# Quick Start Guide

## ✅ System is Ready!

Your Bulk SMS CRM is fully configured and working with Onfon SMS integration.

## Access the Application

**Web UI**: http://127.0.0.1:8000

### Main Pages
- Dashboard: http://127.0.0.1:8000
- Contacts: http://127.0.0.1:8000/contacts
- Templates: http://127.0.0.1:8000/templates  
- Campaigns: http://127.0.0.1:8000/campaigns
- Messages: http://127.0.0.1:8000/messages

## Sending Your First Campaign

### Option 1: Via Web UI

1. Go to **Contacts** → Click "Import CSV"
2. Upload CSV with format: `Name, Phone, Department`
3. Go to **Campaigns** → Click "Create Campaign"
4. Fill in:
   - Name: "Test Campaign"
   - Sender ID: "PRADY_TECH"
   - Message: Your text
   - Recipients: Paste phone numbers (comma-separated)
5. Click "Create Campaign" then "Send Now"

### Option 2: Via API

```bash
curl -X POST http://127.0.0.1:8000/api/1/messages/send \
  -H "X-API-KEY: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "PRADY_TECH",
    "body": "Test message via API"
  }'
```

## Your API Key

**Client ID 1 API Key**: `bae377bc-0282-4fc9-a2a1-e338b18da77a`

Use this in the `X-API-KEY` header for all API requests.

## Onfon Configuration (Already Set Up)

Your Onfon SMS channel is configured with:
- **Provider**: Onfon
- **Sender ID**: PRADY_TECH
- **Status**: ✅ Working (tested successfully)

## Webhook URL

Configure this URL in your Onfon portal for delivery reports:
```
http://your-public-domain.com/api/webhooks/onfon/dlr
```

(For local testing, use ngrok or similar tunneling service)

## Features Implemented

✅ SMS sending via Onfon  
✅ Contact management with CSV import  
✅ Message templates  
✅ Bulk campaigns  
✅ Message history and tracking  
✅ Dashboard with stats  
✅ REST API with authentication  
✅ Webhook support for delivery reports  
✅ Queue jobs for async sending (configured)  
✅ Multi-channel architecture (SMS, WhatsApp, Email)  

## Next Steps

### Add More Channels

**WhatsApp**: Update `CloudWhatsAppSender` with WhatsApp Cloud API credentials  
**Email**: Update `SmtpEmailSender` with SMTP or provider credentials

### Queue Workers

For production, run queue workers:
```bash
php artisan queue:work --tries=3
```

### Production Deployment

1. Set `APP_ENV=production` in `.env`
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Set up Supervisor for queue workers
5. Configure web server (Nginx/Apache)
6. Enable HTTPS
7. Set up proper logging and monitoring

## Support

Need help? Check:
- README.md for full documentation
- Laravel logs: `storage/logs/laravel.log`
- Onfon docs: https://www.docs.onfonmedia.co.ke/rest/

## System Status

🟢 All systems operational  
🟢 Onfon SMS: Connected and tested  
🟢 Database: MySQL running  
🟢 Web UI: Accessible  
🟢 API: Ready

