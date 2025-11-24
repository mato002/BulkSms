<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Bulk SMS Platform</title>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; margin-top: 200px; }
         header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); position: fixed; top: 0; left: 0; right: 0; z-index: 1000; }
         header h1 { font-size: 2.5em; margin-bottom: 10px; }
         header p { font-size: 1.2em; opacity: 0.9; }
         header a:hover { background: rgba(255,255,255,0.2); }
        .sidebar { position: fixed; left: 20px; top: 220px; width: 250px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-height: calc(100vh - 240px); overflow-y: auto; }
        .sidebar h3 { margin-bottom: 15px; color: #667eea; font-size: 1.1em; }
        .sidebar ul { list-style: none; }
        .sidebar li { margin-bottom: 8px; }
        .sidebar a { color: #666; text-decoration: none; display: block; padding: 5px 10px; border-radius: 4px; transition: all 0.3s; }
        .sidebar a:hover { background: #f0f0f0; color: #667eea; }
        .content { margin-left: 280px; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .section { margin-bottom: 50px; }
        .section h2 { color: #667eea; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #667eea; font-size: 1.8em; }
        .section h3 { color: #555; margin-top: 30px; margin-bottom: 15px; font-size: 1.3em; }
        .endpoint { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; border-left: 4px solid #667eea; }
        .method { display: inline-block; padding: 5px 12px; border-radius: 4px; font-weight: bold; color: white; margin-right: 10px; font-size: 0.85em; }
        .method.get { background: #28a745; }
        .method.post { background: #007bff; }
        .method.put { background: #ffc107; color: #333; }
        .method.delete { background: #dc3545; }
        .url { font-family: 'Courier New', monospace; background: #fff; padding: 10px 15px; border-radius: 4px; display: inline-block; margin-top: 10px; border: 1px solid #ddd; }
        .code-block { background: #2d2d2d; color: #f8f8f2; padding: 20px; border-radius: 8px; overflow-x: auto; margin: 15px 0; font-family: 'Courier New', monospace; font-size: 0.9em; }
        .code-block pre { margin: 0; }
        .param-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .param-table th, .param-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .param-table th { background: #f8f9fa; font-weight: 600; color: #667eea; }
        .param-table tr:hover { background: #f8f9fa; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 0.75em; font-weight: bold; margin-left: 5px; }
        .badge.required { background: #dc3545; color: white; }
        .badge.optional { background: #6c757d; color: white; }
        .alert { padding: 15px; border-radius: 8px; margin: 20px 0; }
        .alert-info { background: #e7f3ff; border-left: 4px solid #2196F3; color: #0d47a1; }
        .alert-warning { background: #fff3cd; border-left: 4px solid #ffc107; color: #856404; }
        .alert-success { background: #d4edda; border-left: 4px solid #28a745; color: #155724; }
        .tabs { display: flex; border-bottom: 2px solid #ddd; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; border: none; background: none; font-size: 1em; color: #666; transition: all 0.3s; }
        .tab.active { color: #667eea; border-bottom: 2px solid #667eea; margin-bottom: -2px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        @media (max-width: 768px) {
            .sidebar { position: static; width: 100%; margin-bottom: 20px; }
            .content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <header>
        <div style="max-width: 1200px; margin: 0 auto; position: relative;">
            <a href="/" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: white; text-decoration: none; font-size: 1.1em; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: rgba(255,255,255,0.1); border-radius: 8px; transition: all 0.3s;">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
            <h1><i class="fas fa-code"></i> API Documentation</h1>
            <p>Comprehensive guide to integrate SMS & WhatsApp messaging</p>
        </div>
    </header>

    <div class="container">
        <div class="sidebar">
            <h3>Quick Navigation</h3>
            <ul>
                <li><a href="#getting-started">Getting Started</a></li>
                <li><a href="#authentication">Authentication</a></li>
                <li><a href="#sms">SMS Endpoints</a></li>
                <li><a href="#whatsapp">WhatsApp Endpoints</a></li>
                <li><a href="#wallet">Wallet & Top-up</a></li>
                <li><a href="#contacts">Contacts</a></li>
                <li><a href="#campaigns">Campaigns</a></li>
                <li><a href="#webhooks">Webhooks</a></li>
                <li><a href="#errors">Error Codes</a></li>
                <li><a href="#code-examples">Code Examples</a></li>
                <li><a href="#integration-guide">Integration Guide</a></li>
            </ul>
        </div>

        <div class="content">
            <!-- GETTING STARTED -->
            <section id="getting-started" class="section">
                <h2><i class="fas fa-rocket"></i> Getting Started</h2>
                
                <div class="alert alert-info">
                    <strong>Welcome!</strong> This API allows you to send SMS and WhatsApp messages from your application. Get your API key from your account manager and start integrating in minutes.
                </div>

                <h3>Base URL</h3>
                <div class="code-block">
                    <pre>{{ config('app.url') }}/api</pre>
                </div>

                <h3>Quick Start</h3>
                <p>Follow these steps to send your first SMS:</p>
                <ol style="margin-left: 20px; margin-top: 15px;">
                    <li><strong>Get your API key</strong> - Go to Settings → Profile in your dashboard</li>
                    <li><strong>Note your client ID</strong> - Usually 1 for the main account</li>
                    <li><strong>Make your first API call</strong> - Use the unified messages endpoint</li>
                </ol>

                <div class="code-block">
<pre>curl -X POST {{ config('app.url') }}/api/1/messages/send \
  -H "X-API-KEY: your-api-key-here" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "channel": "sms",
    "recipient": "254712345678",
    "sender": "YOUR-SENDER-ID",
    "body": "Hello from our platform!"
  }'</pre>
                </div>
            </section>

            <!-- AUTHENTICATION -->
            <section id="authentication" class="section">
                <h2><i class="fas fa-lock"></i> Authentication</h2>
                
                <p>All API requests must include your API key in the request header.</p>

                <h3>API Key Header</h3>
                <div class="code-block">
                    <pre>X-API-KEY: your-api-key-here</pre>
                </div>

                <div class="alert alert-warning">
                    <strong>Keep it secret!</strong> Never expose your API key in client-side code or public repositories. The header name is case-sensitive: use <code>X-API-KEY</code> (all caps).
                </div>

                <h3>Client ID</h3>
                <p>Your Client ID is part of the API endpoint URL. It identifies your account.</p>
                <div class="url">/api/{client_id}/endpoint</div>
            </section>

            <!-- SMS ENDPOINTS -->
            <section id="sms" class="section">
                <h2><i class="fas fa-sms"></i> SMS Endpoints</h2>

                <div class="alert alert-info">
                    <strong>💡 Recommended:</strong> Use the Unified Messages API (<code>/messages/send</code>) for simpler integration. It works for SMS, WhatsApp, and Email with consistent parameters.
                </div>

                <!-- Send SMS (Unified API) -->
                <div class="endpoint">
                    <h3>Send SMS (Unified API - Recommended)</h3>
                    <div>
                        <span class="method post">POST</span>
                        <span class="url">/api/{client_id}/messages/send</span>
                    </div>

                    <p style="margin-top: 15px;">Send an SMS message using the unified messaging API. This is the simplest and most flexible method.</p>

                    <h4 style="margin-top: 20px;">Request Parameters</h4>
                    <table class="param-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>client_id</code></td>
                                <td>integer</td>
                                <td><span class="badge required">Required</span></td>
                                <td>Your client ID (usually 1)</td>
                            </tr>
                            <tr>
                                <td><code>channel</code></td>
                                <td>string</td>
                                <td><span class="badge required">Required</span></td>
                                <td>Message channel: <code>sms</code>, <code>whatsapp</code>, or <code>email</code></td>
                            </tr>
                            <tr>
                                <td><code>recipient</code></td>
                                <td>string</td>
                                <td><span class="badge required">Required</span></td>
                                <td>Phone number in international format (e.g., 254712345678)</td>
                            </tr>
                            <tr>
                                <td><code>sender</code></td>
                                <td>string</td>
                                <td><span class="badge required">Required</span></td>
                                <td>Your approved sender ID (max 11 characters)</td>
                            </tr>
                            <tr>
                                <td><code>body</code></td>
                                <td>string</td>
                                <td><span class="badge required">Required</span></td>
                                <td>Message content (max 480 characters for concatenated SMS)</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Example Request</h4>
                    <div class="tabs">
                        <button class="tab active" onclick="showTab(event, 'curl-send')">cURL</button>
                        <button class="tab" onclick="showTab(event, 'php-send')">PHP</button>
                        <button class="tab" onclick="showTab(event, 'python-send')">Python</button>
                        <button class="tab" onclick="showTab(event, 'node-send')">Node.js</button>
                    </div>

                    <div id="curl-send" class="tab-content active">
                        <div class="code-block">
<pre>curl -X POST {{ config('app.url') }}/api/1/messages/send \
  -H "X-API-KEY: your-api-key-here" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "channel": "sms",
    "recipient": "254712345678",
    "sender": "HOSPITAL",
    "body": "Your appointment is tomorrow at 10am"
  }'</pre>
                        </div>
                    </div>

                    <div id="php-send" class="tab-content">
                        <div class="code-block">
<pre>$ch = curl_init('{{ config('app.url') }}/api/1/messages/send');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-KEY: your-api-key-here',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'client_id' => 1,
    'channel' => 'sms',
    'recipient' => '254712345678',
    'sender' => 'HOSPITAL',
    'body' => 'Your appointment is tomorrow at 10am'
]));

$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

print_r($result);</pre>
                        </div>
                    </div>

                    <div id="python-send" class="tab-content">
                        <div class="code-block">
<pre>import requests

url = '{{ config('app.url') }}/api/1/messages/send'
headers = {
    'X-API-KEY': 'your-api-key-here',
    'Content-Type': 'application/json'
}
data = {
    'client_id': 1,
    'channel': 'sms',
    'recipient': '254712345678',
    'sender': 'HOSPITAL',
    'body': 'Your appointment is tomorrow at 10am'
}

response = requests.post(url, headers=headers, json=data)
result = response.json()
print(result)</pre>
                        </div>
                    </div>

                    <div id="node-send" class="tab-content">
                        <div class="code-block">
<pre>const axios = require('axios');

axios.post('{{ config('app.url') }}/api/1/messages/send', {
  client_id: 1,
  channel: 'sms',
  recipient: '254712345678',
  sender: 'HOSPITAL',
  body: 'Your appointment is tomorrow at 10am'
}, {
  headers: {
    'X-API-KEY': 'your-api-key-here',
    'Content-Type': 'application/json'
  }
})
.then(response => {
  console.log(response.data);
})
.catch(error => {
  console.error(error.response.data);
});</pre>
                        </div>
                    </div>

                    <h4>Example Response (Success)</h4>
                    <div class="code-block">
<pre>{
  "id": 123,
  "status": "sent",
  "provider_message_id": "MSG-ABC123"
}</pre>
                    </div>
                </div>

                <!-- Send Bulk SMS -->
                <div class="endpoint">
                    <h3>Send Bulk SMS (Alternative Method)</h3>
                    <div>
                        <span class="method post">POST</span>
                        <span class="url">/api/{client_id}/sms/send</span>
                    </div>

                    <p style="margin-top: 15px;">Send SMS to multiple recipients at once. Note: This endpoint uses different parameter names.</p>

                    <div class="alert alert-warning">
                        <strong>⚠️ Important:</strong> This endpoint uses <code>recipients</code> (plural, array) not <code>recipient</code> (singular)!
                    </div>

                    <h4 style="margin-top: 20px;">Request Parameters</h4>
                    <table class="param-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>recipients</code></td>
                                <td>array</td>
                                <td><span class="badge required">Required</span></td>
                                <td>Array of phone numbers (max 1000 per request)</td>
                            </tr>
                            <tr>
                                <td><code>message</code></td>
                                <td>string</td>
                                <td><span class="badge required">Required</span></td>
                                <td>Message content (max 160 characters per SMS)</td>
                            </tr>
                            <tr>
                                <td><code>sender_id</code></td>
                                <td>string</td>
                                <td><span class="badge optional">Optional</span></td>
                                <td>Sender ID (uses default if not provided)</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Example Request</h4>
                    <div class="code-block">
<pre>curl -X POST {{ config('app.url') }}/api/1/sms/send \
  -H "X-API-KEY: your-api-key-here" \
  -H "Content-Type: application/json" \
  -d '{
    "recipients": ["254712345678", "254723456789"],
    "message": "Bulk SMS notification",
    "sender_id": "HOSPITAL"
  }'</pre>
                    </div>

                    <h4>Example Response</h4>
                    <div class="code-block">
<pre>{
  "status": "success",
  "sent": 2,
  "failed": 0,
  "total_cost": 2.00,
  "results": [
    {
      "recipient": "254712345678",
      "status": "sent",
      "message_id": "MSG-123"
    },
    {
      "recipient": "254723456789",
      "status": "sent",
      "message_id": "MSG-124"
    }
  ]
}</pre>
                    </div>
                </div>

                <!-- Check SMS Status -->
                <div class="endpoint">
                    <h3>Check SMS Status</h3>
                    <div>
                        <span class="method get">GET</span>
                        <span class="url">/api/{client_id}/sms/status/{message_id}</span>
                    </div>

                    <p style="margin-top: 15px;">Check the delivery status of a sent message.</p>

                    <h4>Example Request</h4>
                    <div class="code-block">
<pre>curl -X GET {{ config('app.url') }}/api/1/sms/status/MSG-123456 \
  -H "X-API-Key: sk_abc123xyz456"</pre>
                    </div>

                    <h4>Example Response</h4>
                    <div class="code-block">
<pre>{
  "message_id": "MSG-123456",
  "recipient": "254712345678",
  "status": "delivered",
  "sent_at": "2025-10-09T14:30:00Z",
  "delivered_at": "2025-10-09T14:30:15Z"
}</pre>
                    </div>

                    <h4>Possible Status Values</h4>
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li><code>queued</code> - Message is queued for sending</li>
                        <li><code>sent</code> - Message has been sent to the provider</li>
                        <li><code>delivered</code> - Message was delivered to recipient</li>
                        <li><code>failed</code> - Message delivery failed</li>
                    </ul>
                </div>

                <!-- Get SMS History -->
                <div class="endpoint">
                    <h3>Get SMS History</h3>
                    <div>
                        <span class="method get">GET</span>
                        <span class="url">/api/{client_id}/sms/history</span>
                    </div>

                    <p style="margin-top: 15px;">Retrieve your message history with optional filters.</p>

                    <h4>Query Parameters</h4>
                    <table class="param-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>page</code></td>
                                <td>integer</td>
                                <td><span class="badge optional">Optional</span></td>
                                <td>Page number (default: 1)</td>
                            </tr>
                            <tr>
                                <td><code>per_page</code></td>
                                <td>integer</td>
                                <td><span class="badge optional">Optional</span></td>
                                <td>Results per page (default: 20, max: 100)</td>
                            </tr>
                            <tr>
                                <td><code>status</code></td>
                                <td>string</td>
                                <td><span class="badge optional">Optional</span></td>
                                <td>Filter by status (queued, sent, delivered, failed)</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Example Request</h4>
                    <div class="code-block">
<pre>curl -X GET "{{ config('app.url') }}/api/1/sms/history?page=1&per_page=20&status=delivered" \
  -H "X-API-Key: sk_abc123xyz456"</pre>
                    </div>

                    <h4>Example Response</h4>
                    <div class="code-block">
<pre>{
  "data": [
    {
      "message_id": "MSG-123456",
      "recipient": "254712345678",
      "message": "Your appointment is tomorrow",
      "status": "delivered",
      "cost": 1.00,
      "sent_at": "2025-10-09T14:30:00Z",
      "delivered_at": "2025-10-09T14:30:15Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 1250,
    "per_page": 20,
    "last_page": 63
  }
}</pre>
                    </div>
                </div>

                <!-- Get Statistics -->
                <div class="endpoint">
                    <h3>Get SMS Statistics</h3>
                    <div>
                        <span class="method get">GET</span>
                        <span class="url">/api/{client_id}/sms/statistics</span>
                    </div>

                    <p style="margin-top: 15px;">Get summary statistics for your SMS usage.</p>

                    <h4>Example Response</h4>
                    <div class="code-block">
<pre>{
  "total_sent": 1250,
  "total_delivered": 1200,
  "total_failed": 50,
  "total_cost": 1250.00,
  "today": {
    "sent": 45,
    "delivered": 43,
    "failed": 2,
    "cost": 45.00
  },
  "this_month": {
    "sent": 890,
    "delivered": 850,
    "failed": 40,
    "cost": 890.00
  }
}</pre>
                    </div>
                </div>
            </section>

            <!-- WHATSAPP ENDPOINTS -->
            <section id="whatsapp" class="section">
                <h2><i class="fab fa-whatsapp"></i> WhatsApp Endpoints</h2>

                <div class="endpoint">
                    <h3>Send WhatsApp Message</h3>
                    <div>
                        <span class="method post">POST</span>
                        <span class="url">/api/{client_id}/messages/send</span>
                    </div>

                    <p style="margin-top: 15px;">Send a WhatsApp message using the unified messaging endpoint.</p>

                    <h4>Request Parameters</h4>
                    <table class="param-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>channel</code></td>
                                <td>string</td>
                                <td><span class="badge required">Required</span></td>
                                <td>Must be "whatsapp"</td>
                            </tr>
                            <tr>
                                <td><code>recipient</code></td>
                                <td>string</td>
                                <td><span class="badge required">Required</span></td>
                                <td>Phone number in international format</td>
                            </tr>
                            <tr>
                                <td><code>body</code></td>
                                <td>string</td>
                                <td><span class="badge required">Required</span></td>
                                <td>Message content</td>
                            </tr>
                            <tr>
                                <td><code>sender</code></td>
                                <td>string</td>
                                <td><span class="badge optional">Optional</span></td>
                                <td>Your WhatsApp sender ID</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Example Request</h4>
                    <div class="code-block">
<pre>curl -X POST {{ config('app.url') }}/api/1/messages/send \
  -H "X-API-Key: sk_abc123xyz456" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "whatsapp",
    "recipient": "254712345678",
    "body": "Your order has been shipped!",
    "sender": "YOUR-BUSINESS"
  }'</pre>
                    </div>
                </div>
            </section>

            <!-- WALLET & TOP-UP ENDPOINTS -->
            <section id="wallet" class="section">
                <h2><i class="fas fa-wallet"></i> Wallet & Top-up Endpoints</h2>

                <!-- Check Balance -->
                <div class="endpoint">
                    <h3>Check Balance</h3>
                    <div>
                        <span class="method get">GET</span>
                        <span class="url">/api/{client_id}/client/balance</span>
                    </div>

                    <p style="margin-top: 15px;">Check your current account balance.</p>

                    <h4>Example Request</h4>
                    <div class="code-block">
<pre>curl -X GET {{ config('app.url') }}/api/1/client/balance \
  -H "X-API-Key: sk_abc123xyz456"</pre>
                    </div>

                    <h4>Example Response</h4>
                    <div class="code-block">
<pre>{
  "balance": 1049.00,
  "currency": "KES",
  "units": 1049,
  "price_per_unit": 1.00,
  "low_balance": false
}</pre>
                    </div>
                </div>

                <!-- Initiate Top-up -->
                <div class="endpoint">
                    <h3>Initiate Top-up (M-Pesa)</h3>
                    <div>
                        <span class="method post">POST</span>
                        <span class="url">/api/{client_id}/wallet/topup</span>
                    </div>

                    <div class="alert alert-info">
                        <strong>New Feature!</strong> This endpoint initiates an M-Pesa STK Push to add credits to your account.
                    </div>

                    <p style="margin-top: 15px;">Initiate a top-up request using M-Pesa STK Push.</p>

                    <h4>Request Parameters</h4>
                    <table class="param-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>amount</code></td>
                                <td>number</td>
                                <td><span class="badge required">Required</span></td>
                                <td>Amount in KES (min: 100, max: 50000)</td>
                            </tr>
                            <tr>
                                <td><code>payment_method</code></td>
                                <td>string</td>
                                <td><span class="badge required">Required</span></td>
                                <td>Must be "mpesa"</td>
                            </tr>
                            <tr>
                                <td><code>phone_number</code></td>
                                <td>string</td>
                                <td><span class="badge required">Required</span></td>
                                <td>M-Pesa phone number (254XXXXXXXXX)</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Example Request</h4>
                    <div class="code-block">
<pre>curl -X POST {{ config('app.url') }}/api/1/wallet/topup \
  -H "X-API-Key: sk_abc123xyz456" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 1000,
    "payment_method": "mpesa",
    "phone_number": "254712345678"
  }'</pre>
                    </div>

                    <h4>Example Response</h4>
                    <div class="code-block">
<pre>{
  "status": "pending",
  "message": "Please check your phone for M-Pesa prompt",
  "transaction_id": "TXN-20251009-001",
  "amount": 1000,
  "checkout_request_id": "ws_CO_09012025..."
}</pre>
                    </div>
                </div>

                <!-- Check Top-up Status -->
                <div class="endpoint">
                    <h3>Check Top-up Status</h3>
                    <div>
                        <span class="method get">GET</span>
                        <span class="url">/api/{client_id}/wallet/topup/{transaction_id}</span>
                    </div>

                    <p style="margin-top: 15px;">Check the status of a top-up request.</p>

                    <h4>Example Request</h4>
                    <div class="code-block">
<pre>curl -X GET {{ config('app.url') }}/api/1/wallet/topup/TXN-20251009-001 \
  -H "X-API-Key: sk_abc123xyz456"</pre>
                    </div>

                    <h4>Example Response</h4>
                    <div class="code-block">
<pre>{
  "transaction_id": "TXN-20251009-001",
  "status": "completed",
  "amount": 1000.00,
  "payment_method": "mpesa",
  "mpesa_receipt": "PGH7X8Y9Z0",
  "completed_at": "2025-10-09T14:35:00Z"
}</pre>
                    </div>

                    <h4>Possible Status Values</h4>
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li><code>pending</code> - Waiting for payment</li>
                        <li><code>processing</code> - Payment is being processed</li>
                        <li><code>completed</code> - Payment successful, balance updated</li>
                        <li><code>failed</code> - Payment failed or cancelled</li>
                    </ul>
                </div>

                <!-- Transaction History -->
                <div class="endpoint">
                    <h3>Transaction History</h3>
                    <div>
                        <span class="method get">GET</span>
                        <span class="url">/api/{client_id}/wallet/transactions</span>
                    </div>

                    <p style="margin-top: 15px;">Get your wallet transaction history.</p>

                    <h4>Query Parameters</h4>
                    <table class="param-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>from_date</code></td>
                                <td>date</td>
                                <td>Filter from date (YYYY-MM-DD)</td>
                            </tr>
                            <tr>
                                <td><code>to_date</code></td>
                                <td>date</td>
                                <td>Filter to date (YYYY-MM-DD)</td>
                            </tr>
                            <tr>
                                <td><code>type</code></td>
                                <td>string</td>
                                <td>credit, debit, or refund</td>
                            </tr>
                            <tr>
                                <td><code>page</code></td>
                                <td>integer</td>
                                <td>Page number (default: 1)</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Example Response</h4>
                    <div class="code-block">
<pre>{
  "data": [
    {
      "id": 123,
      "type": "credit",
      "amount": 1000.00,
      "payment_method": "mpesa",
      "mpesa_receipt": "PGH7X8Y9Z0",
      "status": "completed",
      "created_at": "2025-10-09T14:35:00Z"
    },
    {
      "id": 122,
      "type": "debit",
      "amount": 1.00,
      "description": "SMS to 254712345678",
      "status": "completed",
      "created_at": "2025-10-09T14:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 150,
    "per_page": 20
  }
}</pre>
                    </div>
                </div>
            </section>

            <!-- CONTACTS ENDPOINTS -->
            <section id="contacts" class="section">
                <h2><i class="fas fa-address-book"></i> Contacts Endpoints</h2>

                <div class="endpoint">
                    <h3>List Contacts</h3>
                    <div>
                        <span class="method get">GET</span>
                        <span class="url">/api/{client_id}/contacts</span>
                    </div>
                </div>

                <div class="endpoint">
                    <h3>Create Contact</h3>
                    <div>
                        <span class="method post">POST</span>
                        <span class="url">/api/{client_id}/contacts</span>
                    </div>

                    <h4>Example Request</h4>
                    <div class="code-block">
<pre>{
  "name": "John Doe",
  "phone": "254712345678",
  "email": "john@example.com",
  "department": "Sales"
}</pre>
                    </div>
                </div>

                <div class="endpoint">
                    <h3>Bulk Import Contacts (CSV)</h3>
                    <div>
                        <span class="method post">POST</span>
                        <span class="url">/api/{client_id}/contacts/bulk-import</span>
                    </div>

                    <p style="margin-top: 15px;">Upload a CSV file with contacts.</p>

                    <h4>CSV Format</h4>
                    <div class="code-block">
<pre>Name,Phone,Email,Department
John Doe,254712345678,john@example.com,Sales
Jane Smith,254723456789,jane@example.com,Marketing</pre>
                    </div>
                </div>
            </section>

            <!-- CAMPAIGNS ENDPOINTS -->
            <section id="campaigns" class="section">
                <h2><i class="fas fa-bullhorn"></i> Campaigns Endpoints</h2>

                <div class="endpoint">
                    <h3>Create Campaign</h3>
                    <div>
                        <span class="method post">POST</span>
                        <span class="url">/api/{client_id}/campaigns</span>
                    </div>

                    <h4>Example Request</h4>
                    <div class="code-block">
<pre>{
  "name": "Monthly Newsletter",
  "message": "Check out our latest offers!",
  "sender": "YOURSTORE",
  "recipients": ["254712345678", "254723456789"]
}</pre>
                    </div>
                </div>

                <div class="endpoint">
                    <h3>Send Campaign</h3>
                    <div>
                        <span class="method post">POST</span>
                        <span class="url">/api/{client_id}/campaigns/{campaign_id}/send</span>
                    </div>

                    <p style="margin-top: 15px;">Send a created campaign to all recipients.</p>
                </div>
            </section>

            <!-- WEBHOOKS -->
            <section id="webhooks" class="section">
                <h2><i class="fas fa-bell"></i> Webhooks</h2>

                <div class="alert alert-info">
                    <strong>Stay updated!</strong> Configure a webhook URL to receive real-time notifications about events in your account.
                </div>

                <h3>Configure Webhook</h3>
                <p>Contact your account manager to set up your webhook URL and secret key.</p>

                <h3>Webhook Events</h3>
                <ul style="margin-left: 20px; margin-top: 15px;">
                    <li><code>balance.updated</code> - Your balance has changed</li>
                    <li><code>message.delivered</code> - A message was delivered</li>
                    <li><code>message.failed</code> - A message failed to deliver</li>
                    <li><code>topup.completed</code> - A top-up was successful</li>
                    <li><code>topup.failed</code> - A top-up failed</li>
                </ul>

                <h3>Webhook Payload Format</h3>
                <div class="code-block">
<pre>POST https://your-system.com/webhook

Headers:
  X-Webhook-Signature: sha256_hmac_signature
  X-Webhook-Event: balance.updated
  Content-Type: application/json

Body:
{
  "event": "balance.updated",
  "client_id": 1,
  "timestamp": "2025-10-09T14:35:00Z",
  "data": {
    "old_balance": 50.00,
    "new_balance": 1050.00,
    "amount_added": 1000.00,
    "transaction_id": "TXN-20251009-001"
  }
}</pre>
                </div>

                <h3>Verifying Webhook Signatures</h3>
                <div class="tabs">
                    <button class="tab active" onclick="showTab(event, 'php-verify')">PHP</button>
                    <button class="tab" onclick="showTab(event, 'python-verify')">Python</button>
                    <button class="tab" onclick="showTab(event, 'node-verify')">Node.js</button>
                </div>

                <div id="php-verify" class="tab-content active">
                    <div class="code-block">
<pre>$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];
$payload = file_get_contents('php://input');
$secret = 'your-webhook-secret';

$expectedSignature = hash_hmac('sha256', $payload, $secret);

if (hash_equals($expectedSignature, $signature)) {
    // Signature is valid
    $data = json_decode($payload, true);
    // Process webhook...
} else {
    // Invalid signature
    http_response_code(401);
}</pre>
                    </div>
                </div>

                <div id="python-verify" class="tab-content">
                    <div class="code-block">
<pre>import hmac
import hashlib

signature = request.headers.get('X-Webhook-Signature')
payload = request.get_data()
secret = b'your-webhook-secret'

expected_signature = hmac.new(secret, payload, hashlib.sha256).hexdigest()

if hmac.compare_digest(expected_signature, signature):
    # Signature is valid
    data = request.get_json()
    # Process webhook...
else:
    # Invalid signature
    return 'Unauthorized', 401</pre>
                    </div>
                </div>

                <div id="node-verify" class="tab-content">
                    <div class="code-block">
<pre>const crypto = require('crypto');

const signature = req.headers['x-webhook-signature'];
const payload = JSON.stringify(req.body);
const secret = 'your-webhook-secret';

const expectedSignature = crypto
  .createHmac('sha256', secret)
  .update(payload)
  .digest('hex');

if (crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expectedSignature))) {
    // Signature is valid
    // Process webhook...
} else {
    // Invalid signature
    res.status(401).send('Unauthorized');
}</pre>
                    </div>
                </div>
            </section>

            <!-- ERROR CODES -->
            <section id="errors" class="section">
                <h2><i class="fas fa-exclamation-triangle"></i> Error Codes</h2>

                <table class="param-table">
                    <thead>
                        <tr>
                            <th>Status Code</th>
                            <th>Error Code</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>400</td>
                            <td>INVALID_REQUEST</td>
                            <td>Required parameters are missing or invalid</td>
                        </tr>
                        <tr>
                            <td>401</td>
                            <td>INVALID_API_KEY</td>
                            <td>API key is missing or invalid</td>
                        </tr>
                        <tr>
                            <td>402</td>
                            <td>INSUFFICIENT_BALANCE</td>
                            <td>Your account balance is too low</td>
                        </tr>
                        <tr>
                            <td>403</td>
                            <td>UNAUTHORIZED</td>
                            <td>You don't have permission to access this resource</td>
                        </tr>
                        <tr>
                            <td>404</td>
                            <td>NOT_FOUND</td>
                            <td>The requested resource was not found</td>
                        </tr>
                        <tr>
                            <td>429</td>
                            <td>RATE_LIMIT_EXCEEDED</td>
                            <td>Too many requests, slow down</td>
                        </tr>
                        <tr>
                            <td>500</td>
                            <td>SERVER_ERROR</td>
                            <td>Internal server error, please try again</td>
                        </tr>
                    </tbody>
                </table>

                <h3>Error Response Format</h3>
                <div class="code-block">
<pre>{
  "status": "error",
  "error_code": "INSUFFICIENT_BALANCE",
  "message": "Your account balance is too low. Current balance: KES 5.00",
  "balance": 5.00
}</pre>
                </div>
            </section>

            <!-- CODE EXAMPLES -->
            <section id="code-examples" class="section">
                <h2><i class="fas fa-code"></i> Complete Code Examples</h2>

                <h3>PHP Integration Example</h3>
                <div class="code-block">
<pre>&lt;?php

class BulkSMSClient {
    private $apiKey;
    private $clientId;
    private $baseUrl;
    
    public function __construct($apiKey, $clientId) {
        $this->apiKey = $apiKey;
        $this->clientId = $clientId;
        $this->baseUrl = '{{ config('app.url') }}/api/' . $clientId;
    }
    
    public function sendSMS($recipient, $message, $sender) {
        $ch = curl_init($this->baseUrl . '/sms/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'recipient' => $recipient,
            'message' => $message,
            'sender' => $sender
        ]));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
    
    public function checkBalance() {
        $ch = curl_init($this->baseUrl . '/client/balance');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    public function topUp($amount, $phoneNumber) {
        $ch = curl_init($this->baseUrl . '/wallet/topup');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'amount' => $amount,
            'payment_method' => 'mpesa',
            'phone_number' => $phoneNumber
        ]));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}

// Usage
$client = new BulkSMSClient('sk_abc123xyz456', 1);

// Send SMS
$result = $client->sendSMS('254712345678', 'Hello World', 'YOURAPP');
print_r($result);

// Check balance
$balance = $client->checkBalance();
echo "Balance: KES " . $balance['balance'];

// Top up
$topup = $client->topUp(1000, '254712345678');
print_r($topup);
?&gt;</pre>
                </div>

                <h3>Python Integration Example</h3>
                <div class="code-block">
<pre>import requests

class BulkSMSClient:
    def __init__(self, api_key, client_id):
        self.api_key = api_key
        self.client_id = client_id
        self.base_url = f'{{ config('app.url') }}/api/{client_id}'
        self.headers = {
            'X-API-Key': api_key,
            'Content-Type': 'application/json'
        }
    
    def send_sms(self, recipient, message, sender):
        url = f'{self.base_url}/sms/send'
        data = {
            'recipient': recipient,
            'message': message,
            'sender': sender
        }
        response = requests.post(url, headers=self.headers, json=data)
        return response.json()
    
    def check_balance(self):
        url = f'{self.base_url}/client/balance'
        response = requests.get(url, headers=self.headers)
        return response.json()
    
    def topup(self, amount, phone_number):
        url = f'{self.base_url}/wallet/topup'
        data = {
            'amount': amount,
            'payment_method': 'mpesa',
            'phone_number': phone_number
        }
        response = requests.post(url, headers=self.headers, json=data)
        return response.json()

# Usage
client = BulkSMSClient('sk_abc123xyz456', 1)

# Send SMS
result = client.send_sms('254712345678', 'Hello World', 'YOURAPP')
print(result)

# Check balance
balance = client.check_balance()
print(f"Balance: KES {balance['balance']}")

# Top up
topup_result = client.topup(1000, '254712345678')
print(topup_result)</pre>
                </div>
            </section>

            <!-- CLIENT INTEGRATION GUIDE -->
            <section id="integration-guide" class="section">
                <h2><i class="fas fa-plug"></i> Client Integration Guide</h2>
                
                <div class="alert alert-info">
                    <strong>For Organizations Integrating with Our API:</strong> This guide provides everything you need to integrate SMS sending into your application using our API.
                </div>

                <h3>📋 Your API Credentials</h3>
                <p>You will receive these credentials from your account manager:</p>
                <div class="code-block">
<pre>API Base URL:  {{ config('app.url') }}/api
Client ID:     1 (or your assigned ID)
API Key:       Your unique API key
Sender ID:     Your approved sender name</pre>
                </div>

                <div class="alert alert-warning">
                    <strong>⚠️ Security:</strong> Never commit your API key to version control or share it publicly. Store it securely in environment variables.
                </div>

                <h3>🚀 Quick Start Configuration</h3>
                
                <div class="tabs">
                    <button class="tab active" onclick="showTab(event, 'env-setup')">Environment Setup</button>
                    <button class="tab" onclick="showTab(event, 'php-class')">PHP Class</button>
                    <button class="tab" onclick="showTab(event, 'laravel-service')">Laravel Service</button>
                    <button class="tab" onclick="showTab(event, 'python-class')">Python Class</button>
                    <button class="tab" onclick="showTab(event, 'nodejs-class')">Node.js Class</button>
                </div>

                <!-- Environment Setup Tab -->
                <div id="env-setup" class="tab-content active">
                    <h4>Add to your .env file:</h4>
                    <div class="code-block">
<pre># SMS API Configuration
SMS_API_URL={{ config('app.url') }}/api
SMS_CLIENT_ID=1
SMS_API_KEY=your_api_key_here
SMS_SENDER_ID=YOUR_SENDER</pre>
                    </div>
                </div>

                <!-- PHP Class Tab -->
                <div id="php-class" class="tab-content">
                    <h4>Complete PHP Integration Class:</h4>
                    <div class="code-block">
<pre>&lt;?php

class SMSClient {
    private $apiUrl;
    private $clientId;
    private $apiKey;
    private $senderId;

    public function __construct() {
        $this->apiUrl = getenv('SMS_API_URL') ?: '{{ config('app.url') }}/api';
        $this->clientId = getenv('SMS_CLIENT_ID') ?: '1';
        $this->apiKey = getenv('SMS_API_KEY');
        $this->senderId = getenv('SMS_SENDER_ID');
    }

    /**
     * Send SMS message
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
     * Check account balance
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
     * Get SMS history
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
    $sms = new SMSClient();
    
    // Send SMS
    $result = $sms->sendSMS('254712345678', 'Hello from your app!');
    echo "Message sent! ID: " . $result['data']['id'] . "\n";
    
    // Check Balance
    $balance = $sms->checkBalance();
    echo "Balance: KSH " . $balance['data']['balance'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?&gt;</pre>
                    </div>
                </div>

                <!-- Laravel Service Tab -->
                <div id="laravel-service" class="tab-content">
                    <h4>Laravel Service Class (app/Services/SmsService.php):</h4>
                    <div class="code-block">
<pre>&lt;?php

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
?&gt;</pre>
                    </div>

                    <h4>Add to config/services.php:</h4>
                    <div class="code-block">
<pre>'sms' => [
    'api_url' => env('SMS_API_URL', '{{ config('app.url') }}/api'),
    'client_id' => env('SMS_CLIENT_ID', '1'),
    'api_key' => env('SMS_API_KEY'),
    'sender_id' => env('SMS_SENDER_ID'),
],</pre>
                    </div>

                    <h4>Usage in Controller:</h4>
                    <div class="code-block">
<pre>use App\Services\SmsService;

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
}</pre>
                    </div>
                </div>

                <!-- Python Class Tab -->
                <div id="python-class" class="tab-content">
                    <h4>Complete Python Integration Class:</h4>
                    <div class="code-block">
<pre>import requests
import os
from typing import Dict

class SMSClient:
    def __init__(self):
        self.api_url = os.getenv('SMS_API_URL', '{{ config('app.url') }}/api')
        self.client_id = os.getenv('SMS_CLIENT_ID', '1')
        self.api_key = os.getenv('SMS_API_KEY')
        self.sender_id = os.getenv('SMS_SENDER_ID')
        
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
    sms = SMSClient()
    
    try:
        # Send SMS
        result = sms.send_sms('254712345678', 'Hello from Python!')
        print(f"Message sent! ID: {result['data']['id']}")
        
        # Check Balance
        balance = sms.check_balance()
        print(f"Balance: KSH {balance['data']['balance']}")
        
    except Exception as e:
        print(f"Error: {e}")</pre>
                    </div>
                </div>

                <!-- Node.js Class Tab -->
                <div id="nodejs-class" class="tab-content">
                    <h4>Complete Node.js Integration Class:</h4>
                    <div class="code-block">
<pre>const axios = require('axios');

class SMSClient {
    constructor() {
        this.apiUrl = process.env.SMS_API_URL || '{{ config('app.url') }}/api';
        this.clientId = process.env.SMS_CLIENT_ID || '1';
        this.apiKey = process.env.SMS_API_KEY;
        this.senderId = process.env.SMS_SENDER_ID;
        
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
    const sms = new SMSClient();
    
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

module.exports = SMSClient;</pre>
                    </div>
                </div>

                <h3>🔌 cURL Quick Test Commands</h3>
                
                <h4>1. Test API Health (No Auth Required):</h4>
                <div class="code-block">
<pre>curl {{ config('app.url') }}/api/health</pre>
                </div>

                <h4>2. Send SMS:</h4>
                <div class="code-block">
<pre>curl -X POST {{ config('app.url') }}/api/1/messages/send \
  -H "X-API-Key: your_api_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "sms",
    "recipient": "254712345678",
    "body": "Hello from API!",
    "sender": "YOUR_SENDER"
  }'</pre>
                </div>

                <h4>3. Check Balance:</h4>
                <div class="code-block">
<pre>curl -H "X-API-Key: your_api_key_here" \
  {{ config('app.url') }}/api/1/client/balance</pre>
                </div>

                <h4>4. Get SMS History:</h4>
                <div class="code-block">
<pre>curl -H "X-API-Key: your_api_key_here" \
  "{{ config('app.url') }}/api/1/sms/history?page=1&status=sent"</pre>
                </div>

                <h3>💰 Pricing & Billing</h3>
                <table class="param-table">
                    <thead>
                        <tr>
                            <th>Message Length</th>
                            <th>Units</th>
                            <th>Cost (KSH 1.00/unit)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1-160 characters</td>
                            <td>1 unit</td>
                            <td>KSH 1.00</td>
                        </tr>
                        <tr>
                            <td>161-320 characters</td>
                            <td>2 units</td>
                            <td>KSH 2.00</td>
                        </tr>
                        <tr>
                            <td>321-480 characters</td>
                            <td>3 units</td>
                            <td>KSH 3.00</td>
                        </tr>
                    </tbody>
                </table>

                <h3>🔒 Security Best Practices</h3>
                <div class="alert alert-warning">
                    <ul style="margin-left: 20px;">
                        <li><strong>Environment Variables:</strong> Always store API keys in environment variables, never hardcode</li>
                        <li><strong>HTTPS Only:</strong> Use <code>https://</code> not <code>http://</code> in production</li>
                        <li><strong>Phone Format:</strong> Always use international format: <code>254XXXXXXXXX</code></li>
                        <li><strong>.gitignore:</strong> Add <code>.env</code> to your <code>.gitignore</code> file</li>
                        <li><strong>Error Handling:</strong> Implement proper try-catch blocks and logging</li>
                        <li><strong>Rate Limiting:</strong> Monitor your API usage and implement backoff strategies</li>
                    </ul>
                </div>

                <h3>📊 API Response Format</h3>
                <h4>Success Response:</h4>
                <div class="code-block">
<pre>{
  "status": "success",
  "message": "Message queued for sending",
  "data": {
    "id": 123,
    "channel": "sms",
    "recipient": "254712345678",
    "status": "queued",
    "cost": 1.00
  }
}</pre>
                </div>

                <h4>Error Response:</h4>
                <div class="code-block">
<pre>{
  "status": "error",
  "message": "Insufficient balance",
  "errors": []
}</pre>
                </div>

                <h3>🧪 Testing Checklist</h3>
                <div class="alert alert-success">
                    <strong>Before going live, test:</strong>
                    <ol style="margin-left: 20px; margin-top: 10px;">
                        <li>✅ API health check works</li>
                        <li>✅ Authentication with your API key works</li>
                        <li>✅ Can check your balance</li>
                        <li>✅ Can send test SMS successfully</li>
                        <li>✅ SMS is actually delivered</li>
                        <li>✅ Balance is deducted correctly</li>
                        <li>✅ Error handling works (try invalid API key)</li>
                        <li>✅ History shows sent messages</li>
                    </ol>
                </div>

                <h3>❗ Common Issues & Solutions</h3>
                <table class="param-table">
                    <thead>
                        <tr>
                            <th>Issue</th>
                            <th>Solution</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>"Invalid API key"</td>
                            <td>Verify API key is correct and header name is exactly <code>X-API-Key</code></td>
                        </tr>
                        <tr>
                            <td>"Insufficient balance"</td>
                            <td>Check your balance and contact support to add more units</td>
                        </tr>
                        <tr>
                            <td>"Message failed"</td>
                            <td>Verify phone number format (254XXXXXXXXX) and sender ID is active</td>
                        </tr>
                        <tr>
                            <td>"Rate limit exceeded"</td>
                            <td>Implement exponential backoff or contact support to upgrade tier</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- SUPPORT -->
            <section class="section">
                <h2><i class="fas fa-headset"></i> Need Help?</h2>
                <div class="alert alert-success">
                    <p><strong>Contact Support:</strong></p>
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li>Email: support@yourplatform.com</li>
                        <li>Phone: +254 XXX XXX XXX</li>
                        <li>Hours: Monday - Friday, 8am - 6pm EAT</li>
                    </ul>
                </div>
            </section>
        </div>
    </div>

    <script>
        function showTab(event, tabId) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Deactivate all tabs
            const tabs = event.target.parentElement.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Activate selected tab
            event.target.classList.add('active');
        }

        // Smooth scroll for sidebar links
        document.querySelectorAll('.sidebar a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>

