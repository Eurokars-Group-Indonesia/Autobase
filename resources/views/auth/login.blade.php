<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - AutoBase</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 15px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,.2);
            overflow: hidden;
        }
        .login-header {
            background: #002856;
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .login-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.8rem;
        }
        .login-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        .login-body {
            padding: 2.5rem 2rem;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #002856;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        .btn-login {
            background: #002856;
            border: none;
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
            color: white;
        }
        .form-check-input:checked {
            background-color: #002856;
            border-color: #002856;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .login-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-shield-lock login-icon"></i>
                <p style="font-size: 24px;"><strong>AutoBase</strong> Login</p>
            </div>
            <div class="login-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="text-center mb-4">
                    <p class="text-muted">Sign in with your Microsoft account to continue</p>
                </div>

                <a href="{{ route('auth.azure') }}" class="btn btn-primary w-100" style="border-radius: 10px; padding: 0.75rem; font-weight: 600; background: #0078d4; border: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-microsoft" viewBox="0 0 16 16" style="margin-right: 8px;">
                        <path d="M7.462 0H0v7.19h7.462zM16 0H8.538v7.19H16zM7.462 8.211H0V16h7.462zm8.538 0H8.538V16H16z"/>
                    </svg>
                    Sign in with Microsoft
                </a>

                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="bi bi-shield-check"></i>
                        Secure authentication via Microsoft Azure AD
                    </small>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <small class="text-muted">&copy; {{ date('Y') }} IT Team Eurokars Group Indonesia. All rights reserved.</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
