<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\SendCustomerAccountOtp;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerAccountController extends Controller
{
    public function sendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'flow' => ['required', 'in:login,register'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'username' => ['required_if:flow,register', 'nullable', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/'],
        ]);

        $email = mb_strtolower(trim($data['email']));
        $phone = $this->normalizePhone($data['phone']);
        if (! preg_match('/^[6-9]\d{9}$/', $phone)) {
            throw ValidationException::withMessages(['phone' => 'Enter a valid 10-digit Indian mobile number.']);
        }

        $key = 'customer-account-otp:'.$data['flow'].':'.hash('sha256', $email.'|'.$phone);
        $throttleKey = 'customer-account-otp-send:'.hash('sha256', $email.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json(['success' => false, 'message' => 'Too many OTP requests. Please try again later.'], 429);
        }
        RateLimiter::hit($throttleKey, 300);

        $user = User::where('email', $email)->where('role', 'customer')->first();
        $validIdentity = $user && $this->normalizePhone((string) $user->phone) === $phone;
        if ($data['flow'] === 'login' && ! $validIdentity) {
            // Keep responses indistinguishable until the user proves control of this email.
            return response()->json([
                'success' => true,
                'message' => 'If these details are eligible, a verification code will be sent to the email address.',
                'expires_in' => 300,
            ]);
        }

        $code = (string) random_int(100000, 999999);
        Cache::put($key, [
            'code' => $code,
            'flow' => $data['flow'],
            'phone' => $phone,
            'username' => trim((string) ($data['username'] ?? '')),
            'existing_email' => $data['flow'] === 'register' && (bool) $user,
            'attempts' => 0,
        ], now()->addMinutes(5));

        try {
            SendCustomerAccountOtp::dispatch($email, $code);
        } catch (\Throwable $exception) {
            Cache::forget($key);
            report($exception);

            return response()->json(['success' => false, 'message' => 'Could not send the verification email. Please try again later.'], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'If these details are eligible, a verification code will be sent to the email address.',
            'expires_in' => 300,
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'flow' => ['required', 'in:login,register'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'code' => ['required', 'digits:6'],
        ]);

        $email = mb_strtolower(trim($data['email']));
        $phone = $this->normalizePhone($data['phone']);
        $key = 'customer-account-otp:'.$data['flow'].':'.hash('sha256', $email.'|'.$phone);
        $attemptKey = 'customer-account-otp-verify:'.hash('sha256', $email.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($attemptKey, 10)) {
            return response()->json(['success' => false, 'message' => 'Too many attempts. Request a new code later.'], 429);
        }
        RateLimiter::hit($attemptKey, 600);

        $challenge = Cache::get($key);
        if (! $challenge || $challenge['flow'] !== $data['flow'] || $challenge['phone'] !== $phone) {
            return response()->json(['success' => false, 'message' => 'The code is invalid or expired. Request a new code.'], 422);
        }

        if (! hash_equals($challenge['code'], $data['code'])) {
            $challenge['attempts']++;
            if ($challenge['attempts'] >= 5) {
                Cache::forget($key);
            } else {
                Cache::put($key, $challenge, now()->addMinutes(5));
            }

            return response()->json(['success' => false, 'message' => 'The code is invalid or expired. Request a new code.'], 422);
        }

        Cache::forget($key);
        RateLimiter::clear($attemptKey);

        if ($data['flow'] === 'register') {
            $existing = User::where('email', $email)->first();
            if ($existing || ($challenge['existing_email'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'already_registered' => true,
                    'message' => 'This email already has an account. Please log in.',
                ], 409);
            }

            if (User::whereRaw('LOWER(username) = ?', [mb_strtolower($challenge['username'])])->exists()) {
                return response()->json(['success' => false, 'username_taken' => true, 'message' => 'That username is already in use. Please choose another.'], 422);
            }

            $user = User::create([
                'name' => $challenge['username'],
                'username' => $challenge['username'],
                'email' => $email,
                'phone' => $phone,
                'role' => 'customer',
                'email_verified_at' => now(),
                'password' => Str::random(64),
            ]);
        } else {
            $user = User::where('email', $email)->where('role', 'customer')->first();
            if (! $user || $this->normalizePhone((string) $user->phone) !== $phone) {
                return response()->json(['success' => false, 'message' => 'The code is invalid or expired. Request a new code.'], 422);
            }
            if (! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
        }

        $this->claimOrders($user, $email, $phone, $request);
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        session()->forget(['customer_email', 'customer_phone']);

        return response()->json([
            'success' => true,
            'message' => 'You are now signed in.',
            'redirect' => route('customer.my-orders'),
            'name' => $user->name,
        ]);
    }

    private function claimOrders(User $user, string $email, string $phone, Request $request): void
    {
        $guestIds = collect($request->session()->get('guest_order_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique();

        if ($guestIds->isNotEmpty()) {
            $guestOrders = Order::whereIn('id', $guestIds)->whereNull('user_id')->get();
            foreach ($guestOrders as $order) {
                if ($this->normalizePhone((string) $order->customer_phone) !== $phone) {
                    continue;
                }
                $order->user_id = $user->id;
                if (! filled($order->customer_email)) {
                    $order->customer_email = $email;
                }
                $order->save();
            }
        }

        // Recover older orders only when both the verified email and phone match.
        $emailOrders = Order::whereNull('user_id')->where('customer_email', $email)->get();
        foreach ($emailOrders as $order) {
            if ($this->normalizePhone((string) $order->customer_phone) === $phone) {
                $order->user_id = $user->id;
                $order->save();
            }
        }

        $request->session()->forget('guest_order_ids');
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        return strlen($digits) > 10 ? substr($digits, -10) : $digits;
    }
}
