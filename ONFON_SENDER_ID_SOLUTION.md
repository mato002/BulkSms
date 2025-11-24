# Onfon Sender ID Filter Issue - Solution

## The Problem
Onfon is rejecting "MATECHTE" with error:
```
Onfon MessageErrorCode 401: Value filter failed for user [e27847c1-a9fe-4eef-b60d-ddb291b175ab] 
(source_address filter mismatch).
```

## Business Model
- **BulkSMS CRM** pays Onfon (you're the customer)
- **Clients** (like Matech) pay BulkSMS CRM
- **You should be able to use ANY sender ID** since you're paying Onfon

## The Issue
Onfon has a **sender ID filter/whitelist** enabled on your account. This is a security feature that restricts which sender IDs can be used.

## Solutions

### Option 1: Disable Sender ID Filter (RECOMMENDED)
Contact Onfon Media support and request:
1. **Disable the sender ID filter/whitelist** for your account
2. Allow **any sender ID** to be used from your account
3. This makes sense since you're the paying customer and managing multiple clients

**Contact Onfon:**
- Email: support@onfonmedia.co.ke
- Phone: Check their website
- Account: Client ID `e27847c1-a9fe-4eef-b60d-ddb291b175ab`

**Request:**
"Please disable the sender ID filter/whitelist for our account. We need to use any sender ID from our clients since we're managing multiple businesses through our platform."

### Option 2: Upgrade Account Plan
Some Onfon account plans allow unrestricted sender IDs. Check if you need to upgrade.

### Option 3: Add Sender IDs in Bulk
Request Onfon to add all your client sender IDs at once, or provide a way to add them programmatically.

## Current API Implementation
The code is correct - it's sending the sender ID properly:
```php
'SenderId' => $message->sender ?? ($this->credentials['default_sender'] ?? ''),
```

The issue is at Onfon's account level, not in the code.

## Next Steps
1. **Contact Onfon Support** to disable sender ID filter
2. **Test with MATECHTE** once filter is disabled
3. **All future sender IDs** will work automatically

## Temporary Workaround
Until Onfon disables the filter, you can:
- Use an approved sender ID like "PRADY_TECH" for testing
- But this defeats the purpose of your multi-tenant system







