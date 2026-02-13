<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
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
        @keyframes swing {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-5deg); }
            75% { transform: rotate(5deg); }
        }

        @keyframes pulse-opacity {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(1px); }
        }

        @keyframes float {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(5px); }
        }

        @keyframes float-reverse {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(-5px); }
        }

        @keyframes tilt {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }

        .sign-board {
            animation: swing 3s ease-in-out infinite;
            transform-origin: center;
        }

        .question-mark {
            animation: pulse-opacity 2s ease-in-out infinite, swing 3s ease-in-out infinite;
            transform-origin: center;
        }

        .car-body {
            animation: bounce 2.5s ease-in-out infinite;
        }

        .wheel {
            animation: bounce 2.5s ease-in-out infinite;
        }

        .emoji {
            animation: bounce 2.5s ease-in-out infinite, tilt 4s ease-in-out infinite;
        }

        .cloud-1 {
            animation: float 8s ease-in-out infinite;
        }

        .cloud-2 {
            animation: float-reverse 10s ease-in-out infinite;
        }

        .error-code {
            font-size: 4rem;
            font-weight: bold;
            color: #4f46e5;
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
                <!-- Illustration: Lost Car -->
                <div class="illustration">
                    <svg viewBox="0 0 400 300">
                        <!-- Sky Background -->
                        <rect x="0" y="0" width="400" height="200" fill="#DBEAFE"/>
                        
                        <!-- Road -->
                        <rect x="0" y="200" width="400" height="100" fill="#6B7280"/>
                        
                        <!-- Road Markings (broken) -->
                        <rect x="50" y="240" width="40" height="8" fill="#FCD34D" opacity="0.8"/>
                        <rect x="120" y="240" width="40" height="8" fill="#FCD34D" opacity="0.8"/>
                        <rect x="190" y="240" width="40" height="8" fill="#FCD34D" opacity="0.8"/>
                        <rect x="260" y="240" width="40" height="8" fill="#FCD34D" opacity="0.8"/>
                        <rect x="330" y="240" width="40" height="8" fill="#FCD34D" opacity="0.8"/>
                        
                        <!-- Question Mark Sign with animation -->
                        <g transform="translate(280, 130)">
                            <!-- Sign Pole -->
                            <rect x="-5" y="40" width="10" height="70" fill="#6B7280"/>
                            
                            <!-- Sign Board with rotation -->
                            <rect class="sign-board" x="-35" y="0" width="70" height="70" fill="#3B82F6" rx="5"/>
                            <rect class="sign-board" x="-32" y="3" width="64" height="64" fill="#60A5FA" rx="4"/>
                            
                            <!-- Question Mark with pulse -->
                            <text class="question-mark" x="0" y="50" text-anchor="middle" font-size="48" font-weight="bold" fill="#FFFFFF">?</text>
                        </g>
                        
                        <!-- Lost Car with animation -->
                        <g transform="translate(100, 180)">
                            <!-- Car Body -->
                            <rect class="car-body" x="0" y="20" width="100" height="35" fill="#8B5CF6" rx="5"/>
                            <rect class="car-body" x="10" y="0" width="80" height="25" fill="#A78BFA" rx="5"/>
                            
                            <!-- Windows -->
                            <rect class="car-body" x="15" y="5" width="30" height="15" fill="#EDE9FE" rx="2"/>
                            <rect class="car-body" x="55" y="5" width="30" height="15" fill="#EDE9FE" rx="2"/>
                            
                            <!-- Headlights -->
                            <circle class="car-body" cx="5" cy="35" r="3" fill="#FCD34D"/>
                            <circle class="car-body" cx="95" cy="35" r="3" fill="#FEF3C7"/>
                            
                            <!-- Wheels (Below car body) with rotation -->
                            <g class="wheel">
                                <circle cx="20" cy="55" r="10" fill="#1F2937"/>
                                <circle cx="20" cy="55" r="5" fill="#6B7280"/>
                            </g>
                            <g class="wheel">
                                <circle cx="80" cy="55" r="10" fill="#1F2937"/>
                                <circle cx="80" cy="55" r="5" fill="#6B7280"/>
                            </g>
                        </g>
                        
                        <!-- Confused Face on Car with animation -->
                        <g transform="translate(150, 195)">
                            <text class="emoji" x="0" y="0" font-size="20">🤔</text>
                        </g>
                        
                        <!-- Clouds with animation -->
                        <g class="cloud-1">
                            <ellipse cx="80" cy="50" rx="30" ry="20" fill="#FFFFFF" opacity="0.7"/>
                            <ellipse cx="100" cy="50" rx="25" ry="18" fill="#FFFFFF" opacity="0.7"/>
                        </g>
                        <g class="cloud-2">
                            <ellipse cx="320" cy="70" rx="35" ry="22" fill="#FFFFFF" opacity="0.7"/>
                            <ellipse cx="345" cy="70" rx="28" ry="20" fill="#FFFFFF" opacity="0.7"/>
                        </g>
                    </svg>
                </div>

                <!-- Error Code -->
                <div>
                    <h1 class="error-code">404</h1>
                    <h2 class="error-title">Page Not Found</h2>
                </div>

                <!-- Message -->
                <div class="message">
                    <p class="message-primary">
                        Oops! Looks like you took a wrong turn.
                    </p>
                    <p class="message-secondary">
                        The page you're looking for doesn't exist or has been moved to another location.
                    </p>
                </div>

                <!-- Actions -->
                <div class="actions">
                    <button onclick="window.history.back()" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i>
                        Go Back
                    </button>
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
