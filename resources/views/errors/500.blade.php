<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
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
        @keyframes smoke-rise {
            0% {
                transform: translateY(0);
                opacity: 0.6;
            }
            100% {
                transform: translateY(-10px);
                opacity: 0;
            }
        }

        .smoke-1 {
            animation: smoke-rise 2s ease-in-out infinite;
        }

        .smoke-2 {
            animation: smoke-rise 2.5s ease-in-out infinite;
        }

        .smoke-3 {
            animation: smoke-rise 3s ease-in-out infinite;
        }

        .error-code {
            font-size: 4rem;
            font-weight: bold;
            color: #64748b;
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
                <!-- Illustration: Broken Down Car -->
                <div class="illustration">
                    <svg viewBox="0 0 400 300">
                        <!-- Sky Background -->
                        <rect x="0" y="0" width="400" height="200" fill="#F1F5F9"/>
                        
                        <!-- Road -->
                        <rect x="0" y="200" width="400" height="100" fill="#6B7280"/>
                        <rect x="0" y="240" width="400" height="10" fill="#FCD34D" opacity="0.8"/>
                        
                        <!-- Broken Car -->
                        <g transform="translate(120, 180)">
                            <!-- Car Body (tilted/broken) -->
                            <rect x="0" y="20" width="100" height="35" fill="#64748B" rx="5"/>
                            <rect x="10" y="0" width="80" height="25" fill="#94A3B8" rx="5"/>
                            
                            <!-- Windows (cracked) -->
                            <rect x="15" y="5" width="30" height="15" fill="#CBD5E1" rx="2"/>
                            <line x1="20" y1="5" x2="40" y2="20" stroke="#475569" stroke-width="1"/>
                            <rect x="55" y="5" width="30" height="15" fill="#CBD5E1" rx="2"/>
                            <line x1="60" y1="5" x2="80" y2="20" stroke="#475569" stroke-width="1"/>
                            
                            <!-- Wheels (flat) -->
                            <ellipse cx="20" cy="58" rx="10" ry="6" fill="#1F2937"/>
                            <ellipse cx="80" cy="58" rx="10" ry="6" fill="#1F2937"/>
                            
                            <!-- Smoke from Engine -->
                            <g opacity="0.6">
                                <ellipse class="smoke-1" cx="10" cy="-5" rx="15" ry="10" fill="#94A3B8"/>
                                <ellipse class="smoke-2" cx="20" cy="-10" rx="12" ry="8" fill="#94A3B8"/>
                                <ellipse class="smoke-3" cx="5" cy="-12" rx="10" ry="7" fill="#94A3B8"/>
                            </g>
                            
                            <!-- Hood Open -->
                            <rect x="0" y="15" width="25" height="8" fill="#475569" rx="2" transform="rotate(-20 12 19)"/>
                        </g>
                        
                        <!-- Sad/Broken Face on Car -->
                        <g transform="translate(170, 195)">
                            <text x="0" y="0" font-size="20">😵</text>
                        </g>
                        
                        <!-- Tool Box -->
                        <g transform="translate(60, 220)">
                            <rect x="0" y="0" width="40" height="25" fill="#DC2626" rx="2"/>
                            <rect x="5" y="5" width="30" height="3" fill="#FFFFFF"/>
                            <circle cx="15" cy="15" r="3" fill="#FFFFFF"/>
                            <circle cx="25" cy="15" r="3" fill="#FFFFFF"/>
                        </g>
                        
                        <!-- Wrench -->
                        <g transform="translate(280, 215) rotate(45)">
                            <rect x="0" y="0" width="8" height="35" fill="#94A3B8" rx="2"/>
                            <circle cx="4" cy="0" r="6" fill="#94A3B8"/>
                            <circle cx="4" cy="0" r="3" fill="#475569"/>
                        </g>
                        
                        <!-- Warning Triangle -->
                        <g transform="translate(300, 200)">
                            <polygon points="0,-30 26,10 -26,10" fill="#FCD34D" stroke="#F59E0B" stroke-width="3"/>
                            <text x="0" y="5" text-anchor="middle" font-size="24" font-weight="bold" fill="#DC2626">!</text>
                        </g>
                    </svg>
                </div>

                <!-- Error Code -->
                <div>
                    <h1 class="error-code">500</h1>
                    <h2 class="error-title">Internal Server Error</h2>
                </div>

                <!-- Message -->
                <div class="message">
                    <p class="message-primary">
                        Oops! Our engine broke down.
                    </p>
                    <p class="message-secondary">
                        Something went wrong on our end. Our team has been notified and is working to fix the issue.
                    </p>
                </div>

                <!-- Actions -->
                <div class="actions">
                    <button onclick="window.location.reload()" class="btn btn-primary">
                        <i class="bi bi-arrow-clockwise"></i>
                        Refresh Page
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
