# 🗄️ DATABASE RELATIONSHIPS & SCHEMA REFERENCE

## Entity Relationship Diagram (Text Format)

```
┌─────────────────┐
│    CLIENTS      │ (Tenants/Companies)
│  (Multi-tenant) │
├─────────────────┤
│ id              │◄────┐
│ name            │     │
│ sender_id       │     │
│ balance (KSH)   │     │ One-to-Many
│ price_per_unit  │     │
│ api_key (unique)│     │
│ onfon_balance   │     │
│ settings (JSON) │     │
└─────────────────┘     │
                        │
        ┌───────────────┼───────────────┬───────────────┬──────────────┐
        │               │               │               │              │
        ▼               ▼               ▼               ▼              ▼
┌─────────────┐  ┌─────────────┐ ┌──────────────┐ ┌──────────┐  ┌──────────┐
│    USERS    │  │  CONTACTS   │ │  CAMPAIGNS   │ │ MESSAGES │  │ CHANNELS │
├─────────────┤  ├─────────────┤ ├──────────────┤ ├──────────┤  ├──────────┤
│ id          │  │ id          │ │ id           │ │ id       │  │ id       │
│ client_id   │  │ client_id   │ │ client_id    │ │ client_id│  │ client_id│
│ name        │  │ name        │ │ name         │ │ channel  │  │ name     │
│ email       │  │ contact     │ │ message      │ │ sender   │  │ provider │
│ role        │  │ department  │ │ channel      │ │ recipient│  │ active   │
└─────────────┘  └─────────────┘ │ recipients[] │ │ body     │  │ credentials
                        │         │ status       │ │ status   │  └──────────┘
                        │         │ sent_count   │ │ direction│
                        │         └──────────────┘ └──────────┘
                        │                                │
                        │         ┌──────────────────────┘
                        │         │ One-to-Many
                        ▼         ▼
                 ┌────────────────────┐
                 │  CONVERSATIONS     │
                 ├────────────────────┤
                 │ id                 │
                 │ client_id          │
                 │ contact_id (FK)    │
                 │ channel            │
                 │ status             │
                 │ unread_count       │
                 │ last_message_at    │
                 └────────────────────┘
                          │
                          │ One-to-Many
                          ▼
                 ┌────────────────────┐
                 │  MESSAGES          │
                 │ (conversation_id)  │
                 └────────────────────┘
```

---

## Table Details

### 🏢 CLIENTS (Core Multi-Tenant Table)

**Purpose:** Represents each tenant/company/sender in the system

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `name` | varchar | Client/company name |
| `contact` | varchar | Contact phone/email |
| `sender_id` | varchar | SMS sender ID (e.g., PRADY_TECH) |
| `company_name` | varchar | Full company name |
| `balance` | decimal(10,2) | Current balance in KSH |
| `price_per_unit` | decimal(8,4) | Cost per SMS unit (e.g., 0.75) |
| `onfon_balance` | decimal(10,2) | Onfon wallet balance (synced) |
| `onfon_last_sync` | timestamp | Last Onfon sync time |
| `auto_sync_balance` | boolean | Auto-sync Onfon balance |
| `api_key` | varchar(255) | Unique API authentication key |
| `status` | boolean | Active/inactive |
| `tier` | varchar | free/basic/premium/enterprise |
| `is_test_mode` | boolean | Test mode flag |
| `settings` | json | Additional settings (Onfon creds, etc.) |
| `webhook_url` | varchar | Client's webhook URL |
| `webhook_secret` | varchar | Webhook authentication secret |
| `webhook_events` | json | Events to notify ['message.sent', ...] |
| `webhook_active` | boolean | Webhook enabled flag |

**Key Relationships:**
- One client → Many users
- One client → Many contacts
- One client → Many campaigns
- One client → Many messages
- One client → Many channels

**Important Methods:**
```php
$client->getBalanceInUnits()              // balance / price_per_unit
$client->hasSufficientBalance($amount)    // Check if balance >= amount
$client->deductBalance($amount)           // Deduct from balance
$client->addBalance($amount)              // Add to balance
$client->unitsToKsh($units)               // Convert units to KSH
$client->kshToUnits($ksh)                 // Convert KSH to units
```

---

### 👤 USERS (System Users)

**Purpose:** Users who access the web dashboard

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `client_id` | int | Foreign key → clients.id |
| `name` | varchar | User's full name |
| `email` | varchar | Unique email (login) |
| `password` | varchar | Hashed password |
| `role` | varchar | admin / user |
| `avatar` | varchar | Profile picture path |
| `phone` | varchar | Phone number |
| `bio` | text | User bio |
| `timezone` | varchar | User timezone |
| `language` | varchar | Preferred language |
| `preferences` | json | UI preferences |

**Relationship:** `belongsTo(Client::class)`

**Role-based access:**
- `admin` → Full system access, manage all clients
- `user` → Limited to own client data

---

### 📇 CONTACTS (Contact Book)

**Purpose:** Store client's contact lists

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `client_id` | int | Foreign key → clients.id |
| `name` | varchar | Contact name |
| `contact` | varchar | Phone number or email |
| `department` | varchar | Department/group (for filtering) |
| `last_message_at` | timestamp | Last message sent/received |
| `total_messages` | int | Total message count |

**Relationship:** `belongsTo(Client::class)`

**Usage:**
- Import from CSV
- Select recipients for campaigns
- Filter by department
- Auto-created when inbound messages arrive

---

### 📢 CAMPAIGNS

**Purpose:** Bulk messaging campaigns

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `client_id` | int | Foreign key → clients.id |
| `name` | varchar | Campaign name |
| `message` | text | Message content |
| `sender_id` | varchar | Sender ID to use |
| `channel` | varchar | sms / whatsapp / email |
| `template_id` | int | Optional template reference |
| `recipients` | json | Array of phone numbers |
| `status` | varchar | draft / sending / sent / failed |
| `scheduled_at` | timestamp | Schedule for later |
| `sent_at` | timestamp | Actual send time |
| `total_recipients` | int | Count of recipients |
| `sent_count` | int | Successfully sent |
| `delivered_count` | int | Delivered count |
| `failed_count` | int | Failed count |
| `total_cost` | decimal(10,2) | Total cost in KSH |

**Relationships:**
- `belongsTo(Client::class)`
- `belongsTo(Template::class)`
- `hasMany(Sms::class)` - Legacy
- `hasMany(Message::class)` - Through campaign flow

**Statuses:**
- `draft` - Created but not sent
- `sending` - Currently being processed
- `sent` - Completed
- `scheduled` - Waiting for scheduled time
- `failed` - Failed to send

---

### 💬 MESSAGES (Unified Messaging)

**Purpose:** All messages (SMS, WhatsApp, Email) in one table

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `client_id` | int | Foreign key → clients.id |
| `template_id` | int | Optional template used |
| `conversation_id` | int | FK → conversations.id |
| `channel` | varchar | sms / whatsapp / email |
| `direction` | varchar | inbound / outbound |
| `provider` | varchar | onfon / ultramsg / twilio |
| `sender` | varchar | Sender ID or number |
| `recipient` | varchar | Recipient phone/email |
| `subject` | varchar | Email subject (if applicable) |
| `body` | text | Message content |
| `status` | varchar | sending / sent / delivered / failed |
| `provider_message_id` | varchar | Provider's message ID |
| `cost` | decimal(8,4) | Cost for this message |
| `metadata` | json | Additional data |
| `error_message` | text | Error details if failed |
| `is_read` | boolean | Read status (for inbox) |
| `sent_at` | timestamp | When sent |
| `delivered_at` | timestamp | When delivered |
| `failed_at` | timestamp | When failed |

**Relationships:**
- `belongsTo(Client::class)`
- `belongsTo(Template::class)`
- `belongsTo(Conversation::class)`

**Statuses:**
- `sending` - Being sent to provider
- `sent` - Sent to provider
- `delivered` - Confirmed delivered
- `failed` - Failed to send
- `pending` - Queued for sending

**Direction:**
- `outbound` - Sent by client
- `inbound` - Received from customer

---

### 💬 CONVERSATIONS (CRM/Inbox)

**Purpose:** Group messages by contact for chat-like interface

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `client_id` | int | Foreign key → clients.id |
| `contact_id` | int | Foreign key → contacts.id |
| `contact_identifier` | varchar | Phone/email of contact |
| `channel` | varchar | sms / whatsapp / email |
| `status` | varchar | open / closed / archived |
| `unread_count` | int | Unread messages count |
| `last_message_preview` | text | Preview of last message |
| `last_message_direction` | varchar | inbound / outbound |
| `last_message_at` | timestamp | Time of last message |

**Relationships:**
- `belongsTo(Client::class)`
- `belongsTo(Contact::class)`
- `hasMany(Message::class)`

**Usage:**
- Inbox/CRM view
- Reply to customer messages
- Track conversation status
- Unread message count

---

### 🔧 CHANNELS (Provider Configurations)

**Purpose:** Store provider credentials per client per channel

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `client_id` | int | Foreign key → clients.id |
| `name` | varchar | sms / whatsapp / email |
| `provider` | varchar | onfon / ultramsg / twilio / smtp |
| `credentials` | json | API keys, secrets (encrypted) |
| `active` | boolean | Channel enabled |
| `config` | json | Additional configuration |

**Relationships:**
- `belongsTo(Client::class)`

**Example credentials:**
```json
{
  "api_key": "xxx",
  "client_id": "yyy",
  "sender_id": "PRADY_TECH"
}
```

**Providers:**
- SMS: `onfon`, `twilio`, `mobitech`
- WhatsApp: `ultramsg`, `whatsapp_cloud`
- Email: `smtp`

---

### 📄 TEMPLATES

**Purpose:** Reusable message templates

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `client_id` | int | Foreign key → clients.id |
| `name` | varchar | Template name |
| `content` | text | Template content |
| `channel` | varchar | sms / whatsapp / email |
| `variables` | json | Placeholder variables |
| `whatsapp_template_id` | varchar | WhatsApp template ID |

**Relationships:**
- `belongsTo(Client::class)`
- `hasMany(Campaign::class)`
- `hasMany(Message::class)`

**Variables support:**
```
Example: "Hello {{name}}, your order {{order_id}} is ready"
Variables: ["name", "order_id"]
```

---

### 💰 WALLET_TRANSACTIONS

**Purpose:** Track all balance changes

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `client_id` | int | Foreign key → clients.id |
| `type` | varchar | topup / deduction / refund |
| `amount` | decimal(10,2) | Transaction amount |
| `balance_before` | decimal(10,2) | Balance before transaction |
| `balance_after` | decimal(10,2) | Balance after transaction |
| `reference` | varchar | Transaction reference |
| `mpesa_reference` | varchar | M-Pesa transaction code |
| `description` | text | Transaction description |
| `status` | varchar | pending / completed / failed |
| `metadata` | json | Additional details |

**Relationships:**
- `belongsTo(Client::class)`

**Types:**
- `topup` - M-Pesa payment received
- `deduction` - SMS sent (balance reduced)
- `refund` - Failed SMS refund
- `adjustment` - Manual admin adjustment

---

### 🔗 SHORT_LINKS (URL Shortener)

**Purpose:** Generate short reply links for SMS

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `message_id` | int | Foreign key → messages.id |
| `code` | varchar(4) | Unique 4-char code |
| `original_url` | text | Full URL |
| `clicks` | int | Click count |
| `last_clicked_at` | timestamp | Last click time |

**Relationships:**
- `belongsTo(Message::class)`

**Format:** `http://domain/x/{code}`  
**Example:** `http://domain/x/aB3k` → Redirects to reply form

**Usage:**
- Appended to every outbound SMS
- Allows recipients to reply via web
- Tracks engagement (clicks)

---

### 🔔 NOTIFICATIONS

**Purpose:** System notifications for users

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `client_id` | int | Foreign key → clients.id |
| `user_id` | int | Foreign key → users.id |
| `type` | varchar | low_balance / message_failed / etc. |
| `title` | varchar | Notification title |
| `message` | text | Notification body |
| `data` | json | Additional data |
| `is_read` | boolean | Read status |
| `read_at` | timestamp | When marked read |

**Types:**
- `low_balance` - Balance below threshold
- `message_failed` - Message delivery failed
- `campaign_completed` - Campaign sent
- `topup_success` - Wallet top-up successful
- `inbound_message` - New inbound message

---

## 🔍 Common Queries

### Get Client with Balance in Units
```php
$client = Client::find(1);
$unitsAvailable = $client->getBalanceInUnits();
// Returns: balance / price_per_unit
```

### Get All Messages for a Campaign
```php
$campaign = Campaign::with('sms')->find(1);
$messages = $campaign->sms; // Legacy
// Or using messages table:
$messages = Message::where('metadata->campaign_id', $campaign->id)->get();
```

### Get Conversation with Messages
```php
$conversation = Conversation::with(['contact', 'messages'])
    ->where('client_id', $clientId)
    ->where('id', $conversationId)
    ->first();

$messages = $conversation->messages()->orderBy('created_at', 'desc')->get();
```

### Get Client's Active Channels
```php
$channels = Channel::where('client_id', $clientId)
    ->where('active', true)
    ->get();

$smsChannel = $channels->where('name', 'sms')->first();
$credentials = $smsChannel->credentials; // Array
```

### Calculate Total Cost for Recipients
```php
$client = Client::find(1);
$recipientCount = 150;
$costPerUnit = $client->price_per_unit;
$totalCost = $recipientCount * $costPerUnit;

if ($client->hasSufficientBalance($totalCost)) {
    // Proceed with sending
}
```

### Get Recent Transactions
```php
$transactions = WalletTransaction::where('client_id', $clientId)
    ->where('status', 'completed')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

### Find Contact and Start Conversation
```php
$contact = Contact::firstOrCreate(
    ['client_id' => $clientId, 'contact' => '+254712345678'],
    ['name' => 'John Doe']
);

$conversation = Conversation::firstOrCreate([
    'client_id' => $clientId,
    'contact_id' => $contact->id,
    'channel' => 'sms'
]);
```

---

## 🎯 Indexes & Performance

### Recommended Indexes

**clients:**
- `api_key` (unique)
- `status`

**messages:**
- `client_id, created_at` (composite)
- `conversation_id`
- `provider_message_id`
- `status`
- `direction`

**campaigns:**
- `client_id, status` (composite)
- `scheduled_at`

**conversations:**
- `client_id, status` (composite)
- `contact_id`
- `last_message_at`

**contacts:**
- `client_id, contact` (composite, unique)
- `department`

---

## 📊 Data Flow Examples

### Outbound SMS Flow
```
1. User creates campaign → campaigns table (status: draft)
2. User sends campaign → Loop through recipients
3. For each recipient:
   - Create message → messages table (status: sending)
   - Create short_link → short_links table
   - Send to Onfon API
   - Update message (status: sent)
   - Deduct balance → wallet_transactions table
   - Update client.balance
4. Update campaign (status: sent, sent_count, total_cost)
5. Create conversation if doesn't exist
```

### Inbound SMS Flow
```
1. Onfon sends webhook POST → /webhooks/onfon/inbound
2. Identify client by sender_id
3. Create message → messages table (direction: inbound)
4. Find or create contact → contacts table
5. Find or create conversation → conversations table
6. Increment conversation.unread_count
7. Create notification → notifications table
8. User sees in inbox
```

### Balance Top-up Flow
```
1. User initiates M-Pesa payment
2. Create transaction → wallet_transactions (status: pending)
3. M-Pesa sends STK push to user's phone
4. User confirms payment
5. M-Pesa sends callback → /webhooks/mpesa/callback
6. Update transaction (status: completed)
7. Add to client.balance
8. Create notification (topup_success)
```

---

**Last Updated:** October 13, 2025


