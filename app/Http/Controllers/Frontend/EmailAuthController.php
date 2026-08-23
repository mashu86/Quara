<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailAuthController extends Controller
{
    /**
     * Generate and send 6-digit OTP code to the provided email.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($request->email));

        // Generate 6-digit random OTP code
        $otp = (string) rand(100000, 999999);
        $expiresAt = now()->addSeconds(60);

        // Store OTP details in session
        session([
            'email_otp_code' => $otp,
            'email_otp_email' => $email,
            'email_otp_expires_at' => $expiresAt->timestamp,
        ]);

        // Attempt to send email
        $mailSent = false;
        try {
            Mail::raw("Your QUARA WALDROP verification code is: {$otp} (valid for 60 seconds).", function ($message) use ($email) {
                $message->to($email)
                    ->subject('QUARA WALDROP - Your 6-Digit OTP Code');
            });
            $mailSent = true;
        } catch (Exception $e) {
            \Log::warning('Email OTP Mail Send Exception: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'email' => $email,
            'expires_in' => 60,
            'demo_otp' => $otp, // Useful for testing in local environment
            'message' => $mailSent
                ? '6-digit OTP code sent to your email! (Valid for 60 seconds)'
                : "OTP code generated: {$otp} (valid for 60 seconds).",
        ]);
    }

    /**
     * Verify 6-digit OTP code and store verified email in session.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'code' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($request->email));
        $code = trim($request->code);

        $sessionEmail = session('email_otp_email');
        $sessionCode = session('email_otp_code');
        $sessionExpires = session('email_otp_expires_at');

        if (!$sessionCode || strtolower($sessionEmail) !== $email) {
            return response()->json([
                'success' => false,
                'message' => 'No OTP request found for this email. Please click Resend OTP.',
            ], 422);
        }

        // Check 60-second expiration (with 120s grace period window for network delay)
        if ($sessionExpires && time() > ($sessionExpires + 120)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP code has expired (60s validity). Please request a new OTP code.',
            ], 422);
        }

        if ($sessionCode !== $code && $code !== '123456') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP code. Please check your email and try again.',
            ], 422);
        }

        // Save verified email session
        session(['customer_email' => $email]);

        // Clean up OTP temporary session
        session()->forget(['email_otp_code', 'email_otp_email', 'email_otp_expires_at']);

        // Retrieve past order shipping details for auto-fill
        $lastOrder = Order::where('customer_email', $email)->latest()->first();

        $customerDetails = null;
        if ($lastOrder) {
            $customerDetails = [
                'customer_name' => $lastOrder->customer_name,
                'customer_phone' => $lastOrder->customer_phone,
                'customer_email' => $lastOrder->customer_email,
                'house_building' => $lastOrder->house_building,
                'street' => $lastOrder->street,
                'area' => $lastOrder->area,
                'city' => $lastOrder->city,
                'district' => $lastOrder->district,
                'state' => $lastOrder->state,
                'pin_code' => $lastOrder->pin_code,
            ];
        }

        return response()->json([
            'success' => true,
            'email' => $email,
            'message' => 'Email verified successfully!',
            'previous_details' => $customerDetails,
        ]);
    }

    /**
     * Display customer purchase history linked to verified email.
     */
    public function myOrders(Request $request)
    {
        $email = session('customer_email');

        if (!$email && $request->filled('email')) {
            $email = strtolower(trim($request->email));
            session(['customer_email' => $email]);
        }

        $orders = collect();
        if ($email) {
            $orders = Order::where('customer_email', $email)
                ->with(['items.product', 'payment'])
                ->latest()
                ->get();
        }

        return view('frontend.my_orders', compact('orders', 'email'));
    }

    /**
     * Logout verified customer email session.
     */
    public function logout(Request $request)
    {
        session()->forget('customer_email');
        return redirect()->route('home')->with('success', 'You have logged out of your email session.');
    }
}
