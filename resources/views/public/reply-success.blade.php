<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply Sent Successfully</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .success-card {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 50px;
            text-align: center;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
        .checkmark {
            font-size: 3rem;
            color: white;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">
            <i class="bi bi-check-lg checkmark"></i>
        </div>
        
        <h2 class="mb-3">Reply Sent Successfully!</h2>
        
        <p class="text-muted mb-4">
            Your message has been delivered to our team. We'll get back to you soon.
        </p>
        
        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill me-2"></i>
            You can close this window now.
        </div>

        <small class="text-muted d-block mt-4">
            <i class="bi bi-shield-check"></i> Thank you for using our service
        </small>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

