<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\SendCustomerAccountOtp;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

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

        $throttleKey = 'checkout-email-otp:'.hash('sha256', $email.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json(['success' => false, 'message' => 'Too many OTP requests. Please try again later.'], 429);
        }
        RateLimiter::hit($throttleKey, 300);

        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        // Store OTP details in session
        session([
            'email_otp_code' => $otp,
            'email_otp_email' => $email,
            'email_otp_expires_at' => $expiresAt->timestamp,
            'email_otp_attempts' => 0,
        ]);

        try {
            (new SendCustomerAccountOtp($email, $otp, 'Email Verification'))->handle();
        } catch (\Throwable $exception) {
            session()->forget(['email_otp_code', 'email_otp_email', 'email_otp_expires_at', 'email_otp_attempts']);
            report($exception);
            return response()->json(['success' => false, 'message' => 'Could not send the verification email. Please try again later.'], 503);
        }

        return response()->json([
            'success' => true,
            'email' => $email,
            'expires_in' => 300,
            'message' => '6-digit OTP code sent to your email! (Valid for 5 minutes)',
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

        if (! $sessionCode || strtolower($sessionEmail) !== $email) {
            return response()->json([
                'success' => false,
                'message' => 'No OTP request found for this email. Please click Resend OTP.',
            ], 422);
        }

        if ($sessionExpires && time() > $sessionExpires) {
            session()->forget(['email_otp_code', 'email_otp_email', 'email_otp_expires_at', 'email_otp_attempts']);
            return response()->json([
                'success' => false,
                'message' => 'OTP code has expired. Please request a new OTP code.',
            ], 422);
        }

        if ((int) session('email_otp_attempts', 0) >= 5) {
            session()->forget(['email_otp_code', 'email_otp_email', 'email_otp_expires_at', 'email_otp_attempts']);
            return response()->json(['success' => false, 'message' => 'Too many incorrect attempts. Request a new code.'], 422);
        }

        if (! hash_equals((string) $sessionCode, $code)) {
            session(['email_otp_attempts' => (int) session('email_otp_attempts', 0) + 1]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP code. Please check your email and try again.',
            ], 422);
        }

        // Save verified email session
        session(['customer_email' => $email]);

        // Clean up OTP temporary session
        session()->forget(['email_otp_code', 'email_otp_email', 'email_otp_expires_at', 'email_otp_attempts']);

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
        $user = Auth::guard('web')->user();
        $isCustomer = $user && $user->role === 'customer';
        $guestIds = collect($request->session()->get('guest_order_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique();

        $successfulOrderStatuses = ['confirmed', 'processing', 'packed', 'shipped', 'delivered'];
        $ordersQuery = Order::whereIn('order_status', $successfulOrderStatuses)
            ->with(['items.product', 'payment', 'operations', 'shipment']);

        if ($isCustomer) {
            $ordersQuery->where('user_id', $user->id);
        } else {
            $ordersQuery->whereIn('id', $guestIds)->whereNull('user_id');
        }

        $orders = $ordersQuery->latest()->get();

        $email = $isCustomer ? $user->email : null;
        $phone = $isCustomer ? $user->phone : null;

        return view('frontend.my_orders', compact('orders', 'email', 'phone', 'isCustomer'));
    }

    /**
     * Logout verified customer email session.
     */
    public function logout(Request $request)
    {
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'customer') {
            Auth::guard('web')->logout();
            $request->session()->regenerate();
        }

        session()->forget(['customer_email', 'customer_phone', 'guest_order_ids']);

        return redirect()->route('home')->with('success', 'You have logged out of your session.');
    }
}
