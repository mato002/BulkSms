# 🔐 New Senders API Credentials

**Generated:** 2025-10-17 19:38:55

## Overview

All senders use the same BulkSms API endpoint but with different credentials.

**Production Domain:** https://crm.pradytecai.com  
**Local Development:** http://127.0.0.1:8000

---

## Falley Medical Center

### Client Details
- **Client ID:** 3
- **Sender ID:** FALLEY-MED
- **API Key:** `6d3d9896-6d4a-42dc-9bd6-675fd2f4e3f4`
- **Balance:** KSH 150

### API Endpoint
```
PRODUCTION: POST https://crm.pradytecai.com/api/3/messages/send
LOCAL:      POST http://127.0.0.1:8000/api/3/messages/send
```

### Headers
```
X-API-Key: 6d3d9896-6d4a-42dc-9bd6-675fd2f4e3f4
Content-Type: application/json
```

### Request Body Example
```json
{
    "client_id": 3,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "FALLEY-MED",
    "body": "Your message here"
}
```

### cURL Example (Production)
```bash
curl -X POST https://crm.pradytecai.com/api/3/messages/send \
  -H "X-API-Key: 6d3d9896-6d4a-42dc-9bd6-675fd2f4e3f4" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 3,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "FALLEY-MED",
    "body": "Test message from FALLEY-MED"
  }'
```

---

## Logic Link Technologies

### Client Details
- **Client ID:** 4
- **Sender ID:** LOGIC-LINK
- **API Key:** `45cd60bb-ff46-41ce-9920-d25306315c1b`
- **Balance:** KSH 200

### API Endpoint
```
PRODUCTION: POST https://crm.pradytecai.com/api/4/messages/send
LOCAL:      POST http://127.0.0.1:8000/api/4/messages/send
```

### Headers
```
X-API-Key: 45cd60bb-ff46-41ce-9920-d25306315c1b
Content-Type: application/json
```

### Request Body Example
```json
{
    "client_id": 4,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "LOGIC-LINK",
    "body": "Your message here"
}
```

### cURL Example (Production)
```bash
curl -X POST https://crm.pradytecai.com/api/4/messages/send \
  -H "X-API-Key: 45cd60bb-ff46-41ce-9920-d25306315c1b" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 4,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "LOGIC-LINK",
    "body": "Test message from LOGIC-LINK"
  }'
```

---

## Brisk Credit Services

### Client Details
- **Client ID:** 5
- **Sender ID:** BriskCredit
- **API Key:** `fa40c0fc-516b-4c21-8a5d-bf7fbe740a4e`
- **Balance:** KSH 175

### API Endpoint
```
PRODUCTION: POST https://crm.pradytecai.com/api/5/messages/send
LOCAL:      POST http://127.0.0.1:8000/api/5/messages/send
```

### Headers
```
X-API-Key: fa40c0fc-516b-4c21-8a5d-bf7fbe740a4e
Content-Type: application/json
```

### Request Body Example
```json
{
    "client_id": 5,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "BriskCredit",
    "body": "Your message here"
}
```

### cURL Example (Production)
```bash
curl -X POST https://crm.pradytecai.com/api/5/messages/send \
  -H "X-API-Key: fa40c0fc-516b-4c21-8a5d-bf7fbe740a4e" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 5,
    "channel": "sms",
    "recipient": "254728883160",
    "sender": "BriskCredit",
    "body": "Test message from BriskCredit"
  }'
```

---

## Summary Table

| Client ID | Name | Sender ID | API Key | Balance |
|-----------|------|-----------|---------|----------|
| 3 | Falley Medical Center | FALLEY-MED | `6d3d9896-6d4a-42dc-9bd6-675fd2f4e3f4` | KSH 150 |
| 4 | Logic Link Technologies | LOGIC-LINK | `45cd60bb-ff46-41ce-9920-d25306315c1b` | KSH 200 |
| 5 | Brisk Credit Services | BriskCredit | `fa40c0fc-516b-4c21-8a5d-bf7fbe740a4e` | KSH 175 |

## How It Works

```
Client → BulkSms API → Onfon (PRADY_TECH Account) → Recipient
```

**All clients:**
- Call YOUR BulkSms API (not Onfon directly)
- Use their unique API key
- Have their own sender ID
- Share YOUR Onfon account credentials (transparent to them)
- Have separate balance tracking

