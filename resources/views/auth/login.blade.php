<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Bulk SMS CRM</title>
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
            padding-right: 48px;
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

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 18px;
            z-index: 2;
            padding: 4px;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .form-check {
            margin: 0;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid #cbd5e1;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-check-label {
            font-size: 14px;
            color: #475569;
            cursor: pointer;
            margin-left: 4px;
        }

        .forgot-link {
            font-size: 14px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .forgot-link:hover {
            color: #764ba2;
        }

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-spinner {
            display: none;
            margin-left: 8px;
        }

        .btn-login.loading .btn-text {
            opacity: 0;
        }

        .btn-login.loading .btn-spinner {
            display: inline-block;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 30px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider span {
            padding: 0 15px;
            color: #94a3b8;
            font-size: 14px;
        }

        .login-footer-note {
            text-align: center;
            margin-top: 24px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .login-footer-note p {
            margin: 0;
            font-size: 14px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .login-footer-note i {
            color: #667eea;
            font-size: 16px;
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

        .alert ul {
            margin: 0;
            padding-left: 20px;
            list-style: none;
        }

        .alert ul li {
            margin-bottom: 4px;
        }

        .alert ul li:last-child {
            margin-bottom: 0;
        }

        .alert ul li::before {
            content: "•";
            margin-right: 8px;
        }

        /* Social Login Buttons (optional) */
        .social-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .btn-social {
            height: 48px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            color: #475569;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-social:hover {
            border-color: #667eea;
            background: #f8fafc;
            transform: translateY(-2px);
        }

        .btn-social i {
            font-size: 18px;
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

        /* Loading spinner */
        .spinner-border-sm {
            width: 18px;
            height: 18px;
            border-width: 2px;
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
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
                <h2>Bulk SMS CRM</h2>
                <p>Enterprise Messaging Platform</p>
            </div>

            <div class="login-body">
                <div class="welcome-text">
                    <h5>Welcome Back!</h5>
                    <p>Sign in to continue to your account</p>
                </div>

                @if(session('status'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <div>
                            @if($errors->count() == 1)
                                {{ $errors->first() }}
                            @else
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    
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
                                placeholder="Enter your email"
                                required 
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group-custom">
                            <i class="bi bi-lock input-icon"></i>
                            <input 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                placeholder="Enter your password"
                                required
                            >
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="remember-forgot">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="{{ url('/forgot-password') }}" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-login" id="loginBtn">
                        <span class="btn-text">Sign In</span>
                        <div class="spinner-border spinner-border-sm btn-spinner" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </button>
                </form>

                <div class="login-footer-note">
                    <p><i class="bi bi-info-circle"></i> Need access? Contact your administrator.</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <p style="color: rgba(255, 255, 255, 0.8); font-size: 14px;">
                &copy; {{ date('Y') }} Bulk SMS CRM. All rights reserved.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password Toggle Functionality
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle eye icon
            if (type === 'password') {
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            } else {
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            }
        });

        // Form Submit Loading State
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');

        loginForm.addEventListener('submit', function() {
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
        });

        // Input Focus Animation
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.01)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Auto-hide error messages after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
    </script>
</body>
</html>

