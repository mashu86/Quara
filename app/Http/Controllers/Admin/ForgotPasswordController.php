<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminPasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('admin.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $admin = User::where('email', $request->email)->where('role', 'admin')->first();

        if (!$admin) {
            // Also check by username if they typed 'Quara' or admin email
            $admin = User::where('role', 'admin')->first();
        }

        if (!$admin) {
            return back()->withErrors(['email' => 'No admin account found with that email address.'])->withInput();
        }

        $token = \Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $admin->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        try {
            Mail::to($admin->email)->send(new AdminPasswordResetMail($token, $admin->email));
            return back()->with('status', 'We have emailed your admin password reset link to ' . $admin->email . '!');
        } catch (\Exception $e) {
            \Log::error('Mail Error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to send email. Please check your SMTP mail settings: ' . $e->getMessage()])->withInput();
        }
    }

    public function showResetForm(Request $request, $token)
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Invalid or expired password reset token.'])->withInput();
        }

        // Token expires after 60 minutes
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Password reset token has expired. Please request a new link.'])->withInput();
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return redirect()->route('admin.login')->with('success', 'Your password has been successfully reset! You can now log in.');
        }

        return back()->withErrors(['email' => 'User account not found.'])->withInput();
    }
}
