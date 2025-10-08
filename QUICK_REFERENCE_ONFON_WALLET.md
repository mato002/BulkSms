# Onfon Wallet Integration - Quick Reference

## 🚀 Quick Start (5 Steps)

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Configure Sender
1. Go to **Admin → Manage Senders**
2. Edit any sender
3. Scroll to **"Onfon Media Wallet"** section
4. Fill in:
   - **Onfon API Key**: From portal.onfonmedia.co.ke
   - **Onfon Client ID**: Your client UUID
   - **Access Key Header**: (optional, has default)
   - **Default Sender ID**: e.g., PRADY_TECH
5. Check **"Auto-sync balance"** (optional)
6. Click **"Save Onfon Credentials"**

### 3. Test Connection
Click **"Test Connection"** button to verify

### 4. Sync Balance
Click **"Sync from Onfon Now"** button

### 5. Set Up Cron (Production)
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## 📊 Dashboard Quick Access

| Feature | Location | Action |
|---------|----------|--------|
| View Balances | `/admin/senders/{id}` | See Local & Onfon balance cards |
| Configure | `/admin/senders/{id}/edit` | Scroll to "Onfon Media Wallet" |
| Manual Sync | `/admin/senders/{id}/edit` | Click "Sync from Onfon Now" |
| Test Connection | `/admin/senders/{id}/edit` | Click "Test Connection" |

## 🔌 API Quick Reference

### Authentication
```bash
-H "X-API-Key: your-sender-api-key"
```

### Endpoints

**Get Balance**
```bash
GET /api/{company_id}/wallet/balance
```

**Sync Balance**
```bash
POST /api/{company_id}/wallet/sync
```

**Test Connection**
```bash
POST /api/{company_id}/wallet/test-connection
```

**Get Transactions**
```bash
GET /api/{company_id}/wallet/transactions?from_date=2025-10-01&to_date=2025-10-08
```

**Check Sufficient**
```bash
POST /api/{company_id}/wallet/check-sufficient
Content-Type: application/json

{"amount": 100}
```

## 💻 Command Line

### Sync All Auto-Enabled Clients
```bash
php artisan onfon:sync-balances
```

### Sync Specific Clients
```bash
php artisan onfon:sync-balances --client=1 --client=2
```

### View Schedule
```bash
php artisan schedule:list
```

### Run Schedule Manually
```bash
php artisan schedule:run
```

## 🔍 Troubleshooting

### Connection Failed
```bash
# Check credentials in admin panel
# Test connection button
# Check logs
tail -f storage/logs/laravel.log | grep Onfon
```

### Auto-Sync Not Working
```bash
# Check if cron is running
php artisan schedule:work

# Check auto-sync is enabled
SELECT name, auto_sync_balance FROM clients WHERE id=1;
```

### Balance Not Updating
```bash
# Manual sync via command
php artisan onfon:sync-balances --client=1

# Check Onfon credentials
# Verify API key and Client ID
```

## 📝 Code Snippets

### Get Balance Programmatically
```php
use App\Services\OnfonWalletService;

$service = app(OnfonWalletService::class);
$client = Client::find(1);

$result = $service->getBalance($client);
// $result['success'], $result['balance'], $result['units']
```

### Sync Balance
```php
$result = $service->syncBalance($client);
// $result['old_balance'], $result['new_balance']
```

### Check Sufficient
```php
$result = $service->hasSufficientBalance($client, 100);
// $result['sufficient'], $result['shortfall']
```

## 🎯 Common Use Cases

### Use Case 1: Add New Sender with Onfon
1. Create sender in admin
2. Get Onfon API key from portal.onfonmedia.co.ke
3. Configure in edit page
4. Test connection
5. Enable auto-sync
6. Done!

### Use Case 2: Monitor Balance
- View sender details page
- See both Local and Onfon balance
- If different, click sync
- Enable auto-sync to keep in sync

### Use Case 3: API Integration
```bash
# Your app calls
curl -X GET http://yourapp.com/api/1/wallet/balance \
  -H "X-API-Key: sk_sender1_key"

# Returns
{
  "status": "success",
  "data": {
    "onfon_balance": 1500.50,
    "local_balance": 1450.00,
    "currency": "KES"
  }
}
```

## 📋 Checklist for Each Sender

- [ ] Create sender in admin panel
- [ ] Generate API key (auto-generated)
- [ ] Get Onfon credentials from portal
- [ ] Configure Onfon in edit page
- [ ] Test connection (green = success)
- [ ] Sync balance once
- [ ] Enable auto-sync (optional)
- [ ] Set price per unit
- [ ] Test API endpoint
- [ ] Monitor balance regularly

## 🔗 Important URLs

- **Admin Dashboard**: `/admin/senders`
- **Onfon Portal**: https://portal.onfonmedia.co.ke/
- **API Base**: `/api/{company_id}/wallet/`
- **Full Docs**: See `ONFON_WALLET_INTEGRATION.md`

## 🆘 Emergency Commands

### Force Sync All
```bash
php artisan onfon:sync-balances
```

### Check Specific Client
```bash
php artisan onfon:sync-balances --client=1
```

### View Logs
```bash
tail -f storage/logs/laravel.log | grep -i onfon
```

### Database Check
```sql
-- View Onfon settings
SELECT id, name, onfon_balance, onfon_last_sync, auto_sync_balance 
FROM clients;

-- View credentials (be careful!)
SELECT id, name, settings 
FROM clients 
WHERE JSON_EXTRACT(settings, '$.onfon_credentials') IS NOT NULL;
```

---

**Need More Help?** See `ONFON_WALLET_INTEGRATION.md` for detailed documentation.

