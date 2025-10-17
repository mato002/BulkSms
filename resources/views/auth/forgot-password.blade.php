<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - Bulk SMS CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        .bg-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -100px;
            right: -100px;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 50%;
            right: 10%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 40px 30px;
            text-align: center;
            color: white;
        }

        .logo-circle {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .logo-circle i {
            font-size: 40px;
            color: white;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .login-header p {
            font-size: 15px;
            opacity: 0.9;
            margin: 0;
        }

        .login-body {
            padding: 40px;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome-text h5 {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .welcome-text p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .input-group-custom {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            z-index: 2;
        }

        .form-control {
            height: 52px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding-left: 48px;
            padding-right: 16px;
            font-size: 15px;
            transition: all 0.3s;
            background: #f8fafc;
        }

        .form-control:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: #ef4444;
            background: #fef2f2;
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .btn-submit {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .back-to-login {
            text-align: center;
            margin-top: 24px;
        }

        .back-to-login a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .back-to-login a:hover {
            color: #764ba2;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 14px 18px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
        }

        .alert i {
            font-size: 20px;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-body {
                padding: 30px 24px;
            }

            .login-header {
                padding: 30px 24px 24px;
            }

            .logo-circle {
                width: 70px;
                height: 70px;
            }

            .logo-circle i {
                font-size: 35px;
            }

            .login-header h2 {
                font-size: 24px;
            }

            .welcome-text h5 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-circle">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h2>Forgot Password?</h2>
                <p>No worries, we'll send you reset instructions</p>
            </div>

            <div class="login-body">
                @if(session('status'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                <form id="forgotPasswordForm">
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group-custom">
                            <i class="bi bi-envelope input-icon"></i>
                            <input 
                                type="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                placeholder="Enter your registered email"
                                required 
                                autofocus
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="bi bi-send"></i> Send Reset Link
                    </button>
                </form>

                <div class="back-to-login">
                    <a href="{{ route('login') }}">
                        <i class="bi bi-arrow-left"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <p style="color: rgba(255, 255, 255, 0.8); font-size: 14px;">
                &copy; {{ date('Y') }} BulkSms by Matech Technologies. All rights reserved.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
    <script>
        // Initialize EmailJS
        (function() {
            // Your EmailJS public key
            emailjs.init("RBLQC1t5BLQc0K7GC");
        })();

        // Auto-hide alert messages after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });

        // Check if EmailJS is loaded
        console.log('Script loaded successfully!');
        console.log('EmailJS loaded:', typeof emailjs !== 'undefined');
        
        // Test EmailJS connection
        if (typeof emailjs !== 'undefined') {
            console.log('EmailJS is available');
            console.log('EmailJS version:', emailjs.version);
        } else {
            console.error('EmailJS is NOT loaded!');
        }
        
        // Test function - call this from browser console: testEmailJS()
        window.testEmailJS = function() {
            console.log('Testing EmailJS...');
            const templateParams = {
                to_email: 'test@example.com',
                from_name: 'Bulk SMS CRM',
                subject: 'Test Email',
                message: 'This is a test email from EmailJS',
                reset_url: window.location.origin + '/reset-password-manual?email=test@example.com'
            };
            
            emailjs.send('service_3f1hams', 'template_qnuno0t', templateParams)
                .then(function(response) {
                    console.log('TEST SUCCESS!', response);
                }, function(error) {
                    console.log('TEST FAILED!', error);
                });
        };
        
        // Handle form submission
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const submitBtn = document.querySelector('.btn-submit');
            const originalText = submitBtn.innerHTML;
            
            console.log('Form submitted with email:', email);
            
            // Show loading state
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';
            submitBtn.disabled = true;
            
            // EmailJS template parameters
            const templateParams = {
                to_email: email,
                to_name: 'User',
                from_name: 'Bulk SMS CRM',
                subject: 'Password Reset Request',
                message: `Password reset requested for ${email}. Please contact your administrator for password reset.`,
                reset_url: window.location.origin + '/reset-password-manual?email=' + encodeURIComponent(email),
                user_email: email,
                reply_to: email
            };
            
            // Send email using EmailJS
            emailjs.send('service_3f1hams', 'template_qnuno0t', templateParams)
                .then(function(response) {
                    console.log('SUCCESS!', response.status, response.text);
                    console.log('Full response:', response);
                    
                    // Show success message
                    showAlert('success', '✅ Password reset request sent! Please check your email and click the reset link.');
                    
                    // Reset form
                    document.getElementById('forgotPasswordForm').reset();
                    
                }, function(error) {
                    console.log('FAILED...', error);
                    console.error('EmailJS Error:', error);
                    console.error('Error details:', JSON.stringify(error, null, 2));
                    
                    // Show detailed error message
                    let errorMsg = 'Failed to send reset request. ';
                    if (error.text) {
                        errorMsg += 'Error: ' + error.text;
                    } else if (error.message) {
                        errorMsg += 'Error: ' + error.message;
                    } else {
                        errorMsg += 'Please check your EmailJS configuration or try again.';
                    }
                    showAlert('danger', errorMsg);
                })
                .finally(function() {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });
        
        function showAlert(type, message) {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());
            
            // Create new alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
                <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'}"></i>
                <div>${message}</div>
            `;
            
            // Find the form and insert alert before it
            const form = document.getElementById('forgotPasswordForm');
            if (form) {
                form.parentNode.insertBefore(alertDiv, form);
            } else {
                // Fallback: insert at top of login-body
                const loginBody = document.querySelector('.login-body');
                if (loginBody) {
                    loginBody.insertBefore(alertDiv, loginBody.firstChild);
                }
            }
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertDiv.style.transition = 'opacity 0.5s';
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 500);
            }, 5000);
        }
    </script>
</body>
</html>

