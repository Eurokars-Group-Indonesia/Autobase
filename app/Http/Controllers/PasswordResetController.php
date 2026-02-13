<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Show forgot password form
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send reset password link to email
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:ms_users,email',
        ], [
            'email.exists' => 'Email not found in our system.',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_active', '1')
            ->first();

        if (!$user) {
            return back()->with('error', 'User account is not active.');
        }

        // Generate token
        $token = Str::random(64);
        $expiresAt = Carbon::now()->addHour(); // Expired 1 jam

        // Delete old tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'expires_at' => $expiresAt,
            'created_at' => Carbon::now(),
        ]);

        // Send email using Mailable
        $resetLink = route('password.reset', ['token' => $token]);

        try {
            \Log::info('Attempting to send password reset email', [
                'to' => $user->email,
                'reset_link' => $resetLink,
            ]);

            // Send email using Mailable class
            Mail::to($user->email)->send(new \App\Mail\ResetPasswordMail($user, $resetLink, $expiresAt));

            \Log::info('Password reset email sent successfully', [
                'to' => $user->email,
                'token_expires_at' => $expiresAt,
            ]);

            return back()->with('success', 'Password reset link has been sent to your email!');
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email', [
                'error' => $e->getMessage(),
                'to' => $user->email,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    /**
     * Show reset password form
     */
    public function showResetForm(Request $request, $token)
    {
        // Find token in database
        $resetTokens = DB::table('password_reset_tokens')->get();
        
        $resetToken = null;
        $email = null;
        
        // Check each token to find matching hash
        foreach ($resetTokens as $tokenRecord) {
            if (Hash::check($token, $tokenRecord->token)) {
                $resetToken = $tokenRecord;
                $email = $tokenRecord->email;
                break;
            }
        }

        if (!$resetToken) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid or expired reset link.');
        }

        // Check if expired
        if (Carbon::parse($resetToken->expires_at)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('password.request')
                ->with('error', 'Reset link has expired. Please request a new one.');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Reset password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:ms_users,email',
            'password' => [
                'required',
                'string',
                'min:12',
                'max:255',
                'confirmed',
                new \App\Rules\StrongPassword($request->input('email'))
            ],
        ]);

        // Find token in database
        $resetTokens = DB::table('password_reset_tokens')->get();
        
        $resetToken = null;
        $tokenEmail = null;
        
        // Check each token to find matching hash
        foreach ($resetTokens as $tokenRecord) {
            if (Hash::check($request->token, $tokenRecord->token)) {
                $resetToken = $tokenRecord;
                $tokenEmail = $tokenRecord->email;
                break;
            }
        }

        if (!$resetToken) {
            return back()->with('error', 'Invalid reset link.');
        }

        // Verify email matches
        if ($tokenEmail !== $request->email) {
            return back()->with('error', 'Invalid reset link.');
        }

        // Check if expired
        if (Carbon::parse($resetToken->expires_at)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $tokenEmail)->delete();
            return back()->with('error', 'Reset link has expired. Please request a new one.');
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->updated_by = $user->user_id;
        $user->save();

        // Delete token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Password has been reset successfully! You can now login with your new password.');
    }
}
