<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 30px; }
        .success-box { background: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin: 20px 0; border-radius: 4px; color: #155724; }
        .transaction-details { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .transaction-details table { width: 100%; border-collapse: collapse; }
        .transaction-details td { padding: 10px 0; border-bottom: 1px solid #dee2e6; }
        .transaction-details td:first-child { font-weight: bold; color: #666; }
        .transaction-details tr:last-child td { border-bottom: none; }
        .balance-highlight { background: #fff; border: 2px solid #28a745; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .balance-amount { font-size: 32px; font-weight: bold; color: #28a745; margin: 10px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 14px; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Top-up Successful!</h1>
            <p>Your payment has been received</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $client->name }}</strong>,</p>

            <div class="success-box">
                <strong>✅ Payment Confirmed</strong><br>
                Your top-up of <strong>KES {{ number_format($transaction->amount, 2) }}</strong> has been successfully processed and added to your account.
            </div>

            <div class="transaction-details">
                <h3 style="margin-top: 0;">📄 Transaction Details</h3>
                <table>
                    <tr>
                        <td>Transaction ID:</td>
                        <td><code>{{ $transaction->transaction_ref }}</code></td>
                    </tr>
                    @if($transaction->mpesa_receipt)
                    <tr>
                        <td>M-Pesa Receipt:</td>
                        <td><strong>{{ $transaction->mpesa_receipt }}</strong></td>
                    </tr>
                    @endif
                    <tr>
                        <td>Amount:</td>
                        <td><strong>KES {{ number_format($transaction->amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Payment Method:</td>
                        <td>{{ ucfirst($transaction->payment_method) }}</td>
                    </tr>
                    <tr>
                        <td>Date & Time:</td>
                        <td>{{ $transaction->completed_at ? $transaction->completed_at->format('d M Y, h:i A') : $transaction->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                </table>
            </div>

            <div class="balance-highlight">
                <p style="margin: 0; color: #666; font-size: 14px;">New Account Balance</p>
                <div class="balance-amount">KES {{ number_format($client->balance, 2) }}</div>
                <p style="margin: 0; color: #666; font-size: 14px;">≈ {{ number_format($client->getBalanceInUnits(), 0) }} SMS available</p>
            </div>

            <h3>📊 Account Summary</h3>
            <ul style="margin-left: 20px;">
                <li><strong>Account:</strong> {{ $client->sender_id }}</li>
                <li><strong>Previous Balance:</strong> KES {{ number_format($client->balance - $transaction->amount, 2) }}</li>
                <li><strong>Amount Added:</strong> + KES {{ number_format($transaction->amount, 2) }}</li>
                <li><strong>New Balance:</strong> KES {{ number_format($client->balance, 2) }}</li>
                <li><strong>SMS Available:</strong> ~{{ number_format($client->getBalanceInUnits(), 0) }} messages</li>
            </ul>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ config('app.url') }}/api/{{ $client->id }}/wallet/transactions" class="button">View Transaction History</a>
                <a href="{{ config('app.url') }}/api-documentation" class="button">API Documentation</a>
            </div>

            <h3>💡 What's Next?</h3>
            <p>Your account is now topped up and ready to go! You can:</p>
            <ul style="margin-left: 20px;">
                <li>Send SMS messages via our API</li>
                <li>Check your balance anytime via <code>GET /api/{{ $client->id}}/client/balance</code></li>
                <li>View transaction history for all top-ups</li>
            </ul>

            <p style="margin-top: 30px;">Thank you for using {{ config('app.name') }}!</p>
            <p>Best regards,<br><strong>The {{ config('app.name') }} Team</strong></p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>For support, contact us at <a href="mailto:support@yourplatform.com">support@yourplatform.com</a></p>
        </div>
    </div>
</body>
</html>

