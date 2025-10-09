<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 30px; }
        .error-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; margin: 20px 0; border-radius: 4px; color: #721c24; }
        .transaction-details { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .button { display: inline-block; padding: 15px 40px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; font-size: 16px; font-weight: bold; }
        .button:hover { background: #5568d3; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 14px; }
        .list { margin-left: 20px; }
        .help-box { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 20px; margin: 20px 0; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>❌ Top-up Failed</h1>
            <p>Your payment could not be processed</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $client->name }}</strong>,</p>

            <p>We're writing to inform you that your recent top-up attempt was unsuccessful.</p>

            <div class="error-box">
                <strong>❌ Payment Failed</strong><br>
                <p style="margin: 10px 0 0 0;">
                    <strong>Reason:</strong> {{ $reason }}
                </p>
            </div>

            <div class="transaction-details">
                <h3 style="margin-top: 0;">📄 Transaction Details</h3>
                <ul class="list" style="margin-left: 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0; border-bottom: 1px solid #dee2e6;">
                        <strong>Transaction ID:</strong> <code>{{ $transaction->transaction_ref }}</code>
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #dee2e6;">
                        <strong>Amount:</strong> KES {{ number_format($transaction->amount, 2) }}
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #dee2e6;">
                        <strong>Payment Method:</strong> {{ ucfirst($transaction->payment_method) }}
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #dee2e6;">
                        <strong>Attempted At:</strong> {{ $transaction->created_at->format('d M Y, h:i A') }}
                    </li>
                    <li style="padding: 8px 0;">
                        <strong>Status:</strong> <span style="color: #dc3545;">Failed</span>
                    </li>
                </ul>
            </div>

            <h3>🔄 What to Do Next?</h3>
            <p>Please try again using one of the following methods:</p>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/api/{{ $client->id }}/wallet/topup" class="button">Try Again with M-Pesa</a>
            </div>

            <h3>❓ Common Reasons for Payment Failure</h3>
            <ul class="list">
                <li><strong>Cancelled by user:</strong> You may have cancelled the M-Pesa prompt</li>
                <li><strong>Insufficient funds:</strong> Not enough balance in your M-Pesa account</li>
                <li><strong>Wrong PIN:</strong> Incorrect M-Pesa PIN entered</li>
                <li><strong>Timeout:</strong> Payment request expired (you took too long to respond)</li>
                <li><strong>Network issues:</strong> Temporary connectivity problems</li>
            </ul>

            <div class="help-box">
                <h3 style="margin-top: 0;">💡 Need Help?</h3>
                <p>If you continue to experience issues or believe this was an error:</p>
                <ul class="list">
                    <li>📧 Email us: <a href="mailto:support@yourplatform.com">support@yourplatform.com</a></li>
                    <li>📞 Call us: +254 XXX XXX XXX</li>
                    <li>🕐 Support Hours: Monday - Friday, 8am - 6pm EAT</li>
                </ul>
                <p><strong>Reference Number:</strong> {{ $transaction->transaction_ref }}</p>
            </div>

            <h3>🔄 Alternative Payment Methods</h3>
            <p>You can also top up via:</p>
            <ul class="list">
                <li><strong>API:</strong> Use our top-up API endpoint</li>
                <li><strong>Bank Transfer:</strong> Contact support for bank details</li>
                <li><strong>Manual Top-up:</strong> Request manual credit from support</li>
            </ul>

            <h3>📊 Current Account Status</h3>
            <ul class="list">
                <li><strong>Account:</strong> {{ $client->sender_id }}</li>
                <li><strong>Current Balance:</strong> KES {{ number_format($client->balance, 2) }}</li>
                <li><strong>SMS Remaining:</strong> ~{{ number_format($client->getBalanceInUnits(), 0) }} messages</li>
            </ul>

            @if($client->balance < 100)
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong>⚠️ Low Balance Warning:</strong><br>
                Your current balance is low. Please top up to continue sending messages without interruption.
            </div>
            @endif

            <p style="margin-top: 30px;">We apologize for any inconvenience. Please try again or contact our support team for assistance.</p>
            <p>Best regards,<br><strong>The {{ config('app.name') }} Team</strong></p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>This is an automated notification about your payment attempt.</p>
        </div>
    </div>
</body>
</html>

