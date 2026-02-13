<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show change password form
     */
    public function showChangePasswordForm()
    {
        return view('profile.change-password');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'min:12',
                'max:255',
                'confirmed',
                'different:current_password',
                new \App\Rules\StrongPassword(auth()->user()->full_name)
            ],
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 12 characters.',
            'new_password.max' => 'New password must not be greater than 255 characters.',
            'new_password.confirmed' => 'New password confirmation does not match.',
            'new_password.different' => 'New password must be different from current password.',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        // Update password
        $user = auth()->user();
        $user->password = Hash::make($request->new_password);
        $user->updated_by = $user->user_id;
        $user->save();

        // Log the password change
        \Log::info('Password changed successfully', [
            'user_id' => $user->user_id,
            'email' => $user->email,
            'changed_at' => now(),
        ]);

        return redirect()->route('profile.change-password')
            ->with('success', 'Password has been changed successfully!');
    }
}
