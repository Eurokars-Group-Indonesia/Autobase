<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmailController extends Controller
{
    /**
     * Show test email form
     */
    public function index()
    {
        return view('test-email.index');
    }

    /**
     * Send test email
     */
    public function send(Request $request)
    {
        $request->validate([
            'to_email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            $toEmail = $request->to_email;
            $subject = $request->subject;
            $messageContent = $request->message;

            // Send email using PHPMailer
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = config('mail.mailers.smtp.host');
            $mail->Port = config('mail.mailers.smtp.port');
            $mail->Hostname = 'localhost'; // Set hostname seperti email verified
            
            // Check if authentication is needed
            $username = config('mail.mailers.smtp.username');
            $password = config('mail.mailers.smtp.password');
            
            if (!empty($username) && !empty($password)) {
                $mail->SMTPAuth = true;
                $mail->Username = $username;
                $mail->Password = $password;
            } else {
                $mail->SMTPAuth = false;
            }
            
            // Encryption
            $encryption = config('mail.mailers.smtp.encryption');
            if ($encryption === 'tls') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }
            
            // Recipients
            $mail->setFrom(config('mail.from.address'), config('mail.from.name'));
            $mail->addAddress($toEmail);
            $mail->addReplyTo(config('mail.from.address'), config('mail.from.name'));
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $messageContent;
            $mail->CharSet = 'UTF-8';
            $mail->XMailer = 'PHPMailer 7.0.2 (https://github.com/PHPMailer/PHPMailer)'; // Set X-Mailer seperti email verified
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $messageContent;
            $mail->CharSet = 'UTF-8';
            
            $mail->send();

            Log::info('Test email sent successfully', [
                'to' => $toEmail,
                'subject' => $subject,
                'sent_by' => auth()->user()->email ?? 'system'
            ]);

            return redirect()->back()->with('success', 'Test email sent successfully to ' . $toEmail);
        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to send email: ' . $e->getMessage())
                ->withInput();
        }
    }
}
