<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Successful - BulkSMS CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        .success-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        
        .success-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        
        .success-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 3rem 2rem;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        .success-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .success-header p {
            opacity: 0.9;
            margin: 0;
            font-size: 1.1rem;
        }
        
        .success-body {
            padding: 2rem;
        }
        
        .next-steps {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .next-steps h5 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: white;
            border-radius: 6px;
            border-left: 4px solid #667eea;
        }
        
        .step-item:last-child {
            margin-bottom: 0;
        }
        
        .step-number {
            width: 24px;
            height: 24px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .step-content h6 {
            margin: 0 0 0.25rem 0;
            color: #333;
            font-weight: 600;
        }
        
        .step-content p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            color: white;
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
        
        .contact-info {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 2rem;
            border-left: 4px solid #2196f3;
        }
        
        .contact-info h6 {
            color: #1976d2;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .contact-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .contact-info a {
            color: #1976d2;
            text-decoration: none;
            font-weight: 500;
        }
        
        .contact-info a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .success-container {
                padding: 1rem;
            }
            
            .success-header {
                padding: 2rem 1.5rem;
            }
            
            .success-header h1 {
                font-size: 1.5rem;
            }
            
            .success-body {
                padding: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Registration Successful!</h1>
                <p>Welcome to BulkSMS CRM. Your business account has been created.</p>
            </div>
            
            <div class="success-body">
                <div class="next-steps">
                    <h5><i class="fas fa-list-check"></i> What Happens Next?</h5>
                    
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h6>Check Your Email</h6>
                            <p>We've sent you a welcome email with your account details and API credentials.</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h6>Account Review</h6>
                            <p>Our team will review your registration and approve your account within 24 hours.</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h6>Start Sending</h6>
                            <p>Once approved, you can start sending SMS, WhatsApp, and Email messages immediately.</p>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In to Your Account
                    </a>
                    <a href="{{ route('api.documentation') }}" class="btn btn-secondary">
                        <i class="fas fa-book"></i>
                        View API Documentation
                    </a>
                </div>
                
                <div class="contact-info">
                    <h6><i class="fas fa-headset"></i> Need Help?</h6>
                    <p>
                        If you have any questions or need assistance, contact our support team at 
                        <a href="mailto:mathiasodhis@gmail.com">mathiasodhis@gmail.com</a> or 
                        <a href="tel:+254728883160">+254 728 883 160</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


