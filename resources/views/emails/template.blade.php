<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .email-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 30px 20px;
        }
        .email-content {
            font-size: 16px;
            line-height: 1.8;
            white-space: pre-wrap;
        }
        .email-footer {
            background: #f8fafc;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #64748b;
        }
        .sender-info {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-header, .email-body {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>{{ $from_name }}</h1>
        </div>
        
        <div class="email-body">
            <div class="sender-info">
                <strong>From:</strong> {{ $from_name }} &lt;{{ $from_email }}&gt;<br>
                <strong>To:</strong> {{ $recipient }}
            </div>
            
            <div class="email-content">
                {!! nl2br(e($body)) !!}
            </div>
        </div>
        
        <div class="email-footer">
            <p>This email was sent via BulkSMS Platform</p>
            <p>© {{ date('Y') }} BulkSMS Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>



