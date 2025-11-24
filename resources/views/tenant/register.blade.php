<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register Your Business - BulkSMS CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        .registration-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        
        .registration-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 780px;
            width: 100%;
        }
        
        .registration-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            text-align: center;
        }
        
        .registration-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .registration-header p {
            opacity: 0.9;
            margin: 0;
        }
        
        .registration-body {
            padding: 1.25rem 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 1.5px solid #e9ecef;
            border-radius: 6px;
            padding: 0.6rem 0.85rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .btn-register:disabled {
            opacity: 0.6;
            transform: none;
        }
        
        .pricing-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .pricing-info h6 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .pricing-info small {
            color: #666;
        }
        
        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .terms-checkbox input[type="checkbox"] {
            margin-top: 0.25rem;
        }
        
        .terms-checkbox label {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.4;
        }
        
        .terms-checkbox a {
            color: #667eea;
            text-decoration: none;
        }
        
        .terms-checkbox a:hover {
            text-decoration: underline;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .back-to-home {
            position: absolute;
            top: 1rem;
            left: 1rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-to-home:hover {
            color: rgba(255,255,255,0.8);
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #999;
            font-size: 0.9rem;
        }
        
        .step.active {
            color: #667eea;
            font-weight: 600;
        }
        
        .step-number {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        
        .step-separator {
            width: 40px;
            height: 2px;
            background: #e9ecef;
            margin: 0 1rem;
        }
        
        .step.active + .step-separator {
            background: #667eea;
        }
        
        @media (max-width: 768px) {
            .registration-container {
                padding: 0.75rem;
            }
            
            .registration-header {
                padding: 1rem 1.25rem;
            }
            
            .registration-header h1 {
                font-size: 1.25rem;
            }
            
            .registration-body {
                padding: 1rem 1.25rem;
            }
            
            .back-to-home {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 0.5rem;
                color: #667eea;
            }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-card">
            <div class="registration-header">
                <a href="{{ route('home') }}" class="back-to-home">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
                <h1><i class="fas fa-building"></i> Register Your Business</h1>
                <p>Join thousands of businesses using our multi-channel messaging platform</p>
            </div>
            
            <div class="registration-body">
                <div class="step-indicator">
                    <div class="step active">
                        <div class="step-number">1</div>
                        <span>Business Info</span>
                    </div>
                    <div class="step-separator"></div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <span>Review & Submit</span>
                    </div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle"></i> Please correct the following errors:</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('tenant.register.submit') }}" id="registrationForm">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_name" class="form-label">
                                    <i class="fas fa-building"></i> Company Name *
                                </label>
                                <input type="text" class="form-control" id="company_name" name="company_name" 
                                       value="{{ old('company_name') }}" required autofocus>
                                <small class="text-muted">This will be your sender ID for SMS</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact_person" class="form-label">
                                    <i class="fas fa-user"></i> Contact Person *
                                </label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                       value="{{ old('contact_person') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email Address *
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="{{ old('email') }}" required>
                                <small class="text-muted">This will be your login email</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone"></i> Phone Number *
                                </label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="{{ old('phone') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="business_type" class="form-label">
                                    <i class="fas fa-industry"></i> Business Type *
                                </label>
                                <select class="form-control" id="business_type" name="business_type" required>
                                    <option value="">Select Business Type</option>
                                    <option value="technology" {{ old('business_type') == 'technology' ? 'selected' : '' }}>Technology</option>
                                    <option value="healthcare" {{ old('business_type') == 'healthcare' ? 'selected' : '' }}>Healthcare</option>
                                    <option value="finance" {{ old('business_type') == 'finance' ? 'selected' : '' }}>Finance</option>
                                    <option value="retail" {{ old('business_type') == 'retail' ? 'selected' : '' }}>Retail</option>
                                    <option value="education" {{ old('business_type') == 'education' ? 'selected' : '' }}>Education</option>
                                    <option value="nonprofit" {{ old('business_type') == 'nonprofit' ? 'selected' : '' }}>Non-Profit</option>
                                    <option value="government" {{ old('business_type') == 'government' ? 'selected' : '' }}>Government</option>
                                    <option value="other" {{ old('business_type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expected_volume" class="form-label">
                                    <i class="fas fa-chart-line"></i> Expected Monthly Volume *
                                </label>
                                <select class="form-control" id="expected_volume" name="expected_volume" required>
                                    <option value="">Select Volume</option>
                                    <option value="low" {{ old('expected_volume') == 'low' ? 'selected' : '' }}>Low (< 1,000 messages)</option>
                                    <option value="medium" {{ old('expected_volume') == 'medium' ? 'selected' : '' }}>Medium (1,000 - 10,000 messages)</option>
                                    <option value="high" {{ old('expected_volume') == 'high' ? 'selected' : '' }}>High (10,000 - 100,000 messages)</option>
                                    <option value="enterprise" {{ old('expected_volume') == 'enterprise' ? 'selected' : '' }}>Enterprise (> 100,000 messages)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pricing-info">
                        <h6><i class="fas fa-info-circle"></i> Pricing Information</h6>
                        <small>
                            <strong>Low Volume:</strong> KES 2.50 per SMS | 
                            <strong>Medium Volume:</strong> KES 2.00 per SMS | 
                            <strong>High Volume:</strong> KES 1.50 per SMS | 
                            <strong>Enterprise:</strong> KES 1.00 per SMS
                        </small>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password *
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock"></i> Confirm Password *
                                </label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="terms-checkbox">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>. 
                            I understand that my account will be reviewed and approved within 24 hours.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-register" id="submitBtn">
                        <i class="fas fa-rocket"></i> Register My Business
                    </button>
                </form>
                
                <div class="login-link">
                    Already have an account? <a href="{{ route('login') }}">Sign in here</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form validation and UX enhancements
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
        });
        
        // Real-time pricing update
        document.getElementById('expected_volume').addEventListener('change', function() {
            const volume = this.value;
            const pricingInfo = document.querySelector('.pricing-info small');
            
            const prices = {
                'low': 'KES 2.50 per SMS',
                'medium': 'KES 2.00 per SMS', 
                'high': 'KES 1.50 per SMS',
                'enterprise': 'KES 1.00 per SMS'
            };
            
            if (volume && prices[volume]) {
                pricingInfo.innerHTML = `<strong>Selected Pricing:</strong> ${prices[volume]} | <strong>Volume:</strong> ${this.options[this.selectedIndex].text}`;
            }
        });
        
        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('254')) {
                value = '+' + value;
            } else if (value.startsWith('0')) {
                value = '+254' + value.substring(1);
            } else if (value && !value.startsWith('+')) {
                value = '+254' + value;
            }
            e.target.value = value;
        });
    </script>
</body>
</html>


