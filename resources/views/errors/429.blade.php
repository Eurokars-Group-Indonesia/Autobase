<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 - Too Many Requests</title>
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
        @keyframes glow-red {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.5; }
        }

        @keyframes pulse-light {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 1; }
        }

        @keyframes bounce-car {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(2px); }
        }

        @keyframes smoke-rise {
            0% {
                transform: translateY(0);
                opacity: 0.5;
            }
            100% {
                transform: translateY(-10px);
                opacity: 0;
            }
        }

        @keyframes swing-sign {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-5deg); }
            75% { transform: rotate(5deg); }
        }

        .red-light-glow {
            animation: glow-red 1s ease-in-out infinite;
        }

        .red-light-pulse {
            animation: pulse-light 1s ease-in-out infinite;
        }

        .car-bounce {
            animation: bounce-car 2s ease-in-out infinite;
        }

        .smoke {
            animation: smoke-rise 3s ease-in-out infinite;
        }

        .smoke-2 {
            animation: smoke-rise 3.5s ease-in-out infinite;
        }

        .smoke-3 {
            animation: smoke-rise 4s ease-in-out infinite;
        }

        .warning-sign {
            animation: swing-sign 2s ease-in-out infinite;
            transform-origin: center;
        }

        .error-code {
            font-size: 4rem;
            font-weight: bold;
            color: #ea580c;
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

        .countdown-box {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #fed7aa;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .countdown-icon {
            width: 1.25rem;
            height: 1.25rem;
            color: #ea580c;
        }

        .countdown-text {
            font-size: 1rem;
            font-weight: 600;
            color: #c2410c;
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

        .btn-primary:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        .btn-primary:disabled:hover {
            background: #9ca3af;
            transform: none;
            box-shadow: none;
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
                <!-- Illustration: Car in Traffic Jam -->
                <div class="illustration">
                    <svg viewBox="0 0 400 300">
                        <!-- Sky Background -->
                        <rect x="0" y="0" width="400" height="200" fill="#FEF3C7"/>
                        
                        <!-- Road -->
                        <rect x="0" y="200" width="400" height="100" fill="#6B7280"/>
                        <rect x="0" y="240" width="400" height="10" fill="#FCD34D" opacity="0.8"/>
                        
                        <!-- Traffic Light -->
                        <g transform="translate(320, 120)">
                            <!-- Pole -->
                            <rect x="-5" y="40" width="10" height="80" fill="#4B5563"/>
                            
                            <!-- Light Box -->
                            <rect x="-20" y="0" width="40" height="90" fill="#1F2937" rx="5"/>
                            
                            <!-- Red Light (ON) with glow -->
                            <circle class="red-light-glow" cx="0" cy="15" r="15" fill="#DC2626" opacity="0.3"/>
                            <circle cx="0" cy="15" r="12" fill="#DC2626"/>
                            <circle class="red-light-pulse" cx="0" cy="15" r="8" fill="#FCA5A5" opacity="0.8"/>
                            <circle class="red-light-pulse" cx="0" cy="15" r="6" fill="#FFFFFF" opacity="0.6"/>
                            
                            <!-- Yellow Light (OFF) -->
                            <circle cx="0" cy="45" r="12" fill="#92400E"/>
                            
                            <!-- Green Light (OFF) -->
                            <circle cx="0" cy="75" r="12" fill="#065F46"/>
                        </g>
                        
                        <!-- Car 1 (Front - Main) -->
                        <g transform="translate(50, 180)">
                            <rect class="car-bounce" x="0" y="20" width="90" height="35" fill="#F59E0B" rx="5"/>
                            <rect class="car-bounce" x="10" y="0" width="70" height="25" fill="#FBBF24" rx="5"/>
                            <rect class="car-bounce" x="15" y="5" width="25" height="15" fill="#FEF3C7" rx="2"/>
                            <rect class="car-bounce" x="50" y="5" width="25" height="15" fill="#FEF3C7" rx="2"/>
                            
                            <!-- Wheels -->
                            <circle class="car-bounce" cx="18" cy="55" r="10" fill="#1F2937"/>
                            <circle class="car-bounce" cx="18" cy="55" r="5" fill="#6B7280"/>
                            <circle class="car-bounce" cx="72" cy="55" r="10" fill="#1F2937"/>
                            <circle class="car-bounce" cx="72" cy="55" r="5" fill="#6B7280"/>
                            
                            <!-- Headlights -->
                            <circle class="car-bounce" cx="5" cy="35" r="3" fill="#FCD34D"/>
                            <circle class="car-bounce" cx="85" cy="35" r="3" fill="#FEF3C7"/>
                            
                            <!-- Exhaust smoke -->
                            <g opacity="0.5">
                                <ellipse class="smoke" cx="0" cy="40" rx="12" ry="8" fill="#94A3B8"/>
                                <ellipse class="smoke-2" cx="-5" cy="35" rx="10" ry="7" fill="#94A3B8"/>
                                <ellipse class="smoke-3" cx="5" cy="38" rx="8" ry="6" fill="#94A3B8"/>
                            </g>
                        </g>
                        
                        <!-- Frustrated Face on Main Car -->
                        <g transform="translate(95, 195)">
                            <text class="car-bounce" x="0" y="0" font-size="18">😤</text>
                        </g>
                        
                        <!-- Car 2 (Behind) -->
                        <g transform="translate(160, 185)" opacity="0.7">
                            <rect x="0" y="20" width="80" height="30" fill="#3B82F6" rx="4"/>
                            <rect x="10" y="0" width="60" height="22" fill="#60A5FA" rx="4"/>
                            <circle cx="15" cy="50" r="8" fill="#1F2937"/>
                            <circle cx="65" cy="50" r="8" fill="#1F2937"/>
                        </g>
                        
                        <!-- Car 3 (Far Behind) -->
                        <g transform="translate(260, 190)" opacity="0.5">
                            <rect x="0" y="20" width="70" height="25" fill="#EF4444" rx="3"/>
                            <rect x="8" y="0" width="54" height="20" fill="#F87171" rx="3"/>
                            <circle cx="12" cy="45" r="7" fill="#1F2937"/>
                            <circle cx="58" cy="45" r="7" fill="#1F2937"/>
                        </g>
                        
                        <!-- Speed Lines (indicating stopped) -->
                        <g opacity="0.3">
                            <line x1="30" y1="170" x2="20" y2="170" stroke="#F59E0B" stroke-width="2"/>
                            <line x1="30" y1="180" x2="15" y2="180" stroke="#F59E0B" stroke-width="2"/>
                            <line x1="30" y1="190" x2="18" y2="190" stroke="#F59E0B" stroke-width="2"/>
                        </g>
                        
                        <!-- Warning Sign -->
                        <g transform="translate(200, 160)">
                            <polygon class="warning-sign" points="0,-25 22,0 0,25 -22,0" fill="#FCD34D" stroke="#F59E0B" stroke-width="2"/>
                            <text class="warning-sign" x="0" y="8" text-anchor="middle" font-size="24" font-weight="bold" fill="#DC2626">!</text>
                        </g>
                    </svg>
                </div>

                <!-- Error Code -->
                <div>
                    <h1 class="error-code">429</h1>
                    <h2 class="error-title">Too Many Requests</h2>
                </div>

                <!-- Message -->
                <div class="message">
                    <p class="message-primary">
                        Whoa there! You're going too fast.
                    </p>
                    <p class="message-secondary">
                        You've made too many requests in a short time. Please slow down and try again in a moment.
                    </p>
                </div>

                <!-- Countdown Timer -->
                <div class="countdown-box" id="countdown-box">
                    <i class="bi bi-hourglass-split countdown-icon"></i>
                    <span class="countdown-text" id="countdown-text">Please wait <span id="countdown">60</span> seconds...</span>
                </div>

                <!-- Actions -->
                <div class="actions">
                    <a href="{{ url('/') }}" class="btn btn-primary" id="home-btn">
                        <i class="bi bi-house"></i>
                        Go Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Countdown timer
        let countdown = 60;
        const countdownElement = document.getElementById('countdown');
        const countdownBox = document.getElementById('countdown-box');
        const homeBtn = document.getElementById('home-btn');

        // Disable button initially
        homeBtn.style.pointerEvents = 'none';
        homeBtn.style.opacity = '0.5';

        const interval = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(interval);
                countdownBox.style.display = 'none';
                homeBtn.style.pointerEvents = 'auto';
                homeBtn.style.opacity = '1';
            }
        }, 1000);
    </script>
</body>
</html>
