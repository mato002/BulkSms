<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Message</title>
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
        .reply-card {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .reply-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        .reply-body {
            padding: 30px;
        }
        .original-message {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 5px;
        }
        .btn-send {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="reply-card">
        <div class="reply-header">
            <i class="bi bi-chat-dots-fill" style="font-size: 3rem; margin-bottom: 15px;"></i>
            <h2 class="mb-0">Reply to Message</h2>
            <p class="mb-0 mt-2 opacity-75">Send your response</p>
        </div>
        
        <div class="reply-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <div class="original-message">
                <small class="text-muted d-block mb-2">
                    <i class="bi bi-envelope"></i> Original Message:
                </small>
                <p class="mb-0">{{ $message->body }}</p>
                <small class="text-muted d-block mt-2">
                    <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($message->created_at)->diffForHumans() }}
                </small>
            </div>

            <form action="{{ route('public.reply.submit', $token) }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="reply" class="form-label fw-bold">
                        <i class="bi bi-pencil-square"></i> Your Reply
                    </label>
                    <textarea 
                        name="reply" 
                        id="reply" 
                        class="form-control @error('reply') is-invalid @enderror" 
                        rows="5" 
                        placeholder="Type your message here..."
                        required
                        autofocus
                    ></textarea>
                    @error('reply')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        <i class="bi bi-info-circle"></i> Maximum 1000 characters
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-send btn-lg">
                        <i class="bi bi-send-fill me-2"></i> Send Reply
                    </button>
                </div>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="bi bi-shield-check"></i> Your reply will be securely delivered
                    </small>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

