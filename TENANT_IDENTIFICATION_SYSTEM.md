# Multi-Tenant Identification System - Step by Step

## Overview

This document explains **exactly** how the system identifies which tenant (sender/client) is making a request, ensuring that each sender can only access their own data and resources.

---

## 🔐 The Two-Layer Authentication System

Your platform uses a **TWO-LAYER** authentication approach:

1. **Layer 1: API Key Authentication** - Identifies WHO is making the request
2. **Layer 2: Client ID Verification** - Ensures they can only access THEIR data

---

## 📊 Step-by-Step Request Flow

### Example Request from PRADY_TECH

Let's trace what happens when PRADY_TECH sends this request:

```bash
POST http://localhost/api/1/messages/send
Headers:
  X-API-Key: abc123xyz789
  Content-Type: application/json
Body:
  {
    "channel": "sms",
    "recipient": "254728883160",
    "body": "Hello!",
    "sender": "PRADY_TECH"
  }
```

---

## 🔄 Step-by-Step Processing

### **STEP 1: Request Arrives at Laravel**

The request hits your Laravel application:
```
POST /api/1/messages/send
```

Components in the URL:
- `/api/` - API prefix
- `1` - **Client ID** (claims to be client 1)
- `/messages/send` - The endpoint

**Key Point:** The client ID in the URL is just a CLAIM at this point. It hasn't been verified yet!

---

### **STEP 2: Route Matching**

Laravel's router matches the request to this route:

**File:** `routes/api.php`
```php
Route::middleware(['api.auth'])->group(function () {
    Route::prefix('{company_id}')->middleware(['company.auth', 'tier.rate.limit'])->group(function () {
        Route::post('/messages/send', [MessageController::class, 'send']);
    });
});
```

**Middleware Stack Applied:**
1. `api.auth` - First layer: API key authentication
2. `company.auth` - Second layer: Client ID verification
3. `tier.rate.limit` - Rate limiting based on tier

---

### **STEP 3: ApiAuth Middleware (Layer 1)**

**File:** `app/Http/Middleware/ApiAuth.php`

#### 3.1: Extract API Key from Request

```php
public function handle(Request $request, Closure $next)
{
    // Get API key from header OR query parameter
    $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
    
    // Example: $apiKey = "abc123xyz789"
```

**Two ways to send API key:**
- **Header (Recommended):** `X-API-Key: abc123xyz789`
- **Query Parameter:** `?api_key=abc123xyz789`

#### 3.2: Check if API Key Exists

```php
    if (!$apiKey) {
        return response()->json([
            'status' => 'error',
            'message' => 'API key required'
        ], 401);
    }
```

**If no API key → REJECT with 401 Unauthorized**

#### 3.3: Look Up Client in Database

```php
    $client = Client::where('api_key', $apiKey)
                   ->where('status', true)
                   ->first();
```

**Database Query:**
```sql
SELECT * FROM clients 
WHERE api_key = 'abc123xyz789' 
  AND status = 1 
LIMIT 1;
```

**What's checked:**
- ✅ API key matches exactly
- ✅ Account is active (status = true)

**Example Result:**
```
Client {
  id: 1,
  name: "Prady Technologies",
  sender_id: "PRADY_TECH",
  api_key: "abc123xyz789",
  balance: 100.00,
  status: true
}
```

#### 3.4: Validate Client

```php
    if (!$client) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid API key'
        ], 401);
    }
```

**If API key not found OR account inactive → REJECT with 401**

#### 3.5: Attach Client to Request

```php
    // Set the authenticated client
    $request->setUserResolver(function () use ($client) {
        return $client;
    });
    
    return $next($request);
}
```

**What happens:**
- Client object is attached to the request
- Now accessible via `$request->user()`
- Request moves to next middleware

**✅ LAYER 1 COMPLETE:** We now know WHO is making the request (Client ID: 1, PRADY_TECH)

---

### **STEP 4: CompanyAuth Middleware (Layer 2)**

**File:** `app/Http/Middleware/CompanyAuth.php`

#### 4.1: Extract Company ID from URL

```php
public function handle(Request $request, Closure $next)
{
    // Get the company_id from the URL route parameter
    $companyId = $request->route('company_id');
    
    // Example: $companyId = "1" (from /api/1/messages/send)
```

#### 4.2: Get Authenticated Client

```php
    // Get the client that was authenticated in Layer 1
    $client = $request->user();
    
    // Example: $client->id = 1
```

#### 4.3: Verify Client Can Access This Company

```php
    if (!$client || $client->id != $companyId) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized access to company data'
        ], 403);
    }
```

**What's being checked:**
- ✅ Client exists (from Layer 1)
- ✅ Client's ID matches the company_id in the URL

**Example Check:**
```
URL company_id: 1
Client ID from API key: 1
Match? YES ✅ → Allow
```

**Example of FAILED check:**
```
URL: /api/2/messages/send  (trying to access client 2)
API Key belongs to: Client 1 (PRADY_TECH)
Match? NO ❌ → REJECT with 403 Forbidden
```

#### 4.4: Allow Request to Proceed

```php
    return $next($request);
}
```

**✅ LAYER 2 COMPLETE:** Client can only access their own data!

---

### **STEP 5: Rate Limiting Middleware**

**File:** `app/Http/Middleware/TierBasedRateLimit.php`

Applies rate limits based on client tier:
- Basic tier: 60 requests/minute
- Standard tier: 120 requests/minute
- Premium tier: 300 requests/minute

---

### **STEP 6: Controller Processes Request**

**File:** `app/Http/Controllers/Api/MessageController.php`

```php
public function send(Request $request)
{
    // Get the authenticated client
    $client = $request->user();
    
    // At this point, we're 100% sure:
    // - Client is authenticated
    // - Client is active
    // - Client can only access their own data
    
    // Example: $client->id = 1, $client->sender_id = "PRADY_TECH"
    
    // Validate request data
    $validated = $request->validate([
        'channel' => 'required|in:sms,whatsapp,email',
        'recipient' => 'required|string',
        'body' => 'required|string',
        'sender' => 'required|string',
    ]);
    
    // Check balance
    if ($client->balance < 1) {
        return response()->json([
            'status' => 'error',
            'message' => 'Insufficient balance'
        ], 400);
    }
    
    // Send message using MessageDispatcher
    $outbound = new OutboundMessage([
        'clientId' => $client->id,  // Always uses authenticated client's ID
        'channel' => $validated['channel'],
        'recipient' => $validated['recipient'],
        'body' => $validated['body'],
        'sender' => $validated['sender'],
    ]);
    
    $message = $this->dispatcher->dispatch($outbound);
    
    return response()->json([
        'status' => 'success',
        'data' => $message
    ]);
}
```

---

## 🔍 Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────┐
│  Request from PRADY_TECH                                │
│  POST /api/1/messages/send                              │
│  Header: X-API-Key: abc123xyz789                        │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  STEP 1: Laravel Router                                 │
│  - Matches route pattern                                │
│  - Extracts company_id = "1"                            │
│  - Applies middleware stack                             │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  STEP 2: ApiAuth Middleware (Layer 1)                   │
│  ┌──────────────────────────────────────────────────┐   │
│  │ 1. Extract API key: "abc123xyz789"               │   │
│  │ 2. Query database:                               │   │
│  │    SELECT * FROM clients                         │   │
│  │    WHERE api_key = 'abc123xyz789'                │   │
│  │      AND status = 1                              │   │
│  │ 3. Found: Client ID 1 (PRADY_TECH)               │   │
│  │ 4. Attach client to request                      │   │
│  └──────────────────────────────────────────────────┘   │
│  Result: $request->user() = Client #1 ✅                │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  STEP 3: CompanyAuth Middleware (Layer 2)               │
│  ┌──────────────────────────────────────────────────┐   │
│  │ 1. URL company_id: 1                             │   │
│  │ 2. Authenticated client ID: 1                    │   │
│  │ 3. Compare: 1 == 1? YES ✅                        │   │
│  │ 4. Authorization granted                         │   │
│  └──────────────────────────────────────────────────┘   │
│  Result: Client can access company 1's data ✅          │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  STEP 4: TierBasedRateLimit Middleware                  │
│  - Check request count for this client                  │
│  - Apply tier-specific limits                           │
│  - Allow or reject based on rate                        │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  STEP 5: MessageController                              │
│  - Client is fully authenticated                        │
│  - Client is authorized for this company                │
│  - Process message send request                         │
│  - Deduct balance                                       │
│  - Return response                                      │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  Response to Client                                     │
│  { "status": "success", "data": {...} }                 │
└─────────────────────────────────────────────────────────┘
```

---

## 🛡️ Security Scenarios

### Scenario 1: Valid Request from PRADY_TECH

```
Request: POST /api/1/messages/send
API Key: abc123xyz789 (belongs to Client 1)
URL Client ID: 1
```

**Result:**
1. ✅ API key found → Client 1
2. ✅ Client 1 accessing Client 1's data
3. ✅ **REQUEST ALLOWED**

---

### Scenario 2: PRADY_TECH Trying to Access Another Client's Data

```
Request: POST /api/5/messages/send (trying to access Client 5)
API Key: abc123xyz789 (belongs to Client 1)
URL Client ID: 5
```

**Result:**
1. ✅ API key found → Client 1
2. ❌ Client 1 trying to access Client 5's data
3. ❌ **REQUEST REJECTED** - 403 Forbidden

**Response:**
```json
{
  "status": "error",
  "message": "Unauthorized access to company data"
}
```

---

### Scenario 3: Invalid API Key

```
Request: POST /api/1/messages/send
API Key: invalid_key_xyz
URL Client ID: 1
```

**Result:**
1. ❌ API key not found in database
2. ❌ **REQUEST REJECTED** - 401 Unauthorized

**Response:**
```json
{
  "status": "error",
  "message": "Invalid API key"
}
```

---

### Scenario 4: No API Key Provided

```
Request: POST /api/1/messages/send
API Key: (none)
URL Client ID: 1
```

**Result:**
1. ❌ No API key in header or query
2. ❌ **REQUEST REJECTED** - 401 Unauthorized

**Response:**
```json
{
  "status": "error",
  "message": "API key required"
}
```

---

### Scenario 5: Inactive Account

```
Request: POST /api/1/messages/send
API Key: abc123xyz789
Client Status: Inactive (status = 0)
```

**Result:**
1. 🔍 API key found BUT client is inactive
2. ❌ **REQUEST REJECTED** - 401 Unauthorized

**Response:**
```json
{
  "status": "error",
  "message": "Invalid API key"
}
```

---

## 📊 Database Structure

### Clients Table

```sql
CREATE TABLE clients (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    sender_id VARCHAR(255),        -- e.g., "PRADY_TECH"
    company_name VARCHAR(255),
    api_key VARCHAR(255) UNIQUE,   -- e.g., "abc123xyz789"
    balance DECIMAL(10,2),
    price_per_unit DECIMAL(10,4),
    status BOOLEAN DEFAULT 1,      -- Active/Inactive
    tier VARCHAR(50),              -- basic, standard, premium
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Example Data

```sql
+----+-------------------+--------------+-------------+------------------+---------+------+--------+
| id | name              | sender_id    | api_key     | balance | status | tier     |
+----+-------------------+--------------+-------------+------------------+---------+------+--------+
| 1  | Prady Tech        | PRADY_TECH   | abc123xyz   | 100.00  | 1      | standard |
| 2  | Test Sender       | TEST_SENDER  | def456uvw   | 50.00   | 1      | basic    |
| 3  | Fortress Ltd      | FORTRESS     | ghi789rst   | 500.00  | 1      | premium  |
+----+-------------------+--------------+-------------+------------------+---------+------+--------+
```

---

## 🔐 API Key Generation

When a new client is created, an API key is generated:

```php
use Illuminate\Support\Str;

$client = Client::create([
    'name' => 'PRADY_TECH',
    'sender_id' => 'PRADY_TECH',
    'api_key' => Str::random(32),  // Generates: "abc123xyz789..."
    'balance' => 0,
    'status' => true,
]);
```

**Properties:**
- **Length:** 32 characters
- **Randomness:** Cryptographically secure
- **Uniqueness:** Enforced by database unique constraint
- **Regeneration:** Can be regenerated by admin

---

## 🔄 Complete Code Flow with Examples

### Example 1: Successful Request

```php
// Request arrives
POST /api/1/messages/send
Headers: X-API-Key: abc123xyz789

// ApiAuth Middleware
$apiKey = "abc123xyz789";
$client = Client::where('api_key', 'abc123xyz789')->first();
// Returns: Client { id: 1, name: "PRADY_TECH", ... }

$request->setUserResolver(fn() => $client);

// CompanyAuth Middleware
$companyId = 1; // from URL
$client = $request->user(); // Client with id = 1
if ($client->id != $companyId) { ... } // 1 == 1 ✅

// Controller
$client = $request->user(); // Client with id = 1
// Process message for Client 1
// Use Client 1's balance, Client 1's channel config, etc.
```

### Example 2: Unauthorized Access Attempt

```php
// Request arrives - Client 1 trying to access Client 2's data
POST /api/2/messages/send
Headers: X-API-Key: abc123xyz789 (belongs to Client 1)

// ApiAuth Middleware
$apiKey = "abc123xyz789";
$client = Client::where('api_key', 'abc123xyz789')->first();
// Returns: Client { id: 1, ... } ✅

$request->setUserResolver(fn() => $client);

// CompanyAuth Middleware
$companyId = 2; // from URL
$client = $request->user(); // Client with id = 1
if ($client->id != $companyId) { // 1 != 2 ❌
    return response()->json([
        'status' => 'error',
        'message' => 'Unauthorized access to company data'
    ], 403);
}

// REQUEST REJECTED - Never reaches controller
```

---

## 📝 Summary

### How Tenant Identification Works:

1. **Client sends request** with API key in header
2. **ApiAuth middleware** looks up API key in database
3. **Database returns** the client that owns that API key
4. **Client is attached** to the request object
5. **CompanyAuth middleware** ensures client can only access their own company_id
6. **Controller uses** the authenticated client for all operations
7. **All database queries** are automatically scoped to that client

### Key Points:

✅ **API Key = Client Identity** - Each client has a unique API key  
✅ **Database Lookup** - API key is looked up in `clients` table  
✅ **Automatic Isolation** - Clients can ONLY access their own data  
✅ **Two-Layer Security** - API key auth + Company ID verification  
✅ **Status Check** - Inactive accounts are automatically rejected  
✅ **No Client Confusion** - Impossible for one client to access another's data  

---

## 🎯 Real-World Example

### PRADY_TECH Scenario:

**Setup:**
```sql
Client:
  ID: 1
  Name: "Prady Technologies"
  Sender: "PRADY_TECH"
  API Key: "abc123xyz789prady"
  Balance: 500 KSH
  Status: Active
```

**Valid Requests:**
```bash
# ✅ Sending message
POST /api/1/messages/send
X-API-Key: abc123xyz789prady

# ✅ Checking balance
GET /api/1/client/balance
X-API-Key: abc123xyz789prady

# ✅ Viewing history
GET /api/1/sms/history
X-API-Key: abc123xyz789prady
```

**Invalid Requests:**
```bash
# ❌ Wrong client ID (trying to access Client 2)
POST /api/2/messages/send
X-API-Key: abc123xyz789prady
→ 403 Forbidden

# ❌ No API key
POST /api/1/messages/send
→ 401 Unauthorized

# ❌ Wrong API key
POST /api/1/messages/send
X-API-Key: wrong_key
→ 401 Unauthorized
```

---

**The system is completely secure!** Each tenant can ONLY access their own data, and there's no way to bypass this without the correct API key.

