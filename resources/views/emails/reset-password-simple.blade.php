<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #002856; color: white; padding: 20px; text-align: center; border-radius: 5px;">
        <h1 style="margin: 0;">Reset Password</h1>
    </div>
    
    <div style="padding: 20px; background-color: #f9f9f9; margin-top: 20px; border-radius: 5px;">
        <p>Hello <strong>{{ $user->full_name }}</strong>,</p>
        
        <p>You are receiving this email because we received a password reset request for your account.</p>
        
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetLink }}" style="background-color: #FA891A; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a>
        </p>
        
        <p><strong>This link will expire in 1 hour</strong><br>
        Expires at: {{ $expiresAt->format('d M Y, H:i:s') }}</p>
        
        <p>If the button doesn't work, copy and paste this link:</p>
        <p style="word-break: break-all; background-color: #fff; padding: 10px; border: 1px solid #ddd;">{{ $resetLink }}</p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
        
        <p style="font-size: 14px; color: #666;">
            <strong>Security Notice:</strong><br>
            • If you didn't request this, please ignore this email<br>
            • Never share your password with anyone<br>
            • AutoBase will never ask for your password via email
        </p>
        
        <p>Best regards,<br>
        <strong>AutoBase Team</strong></p>
    </div>
    
    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #999;">
        <p>© 2026 IT Team Eurokars Group Indonesia. All rights reserved.</p>
        <p>This is an automated email. Please do not reply.</p>
    </div>
</body>
</html>
