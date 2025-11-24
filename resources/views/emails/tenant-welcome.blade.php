<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to BulkSMS CRM</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .email-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .email-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        
        .email-body {
            padding: 2rem;
        }
        
        .welcome-section {
            margin-bottom: 2rem;
        }
        
        .welcome-section h2 {
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }
        
        .credentials-box {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .credentials-box h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .credential-item:last-child {
            border-bottom: none;
        }
        
        .credential-label {
            font-weight: 600;
            color: #555;
        }
        
        .credential-value {
            font-family: 'Courier New', monospace;
            background: #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
            word-break: break-all;
        }
        
        .action-buttons {
            text-align: center;
            margin: 2rem 0;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }
        
        .next-steps {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-left: 4px solid #2196f3;
        }
        
        .next-steps h3 {
            color: #1976d2;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .step-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .step-list li {
            padding: 0.5rem 0;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .step-number {
            width: 20px;
            height: 20px;
            background: #1976d2;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .contact-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: center;
        }
        
        .contact-info h3 {
            color: #333;
            margin-bottom: 1rem;
        }
        
        .contact-info a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .contact-info a:hover {
            text-decoration: underline;
        }
        
        .email-footer {
            background: #f8f9fa;
            padding: 1.5rem 2rem;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .email-footer p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            
            .email-header,
            .email-body,
            .email-footer {
                padding: 1.5rem;
            }
            
            .credential-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .credential-value {
                width: 100%;
                word-break: break-all;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🎉 Welcome to BulkSMS CRM!</h1>
            <p>Your business account has been successfully created</p>
        </div>
        
        <div class="email-body">
            <div class="welcome-section">
                <h2>Hello {{ $user->name }}!</h2>
                <p>
                    Thank you for registering <strong>{{ $client->company_name }}</strong> with BulkSMS CRM. 
                    We're excited to help you streamline your communication with customers.
                </p>
                <p>
                    Your account is currently in <strong>pending approval</strong> status. Our team will review 
                    your registration and approve your account within 24 hours.
                </p>
            </div>
            
            <div class="credentials-box">
                <h3>🔑 Your Account Credentials</h3>
                <div class="credential-item">
                    <span class="credential-label">Company Name:</span>
                    <span class="credential-value">{{ $client->company_name }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Sender ID:</span>
                    <span class="credential-value">{{ $client->sender_id }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">API Key:</span>
                    <span class="credential-value">{{ $client->api_key }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Login Email:</span>
                    <span class="credential-value">{{ $user->email }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Account Tier:</span>
                    <span class="credential-value">{{ ucfirst($client->tier) }}</span>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="{{ $loginUrl }}" class="btn">Sign In to Your Account</a>
                <a href="{{ $apiDocsUrl }}" class="btn btn-secondary">View API Documentation</a>
            </div>
            
            <div class="next-steps">
                <h3>📋 What Happens Next?</h3>
                <ul class="step-list">
                    <li>
                        <div class="step-number">1</div>
                        <div>Our team will review your registration details</div>
                    </li>
                    <li>
                        <div class="step-number">2</div>
                        <div>You'll receive an approval email within 24 hours</div>
                    </li>
                    <li>
                        <div class="step-number">3</div>
                        <div>Start sending SMS, WhatsApp, and Email messages</div>
                    </li>
                    <li>
                        <div class="step-number">4</div>
                        <div>Access your dashboard and manage campaigns</div>
                    </li>
                </ul>
            </div>
            
            <div class="contact-info">
                <h3>🆘 Need Help?</h3>
                <p>
                    Our support team is here to help you get started. Contact us at:<br>
                    <a href="mailto:mathiasodhis@gmail.com">mathiasodhis@gmail.com</a> | 
                    <a href="tel:+254728883160">+254 728 883 160</a>
                </p>
            </div>
        </div>
        
        <div class="email-footer">
            <p>
                This email was sent to {{ $user->email }} because you registered for BulkSMS CRM.<br>
                <a href="#">Unsubscribe</a> | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
            </p>
        </div>
    </div>
</body>
</html>


