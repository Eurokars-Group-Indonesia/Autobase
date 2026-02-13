<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 - Unauthorized</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #002856;
            padding: 1rem;
        }

        .container {
            max-width: 56rem;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 2rem;
        }

        .content {
            text-align: center;
        }

        .illustration {
            position: relative;
            width: 100%;
            max-width: 32rem;
            height: 12rem;
            margin: 0 auto 2rem;
        }

        .illustration svg {
            width: 100%;
            height: 100%;
        }

        /* Animations */
        @keyframes shake {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-1px); }
        }

        @keyframes pulse {
            0%, 100% { 
                transform: scale(1);
                opacity: 0.2;
            }
            50% { 
                transform: scale(1.2);
                opacity: 0;
            }
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(1px); }
        }

        .barrier-arm {
            animation: shake 3s ease-in-out infinite;
        }

        .warning-sign {
            animation: pulse 2s ease-in-out infinite;
        }

        .warning-text {
            animation: blink 1.5s ease-in-out infinite;
        }

        .car-body {
            animation: bounce 2.5s ease-in-out infinite;
        }

        .headlight {
            animation: blink 3s ease-in-out infinite;
        }

        .error-code {
            font-size: 4rem;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 0.5rem;
        }

        .error-title {
            font-size: 1.875rem;
            font-weight: bold;
            color: #111827;
            margin-bottom: 1rem;
        }

        .message {
            max-width: 32rem;
            margin: 0 auto 1.5rem;
        }

        .message-primary {
            font-size: 1rem;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }

        .message-secondary {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            justify-content: center;
            align-items: center;
            margin-top: 1.5rem;
        }

        @media (min-width: 640px) {
            .actions {
                flex-direction: row;
            }
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
        }

        .btn-outline {
            background: white;
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }

        .btn-outline:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            transform: translateY(-2px);
        }

        @media (max-width: 640px) {
            .error-code {
                font-size: 3rem;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="content">
                <!-- Illustration: Car at Closed Gate -->
                <div class="illustration">
                    <svg viewBox="0 0 400 300">
                        <!-- Sky Background -->
                        <rect x="0" y="0" width="400" height="200" fill="#E0F2FE"/>
                        
                        <!-- Road -->
                        <rect x="0" y="200" width="400" height="100" fill="#6B7280"/>
                        <rect x="0" y="240" width="400" height="10" fill="#FCD34D" opacity="0.8"/>
                        
                        <!-- Closed Gate (Barrier) with animation -->
                        <g transform="translate(200, 120)">
                            <!-- Gate Post Left -->
                            <rect x="-120" y="0" width="15" height="80" fill="#DC2626"/>
                            <rect x="-120" y="0" width="15" height="15" fill="#991B1B"/>
                            
                            <!-- Gate Post Right -->
                            <rect x="105" y="0" width="15" height="80" fill="#DC2626"/>
                            <rect x="105" y="0" width="15" height="15" fill="#991B1B"/>
                            
                            <!-- Barrier Arm (Closed - Horizontal) with slight shake -->
                            <rect class="barrier-arm" x="-105" y="15" width="210" height="12" fill="#DC2626" rx="2"/>
                            <rect class="barrier-arm" x="-105" y="15" width="35" height="12" fill="#FFFFFF" rx="2"/>
                            <rect class="barrier-arm" x="-55" y="15" width="35" height="12" fill="#FFFFFF" rx="2"/>
                            <rect class="barrier-arm" x="-5" y="15" width="35" height="12" fill="#FFFFFF" rx="2"/>
                            <rect class="barrier-arm" x="45" y="15" width="35" height="12" fill="#FFFFFF" rx="2"/>
                            
                            <!-- Warning Sign with pulse -->
                            <circle cx="0" cy="50" r="20" fill="#FEF3C7" stroke="#F59E0B" stroke-width="3"/>
                            <circle class="warning-sign" cx="0" cy="50" r="25" fill="#F59E0B" opacity="0.2"/>
                            <text class="warning-text" x="0" y="58" text-anchor="middle" font-size="24" font-weight="bold" fill="#DC2626">!</text>
                        </g>
                        
                        <!-- Car with animation -->
                        <g transform="translate(80, 180)">
                            <!-- Car Body -->
                            <rect class="car-body" x="0" y="20" width="100" height="35" fill="#3B82F6" rx="5"/>
                            <rect class="car-body" x="10" y="0" width="80" height="25" fill="#60A5FA" rx="5"/>
                            
                            <!-- Windows -->
                            <rect class="car-body" x="15" y="5" width="30" height="15" fill="#DBEAFE" rx="2"/>
                            <rect class="car-body" x="55" y="5" width="30" height="15" fill="#DBEAFE" rx="2"/>
                            
                            <!-- Wheels -->
                            <circle class="car-body" cx="20" cy="55" r="10" fill="#1F2937"/>
                            <circle class="car-body" cx="20" cy="55" r="5" fill="#6B7280"/>
                            <circle class="car-body" cx="80" cy="55" r="10" fill="#1F2937"/>
                            <circle class="car-body" cx="80" cy="55" r="5" fill="#6B7280"/>
                            
                            <!-- Headlights with blink -->
                            <circle class="car-body headlight" cx="5" cy="35" r="3" fill="#FCD34D"/>
                            <circle class="car-body headlight" cx="95" cy="35" r="3" fill="#FEF3C7"/>
                        </g>
                        
                        <!-- Sad Face on Car with animation -->
                        <g transform="translate(130, 195)">
                            <text class="car-body" x="0" y="0" font-size="20">😔</text>
                        </g>
                    </svg>
                </div>

                <!-- Error Code -->
                <div>
                    <h1 class="error-code">401</h1>
                    <h2 class="error-title">Access Denied</h2>
                </div>

                <!-- Message -->
                <div class="message">
                    <p class="message-primary">
                        Sorry, you need to authenticate to access this area.
                    </p>
                    <p class="message-secondary">
                        The gate is closed. Please log in with valid credentials to proceed.
                    </p>
                </div>

                <!-- Actions -->
                <div class="actions">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Go to Login
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-outline">
                        <i class="bi bi-house"></i>
                        Go Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
