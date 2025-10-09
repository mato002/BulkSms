<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 30px; }
        .alert-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .balance-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .balance-amount { font-size: 36px; font-weight: bold; color: #dc3545; margin: 10px 0; }
        .button { display: inline-block; padding: 15px 40px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; font-size: 16px; font-weight: bold; }
        .button:hover { background: #5568d3; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 14px; }
        .list { margin-left: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Low Balance Alert</h1>
            <p>Your account balance is running low</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $client->name }}</strong>,</p>

            <p>This is an important notification about your {{ config('app.name') }} account balance.</p>

            <div class="balance-info">
                <p style="margin: 0; color: #666;">Current Balance</p>
                <div class="balance-amount">KES {{ number_format($client->balance, 2) }}</div>
                <p style="margin: 0; color: #666;">≈ {{ number_format($client->getBalanceInUnits(), 0) }} SMS remaining</p>
            </div>

            <div class="alert-box">
                <strong>⚠️ Action Required</strong><br>
                Your balance has fallen below KES {{ number_format($threshold, 2) }}. To continue sending messages without interruption, please top up your account now.
            </div>

            <h3>💳 Top Up Your Account</h3>
            <p>You can top up instantly via M-Pesa or other payment methods:</p>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/api/{{ $client->id }}/wallet/topup" class="button">Top Up Now via M-Pesa</a>
            </div>

            <h3>📊 Account Summary</h3>
            <ul class="list">
                <li><strong>Account:</strong> {{ $client->sender_id }}</li>
                <li><strong>Current Balance:</strong> KES {{ number_format($client->balance, 2) }}</li>
                <li><strong>SMS Remaining:</strong> ~{{ number_format($client->getBalanceInUnits(), 0) }} messages</li>
                <li><strong>Price per SMS:</strong> KES {{ number_format($client->price_per_unit, 2) }}</li>
            </ul>

            <h3>🔄 Alternative Top-up Methods</h3>
            <p>You can also top up programmatically using our API:</p>

            <div style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: 'Courier New', monospace; font-size: 13px;">
<pre>POST {{ config('app.url') }}/api/{{ $client->id }}/wallet/topup
{
  "amount": 1000,
  "payment_method": "mpesa",
  "phone_number": "254XXXXXXXXX"
}</pre>
            </div>

            <h3>💡 Need Help?</h3>
            <p>If you have any questions or need assistance with top-up:</p>
            <ul class="list">
                <li>📧 Email: <a href="mailto:support@yourplatform.com">support@yourplatform.com</a></li>
                <li>📞 Phone: +254 XXX XXX XXX</li>
            </ul>

            <p style="margin-top: 30px;">Thank you for using {{ config('app.name') }}!</p>
            <p>Best regards,<br><strong>The {{ config('app.name') }} Team</strong></p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>This is an automated alert. You can adjust notification settings in your account.</p>
        </div>
    </div>
</body>
</html>

