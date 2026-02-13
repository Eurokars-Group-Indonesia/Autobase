<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - AutoBase</title>
    
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
        .forgot-container {
            width: 100%;
            max-width: 450px;
            padding: 15px;
        }
        .forgot-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,.2);
            overflow: hidden;
        }
        .forgot-header {
            background: #002856;
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .forgot-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.8rem;
        }
        .forgot-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        .forgot-body {
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
        .btn-primary {
            background: #002856;
            border: none;
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
            color: white;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .forgot-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .back-to-login {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-to-login a {
            color: #002856;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        .back-to-login a:hover {
            color: #0b5ed7;
        }
        .info-text {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="forgot-header">
                <i class="bi bi-key forgot-icon"></i>
                <p style="font-size: 24px;"><strong>AutoBase</strong> Forgot Password</p>
            </div>
            <div class="forgot-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <p class="info-text">
                    <i class="bi bi-info-circle"></i> Enter your email address and we'll send you a link to reset your password
                </p>

                <form action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" required autofocus
                                   placeholder="Enter your email">
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
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
        <div class="text-center mt-3">
            <small class="text-muted">&copy; {{ date('Y') }} IT Team Eurokars Group Indonesia. All rights reserved.</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
