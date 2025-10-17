# Tenant Storage - Quick Reference

## 🎯 Simple Answer

**Tenants are stored in the `clients` table.** Each row = One tenant/sender.

---

## 📊 Main Table: `clients`

```sql
CREATE TABLE clients (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    name            VARCHAR(255),           -- "Prady Technologies"
    sender_id       VARCHAR(255),           -- "PRADY_TECH" (SMS sender name)
    contact         VARCHAR(255),           -- "admin@prady.com"
    api_key         VARCHAR(255) UNIQUE,    -- "abc123xyz789..." (32 chars)
    balance         DECIMAL(10,2),          -- 1000.00 (KSH)
    price_per_unit  DECIMAL(10,4),          -- 1.0000 (cost per SMS)
    status          BOOLEAN,                -- 1 = Active, 0 = Inactive
    tier            VARCHAR(50),            -- "basic", "standard", "premium"
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);
```

---

## 💾 Example Tenant Record

```json
{
  "id": 1,
  "name": "Prady Technologies",
  "sender_id": "PRADY_TECH",
  "contact": "admin@pradytech.com",
  "api_key": "abc123xyz789pradytech12345678",
  "balance": 1000.00,
  "price_per_unit": 1.0000,
  "status": 1,
  "tier": "standard",
  "created_at": "2025-01-15 10:30:00",
  "updated_at": "2025-10-10 14:30:00"
}
```

---

## 🔗 Complete Database Structure

```
┌──────────────────────────────────────────────────┐
│                    clients                       │
│  (Main table - stores all tenants)               │
│                                                  │
│  • id (primary key)                              │
│  • name                                          │
│  • sender_id                                     │
│  • api_key (unique)                              │
│  • balance                                       │
│  • status                                        │
└────────────────┬─────────────────────────────────┘
                 │ client_id (foreign key)
                 │
    ┌────────────┼────────────┬──────────────┬──────────────┐
    │            │            │              │              │
    ▼            ▼            ▼              ▼              ▼
┌────────┐  ┌─────────┐  ┌─────────┐  ┌──────────┐  ┌──────────┐
│ users  │  │channels │  │messages │  │ contacts │  │campaigns │
│(Login) │  │(Config) │  │(Sent)   │  │(Book)    │  │(Bulk)    │
└────────┘  └─────────┘  └─────────┘  └──────────┘  └──────────┘
```

**Key Point:** Every related table has `client_id` linking back to `clients.id`

---

## 🔍 How to View Tenants

### Method 1: PHP Script
```bash
php show_tenants.php
```

### Method 2: Artisan Tinker
```bash
php artisan tinker

>>> App\Models\Client::all();
>>> App\Models\Client::find(1);
>>> App\Models\Client::where('sender_id', 'PRADY_TECH')->first();
```

### Method 3: SQL Query
```sql
SELECT id, name, sender_id, balance, api_key, status
FROM clients
ORDER BY id;
```

---

## 🆕 How to Create a Tenant

### Quick Creation Script
```bash
php artisan tinker

>>> use App\Models\Client;
>>> use Illuminate\Support\Str;
>>> 
>>> $client = Client::create([
...   'name' => 'New Company',
...   'sender_id' => 'NEWCOMPANY',
...   'contact' => 'admin@newcompany.com',
...   'api_key' => Str::random(32),
...   'balance' => 0,
...   'status' => true,
...   'tier' => 'basic'
... ]);
>>> 
>>> echo "Created: Client ID {$client->id}, API Key: {$client->api_key}";
```

### Using Admin Dashboard
1. Login as admin
2. Go to: `http://localhost:8000/admin/senders`
3. Click "Create New Sender"
4. Fill in details
5. Save

---

## 📋 Key Fields Explained

| Field | What It Is | Example |
|-------|------------|---------|
| `id` | Unique tenant ID | 1, 2, 3... |
| `sender_id` | SMS sender name (brand) | "PRADY_TECH" |
| `api_key` | Authentication key (32 chars) | "abc123xyz789..." |
| `balance` | Account balance in KSH | 1000.00 |
| `status` | Active (1) or Inactive (0) | 1 |
| `tier` | Service level | "standard" |

---

## 🔐 Tenant Isolation

### How It Works:

```php
// Request comes from PRADY_TECH (Client 1)
$client = $request->user();  // Client with id = 1

// Get their messages
$messages = Message::where('client_id', $client->id)->get();
// Returns ONLY Client 1's messages

// Get their contacts  
$contacts = Contact::where('client_id', $client->id)->get();
// Returns ONLY Client 1's contacts

// Try to access Client 2's data?
// IMPOSSIBLE - the api_key belongs to Client 1 only!
```

**Result:** Complete data isolation! No way for tenants to see each other's data.

---

## 📊 Example: Multiple Tenants

```
┌───┬──────────────────┬──────────────┬───────────┬────────┐
│ID │ Name             │ Sender ID    │ Balance   │ Status │
├───┼──────────────────┼──────────────┼───────────┼────────┤
│ 1 │ Prady Tech       │ PRADY_TECH   │ 1,000.00  │ Active │
│ 2 │ Test Sender      │ TEST_SENDER  │   50.00   │ Active │
│ 3 │ Fortress Ltd     │ FORTRESS     │ 5,000.00  │ Active │
│ 4 │ Logic Link       │ LOGIC-LINK   │  200.00   │ Inactive│
└───┴──────────────────┴──────────────┴───────────┴────────┘
```

Each tenant:
- ✅ Has unique ID
- ✅ Has unique API key
- ✅ Has own balance
- ✅ Can be activated/deactivated
- ✅ Has completely separate data

---

## 🎯 Summary

**Storage:**
- Main table: `clients`
- One row per tenant
- Related tables: `users`, `channels`, `messages`, `contacts`, `campaigns`
- All linked by `client_id`

**Authentication:**
- Each tenant has unique `api_key`
- API key looked up in `clients` table
- Returns the tenant's `id` and data

**Isolation:**
- All queries filtered by `client_id`
- Tenant 1 cannot access Tenant 2's data
- **100% secure multi-tenancy!**

---

For full details, see: `TENANT_DATABASE_STORAGE.md`

