<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Tenant Registration - Approval Required</title>
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
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
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
        
        .alert-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            border-left: 4px solid #f39c12;
        }
        
        .alert-section h3 {
            color: #d68910;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        
        .tenant-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .tenant-info h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
        }
        
        .info-value {
            color: #333;
            font-weight: 500;
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
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }
        
        .btn-danger:hover {
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }
        
        .approval-checklist {
            background: #e8f5e8;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-left: 4px solid #28a745;
        }
        
        .approval-checklist h3 {
            color: #155724;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .checklist-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.5rem 0;
        }
        
        .checklist-icon {
            width: 20px;
            height: 20px;
            background: #28a745;
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
            
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🚨 New Tenant Registration</h1>
            <p>Approval Required - Action Needed</p>
        </div>
        
        <div class="email-body">
            <div class="alert-section">
                <h3>⚠️ Immediate Action Required</h3>
                <p>
                    A new business has registered for BulkSMS CRM and requires your approval. 
                    Please review the details below and approve or reject the registration.
                </p>
            </div>
            
            <div class="tenant-info">
                <h3>📋 Registration Details</h3>
                <div class="info-item">
                    <span class="info-label">Company Name:</span>
                    <span class="info-value">{{ $client->company_name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Contact Person:</span>
                    <span class="info-value">{{ $user->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email Address:</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone Number:</span>
                    <span class="info-value">{{ $client->settings['phone'] ?? 'Not provided' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Business Type:</span>
                    <span class="info-value">{{ ucfirst($client->settings['business_type'] ?? 'Not specified') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Expected Volume:</span>
                    <span class="info-value">{{ ucfirst($client->tier) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Sender ID:</span>
                    <span class="info-value">{{ $client->sender_id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Registration Date:</span>
                    <span class="info-value">{{ $client->settings['registration_date'] ?? $client->created_at->format('Y-m-d') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">API Key:</span>
                    <span class="info-value">{{ $client->api_key }}</span>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="{{ $approvalUrl }}" class="btn">Review & Approve</a>
                <a href="{{ $approvalUrl }}" class="btn btn-danger">View Details</a>
            </div>
            
            <div class="approval-checklist">
                <h3>✅ Approval Checklist</h3>
                <div class="checklist-item">
                    <div class="checklist-icon">1</div>
                    <div>Verify business legitimacy and contact information</div>
                </div>
                <div class="checklist-item">
                    <div class="checklist-icon">2</div>
                    <div>Check if sender ID conflicts with existing clients</div>
                </div>
                <div class="checklist-item">
                    <div class="checklist-icon">3</div>
                    <div>Review expected volume and pricing tier</div>
                </div>
                <div class="checklist-item">
                    <div class="checklist-icon">4</div>
                    <div>Ensure compliance with SMS regulations</div>
                </div>
                <div class="checklist-item">
                    <div class="checklist-icon">5</div>
                    <div>Activate account and send welcome email</div>
                </div>
            </div>
            
            <div class="contact-info">
                <h3>📞 Need Assistance?</h3>
                <p>
                    If you have questions about this registration, contact the support team at:<br>
                    <a href="mailto:mathiasodhis@gmail.com">mathiasodhis@gmail.com</a> | 
                    <a href="tel:+254728883160">+254 728 883 160</a>
                </p>
            </div>
        </div>
        
        <div class="email-footer">
            <p>
                This is an automated notification from BulkSMS CRM Admin System.<br>
                Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>


