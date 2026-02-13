<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #002856 0%, #003d7a 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .email-header .icon {
            width: 80px;
            height: 80px;
            background-color: #FA891A;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 40px;
            color: #ffffff;
        }
        .email-body {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.6;
        }
        .email-body h2 {
            color: #002856;
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .email-body p {
            margin: 15px 0;
            font-size: 16px;
        }
        .reset-button {
            display: inline-block;
            padding: 15px 40px;
            background-color: #FA891A;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 25px 0;
            transition: background-color 0.3s;
        }
        .reset-button:hover {
            background-color: #e67a0f;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #FA891A;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-box p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .info-box strong {
            color: #002856;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .warning-box p {
            margin: 5px 0;
            font-size: 14px;
            color: #856404;
        }
        .email-footer {
            background-color: #002856;
            color: #ffffff;
            padding: 30px;
            text-align: center;
            font-size: 14px;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .email-footer a {
            color: #FA891A;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 30px 0;
        }
        .link-text {
            word-break: break-all;
            color: #002856;
            font-size: 14px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Body -->
        <div class="email-body">
            <h2>Hello, {{ $user->full_name }}!</h2>
            
            <p>We received a request to reset your password for your <strong>AutoBase</strong> account.</p>
            
            <p>Click the button below to reset your password:</p>

            <div class="button-container">
                <a href="{{ $resetLink }}" class="reset-button">Reset Password</a>
            </div>

            <div class="divider"></div>

            <p><strong>If the button doesn't work, copy and paste this link into your browser:</strong></p>
            <div class="link-text">{{ $resetLink }}</div>

            <div class="info-box">
                <p><strong>⏰ This link will expire in 1 hour</strong></p>
                <p>Expires at: <strong>{{ $expiresAt->format('d M Y, H:i:s') }}</strong></p>
            </div>


            <div class="warning-box">
                <p><strong>⚠️ Security Notice:</strong></p>
                <p>• If you didn't request this password reset, please ignore this email.</p>
                <p>• Never share your password with anyone.</p>
                <p>• AutoBase will never ask for your password via email.</p>
            </div>

            <div class="divider"></div>

            <p>If you have any questions or concerns, please contact our support team.</p>
            
            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong style="color: #002856;">AutoBase Team</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>AutoBase</strong> - Autoline Database</p>
            <p>This is an automated email. Please do not reply to this message.</p>
            <p style="margin-top: 15px; font-size: 12px; color: #aaa;">
                © 2026 IT Team Eurokars Group Indonesia. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
