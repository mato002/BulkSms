# API Configuration for Prady Tech Integration

## 🔐 Your API Credentials

```plaintext
API Base URL:     https://crm.pradytecai.com/api
Client ID:        1
API Key:          bae377bc-0282-4fc9-a2a1-e338b18da77a
Sender ID:        PRADY_TECH
```

> ⚠️ **IMPORTANT:** Keep your API Key secret! Never commit it to version control or share it publicly.

---

## 🚀 Quick Start

### Step 1: Configuration in Your Application

Add these to your environment variables:

```env
# Prady Tech SMS API Configuration
SMS_API_URL=https://crm.pradytecai.com/api
SMS_CLIENT_ID=1
SMS_API_KEY=bae377bc-0282-4fc9-a2a1-e338b18da77a
SMS_SENDER_ID=PRADY_TECH
```

### Step 2: Send Your First SMS

Choose your preferred language below and follow the examples.

---

## 📝 API Integration Examples

### PHP Integration

```php
<?php

class PradyTechSMS {
    private $apiUrl;
    private $clientId;
    private $apiKey;
    private $senderId;

    public function __construct() {
        $this->apiUrl = 'https://crm.pradytecai.com/api';
        $this->clientId = '1';
        $this->apiKey = 'bae377bc-0282-4fc9-a2a1-e338b18da77a';
        $this->senderId = 'PRADY_TECH';
    }

    /**
     * Send SMS
     */
    public function sendSMS($recipient, $message) {
        $url = "{$this->apiUrl}/{$this->clientId}/messages/send";
        
        $data = [
            'channel' => 'sms',
            'recipient' => $recipient,
            'body' => $message,
            'sender' => $this->senderId
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            throw new Exception("SMS Error: " . $response);
        }
    }

    /**
     * Check Balance
     */
    public function checkBalance() {
        $url = "{$this->apiUrl}/{$this->clientId}/client/balance";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Get SMS History
     */
    public function getHistory($page = 1, $status = 'all') {
        $url = "{$this->apiUrl}/{$this->clientId}/sms/history?page={$page}&status={$status}";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

// Usage Example
try {
    $sms = new PradyTechSMS();
    
    // Send SMS
    $result = $sms->sendSMS('254712345678', 'Hello from Prady Tech!');
    echo "Message sent! ID: " . $result['data']['id'] . "\n";
    
    // Check Balance
    $balance = $sms->checkBalance();
    echo "Balance: KSH " . $balance['data']['balance'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

---

### Laravel Integration

**1. Create a Service Class: `app/Services/SmsService.php`**

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private $apiUrl;
    private $clientId;
    private $apiKey;
    private $senderId;

    public function __construct()
    {
        $this->apiUrl = config('services.sms.api_url');
        $this->clientId = config('services.sms.client_id');
        $this->apiKey = config('services.sms.api_key');
        $this->senderId = config('services.sms.sender_id');
    }

    public function send($recipient, $message)
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/{$this->clientId}/messages/send", [
                'channel' => 'sms',
                'recipient' => $recipient,
                'body' => $message,
                'sender' => $this->senderId,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('SMS sending failed: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('SMS Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function checkBalance()
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->get("{$this->apiUrl}/{$this->clientId}/client/balance");

        return $response->json();
    }
}
```

**2. Add to `config/services.php`:**

```php
'sms' => [
    'api_url' => env('SMS_API_URL', 'https://crm.pradytecai.com/api'),
    'client_id' => env('SMS_CLIENT_ID', '1'),
    'api_key' => env('SMS_API_KEY'),
    'sender_id' => env('SMS_SENDER_ID', 'PRADY_TECH'),
],
```

**3. Usage in Controller:**

```php
use App\Services\SmsService;

class NotificationController extends Controller
{
    protected $sms;

    public function __construct(SmsService $sms)
    {
        $this->sms = $sms;
    }

    public function sendNotification()
    {
        try {
            $result = $this->sms->send('254712345678', 'Your verification code is 1234');
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
```

---

### Python Integration

```python
import requests
import os
from typing import Dict, Optional

class PradyTechSMS:
    def __init__(self):
        self.api_url = 'https://crm.pradytecai.com/api'
        self.client_id = '1'
        self.api_key = 'bae377bc-0282-4fc9-a2a1-e338b18da77a'
        self.sender_id = 'PRADY_TECH'
        
        self.headers = {
            'X-API-Key': self.api_key,
            'Content-Type': 'application/json'
        }
    
    def send_sms(self, recipient: str, message: str) -> Dict:
        """Send SMS message"""
        url = f'{self.api_url}/{self.client_id}/messages/send'
        
        data = {
            'channel': 'sms',
            'recipient': recipient,
            'body': message,
            'sender': self.sender_id
        }
        
        response = requests.post(url, headers=self.headers, json=data)
        
        if response.status_code == 200:
            return response.json()
        else:
            raise Exception(f"SMS Error: {response.text}")
    
    def check_balance(self) -> Dict:
        """Check account balance"""
        url = f'{self.api_url}/{self.client_id}/client/balance'
        response = requests.get(url, headers=self.headers)
        return response.json()
    
    def get_history(self, page: int = 1, status: str = 'all') -> Dict:
        """Get SMS history"""
        url = f'{self.api_url}/{self.client_id}/sms/history'
        params = {'page': page, 'status': status}
        response = requests.get(url, headers=self.headers, params=params)
        return response.json()

# Usage Example
if __name__ == '__main__':
    sms = PradyTechSMS()
    
    try:
        # Send SMS
        result = sms.send_sms('254712345678', 'Hello from Python!')
        print(f"Message sent! ID: {result['data']['id']}")
        
        # Check Balance
        balance = sms.check_balance()
        print(f"Balance: KSH {balance['data']['balance']}")
        
    except Exception as e:
        print(f"Error: {e}")
```

---

### Node.js / JavaScript Integration

```javascript
const axios = require('axios');

class PradyTechSMS {
    constructor() {
        this.apiUrl = 'https://crm.pradytecai.com/api';
        this.clientId = '1';
        this.apiKey = 'bae377bc-0282-4fc9-a2a1-e338b18da77a';
        this.senderId = 'PRADY_TECH';
        
        this.headers = {
            'X-API-Key': this.apiKey,
            'Content-Type': 'application/json'
        };
    }

    async sendSMS(recipient, message) {
        try {
            const response = await axios.post(
                `${this.apiUrl}/${this.clientId}/messages/send`,
                {
                    channel: 'sms',
                    recipient: recipient,
                    body: message,
                    sender: this.senderId
                },
                { headers: this.headers }
            );
            
            return response.data;
        } catch (error) {
            throw new Error(`SMS Error: ${error.response?.data || error.message}`);
        }
    }

    async checkBalance() {
        try {
            const response = await axios.get(
                `${this.apiUrl}/${this.clientId}/client/balance`,
                { headers: this.headers }
            );
            return response.data;
        } catch (error) {
            throw new Error(`Balance Check Error: ${error.message}`);
        }
    }

    async getHistory(page = 1, status = 'all') {
        try {
            const response = await axios.get(
                `${this.apiUrl}/${this.clientId}/sms/history`,
                {
                    headers: this.headers,
                    params: { page, status }
                }
            );
            return response.data;
        } catch (error) {
            throw new Error(`History Error: ${error.message}`);
        }
    }
}

// Usage Example
(async () => {
    const sms = new PradyTechSMS();
    
    try {
        // Send SMS
        const result = await sms.sendSMS('254712345678', 'Hello from Node.js!');
        console.log(`Message sent! ID: ${result.data.id}`);
        
        // Check Balance
        const balance = await sms.checkBalance();
        console.log(`Balance: KSH ${balance.data.balance}`);
        
    } catch (error) {
        console.error('Error:', error.message);
    }
})();

module.exports = PradyTechSMS;
```

---

### cURL Examples (Command Line)

**Send SMS:**
```bash
curl -X POST https://crm.pradytecai.com/api/1/messages/send \
  -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "sms",
    "recipient": "254712345678",
    "body": "Hello from Prady Tech!",
    "sender": "PRADY_TECH"
  }'
```

**Check Balance:**
```bash
curl -X GET https://crm.pradytecai.com/api/1/client/balance \
  -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a"
```

**Get SMS History:**
```bash
curl -X GET "https://crm.pradytecai.com/api/1/sms/history?page=1&status=sent" \
  -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a"
```

**Get Statistics:**
```bash
curl -X GET "https://crm.pradytecai.com/api/1/sms/statistics?period=month" \
  -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a"
```

---

## 📋 API Endpoints Reference

### 1. Send SMS
- **URL:** `POST /api/{client_id}/messages/send`
- **Headers:** `X-API-Key`, `Content-Type: application/json`
- **Body:**
  ```json
  {
    "channel": "sms",
    "recipient": "254712345678",
    "body": "Your message here",
    "sender": "PRADY_TECH"
  }
  ```

### 2. Check Balance
- **URL:** `GET /api/{client_id}/client/balance`
- **Headers:** `X-API-Key`

### 3. SMS History
- **URL:** `GET /api/{client_id}/sms/history?page=1&status=sent`
- **Headers:** `X-API-Key`
- **Query Params:** `page`, `per_page`, `status`, `from_date`, `to_date`

### 4. SMS Statistics
- **URL:** `GET /api/{client_id}/sms/statistics?period=month`
- **Headers:** `X-API-Key`
- **Query Params:** `period` (today, week, month, year, custom)

### 5. Health Check (No Auth)
- **URL:** `GET /api/health`

---

## 🔒 Security Best Practices

1. **Never expose your API Key:**
   - ✅ Store in environment variables
   - ✅ Use secrets management
   - ❌ Don't commit to Git
   - ❌ Don't hardcode in files

2. **Always use HTTPS:**
   - Use `https://` not `http://` in production

3. **Validate phone numbers:**
   - Must be in format: `254XXXXXXXXX` (Kenyan numbers)
   - Start with country code `254`

4. **Handle errors properly:**
   - Check response status codes
   - Implement retry logic
   - Log failures for debugging

---

## 💰 Billing & Pricing

- **Cost per SMS:** KSH 1.00 per unit
- **Message Length:** 160 characters = 1 unit
- **Longer messages:** Split into multiple units
  - 161-320 chars = 2 units = KSH 2.00
  - 321-480 chars = 3 units = KSH 3.00

**Check your balance regularly:**
```bash
curl -H "X-API-Key: your_api_key" \
  https://crm.pradytecai.com/api/1/client/balance
```

---

## 📊 Response Format

### Success Response:
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

### Error Response:
```json
{
  "status": "error",
  "message": "Insufficient balance",
  "errors": []
}
```

---

## ❗ Error Codes

| Code | Message | Solution |
|------|---------|----------|
| 200 | Success | All good! |
| 400 | Bad Request | Check request parameters |
| 401 | Unauthorized | Verify your API key |
| 403 | Forbidden | Contact administrator |
| 422 | Validation Error | Check required fields |
| 429 | Rate Limit | Slow down requests |
| 500 | Server Error | Contact support |

---

## 🧪 Testing Your Integration

### Step 1: Test Connection
```bash
curl https://crm.pradytecai.com/api/health
```

Expected: `{"status":"success","message":"Bulk SMS API is running"}`

### Step 2: Check Balance
```bash
curl -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  https://crm.pradytecai.com/api/1/client/balance
```

### Step 3: Send Test SMS
Use your own phone number for testing:
```bash
curl -X POST https://crm.pradytecai.com/api/1/messages/send \
  -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "sms",
    "recipient": "254YOUR_NUMBER",
    "body": "Test message from Prady Tech API",
    "sender": "PRADY_TECH"
  }'
```

---

## 🆘 Support & Troubleshooting

### Common Issues:

**"Invalid API key"**
- Verify you're using the correct API key
- Check the header name is exactly `X-API-Key`

**"Insufficient balance"**
- Check your balance
- Contact admin to add more units

**"Message failed"**
- Verify phone number format (254XXXXXXXXX)
- Check that PRADY_TECH sender ID is active

### Need Help?
- 📧 Email: support@your-domain.com
- 📞 Phone: +254XXXXXXXXX
- 📖 Full Documentation: See `SENDER_API_DOCUMENTATION.md`

---

## 📦 What's Included

This package includes:
- ✅ API credentials
- ✅ Code examples (PHP, Laravel, Python, Node.js)
- ✅ cURL commands for testing
- ✅ Complete API reference
- ✅ Security best practices
- ✅ Troubleshooting guide

---

## 🚀 Ready to Go!

You now have everything needed to integrate SMS into your application. Start with the code example in your preferred language and you'll be sending SMS in minutes!

**Last Updated:** October 19, 2025
**Version:** 1.0.0

