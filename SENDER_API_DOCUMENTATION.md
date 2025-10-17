# Sender API Documentation

## Overview

This API allows authorized senders (like PRADY_TECH) to send SMS messages through our platform without calling Onfon directly. All messages are routed through our system, which handles authentication, billing, and delivery.

## Benefits

✅ **Simplified Integration** - No need to manage Onfon credentials  
✅ **Unified Billing** - Track all SMS usage in one place  
✅ **Better Monitoring** - View history, statistics, and delivery reports  
✅ **Secure** - API key authentication protects your account  
✅ **Scalable** - Handle high-volume messaging with ease  

---

## Getting Started

### 1. Obtain Your API Credentials

Your API credentials include:
- **Client ID** - Your unique client identifier
- **API Key** - Secret key for authentication
- **Sender ID** - Your approved sender name (e.g., PRADY_TECH)

Contact your administrator to receive these credentials.

### 2. Base URL

All API requests should be made to:
```
https://your-domain.com/api
```

For local development:
```
http://localhost/api
```

### 3. Authentication

All API requests require authentication using your API key. Include it in the request header:

```
X-API-Key: your_api_key_here
```

---

## API Endpoints

### 1. Send SMS

Send a single SMS message.

**Endpoint:** `POST /api/{client_id}/messages/send`

**Headers:**
```
X-API-Key: your_api_key
Content-Type: application/json
```

**Request Body:**
```json
{
    "channel": "sms",
    "recipient": "254712345678",
    "body": "Your message here",
    "sender": "PRADY_TECH"
}
```

**Parameters:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| channel | string | Yes | Must be "sms" |
| recipient | string | Yes | Phone number in international format (254...) |
| body | string | Yes | Message content (max 160 chars for single SMS) |
| sender | string | Yes | Your registered sender ID |

**Response (Success):**
```json
{
    "status": "success",
    "message": "Message queued for sending",
    "data": {
        "id": 123,
        "channel": "sms",
        "recipient": "254712345678",
        "status": "queued",
        "cost": 1.00
    }
}
```

**Response (Error):**
```json
{
    "status": "error",
    "message": "Insufficient balance",
    "errors": []
}
```

**Example cURL:**
```bash
curl -X POST https://your-domain.com/api/1/messages/send \
  -H "X-API-Key: your_api_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "sms",
    "recipient": "254712345678",
    "body": "Hello from PRADY_TECH!",
    "sender": "PRADY_TECH"
  }'
```

---

### 2. Check Balance

Get your current account balance and available units.

**Endpoint:** `GET /api/{client_id}/client/balance`

**Headers:**
```
X-API-Key: your_api_key
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "balance": 1000.00,
        "units": 1000.00,
        "price_per_unit": 1.00,
        "currency": "KSH"
    }
}
```

**Example cURL:**
```bash
curl -X GET https://your-domain.com/api/1/client/balance \
  -H "X-API-Key: your_api_key_here"
```

---

### 3. SMS History

Retrieve your SMS sending history with filters and pagination.

**Endpoint:** `GET /api/{client_id}/sms/history`

**Headers:**
```
X-API-Key: your_api_key
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | integer | 1 | Page number |
| per_page | integer | 50 | Items per page (max 100) |
| status | string | all | Filter by status: sent, failed, pending |
| from_date | date | - | Start date (YYYY-MM-DD) |
| to_date | date | - | End date (YYYY-MM-DD) |

**Response:**
```json
{
    "status": "success",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 123,
                "recipient": "254712345678",
                "message": "Test message",
                "sender_id": "PRADY_TECH",
                "status": "sent",
                "cost": 1.00,
                "sent_at": "2025-10-09 10:30:00",
                "delivered_at": "2025-10-09 10:30:15"
            }
        ],
        "total": 100,
        "per_page": 50,
        "last_page": 2
    }
}
```

**Example cURL:**
```bash
curl -X GET "https://your-domain.com/api/1/sms/history?status=sent&page=1" \
  -H "X-API-Key: your_api_key_here"
```

---

### 4. SMS Statistics

Get aggregated statistics about your SMS usage.

**Endpoint:** `GET /api/{client_id}/sms/statistics`

**Headers:**
```
X-API-Key: your_api_key
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| period | string | month | Period: today, week, month, year, custom |
| from_date | date | - | Start date for custom period |
| to_date | date | - | End date for custom period |

**Response:**
```json
{
    "status": "success",
    "data": {
        "total_sent": 1500,
        "total_delivered": 1450,
        "total_failed": 50,
        "delivery_rate": 96.67,
        "total_cost": 1500.00,
        "period": "month",
        "breakdown": {
            "sent": 1500,
            "delivered": 1450,
            "failed": 50,
            "pending": 0
        }
    }
}
```

**Example cURL:**
```bash
curl -X GET "https://your-domain.com/api/1/sms/statistics?period=week" \
  -H "X-API-Key: your_api_key_here"
```

---

### 5. Health Check

Check if the API is running (no authentication required).

**Endpoint:** `GET /api/health`

**Response:**
```json
{
    "status": "success",
    "message": "Bulk SMS API is running",
    "timestamp": "2025-10-09T10:30:00.000000Z"
}
```

---

## Error Codes

| Code | Message | Description |
|------|---------|-------------|
| 200 | Success | Request completed successfully |
| 400 | Bad Request | Invalid request parameters |
| 401 | Unauthorized | Missing or invalid API key |
| 403 | Forbidden | No access to this resource |
| 404 | Not Found | Resource not found |
| 422 | Validation Error | Request validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Server Error | Internal server error |

---

## Best Practices

### 1. Security
- **Never** share your API key publicly
- Store API keys securely (environment variables, secrets manager)
- Use HTTPS for all API requests
- Rotate API keys periodically

### 2. Error Handling
- Always check the response status
- Implement retry logic for failed requests
- Log all API interactions for debugging

### 3. Rate Limiting
- Respect rate limits (varies by tier)
- Implement exponential backoff for retries
- Monitor your usage through statistics endpoint

### 4. Phone Numbers
- Always use international format (254...)
- Validate phone numbers before sending
- Remove duplicates from bulk sends

### 5. Message Content
- Keep messages under 160 characters for single SMS
- Longer messages are split and cost multiple units
- Include opt-out instructions for marketing messages

---

## Code Examples

### PHP Example

```php
<?php

$apiKey = 'your_api_key_here';
$clientId = 1;
$baseUrl = 'https://your-domain.com/api';

// Send SMS
$ch = curl_init("{$baseUrl}/{$clientId}/messages/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: ' . $apiKey,
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'channel' => 'sms',
    'recipient' => '254712345678',
    'body' => 'Hello from PHP!',
    'sender' => 'PRADY_TECH',
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "Message sent! ID: " . $data['data']['id'];
} else {
    echo "Error: " . $response;
}
```

### Python Example

```python
import requests

api_key = 'your_api_key_here'
client_id = 1
base_url = 'https://your-domain.com/api'

# Send SMS
headers = {
    'X-API-Key': api_key,
    'Content-Type': 'application/json'
}

data = {
    'channel': 'sms',
    'recipient': '254712345678',
    'body': 'Hello from Python!',
    'sender': 'PRADY_TECH'
}

response = requests.post(
    f'{base_url}/{client_id}/messages/send',
    headers=headers,
    json=data
)

if response.status_code == 200:
    result = response.json()
    print(f"Message sent! ID: {result['data']['id']}")
else:
    print(f"Error: {response.text}")
```

### JavaScript Example

```javascript
const apiKey = 'your_api_key_here';
const clientId = 1;
const baseUrl = 'https://your-domain.com/api';

// Send SMS
fetch(`${baseUrl}/${clientId}/messages/send`, {
    method: 'POST',
    headers: {
        'X-API-Key': apiKey,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        channel: 'sms',
        recipient: '254712345678',
        body: 'Hello from JavaScript!',
        sender: 'PRADY_TECH'
    })
})
.then(response => response.json())
.then(data => {
    if (data.status === 'success') {
        console.log('Message sent! ID:', data.data.id);
    } else {
        console.error('Error:', data.message);
    }
})
.catch(error => console.error('Error:', error));
```

---

## Postman Collection

Import the provided Postman collection for easy testing:

1. Run: `php generate_api_credentials.php`
2. Copy the Postman JSON from the output
3. Import into Postman
4. Update the API key and client ID variables
5. Start testing!

---

## Testing

### Local Testing

1. Generate credentials:
```bash
php generate_api_credentials.php
```

2. Run tests:
```bash
php test_sender_api.php
```

### Production Testing

1. Use Postman or cURL to test endpoints
2. Start with small test messages
3. Monitor delivery reports
4. Check balance regularly

---

## Troubleshooting

### Issue: "Invalid API key"
- Verify your API key is correct
- Check that you're using the correct client ID
- Ensure your account is active

### Issue: "Insufficient balance"
- Check your balance using the balance endpoint
- Add more units to your account
- Contact admin to top up

### Issue: "Message failed to send"
- Verify recipient number format (254...)
- Check that sender ID is approved
- Review error message in response

### Issue: "Rate limit exceeded"
- Reduce request frequency
- Implement exponential backoff
- Contact admin to upgrade tier

---

## Support

For technical support or questions:
- Email: support@yourdomain.com
- Documentation: https://docs.yourdomain.com
- Status Page: https://status.yourdomain.com

---

## Changelog

### Version 1.0.0 (2025-10-09)
- Initial release
- Send SMS endpoint
- Balance checking
- SMS history
- SMS statistics
- API authentication

---

## License

This API is provided as a service. Usage is subject to the terms and conditions outlined in your service agreement.

