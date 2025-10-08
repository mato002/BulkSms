# Onfon Media Wallet Integration Guide

## Overview

Complete integration with Onfon Media (https://portal.onfonmedia.co.ke/) for managing individual sender wallets and balances directly from your Laravel application.

## ✨ Features Implemented

### 1. **Per-Sender Onfon Wallet Management**
- Each sender can have their own Onfon Media credentials
- Individual wallet balance tracking
- Real-time balance synchronization
- Automatic balance updates before sending

### 2. **Admin Dashboard Integration**
- View both local and Onfon balances side-by-side
- One-click balance synchronization
- Test Onfon connection
- Configure credentials per sender
- Transaction history viewing

### 3. **API Endpoints**
Complete REST API for wallet management:
- Get balance from Onfon
- Sync balance
- Test connection
- View transactions
- Check sufficient balance

### 4. **Automated Balance Sync**
- Scheduled job runs every 15 minutes
- Auto-sync option per sender
- Manual sync available
- Balance history tracking

## 🚀 Quick Start

### Step 1: Run Migration

```bash
php artisan migrate
```

This adds the following fields to `clients` table:
- `onfon_balance` - Last synced Onfon balance
- `onfon_last_sync` - Timestamp of last sync
- `auto_sync_balance` - Enable/disable auto-sync
- `company_name` - Company name
- `price_per_unit` - SMS unit pricing

### Step 2: Configure Onfon Credentials

1. Go to **Admin → Manage Senders**
2. Select a sender
3. Click **Edit**
4. Scroll to **Onfon Media Wallet** section
5. Fill in:
   - Onfon API Key
   - Onfon Client ID
   - Access Key Header (optional)
   - Default Sender ID
6. Enable **Auto-sync balance** if desired
7. Click **Save Onfon Credentials**
8. Click **Test Connection** to verify

### Step 3: Sync Balance

**Via Web UI:**
- Click **Sync from Onfon Now** button on edit page
- Or click **Sync from Onfon** on sender details page

**Via Command Line:**
```bash
# Sync all clients with auto-sync enabled
php artisan onfon:sync-balances

# Sync specific clients
php artisan onfon:sync-balances --client=1 --client=2
```

**Via API:**
```bash
curl -X POST http://your-domain.com/api/{company_id}/wallet/sync \
  -H "X-API-Key: your-api-key"
```

## 📋 Admin Dashboard Features

### Sender Details Page

Shows two balance cards:

**Local Balance (Purple Card)**
- Current balance in your database
- Balance in KES and Units
- Update balance form (Add/Deduct/Set)

**Onfon Balance (Green Card)**
- Real-time balance from Onfon Media
- Last sync timestamp
- Sync button
- Configure button if not set up

### Sender Edit Page

**Onfon Media Wallet Section:**
- Configure Onfon API credentials
- Test connection button
- View local vs Onfon balance
- One-click sync button
- Auto-sync toggle

## 🔌 API Endpoints

All endpoints require authentication via `X-API-Key` header.

### Get Wallet Balance
```bash
GET /api/{company_id}/wallet/balance

Response:
{
  "status": "success",
  "data": {
    "onfon_balance": 1500.50,
    "currency": "KES",
    "units": 1500.50,
    "local_balance": 1450.00,
    "local_units": 1450.00,
    "last_sync": "2025-10-08T10:30:00Z"
  }
}
```

### Sync Balance
```bash
POST /api/{company_id}/wallet/sync

Response:
{
  "status": "success",
  "message": "Balance synchronized successfully",
  "data": {
    "old_balance": 1450.00,
    "new_balance": 1500.50,
    "difference": 50.50,
    "units": 1500.50,
    "synced_at": "2025-10-08T10:35:00Z"
  }
}
```

### Test Connection
```bash
POST /api/{company_id}/wallet/test-connection

Response:
{
  "status": "success",
  "message": "Connection successful!",
  "data": {
    "balance": 1500.50,
    "currency": "KES",
    "units": 1500.50
  }
}
```

### Get Transactions
```bash
GET /api/{company_id}/wallet/transactions?from_date=2025-10-01&to_date=2025-10-08

Response:
{
  "status": "success",
  "data": {
    "transactions": [...],
    "count": 25
  }
}
```

### Check Sufficient Balance
```bash
POST /api/{company_id}/wallet/check-sufficient
Content-Type: application/json

{
  "amount": 100
}

Response:
{
  "status": "success",
  "data": {
    "sufficient": true,
    "current_balance": 1500.50,
    "required_amount": 100,
    "shortfall": 0,
    "units_available": 1500.50
  }
}
```

## ⚙️ Configuration

### Enable Auto-Sync

**Via Admin UI:**
1. Edit sender
2. Check "Auto-sync balance before sending messages"
3. Save

**Programmatically:**
```php
$client->auto_sync_balance = true;
$client->save();
```

### Set Price Per Unit

```php
$client->price_per_unit = 1.00; // KES per SMS
$client->save();
```

### Configure Onfon Credentials

**Via Admin UI:** See Step 2 above

**Programmatically:**
```php
$settings = $client->settings ?? [];
$settings['onfon_credentials'] = [
    'api_key' => 'YOUR_ONFON_API_KEY',
    'client_id' => 'YOUR_ONFON_CLIENT_ID',
    'access_key_header' => '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
    'default_sender' => 'SENDER_ID',
];
$client->settings = $settings;
$client->save();
```

## 🔄 Scheduled Balance Sync

The system automatically syncs balances every 15 minutes for clients with `auto_sync_balance` enabled.

**Cron Setup (Production):**
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Manual Trigger:**
```bash
php artisan schedule:run
```

**Check Schedule:**
```bash
php artisan schedule:list
```

## 📊 Balance Tracking

### Local Balance
- Stored in `clients.balance`
- Managed manually by admin
- Used for internal tracking

### Onfon Balance
- Synced from Onfon Media API
- Stored in `clients.onfon_balance`
- Updated via sync operations
- Last sync time in `clients.onfon_last_sync`

### Balance Sync Logic
1. Fetch balance from Onfon API
2. Update `onfon_balance` with fetched value
3. Update `onfon_last_sync` to current time
4. Optionally update `balance` to match Onfon

## 🔐 Security

### API Credentials
- Stored encrypted in `clients.settings` JSON field
- Never exposed in API responses
- Admin-only access to view/edit

### Access Control
- Only admins can configure Onfon credentials
- API endpoints require valid API key
- Balance sync limited to authenticated clients

## 🛠️ Troubleshooting

### Balance Not Syncing

**Check Credentials:**
1. Go to sender edit page
2. Verify Onfon API Key and Client ID
3. Click "Test Connection"

**Check Logs:**
```bash
tail -f storage/logs/laravel.log | grep Onfon
```

**Manual Sync:**
```bash
php artisan onfon:sync-balances --client=1
```

### Connection Errors

**SSL Certificate Issues:**
```php
// Already disabled in development
->withOptions(['verify' => false])
```

**Timeout Issues:**
Increase timeout in `OnfonWalletService.php`:
```php
Http::timeout(60) // Increase from 30 to 60 seconds
```

### Auto-Sync Not Working

**Check Cron:**
```bash
# Ensure Laravel scheduler is running
php artisan schedule:work
```

**Check Auto-Sync Setting:**
```sql
SELECT id, name, auto_sync_balance FROM clients WHERE auto_sync_balance = 1;
```

## 📈 Usage Examples

### Example 1: Multi-Tenant Setup

```php
// Sender 1: Prady Tech with their own Onfon account
$prady = Client::find(1);
$prady->settings = [
    'onfon_credentials' => [
        'api_key' => 'prady_onfon_key',
        'client_id' => 'prady_client_id',
        'default_sender' => 'PRADY_TECH'
    ]
];
$prady->auto_sync_balance = true;
$prady->save();

// Sender 2: Falley Med with their own Onfon account
$falley = Client::find(2);
$falley->settings = [
    'onfon_credentials' => [
        'api_key' => 'falley_onfon_key',
        'client_id' => 'falley_client_id',
        'default_sender' => 'FALLEY-MED'
    ]
];
$falley->auto_sync_balance = true;
$falley->save();
```

### Example 2: Check Balance Before Sending

```php
use App\Services\OnfonWalletService;

$walletService = app(OnfonWalletService::class);
$client = Client::find(1);

// Check if sufficient balance exists
$check = $walletService->hasSufficientBalance($client, 100);

if ($check['sufficient']) {
    // Send message
    echo "Balance OK: {$check['current_balance']} KES";
} else {
    echo "Insufficient! Need {$check['shortfall']} KES more";
}
```

### Example 3: Get Transaction History

```php
$walletService = app(OnfonWalletService::class);
$client = Client::find(1);

$result = $walletService->getTransactionHistory(
    $client,
    '2025-10-01',
    '2025-10-08'
);

if ($result['success']) {
    foreach ($result['transactions'] as $txn) {
        echo "{$txn['date']}: {$txn['type']} - {$txn['amount']}\n";
    }
}
```

## 📝 Database Schema

### Clients Table (New Fields)

```sql
ALTER TABLE clients ADD COLUMN company_name VARCHAR(255) AFTER sender_id;
ALTER TABLE clients ADD COLUMN price_per_unit DECIMAL(10,4) DEFAULT 1.00 AFTER balance;
ALTER TABLE clients ADD COLUMN onfon_balance DECIMAL(10,2) NULL COMMENT 'Last synced balance from Onfon Media';
ALTER TABLE clients ADD COLUMN onfon_last_sync TIMESTAMP NULL COMMENT 'Last time balance was synced';
ALTER TABLE clients ADD COLUMN auto_sync_balance BOOLEAN DEFAULT FALSE COMMENT 'Auto sync balance before sending';
```

## 🎯 Best Practices

1. **Regular Syncs**: Enable auto-sync for active senders
2. **Monitor Balances**: Set up alerts for low balances
3. **Secure Credentials**: Never expose Onfon API keys
4. **Test First**: Always test connection after configuring
5. **Log Everything**: Monitor sync logs for issues
6. **Backup Settings**: Export client settings regularly

## 🔗 Related Files

- **Service**: `app/Services/OnfonWalletService.php`
- **Controller**: `app/Http/Controllers/AdminController.php`
- **API Controller**: `app/Http/Controllers/Api/WalletController.php`
- **Command**: `app/Console/Commands/SyncOnfonBalances.php`
- **Migration**: `database/migrations/2025_10_08_000001_add_onfon_fields_to_clients_table.php`
- **Views**: 
  - `resources/views/admin/senders/edit.blade.php`
  - `resources/views/admin/senders/show.blade.php`

## 📞 Support

For Onfon Media API documentation and support:
- Portal: https://portal.onfonmedia.co.ke/
- API Docs: Contact Onfon support

For this integration:
- Check logs: `storage/logs/laravel.log`
- Run diagnostics: `php artisan onfon:sync-balances --client=X`
- Test connection via admin UI

---

**Last Updated:** October 8, 2025  
**Version:** 1.0.0

