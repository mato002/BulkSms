# 🚀 Quick Setup Guide - Tasks 1-4

Follow these steps to get everything working.

---

## Step 1: Run Migrations

```bash
cd C:\xampp\htdocs\bulk-sms-laravel
php artisan migrate
```

This creates:
- ✅ `wallet_transactions` table
- ✅ Webhook fields in `clients` table

---

## Step 2: Configure M-Pesa (For Testing - Sandbox)

Add to your `.env` file:

```env
# M-Pesa Sandbox Configuration (for testing)
MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=your_sandbox_consumer_key
MPESA_CONSUMER_SECRET=your_sandbox_consumer_secret
MPESA_PASSKEY=your_sandbox_passkey
MPESA_SHORTCODE=174379
MPESA_TRANSACTION_TYPE=CustomerPayBillOnline
```

**Get sandbox credentials from:** https://developer.safaricom.co.ke

---

## Step 3: Test API Documentation

Visit in your browser:
```
http://localhost:8000/api-documentation
```

You should see the complete API documentation page!

---

## Step 4: Test Top-up API (Manual Mode)

Since M-Pesa needs credentials, test with manual mode first:

```bash
curl -X POST http://localhost:8000/api/1/wallet/topup \
  -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  -H "Content-Type: application/json" \
  -d "{\"amount\": 1000, \"payment_method\": \"manual\", \"phone_number\": \"254712345678\"}"
```

Expected response:
```json
{
  "status": "pending",
  "message": "Manual top-up request created...",
  "transaction_id": "TXN-20251009-001"
}
```

---

## Step 5: Check Transaction History

```bash
curl -X GET http://localhost:8000/api/1/wallet/transactions \
  -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a"
```

You should see your transaction!

---

## Step 6: Configure Webhooks for a Sender

Update the database for client ID 1:

```sql
UPDATE clients 
SET 
  webhook_url = 'https://webhook.site/unique-url',
  webhook_secret = 'test_secret_123',
  webhook_events = '["balance.updated","topup.completed"]',
  webhook_active = 1
WHERE id = 1;
```

**Use webhook.site to test:** Go to https://webhook.site to get a test URL.

---

## Step 7: Test Webhook Sending

Run this in tinker:

```bash
php artisan tinker
```

Then:
```php
$client = \App\Models\Client::find(1);
$webhookService = app(\App\Services\WebhookService::class);

$webhookService->sendBalanceUpdated($client, 100, 200, 'TEST-001');
```

Check your webhook.site URL - you should see the webhook!

---

## Step 8: Setup Queue Worker (For Webhooks)

For webhooks to work asynchronously, run:

```bash
php artisan queue:work
```

Keep this running in a separate terminal.

---

## 🎯 Production Setup (When Ready)

### 1. Get M-Pesa Production Credentials

- Go to: https://developer.safaricom.co.ke
- Register your business
- Get production credentials
- Update `.env` with production keys

### 2. Update .env for Production

```env
MPESA_ENV=production
MPESA_CONSUMER_KEY=your_production_key
MPESA_CONSUMER_SECRET=your_production_secret
MPESA_PASSKEY=your_production_passkey
MPESA_SHORTCODE=your_paybill_number
```

### 3. Configure M-Pesa Callback URL

In your M-Pesa dashboard, set:
```
Callback URL: https://your-domain.com/api/webhooks/mpesa/callback
Timeout URL: https://your-domain.com/api/webhooks/mpesa/timeout
```

### 4. Setup Supervisor (For Queue Worker)

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/worker.log
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## ✅ Verification Checklist

- [ ] Migrations ran successfully
- [ ] API documentation page loads
- [ ] Can create manual top-up request
- [ ] Transaction appears in history
- [ ] Webhook URL configured
- [ ] Test webhook received
- [ ] Queue worker running

---

## 🐛 Troubleshooting

### API Documentation Not Loading

**Problem:** 404 error  
**Solution:** Clear route cache
```bash
php artisan route:clear
php artisan route:cache
```

### M-Pesa STK Push Not Working

**Problem:** "Failed to authenticate"  
**Solution:** 
1. Check credentials in `.env`
2. Verify credentials are correct
3. Check if using sandbox vs production correctly

### Webhooks Not Sending

**Problem:** Webhook not received  
**Solution:**
1. Ensure queue worker is running: `php artisan queue:work`
2. Check `webhook_active = 1` in database
3. Verify webhook_url is correct
4. Check Laravel logs: `storage/logs/laravel.log`

### "Class not found" Errors

**Problem:** MpesaService or WebhookService not found  
**Solution:** 
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

---

## 📚 What's Next?

1. **Test in sandbox** - Use M-Pesa sandbox to test STK Push
2. **Configure real credentials** - Get production M-Pesa account
3. **Test with real money** - Start with small amounts (KES 10)
4. **Monitor logs** - Watch `storage/logs/laravel.log`
5. **Add email notifications** - Welcome emails, low balance alerts
6. **Admin dashboard** - Build UI for webhook management

---

## 💡 Tips

1. **Use webhook.site** for testing webhooks before configuring real URLs
2. **Start with manual top-up** until M-Pesa is fully configured
3. **Keep queue worker running** in production (use Supervisor)
4. **Monitor logs** regularly for errors
5. **Test with sandbox** before going to production

---

## 📞 Need Help?

**Common Issues:**
- M-Pesa credentials: Check with Safaricom support
- Webhook not sending: Ensure queue worker is running
- Migration errors: Check database connection

**Resources:**
- M-Pesa Docs: https://developer.safaricom.co.ke
- Laravel Queues: https://laravel.com/docs/queues
- Webhook Testing: https://webhook.site

---

**You're all set!** 🎉

Visit `/api-documentation` to share with your senders!

