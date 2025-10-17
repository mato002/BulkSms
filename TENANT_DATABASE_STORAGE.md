# How Tenants Are Stored in the Database

## Overview

Tenants (also called **Clients** or **Senders**) are stored in the `clients` table in your database. Each tenant represents an independent company or sender that uses your SMS platform.

---

## 📊 Database Table: `clients`

### Table Structure

**Migration File:** `database/migrations/2024_01_01_000001_create_clients_table.php`

```php
Schema::create('clients', function (Blueprint $table) {
    $table->id();                                    // Primary key
    $table->string('name');                          // Client name
    $table->string('contact');                       // Contact email/phone
    $table->string('sender_id');                     // SMS sender ID (e.g., "PRADY_TECH")
    $table->decimal('balance', 10, 2)->default(0);   // Account balance in KSH
    $table->string('api_key')->unique();             // Unique API key for authentication
    $table->boolean('status')->default(true);        // Active/Inactive
    $table->json('settings')->nullable();            // Additional settings (JSON)
    $table->timestamps();                            // created_at, updated_at
});
```

### Complete Column List

Based on your `Client` model, the actual table has these columns:

| Column | Type | Description | Example |
|--------|------|-------------|---------|
| `id` | BIGINT | Primary key, auto-increment | 1, 2, 3... |
| `name` | VARCHAR(255) | Client/Company name | "Prady Technologies" |
| `contact` | VARCHAR(255) | Contact email or phone | "admin@prady.com" |
| `sender_id` | VARCHAR(255) | SMS sender name (brand) | "PRADY_TECH" |
| `company_name` | VARCHAR(255) | Company name (alias for sender_id) | "PRADY_TECH" |
| `balance` | DECIMAL(10,2) | Account balance in KSH | 100.00, 500.00 |
| `price_per_unit` | DECIMAL(10,4) | Cost per SMS unit | 1.0000, 0.7500 |
| `onfon_balance` | DECIMAL(10,2) | Onfon wallet balance (if using Onfon) | 250.00 |
| `onfon_last_sync` | DATETIME | Last time Onfon balance was synced | 2025-10-10 14:30:00 |
| `auto_sync_balance` | BOOLEAN | Auto-sync with Onfon wallet? | 0 or 1 |
| `api_key` | VARCHAR(255) | Unique API key (32 chars) | "abc123xyz789..." |
| `status` | BOOLEAN | Active (1) or Inactive (0) | 1 |
| `tier` | VARCHAR(50) | Service tier | "basic", "standard", "premium" |
| `is_test_mode` | BOOLEAN | Test mode flag | 0 or 1 |
| `settings` | JSON | Additional settings | `{"timezone": "Africa/Nairobi"}` |
| `webhook_url` | VARCHAR(255) | Webhook URL for callbacks | "https://example.com/webhook" |
| `webhook_secret` | VARCHAR(255) | Webhook authentication secret | "secret_key_123" |
| `webhook_events` | JSON | Events to send to webhook | `["sms.sent", "sms.delivered"]` |
| `webhook_active` | BOOLEAN | Webhook enabled? | 0 or 1 |
| `created_at` | TIMESTAMP | When client was created | 2025-01-15 10:30:00 |
| `updated_at` | TIMESTAMP | Last updated | 2025-10-10 14:30:00 |

---

## 💾 Example Data

### Sample Tenants in Database

```sql
+----+-------------------+------------------+--------------+----------+----------------+--------+----------+
| id | name              | contact          | sender_id    | balance  | api_key        | status | tier     |
+----+-------------------+------------------+--------------+----------+----------------+--------+----------+
| 1  | Prady Tech        | admin@prady.com  | PRADY_TECH   | 1000.00  | abc123xyz789   | 1      | standard |
| 2  | Test Sender       | test@sender.com  | TEST_SENDER  | 50.00    | def456uvw123   | 1      | basic    |
| 3  | Fortress Ltd      | info@fortress.ke | FORTRESS     | 5000.00  | ghi789rst456   | 1      | premium  |
| 4  | Logic Link        | hello@logic.com  | LOGIC-LINK   | 200.00   | jkl012mno789   | 0      | standard |
+----+-------------------+------------------+--------------+----------+----------------+--------+----------+
```

### Detailed Single Record

```json
{
  "id": 1,
  "name": "Prady Technologies",
  "contact": "admin@pradytech.com",
  "sender_id": "PRADY_TECH",
  "company_name": "PRADY_TECH",
  "balance": 1000.00,
  "price_per_unit": 1.0000,
  "onfon_balance": 1500.00,
  "onfon_last_sync": "2025-10-10 14:30:00",
  "auto_sync_balance": true,
  "api_key": "abc123xyz789pradytech12345678",
  "status": true,
  "tier": "standard",
  "is_test_mode": false,
  "settings": {
    "timezone": "Africa/Nairobi",
    "language": "en",
    "notifications": {
      "email": true,
      "sms": false
    }
  },
  "webhook_url": "https://prady.com/webhooks/sms",
  "webhook_secret": "prady_webhook_secret_123",
  "webhook_events": ["sms.sent", "sms.delivered", "sms.failed"],
  "webhook_active": true,
  "created_at": "2025-01-15 10:30:00",
  "updated_at": "2025-10-10 14:30:00"
}
```

---

## 🔗 Related Tables

Each tenant has related data in other tables:

### 1. **Users** (who can login)

**Table:** `users`
**Relationship:** One client has many users

```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    client_id BIGINT,  -- Links to clients.id
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(50),  -- 'admin' or 'user'
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);
```

**Example:**
```sql
+----+-----------+-------------------+----------------------+-------+
| id | client_id | name              | email                | role  |
+----+-----------+-------------------+----------------------+-------+
| 1  | 1         | John Doe          | john@pradytech.com   | admin |
| 2  | 1         | Jane Smith        | jane@pradytech.com   | user  |
| 3  | 2         | Test User         | test@sender.com      | admin |
+----+-----------+-------------------+----------------------+-------+
```

---

### 2. **Channels** (SMS/WhatsApp/Email configuration)

**Table:** `channels`
**Relationship:** One client has many channels

```sql
CREATE TABLE channels (
    id BIGINT PRIMARY KEY,
    client_id BIGINT,  -- Links to clients.id
    name VARCHAR(255),  -- 'sms', 'whatsapp', 'email'
    provider VARCHAR(255),  -- 'onfon', 'twilio', 'ultramsg'
    credentials JSON,  -- Provider API keys/secrets
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);
```

**Example:**
```sql
+----+-----------+----------+----------+-----------------------------------------------+--------+
| id | client_id | name     | provider | credentials                                   | active |
+----+-----------+----------+----------+-----------------------------------------------+--------+
| 1  | 1         | sms      | onfon    | {"api_key":"xxx","client_id":"yyy"}           | 1      |
| 2  | 1         | whatsapp | ultramsg | {"instance_id":"abc","token":"def"}           | 1      |
| 3  | 2         | sms      | onfon    | {"api_key":"zzz","client_id":"www"}           | 1      |
+----+-----------+----------+----------+-----------------------------------------------+--------+
```

---

### 3. **Messages** (all sent messages)

**Table:** `messages`
**Relationship:** One client has many messages

```sql
CREATE TABLE messages (
    id BIGINT PRIMARY KEY,
    client_id BIGINT,  -- Links to clients.id
    channel VARCHAR(255),  -- 'sms', 'whatsapp', 'email'
    recipient VARCHAR(255),
    body TEXT,
    status VARCHAR(50),  -- 'queued', 'sent', 'delivered', 'failed'
    cost DECIMAL(10,4),
    sent_at TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);
```

**Example:**
```sql
+----+-----------+---------+----------------+---------------+-----------+------+---------------------+
| id | client_id | channel | recipient      | body          | status    | cost | sent_at             |
+----+-----------+---------+----------------+---------------+-----------+------+---------------------+
| 1  | 1         | sms     | 254712345678   | Hello World   | delivered | 1.00 | 2025-10-10 10:00:00 |
| 2  | 1         | sms     | 254798765432   | Test msg      | sent      | 1.00 | 2025-10-10 10:05:00 |
| 3  | 2         | sms     | 254728883160   | Test          | delivered | 1.00 | 2025-10-10 10:10:00 |
+----+-----------+---------+----------------+---------------+-----------+------+---------------------+
```

---

### 4. **Contacts** (recipient address book)

**Table:** `contacts`
**Relationship:** One client has many contacts

```sql
CREATE TABLE contacts (
    id BIGINT PRIMARY KEY,
    client_id BIGINT,  -- Links to clients.id
    name VARCHAR(255),
    contact VARCHAR(255),  -- Phone number
    department VARCHAR(255),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);
```

---

### 5. **Campaigns** (bulk messaging campaigns)

**Table:** `campaigns`
**Relationship:** One client has many campaigns

```sql
CREATE TABLE campaigns (
    id BIGINT PRIMARY KEY,
    client_id BIGINT,  -- Links to clients.id
    name VARCHAR(255),
    message TEXT,
    sender_id VARCHAR(255),
    recipients JSON,
    status VARCHAR(50),  -- 'draft', 'scheduled', 'sending', 'completed'
    total_cost DECIMAL(10,2),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);
```

---

## 🔄 Database Relationships Diagram

```
┌─────────────┐
│   clients   │ (Tenants)
│  (Tenants)  │
└──────┬──────┘
       │
       ├─────────────┐
       │             │
       ▼             ▼
┌───────────┐  ┌───────────┐
│   users   │  │ channels  │
│  (Login)  │  │(SMS/WA/EM)│
└───────────┘  └───────────┘
       │
       │
       ├──────────────────┬──────────────┬──────────────┐
       │                  │              │              │
       ▼                  ▼              ▼              ▼
┌───────────┐      ┌───────────┐  ┌───────────┐  ┌───────────┐
│ messages  │      │ contacts  │  │ campaigns │  │ templates │
│(All sent) │      │(Recipients│  │(Bulk send)│  │(Msg tpls) │
└───────────┘      └───────────┘  └───────────┘  └───────────┘
```

**Key:** All tables have `client_id` foreign key that links to `clients.id`

---

## 🔐 Key Fields Explained

### 1. **`id` (Primary Key)**
- Unique identifier for each tenant
- Auto-incrementing integer
- Used in API URLs: `/api/{client_id}/...`
- Referenced by all related tables

### 2. **`sender_id`**
- The SMS sender name (brand identity)
- What recipients see as the sender
- Examples: "PRADY_TECH", "FORTRESS", "TEST_SENDER"
- Must be approved by SMS provider
- Typically all caps, no spaces

### 3. **`api_key`**
- 32-character random string
- **UNIQUE** across all tenants
- Used for API authentication
- Sent in request header: `X-API-Key: {api_key}`
- Cannot be duplicated

### 4. **`balance`**
- Current account balance in KSH (Kenya Shillings)
- Deducted when messages are sent
- Format: DECIMAL(10,2) → allows up to 99,999,999.99
- Example: 1000.00 = KSH 1,000

### 5. **`price_per_unit`**
- Cost per SMS unit
- Used to calculate available units: `balance / price_per_unit`
- Format: DECIMAL(10,4) → very precise pricing
- Example: 1.0000 = KSH 1 per SMS

### 6. **`status`**
- BOOLEAN (0 or 1)
- 1 = Active (can send messages)
- 0 = Inactive (API requests rejected)
- Used in authentication: `WHERE status = 1`

### 7. **`tier`**
- Service level: "basic", "standard", "premium", "enterprise"
- Determines rate limits
- Determines features available
- Can be upgraded/downgraded

### 8. **`settings` (JSON)**
- Flexible storage for additional configuration
- Example:
```json
{
  "timezone": "Africa/Nairobi",
  "language": "en",
  "notifications": {
    "email": true,
    "sms": false,
    "low_balance_alert": 100
  },
  "features": {
    "whatsapp": true,
    "email": false,
    "campaigns": true
  }
}
```

---

## 💡 How Tenant Isolation Works

### Database Level

Every query automatically filters by `client_id`:

```php
// When PRADY_TECH (Client 1) sends a request
$client = $request->user(); // Client with id = 1

// Get contacts - automatically filtered
$contacts = Contact::where('client_id', $client->id)->get();
// SELECT * FROM contacts WHERE client_id = 1

// Get messages - automatically filtered
$messages = Message::where('client_id', $client->id)->get();
// SELECT * FROM messages WHERE client_id = 1

// Get campaigns - automatically filtered
$campaigns = Campaign::where('client_id', $client->id)->get();
// SELECT * FROM campaigns WHERE client_id = 1
```

### Result

- Client 1 can ONLY see Client 1's data
- Client 2 can ONLY see Client 2's data
- **Zero possibility of cross-tenant data leakage!**

---

## 📝 SQL Examples

### 1. Get All Tenants

```sql
SELECT id, name, sender_id, balance, status, tier
FROM clients
ORDER BY id;
```

### 2. Get Active Tenants Only

```sql
SELECT id, name, sender_id, balance, api_key
FROM clients
WHERE status = 1;
```

### 3. Find Tenant by API Key

```sql
SELECT *
FROM clients
WHERE api_key = 'abc123xyz789'
  AND status = 1;
```

### 4. Get Tenant with All Related Data

```sql
-- Get tenant
SELECT * FROM clients WHERE id = 1;

-- Get their users
SELECT * FROM users WHERE client_id = 1;

-- Get their channels
SELECT * FROM channels WHERE client_id = 1;

-- Get their messages (last 10)
SELECT * FROM messages 
WHERE client_id = 1 
ORDER BY created_at DESC 
LIMIT 10;

-- Get their contacts
SELECT * FROM contacts WHERE client_id = 1;

-- Get their campaigns
SELECT * FROM campaigns WHERE client_id = 1;
```

### 5. Get Tenant Message Statistics

```sql
SELECT 
    c.id,
    c.name,
    c.sender_id,
    c.balance,
    COUNT(m.id) as total_messages,
    SUM(CASE WHEN m.status = 'delivered' THEN 1 ELSE 0 END) as delivered,
    SUM(CASE WHEN m.status = 'failed' THEN 1 ELSE 0 END) as failed,
    SUM(m.cost) as total_spent
FROM clients c
LEFT JOIN messages m ON m.client_id = c.id
WHERE c.id = 1
GROUP BY c.id;
```

---

## 🚀 Creating a New Tenant

### Using Laravel (Recommended)

```php
use App\Models\Client;
use App\Models\Channel;
use Illuminate\Support\Str;

// Create client
$client = Client::create([
    'name' => 'New Company Ltd',
    'contact' => 'admin@newcompany.com',
    'sender_id' => 'NEWCOMPANY',
    'company_name' => 'NEWCOMPANY',
    'balance' => 0,
    'price_per_unit' => 1.00,
    'api_key' => Str::random(32),  // Generates unique 32-char key
    'status' => true,
    'tier' => 'basic',
    'is_test_mode' => false,
]);

// Create SMS channel for the client
$channel = Channel::create([
    'client_id' => $client->id,
    'name' => 'sms',
    'provider' => 'onfon',
    'credentials' => json_encode([
        'api_key' => env('ONFON_API_KEY'),
        'client_id' => env('ONFON_CLIENT_ID'),
        'default_sender' => 'NEWCOMPANY',
    ]),
    'active' => true,
]);

// Create admin user for the client
$user = User::create([
    'name' => 'Admin User',
    'email' => 'admin@newcompany.com',
    'password' => Hash::make('password123'),
    'client_id' => $client->id,
    'role' => 'admin',
]);
```

### Using SQL (Direct)

```sql
-- Create client
INSERT INTO clients (
    name, contact, sender_id, balance, 
    api_key, status, tier, created_at, updated_at
) VALUES (
    'New Company Ltd',
    'admin@newcompany.com',
    'NEWCOMPANY',
    0,
    'generated_unique_api_key_32chars',
    1,
    'basic',
    NOW(),
    NOW()
);

-- Get the client ID (last insert)
SET @client_id = LAST_INSERT_ID();

-- Create SMS channel
INSERT INTO channels (
    client_id, name, provider, credentials, active, created_at, updated_at
) VALUES (
    @client_id,
    'sms',
    'onfon',
    '{"api_key":"xxx","client_id":"yyy"}',
    1,
    NOW(),
    NOW()
);
```

---

## 📊 Viewing Current Tenants

### Using PHP Script

```bash
php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

\$clients = App\Models\Client::all();
foreach(\$clients as \$c) {
    echo sprintf(
        'ID: %d | Name: %s | Sender: %s | Balance: KSH %.2f | Status: %s' . PHP_EOL,
        \$c->id,
        \$c->name,
        \$c->sender_id,
        \$c->balance,
        \$c->status ? 'Active' : 'Inactive'
    );
}
"
```

### Using Tinker

```bash
php artisan tinker

# Get all clients
>>> App\Models\Client::all();

# Get specific client
>>> App\Models\Client::find(1);

# Get client by sender ID
>>> App\Models\Client::where('sender_id', 'PRADY_TECH')->first();

# Get client by API key
>>> App\Models\Client::where('api_key', 'abc123xyz')->first();
```

---

## 🔍 Summary

### Tenant Storage Structure:

1. **Main Table:** `clients` - Stores all tenant information
2. **Related Tables:** All linked via `client_id` foreign key
3. **Isolation:** Each tenant's data completely separated
4. **Authentication:** Unique `api_key` per tenant
5. **Identification:** `id` + `api_key` combination

### Key Points:

✅ Each tenant = One row in `clients` table  
✅ Unique `api_key` for authentication  
✅ All tenant data linked via `client_id`  
✅ Complete data isolation between tenants  
✅ Soft relationships with CASCADE delete  
✅ JSON columns for flexible settings  

Your multi-tenant system is **solid and secure**! 🔐

