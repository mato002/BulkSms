# 🏗️ BULK SMS LARAVEL - COMPLETE SYSTEM ARCHITECTURE

**Date Generated:** October 13, 2025  
**Purpose:** Complete system overview for new developers taking over the project

---

## 📋 TABLE OF CONTENTS

1. [System Overview](#system-overview)
2. [Core Architecture](#core-architecture)
3. [Multi-Tenant System](#multi-tenant-system)
4. [Messaging Flow](#messaging-flow)
5. [API Architecture](#api-architecture)
6. [Database Schema](#database-schema)
7. [External Integrations](#external-integrations)
8. [Authentication & Authorization](#authentication--authorization)
9. [Frontend Architecture](#frontend-architecture)
10. [Key Design Patterns](#key-design-patterns)

---

## 🎯 SYSTEM OVERVIEW

### What This System Does
A **multi-tenant bulk messaging platform** that enables clients to send SMS, WhatsApp, and Email messages through various providers. It includes:
- Multi-channel messaging (SMS, WhatsApp, Email)
- Campaign management
- Contact/CRM management
- Wallet & top-up system (M-Pesa integration)
- API for programmatic access
- Real-time inbox/conversations
- Analytics & reporting

### Technology Stack
- **Framework:** Laravel 10.x
- **PHP:** 8.3+
- **Database:** MySQL
- **Frontend:** Blade templates + Vanilla JavaScript
- **APIs:** RESTful API with custom authentication

---

## 🏛️ CORE ARCHITECTURE

### Application Structure

```
app/
├── Console/Commands/          # Artisan commands (cron jobs, utilities)
├── Http/
│   ├── Controllers/          # Web controllers
│   │   └── Api/             # API-specific controllers
│   ├── Middleware/          # Authentication, rate limiting, etc.
│   └── Kernel.php
├── Models/                   # Eloquent models
├── Services/                # Business logic layer
│   ├── Messaging/          # Core messaging system
│   │   ├── Contracts/      # Interfaces
│   │   ├── Drivers/        # SMS/WhatsApp/Email drivers
│   │   └── DTO/            # Data Transfer Objects
│   ├── SmsService.php      # Legacy SMS service
│   ├── OnfonWalletService.php
│   ├── MpesaService.php
│   └── UrlShortenerService.php
├── Jobs/                    # Queue jobs
└── Mail/                    # Mail classes
```

### Key Design Principles

1. **NO CODE DUPLICATION**: Uses service layer pattern
2. **Multi-tenant isolation**: All queries scoped by `client_id`
3. **Provider abstraction**: Unified interface for different SMS/WhatsApp providers
4. **Event-driven**: Jobs and webhooks for async processing

---

## 🏢 MULTI-TENANT SYSTEM

### How Multi-Tenancy Works

**Core Entity:** `Client` (represents a tenant/company)

```php
// Each client has:
- Unique API key
- Own balance (in KSH)
- Own pricing per unit (price_per_unit)
- Own sender ID
- Own contacts, campaigns, messages
- Optional Onfon wallet integration
```

### Tenant Identification

**Web Application:**
- Session-based: `session('client_id')` 
- Set during login from `users.client_id`

**API Requests:**
- Header: `X-API-Key: {client_api_key}`
- URL parameter: `api_key={client_api_key}`
- Route-based: `/api/{company_id}/...`

### Middleware Stack

1. **`api.auth`** - Validates API key, sets authenticated client
2. **`company.auth`** - Ensures client can only access their own data
3. **`tier.rate.limit`** - Rate limiting based on client tier
4. **`admin`** - Admin-only routes

---

## 📨 MESSAGING FLOW

### The Unified Messaging System

**Architecture Pattern:** Strategy Pattern with Service Container

```
Request → MessageDispatcher → MessageSender (interface) → Provider Driver → External API
```

### Flow Breakdown

#### 1. Entry Points

**Web UI:**
```
CampaignController@send() → Creates OutboundMessage → MessageDispatcher
```

**API:**
```
POST /api/{company_id}/messages/send → MessageController → MessageDispatcher
```

#### 2. Message Dispatcher (`MessageDispatcher.php`)

**Responsibilities:**
- Loads client's channel configuration
- Resolves appropriate driver (OnfonSmsSender, UltraMessageSender, etc.)
- Creates Message record (status: 'sending')
- Generates reply link (for SMS)
- Dispatches to provider
- Updates message status (sent/failed)
- Creates/updates conversation
- Auto-creates contact if needed

#### 3. Provider Drivers

**SMS Providers:**
- `OnfonSmsSender` - Onfon Media API
- `TwilioSmsSender` - Twilio API

**WhatsApp Providers:**
- `UltraMessageSender` - UltraMsg API
- `CloudWhatsAppSender` - Meta WhatsApp Cloud API

**Email Providers:**
- `SmtpEmailSender` - Standard SMTP

### Channel Configuration

**Stored in:** `channels` table

```php
[
    'client_id' => 1,
    'name' => 'sms',
    'provider' => 'onfon',
    'active' => true,
    'credentials' => [
        'api_key' => '...',
        'client_id' => '...',
        'sender_id' => 'PRADY_TECH'
    ]
]
```

### Balance & Pricing Logic

**Units vs KSH System:**
```php
// Client has:
balance = 1000 KSH
price_per_unit = 0.75 KSH

// Available units:
units = balance / price_per_unit = 1333 units
```

**Deduction Flow:**
1. Check sufficient balance: `client->hasSufficientBalance(cost)`
2. Send message
3. Deduct: `client->deductBalance(cost)`
4. Save transaction

---

## 🔌 API ARCHITECTURE

### Authentication Flow

```
1. Client receives API key (generated via settings or admin panel)
2. Request includes: X-API-Key: {api_key}
3. ApiAuth middleware validates & loads client
4. CompanyAuth middleware ensures URL company_id matches authenticated client
5. Request proceeds
```

### API Endpoints Structure

**Base URL:** `/api`

#### SMS Endpoints
```
POST   /api/{company_id}/sms/send
GET    /api/{company_id}/sms/status/{id}
GET    /api/{company_id}/sms/history
GET    /api/{company_id}/sms/statistics
```

#### Unified Messaging
```
POST   /api/{company_id}/messages/send
```

**Request Format:**
```json
{
    "client_id": 1,
    "channel": "sms",
    "recipient": "254712345678",
    "sender": "PRADY_TECH",
    "body": "Your message here",
    "metadata": {}
}
```

#### Contact Management
```
GET    /api/{company_id}/contacts
POST   /api/{company_id}/contacts
PUT    /api/{company_id}/contacts/{id}
DELETE /api/{company_id}/contacts/{id}
POST   /api/{company_id}/contacts/bulk-import
```

#### Campaign Management
```
GET    /api/{company_id}/campaigns
POST   /api/{company_id}/campaigns
POST   /api/{company_id}/campaigns/{id}/send
GET    /api/{company_id}/campaigns/{id}/statistics
```

#### Wallet & Balance
```
GET    /api/{company_id}/wallet/balance
POST   /api/{company_id}/wallet/sync
POST   /api/{company_id}/wallet/topup
GET    /api/{company_id}/wallet/transactions
```

#### Analytics
```
GET    /api/{company_id}/analytics/summary
GET    /api/{company_id}/analytics/daily
GET    /api/{company_id}/analytics/by-channel
```

### Rate Limiting

**Tier-based rate limiting:**
- Free tier: 10 requests/minute
- Basic tier: 60 requests/minute
- Premium tier: 300 requests/minute
- Enterprise tier: Unlimited

**Implementation:** `TierBasedRateLimit` middleware

---

## 🗄️ DATABASE SCHEMA

### Core Tables

#### `clients` (Tenants/Companies)
```
id, name, contact, sender_id, company_name
balance (KSH), price_per_unit (KSH per SMS)
onfon_balance, onfon_last_sync
api_key (unique), status, tier
settings (JSON), webhook_url, webhook_secret
```

#### `users` (System users)
```
id, name, email, password
client_id (FK → clients)
role (admin/user)
avatar, phone, bio, timezone, preferences (JSON)
```

#### `contacts` (Client's contact list)
```
id, client_id (FK), name, contact, department
last_message_at, total_messages
```

#### `messages` (All messages - SMS/WhatsApp/Email)
```
id, client_id (FK), template_id (FK)
channel (sms/whatsapp/email)
direction (inbound/outbound)
provider (onfon/ultramsg/twilio)
sender, recipient, subject, body
status (sending/sent/delivered/failed)
provider_message_id, conversation_id
metadata (JSON), cost
sent_at, delivered_at, failed_at
```

#### `campaigns`
```
id, client_id (FK), name, message, sender_id
channel (sms/whatsapp)
recipients (JSON array), status
total_recipients, sent_count, delivered_count, failed_count
total_cost, scheduled_at, sent_at
```

#### `conversations` (CRM/Inbox)
```
id, client_id (FK), contact_id (FK)
contact_identifier (phone/email)
channel, status (open/closed/archived)
unread_count, last_message_preview
last_message_direction, last_message_at
```

#### `channels` (Provider configurations per client)
```
id, client_id (FK), name (sms/whatsapp/email)
provider (onfon/ultramsg/twilio)
credentials (JSON - encrypted), active
```

#### `wallet_transactions`
```
id, client_id (FK), type (topup/deduction)
amount, balance_before, balance_after
reference, mpesa_reference, description, status
```

#### `short_links` (URL shortener for SMS reply links)
```
id, message_id (FK), code (4-char unique), original_url
clicks, last_clicked_at
```

### Legacy Tables

#### `sms` (Legacy - being phased out for `messages`)
```
id, client_id, campaign_id
recipient, message, sender_id
status, message_id, cost
sent_at, delivered_at
```

---

## 🔗 EXTERNAL INTEGRATIONS

### 1. Onfon Media (Primary SMS Provider)

**Base URL:** `https://api.onfonmedia.co.ke/v1`

**Endpoints Used:**
- `/sms/SendBulkSMS` - Send messages
- `/balance/GetBalance` - Check wallet balance
- `/reports/GetTransactionHistory` - Transaction history

**Authentication:**
```php
Headers: [
    'AccessKey' => '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
    'Content-Type' => 'application/json'
]
Body: [
    'ApiKey' => client_specific_api_key,
    'ClientId' => client_specific_id
]
```

**Features:**
- Wallet-based system (clients have balance at Onfon)
- Auto-sync balance feature
- Support for 27+ sender IDs

**Service Class:** `OnfonWalletService.php`

### 2. UltraMsg (WhatsApp Provider)

**Configuration:** `config/services.php`
```php
'whatsapp' => [
    'provider' => 'ultramsg',
    'ultramsg' => [
        'instance_id' => env('ULTRAMSG_INSTANCE_ID'),
        'token' => env('ULTRAMSG_TOKEN'),
    ]
]
```

**Driver:** `UltraMessageSender.php`

### 3. M-Pesa (Payment Integration)

**Config:** `config/mpesa.php`

**Endpoints:**
- STK Push for wallet top-ups
- Callback webhook handling
- Transaction status queries

**Environment:** Sandbox/Production switchable

**Webhooks:**
- `/api/webhooks/mpesa/callback`
- `/api/webhooks/mpesa/timeout`

**Service Class:** `MpesaService.php`

### 4. Mobitech Technologies (Alternative SMS)

**URL:** `http://bulksms.mobitechtechnologies.com/api/sendsms`

**Legacy SMS gateway for specific senders**

### 5. MojaSMS (Alternative SMS)

**URL:** `https://prady-api-p1.mojasms.dev/api/campaign`

**Specific sender IDs:** PIXEL_LTD, NJORO CLUB, MWEGUNI, NJORODAYSEC

---

## 🔐 AUTHENTICATION & AUTHORIZATION

### Web Authentication (Session-based)

**Login Flow:**
```
1. User submits email + password → AuthController@login
2. Auth::attempt($credentials) validates
3. Session created with user ID
4. session('client_id') set from user->client_id
5. Redirect to dashboard
```

**Routes:**
- `/login` - Login page
- `/register` - Registration
- `/forgot-password` - Password reset (EmailJS integration)
- `/logout` - Logout

### API Authentication (API Key)

**Middleware:** `ApiAuth`

**Process:**
```
1. Extract API key from header or query param
2. Find client by API key
3. Check client status is active
4. Set client as authenticated user
5. Continue request
```

### Authorization Levels

**Roles:**
1. **Admin** - Full system access, can manage all clients
2. **User** - Limited to their own client data

**Admin Routes:**
- `/admin/senders/*` - Manage all tenants/clients
- Protected by `admin` middleware

---

## 🎨 FRONTEND ARCHITECTURE

### Technology Stack

**No JS Framework:** Pure Blade templates + vanilla JavaScript

**Structure:**
```
resources/views/
├── layouts/
│   └── app.blade.php           # Main layout
├── auth/                       # Login, register, password reset
├── dashboard/                  # Dashboard views
├── campaigns/                  # Campaign CRUD
├── contacts/                   # Contact management
├── inbox/                      # CRM/conversation views
├── messages/                   # Message history
├── settings/                   # Client settings
├── wallet/                     # Wallet & top-up
├── whatsapp/                   # WhatsApp management
└── admin/                      # Admin tenant management
```

### Key Features

**Dashboard:**
- Balance overview (units + KSH)
- Onfon balance sync
- Recent messages
- Quick stats
- Quick send form

**Campaign Management:**
- Multi-channel support (SMS/WhatsApp)
- Contact selection by department
- Template integration
- Bulk sending
- Real-time status

**Inbox/CRM:**
- Conversation view
- Contact management
- Reply functionality
- Status tracking (open/closed/archived)
- Unread count

**Settings:**
- Channel configuration (SMS, WhatsApp, Email)
- API key management
- Webhook configuration

**Admin Panel:**
- Manage all clients/tenants
- Update balances
- Configure Onfon credentials
- View client statistics

### JavaScript Patterns

**No build process** - Direct `<script>` tags

**Common patterns:**
```javascript
// Fetch API for AJAX
fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify(data)
})

// Inline event handlers
onclick="handleAction()"

// Vanilla DOM manipulation
document.getElementById('element').innerHTML = ''
```

---

## 🔧 KEY DESIGN PATTERNS

### 1. Service Layer Pattern

**Purpose:** Separate business logic from controllers

**Example:**
```
Controller → Service → Model → Database
```

**Services:**
- `SmsService` - SMS sending logic
- `OnfonWalletService` - Onfon API integration
- `MpesaService` - M-Pesa integration
- `MessageDispatcher` - Unified messaging
- `UrlShortenerService` - Short link generation
- `WebhookService` - Webhook dispatching

### 2. Strategy Pattern (Message Drivers)

**Interface:** `MessageSender` (Contract)

**Implementations:**
- `OnfonSmsSender`
- `TwilioSmsSender`
- `UltraMessageSender`
- `CloudWhatsAppSender`
- `SmtpEmailSender`

**Benefits:**
- Easy to add new providers
- No changes to core dispatch logic
- Provider-specific credentials isolated

### 3. Repository Pattern (Light)

**Used in:** Model scopes

```php
// Example: Campaign model
public function scopeForClient($query, $clientId)
{
    return $query->where('client_id', $clientId);
}
```

### 4. Data Transfer Objects (DTO)

**Class:** `OutboundMessage`

**Purpose:** Type-safe data passing between layers

```php
new OutboundMessage(
    clientId: 1,
    channel: 'sms',
    recipient: '254712345678',
    sender: 'PRADY_TECH',
    body: 'Message content'
);
```

### 5. Job Queue Pattern

**Jobs:**
- `SendMessageJob` - Async message sending
- `SendWebhookJob` - Async webhook delivery

**Benefits:**
- Resilience (retry on failure)
- Better performance
- Background processing

### 6. Webhook Pattern

**Inbound webhooks:**
- `/webhooks/onfon/inbound` - Incoming SMS
- `/webhooks/onfon/dlr` - Delivery reports
- `/webhooks/whatsapp` - WhatsApp events
- `/webhooks/mpesa/callback` - Payment notifications

**Outbound webhooks:**
- Clients can configure webhook URLs
- Events: message.sent, message.delivered, message.failed, etc.

---

## 🎯 CRITICAL WORKFLOWS

### Sending an SMS via API

```
1. Client makes POST /api/{company_id}/messages/send
2. ApiAuth middleware validates API key
3. CompanyAuth ensures company_id matches
4. MessageController validates request
5. Creates OutboundMessage DTO
6. MessageDispatcher:
   a. Loads channel config from database
   b. Resolves OnfonSmsSender
   c. Creates Message record (status: sending)
   d. Generates short reply link
   e. Calls Onfon API
   f. Updates message status (sent/failed)
   g. Creates/updates conversation
7. Returns response with message ID
```

### Campaign Send Flow

```
1. User creates campaign (draft)
2. Selects recipients (contacts or manual)
3. Chooses channel (SMS/WhatsApp)
4. Clicks "Send"
5. CampaignController@send():
   a. Loops through recipients
   b. For each: creates OutboundMessage
   c. Dispatches via MessageDispatcher
   d. Counts sent/failed
6. Updates campaign status to 'sent'
7. Redirects with success message
```

### Onfon Balance Sync

```
1. User clicks "Sync Balance" on dashboard
2. WalletController@syncOnfonBalance()
3. OnfonWalletService:
   a. Gets client's Onfon credentials from settings
   b. Calls Onfon GetBalance API
   c. Compares with stored balance
   d. Updates client.onfon_balance
   e. Logs difference
4. Returns updated balance to UI
```

### Inbound SMS Handling

```
1. Onfon sends POST to /webhooks/onfon/inbound
2. WebhookController@onfonInbound():
   a. Validates webhook signature (if configured)
   b. Identifies client by sender_id
   c. Creates Message (direction: inbound)
   d. Creates/updates Conversation
   e. Increments unread_count
   f. Creates notification
3. User sees in Inbox
```

---

## 📊 CONFIGURATION FILES

### Environment Variables (.env)

**Database:**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bulk_sms_laravel
DB_USERNAME=root
DB_PASSWORD=
```

**SMS Gateways:**
```
SMS_DEFAULT_GATEWAY=onfon
ONFON_API_KEY=...
ONFON_CLIENT_ID=...
```

**WhatsApp:**
```
WHATSAPP_PROVIDER=ultramsg
ULTRAMSG_INSTANCE_ID=...
ULTRAMSG_TOKEN=...
```

**M-Pesa:**
```
MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=...
MPESA_CONSUMER_SECRET=...
MPESA_SHORTCODE=174379
MPESA_CALLBACK_URL=...
```

### Config Files

**`config/sms.php`** - SMS gateway configurations
- Multiple gateways (Onfon, Mobitech, Moja)
- Sender ID lists
- Subsidized rates per client
- Allowed duplicates

**`config/services.php`** - Third-party services
- WhatsApp (UltraMsg, Cloud API)
- Slack notifications

**`config/mpesa.php`** - M-Pesa settings
- Environment switching
- API credentials
- Webhook URLs

---

## 🚀 DEPLOYMENT NOTES

### Requirements
- PHP 8.3+
- MySQL 5.7+
- Composer
- XAMPP (for local) or Apache/Nginx (for production)

### Setup Steps
```bash
1. Clone repository
2. Copy .env.example to .env
3. Configure database settings
4. Run: composer install
5. Run: php artisan key:generate
6. Run: php artisan migrate
7. Run: php artisan db:seed (if seeders exist)
8. Configure SMS/WhatsApp credentials in .env
9. Set up cron jobs for scheduled commands
```

### Cron Jobs (Scheduled Commands)

**In `app/Console/Kernel.php`:**
- `CheckSmsDelivery` - Update delivery statuses
- `ProcessScheduledSms` - Send scheduled messages
- `SyncOnfonBalances` - Auto-sync Onfon balances
- `CheckLowBalances` - Send low balance alerts
- `CleanupSmsLogs` - Archive old logs

---

## 🔍 IMPORTANT NOTES

### Subsidized Rates

**Config:** `config/sms.php`

Specific clients get discounted SMS rates:
```php
'subsidized_rates' => [
    7 => 0.7,   // Client ID 7 pays 0.7 KSH per SMS
    19 => 0.65, // Client ID 19 pays 0.65 KSH per SMS
    ...
]
```

### Allowed Duplicates

Some clients can send duplicate messages (bypassing duplicate detection):
```php
'allow_duplicates' => [23, 27, 30, 31, 35, 7]
```

### URL Shortener

**Purpose:** Generate short reply links for SMS

**Format:** `http://domain/x/{4-char-code}`

**Example:** `http://domain/x/aB3k`

**Redirects to:** Public reply form where recipient can respond

---

## 📝 MAINTENANCE GUIDE

### Adding a New SMS Provider

1. Create driver class: `app/Services/Messaging/Drivers/Sms/NewProviderSender.php`
2. Implement `MessageSender` interface
3. Add provider method `provider(): string`
4. Implement `send(OutboundMessage $message): string`
5. Register in service container
6. Update channel configuration UI

### Adding a New Client/Tenant

**Via Admin Panel:**
1. Login as admin
2. Go to `/admin/senders`
3. Click "Create New Sender"
4. Fill details (name, sender_id, balance, price_per_unit)
5. System generates API key
6. Configure channels (SMS/WhatsApp/Email)

**Via Database Seeder:**
```php
Client::create([
    'name' => 'Company Name',
    'sender_id' => 'COMPANY',
    'balance' => 1000.00,
    'price_per_unit' => 0.75,
    'api_key' => Str::random(32),
    'status' => true,
]);
```

### Troubleshooting Common Issues

**Issue:** "Insufficient balance"
- Check `clients.balance` in database
- Ensure `price_per_unit` is set correctly
- Verify balance calculation: `balance / price_per_unit`

**Issue:** "Invalid API key"
- Verify API key in `clients.api_key`
- Check client status is active (`status = 1`)

**Issue:** "SMS not sending"
- Check Laravel logs: `storage/logs/laravel.log`
- Verify provider credentials in channel config
- Test provider connection (Settings page)

**Issue:** "Onfon balance not syncing"
- Check Onfon credentials in client settings
- Test connection in admin panel
- Verify network access to Onfon API

---

## 🎓 LEARNING RESOURCES

### Understanding the Flow

**Start here:**
1. Read `routes/web.php` and `routes/api.php` - Understand endpoints
2. Check `app/Models/` - Understand data structure
3. Review `app/Services/Messaging/MessageDispatcher.php` - Core messaging logic
4. Look at `app/Http/Controllers/CampaignController.php` - See how campaigns work
5. Examine `app/Services/OnfonWalletService.php` - External API integration

### Testing the System

**Local Test:**
```php
// In Tinker (php artisan tinker)
$client = Client::find(1);
$client->balance; // Check balance
$client->getBalanceInUnits(); // Units available
```

**API Test:**
```bash
curl -X POST http://localhost/api/1/messages/send \
  -H "X-API-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "channel": "sms",
    "recipient": "254712345678",
    "sender": "PRADY_TECH",
    "body": "Test message"
  }'
```

---

## ✅ CONCLUSION

This system is a **production-ready, multi-tenant bulk messaging platform** with:
- Clean separation of concerns (Service Layer)
- Provider abstraction (Strategy Pattern)
- Multi-channel support (SMS, WhatsApp, Email)
- Wallet integration (Onfon, M-Pesa)
- RESTful API with authentication
- CRM/Inbox functionality
- Real-time balance tracking

**Key Principle:** Everything is isolated by `client_id` - maintain this in all new features.

**Need Help?**
- Check documentation files (*.md) in project root
- Review Laravel logs
- Check API documentation at `/api-documentation`

---

**Last Updated:** October 13, 2025  
**Maintained By:** Development Team


