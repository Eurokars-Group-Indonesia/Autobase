<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to AutoBase</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', 'Helvetica', sans-serif;
            background-color: #f5f5f5;
        }
        .email-container {
            max-width: 650px;
            margin: 30px auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
        }
        .email-header {
            background-color: #002856;
            padding: 40px 40px 30px 40px;
            border-bottom: 4px solid #FA891A;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-text {
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 2px;
        }
        .logo-subtitle {
            font-size: 14px;
            color: #FA891A;
            margin-top: 5px;
            letter-spacing: 1px;
        }
        .email-body {
            padding: 40px;
            color: #333333;
            line-height: 1.8;
        }
        .greeting {
            font-size: 18px;
            color: #002856;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .body-text {
            font-size: 15px;
            color: #555555;
            margin: 15px 0;
            text-align: justify;
        }
        .credentials-section {
            background-color: #f8f9fa;
            border: 2px solid #002856;
            padding: 30px;
            margin: 30px 0;
            border-radius: 4px;
        }
        .credentials-title {
            font-size: 18px;
            color: #002856;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .credential-row {
            margin: 15px 0;
            padding: 15px;
            background-color: #ffffff;
            border-left: 3px solid #FA891A;
        }
        .credential-label {
            font-size: 12px;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .credential-value {
            font-size: 16px;
            color: #002856;
            font-weight: 600;
            word-break: break-all;
        }
        .button-section {
            text-align: center;
            margin: 35px 0;
        }
        .login-button {
            display: inline-block;
            padding: 15px 45px;
            background-color: #FA891A;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 2px solid #FA891A;
            transition: all 0.3s;
        }
        .login-button:hover {
            background-color: #002856;
            border-color: #002856;
        }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 30px 0;
        }
        .info-section {
            background-color: #f8f9fa;
            padding: 25px;
            margin: 25px 0;
            border-left: 4px solid #002856;
        }
        .info-title {
            font-size: 16px;
            color: #002856;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .info-list {
            margin: 10px 0;
            padding-left: 20px;
        }
        .info-list li {
            margin: 10px 0;
            font-size: 14px;
            color: #555555;
            line-height: 1.6;
        }
        .notice-box {
            background-color: #fff8e1;
            border: 1px solid #ffc107;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .notice-title {
            font-size: 15px;
            color: #856404;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .notice-text {
            font-size: 14px;
            color: #856404;
            margin: 8px 0;
            line-height: 1.6;
        }
        .signature-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        .signature-text {
            font-size: 15px;
            color: #555555;
            margin: 5px 0;
        }
        .signature-name {
            font-size: 16px;
            color: #002856;
            font-weight: bold;
            margin: 10px 0 5px 0;
        }
        .signature-title {
            font-size: 14px;
            color: #FA891A;
            font-weight: 600;
        }
        .email-footer {
            background-color: #002856;
            color: #ffffff;
            padding: 30px 40px;
            text-align: center;
            font-size: 13px;
        }
        .footer-text {
            margin: 8px 0;
            color: #cccccc;
        }
        .footer-link {
            color: #FA891A;
            text-decoration: none;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
        .footer-copyright {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #003d7a;
            font-size: 12px;
            color: #999999;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="logo-section">
                <div class="logo-text">AUTOBASE</div>
                <div class="logo-subtitle">AUTOLINE DATABASE SYSTEM</div>
            </div>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">Dear {{ $user->full_name }},</div>

            <p class="body-text">
                We are pleased to inform you that your account has been successfully created in the AutoBase System. 
                This system has been designed to provide you with comprehensive access to our automotive database 
                management platform.
            </p>

            <p class="body-text">
                As a registered user, you will have access to various features and functionalities that will enable 
                you to efficiently manage and track vehicle information, service records, and related data within 
                our organization.
            </p>

            <div class="divider"></div>

            <!-- Credentials Section -->
            <div class="credentials-section">
                <div class="credentials-title">Your Account Credentials</div>
                
                <div class="credential-row">
                    <div class="credential-label">Email Address</div>
                    <div class="credential-value">{{ $user->email }}</div>
                </div>

                <div class="credential-row">
                    <div class="credential-label">Password</div>
                    <div class="credential-value">{{ $password }}</div>
                </div>
            </div>

            <!-- Login Button -->
            <div class="button-section">
                <a href="{{ $loginUrl }}" class="login-button">Access AutoBase System</a>
            </div>

            <div class="divider"></div>

            <!-- System Information -->
            <div class="info-section">
                <div class="info-title">System Capabilities</div>
                <ul class="info-list">
                    <li>Comprehensive vehicle and service history management</li>
                    <li>Advanced search and filtering capabilities</li>
                    <li>Real-time data analytics and reporting</li>
                    <li>Secure data storage with role-based access control</li>
                    <li>Multi-brand and multi-dealer support</li>
                </ul>
            </div>

            <!-- Security Notice -->
            <div class="notice-box">
                <div class="notice-title">Important Security Notice</div>
                <p class="notice-text">
                    This application uses Microsoft Azure Single Sign-On (SSO) for authentication. 
                    You can login using your Microsoft account credentials.
                </p>
                <p class="notice-text">
                    Please keep your Microsoft account credentials confidential and do not share them with unauthorized individuals. 
                    If you suspect any unauthorized access to your account, please contact the IT department immediately.
                </p>
            </div>

            <div class="divider"></div>

            <p class="body-text">
                Should you require any assistance or have questions regarding the system, please do not hesitate 
                to contact our IT support team. We are committed to ensuring that you have a smooth experience 
                with the AutoBase System.
            </p>

            <!-- Signature -->
            <div class="signature-section">
                <p class="signature-text">Best regards,</p>
                <p class="signature-name">AutoBase System Administrator</p>
                <p class="signature-title">IT Department</p>
                <p class="signature-title">Eurokars Group Indonesia</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p class="footer-text">
                <strong>AutoBase - Autoline Database System</strong>
            </p>
            <p class="footer-text">
                For technical support, please contact: 
                <a href="mailto:support@autobase.com" class="footer-link">support@autobase.com</a>
            </p>
            <p class="footer-text">
                This is an automated message. Please do not reply to this email.
            </p>
            <div class="footer-copyright">
                © 2026 IT Team Eurokars Group Indonesia. All rights reserved.<br>
                This email and any attachments are confidential and intended solely for the addressee.
            </div>
        </div>
    </div>
</body>
</html>
