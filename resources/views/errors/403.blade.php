<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>
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
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-3deg); }
            75% { transform: rotate(3deg); }
        }

        @keyframes glow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.1; }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(1px); }
        }

        @keyframes dash {
            0%, 100% { stroke-dashoffset: 0; }
            50% { stroke-dashoffset: 20; }
        }

        @keyframes pulse-opacity {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        .stop-sign {
            animation: shake 2s ease-in-out infinite;
            transform-origin: center;
        }

        .stop-glow {
            animation: glow 2s ease-in-out infinite, shake 2s ease-in-out infinite;
            transform-origin: center;
        }

        .car-body {
            animation: bounce 2.5s ease-in-out infinite;
        }

        .warning-line {
            animation: dash 2s linear infinite, pulse-opacity 1.5s ease-in-out infinite;
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
                <!-- Illustration: Car Blocked by Stop Sign -->
                <div class="illustration">
                    <svg viewBox="0 0 400 300">
                        <!-- Sky Background -->
                        <rect x="0" y="0" width="400" height="200" fill="#FEE2E2"/>
                        
                        <!-- Road -->
                        <rect x="0" y="200" width="400" height="100" fill="#6B7280"/>
                        <rect x="0" y="240" width="400" height="10" fill="#FCD34D" opacity="0.8"/>
                        
                        <!-- Large Stop Sign with animation -->
                        <g transform="translate(250, 100)">
                            <!-- Stop Sign Pole -->
                            <rect x="-8" y="60" width="16" height="100" fill="#6B7280"/>
                            
                            <!-- Stop Sign (Octagon) with shake -->
                            <polygon 
                                class="stop-sign"
                                points="0,-50 35,-35 50,0 35,35 0,50 -35,35 -50,0 -35,-35" 
                                fill="#DC2626" 
                                stroke="#991B1B" 
                                stroke-width="3"/>
                            
                            <!-- Glow effect -->
                            <polygon 
                                class="stop-glow"
                                points="0,-55 38,-38 55,0 38,38 0,55 -38,38 -55,0 -38,-38" 
                                fill="#DC2626" 
                                opacity="0.3"/>
                            
                            <!-- STOP Text -->
                            <text 
                                class="stop-sign"
                                x="0" 
                                y="8" 
                                text-anchor="middle" 
                                font-size="20" 
                                font-weight="bold" 
                                fill="#FFFFFF" 
                                font-family="Arial">STOP</text>
                        </g>
                        
                        <!-- Barrier Cones -->
                        <g transform="translate(180, 200)">
                            <polygon points="0,0 -15,40 15,40" fill="#F97316"/>
                            <rect x="-12" y="10" width="24" height="5" fill="#FFFFFF"/>
                            <rect x="-12" y="20" width="24" height="5" fill="#FFFFFF"/>
                        </g>
                        <g transform="translate(220, 200)">
                            <polygon points="0,0 -15,40 15,40" fill="#F97316"/>
                            <rect x="-12" y="10" width="24" height="5" fill="#FFFFFF"/>
                            <rect x="-12" y="20" width="24" height="5" fill="#FFFFFF"/>
                        </g>
                        
                        <!-- Car Stopped with animation -->
                        <g transform="translate(60, 180)">
                            <!-- Car Body -->
                            <rect class="car-body" x="0" y="20" width="100" height="35" fill="#EF4444" rx="5"/>
                            <rect class="car-body" x="10" y="0" width="80" height="25" fill="#F87171" rx="5"/>
                            
                            <!-- Windows -->
                            <rect class="car-body" x="15" y="5" width="30" height="15" fill="#FEE2E2" rx="2"/>
                            <rect class="car-body" x="55" y="5" width="30" height="15" fill="#FEE2E2" rx="2"/>
                            
                            <!-- Wheels -->
                            <circle class="car-body" cx="20" cy="55" r="10" fill="#1F2937"/>
                            <circle class="car-body" cx="20" cy="55" r="5" fill="#6B7280"/>
                            <circle class="car-body" cx="80" cy="55" r="10" fill="#1F2937"/>
                            <circle class="car-body" cx="80" cy="55" r="5" fill="#6B7280"/>
                            
                            <!-- Headlights -->
                            <circle class="car-body" cx="5" cy="35" r="3" fill="#FCD34D"/>
                            <circle class="car-body" cx="95" cy="35" r="3" fill="#FEF3C7"/>
                        </g>
                        
                        <!-- Confused Face on Car -->
                        <g transform="translate(110, 195)">
                            <text class="car-body" x="0" y="0" font-size="20">😕</text>
                        </g>
                        
                        <!-- Warning Lines on Road -->
                        <line 
                            class="warning-line"
                            x1="150" 
                            y1="210" 
                            x2="150" 
                            y2="290" 
                            stroke="#DC2626" 
                            stroke-width="4" 
                            stroke-dasharray="10,10"/>
                    </svg>
                </div>

                <!-- Error Code -->
                <div>
                    <h1 class="error-code">403</h1>
                    <h2 class="error-title">Forbidden</h2>
                </div>

                <!-- Message -->
                <div class="message">
                    <p class="message-primary">
                        @if(isset($exception) && $exception->getMessage())
                            {{ $exception->getMessage() }}
                        @else
                            You don't have permission to access this resource.
                        @endif
                    </p>
                    <p class="message-secondary">
                        This route is restricted. Please contact your administrator if you believe this is an error.
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
