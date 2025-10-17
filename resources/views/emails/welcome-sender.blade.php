<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 30px; }
        .credentials { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
        .credentials h3 { margin-top: 0; color: #667eea; }
        .code { font-family: 'Courier New', monospace; background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; margin: 15px 0; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 14px; }
        .list { margin-left: 20px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Welcome to {{ config('app.name') }}!</h1>
            <p>Your account has been created successfully</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $client->name }}</strong>,</p>

            <p>Welcome aboard! Your API account has been successfully created, and you're ready to start sending SMS and WhatsApp messages through our platform.</p>

            <div class="credentials">
                <h3>🔑 Your API Credentials</h3>
                <p><strong>API Key:</strong><br>
                <code style="background: #e9ecef; padding: 5px 10px; border-radius: 4px; display: inline-block; margin-top: 5px;">{{ $client->api_key }}</code></p>
                
                <p><strong>Client ID:</strong> {{ $client->id }}</p>
                <p><strong>Sender ID:</strong> {{ $client->sender_id }}</p>
            </div>

            <div class="warning">
                <strong>⚠️ Keep Your API Key Secret!</strong><br>
                Never share your API key or expose it in client-side code. Treat it like a password.
            </div>

            <h3>🚀 Quick Start Guide</h3>
            <ol class="list">
                <li><strong>Visit Our API Documentation:</strong><br>
                    <a href="{{ $apiDocUrl }}" class="button">View API Docs</a>
                </li>
                <li><strong>Copy a Code Example:</strong> Find examples in PHP, Python, Node.js, and cURL</li>
                <li><strong>Replace the API Key:</strong> Use your key in the header</li>
                <li><strong>Send Your First SMS:</strong> Test with a single message</li>
            </ol>

            <h3>📱 Example: Send Your First SMS</h3>
            <div class="code">
<pre>curl -X POST {{ config('app.url') }}/api/{{ $client->id }}/sms/send \
  -H "X-API-Key: {{ $client->api_key }}" \
  -H "Content-Type: application/json" \
  -d '{
    "recipient": "254712345678",
    "message": "Hello from {{ $client->sender_id }}!",
    "sender": "{{ $client->sender_id }}"
  }'</pre>
            </div>

            <h3>💰 Your Account Details</h3>
            <ul class="list">
                <li><strong>Current Balance:</strong> KES {{ number_format($client->balance, 2) }}</li>
                <li><strong>SMS Units Available:</strong> {{ number_format($client->getBalanceInUnits(), 0) }}</li>
                <li><strong>Price per SMS:</strong> KES {{ number_format($client->price_per_unit, 2) }}</li>
            </ul>

            <h3>🔗 Useful Links</h3>
            <ul class="list">
                <li><a href="{{ $apiDocUrl }}">API Documentation</a></li>
                <li><a href="{{ config('app.url') }}">Dashboard</a></li>
                <li><a href="{{ config('app.url') }}/api/{{ $client->id }}/wallet/transactions">Transaction History</a></li>
            </ul>

            <h3>💡 Need Help?</h3>
            <p>If you have any questions or need assistance integrating our API:</p>
            <ul class="list">
                <li>📧 Email: <a href="mailto:support@yourplatform.com">support@yourplatform.com</a></li>
                <li>📞 Phone: +254 XXX XXX XXX</li>
                <li>🕐 Hours: Monday - Friday, 8am - 6pm EAT</li>
            </ul>

            <p style="margin-top: 30px;">Happy messaging! 🚀</p>
            <p>Best regards,<br><strong>The {{ config('app.name') }} Team</strong></p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }} by Matech Technologies. All rights reserved.</p>
            <p>This is an automated message. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>

