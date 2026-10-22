<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Firebase\FirebaseIdTokenVerifier;
use App\Services\Firebase\InvalidFirebaseIdToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class CustomerPhoneLoginController extends Controller
{
    public function login(Request $request, FirebaseIdTokenVerifier $verifier): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string', 'max:8192'],
            'return_to' => ['nullable', 'in:checkout,my-orders'],
        ]);

        if (Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'admin') {
            return response()->json(['success' => false, 'message' => 'Log out of the admin account before customer sign-in.'], 409);
        }

        try {
            $identity = $verifier->verifyPhoneIdToken($validated['id_token']);
        } catch (InvalidFirebaseIdToken) {
            return response()->json(['success' => false, 'message' => 'Phone verification expired or failed. Please request a new OTP.'], 401);
        } catch (Throwable $exception) {
            Log::error('Firebase phone sign-in verification is unavailable.', ['error' => $exception->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Phone sign-in is temporarily unavailable. Please try again shortly.'], 503);
        }

        $phone = $identity['phone_number'];
        $phoneDigits = substr(preg_replace('/\D+/', '', $phone), -10);
        $matches = User::query()
            ->where('role', 'customer')
            ->whereNotNull('phone')
            ->where('phone', 'like', '%'.substr($phoneDigits, -4))
            ->get()
            ->filter(fn (User $user) => $this->normalizeIndianPhone($user->phone) === $phoneDigits)
            ->values();

        if ($matches->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No customer account was found for this mobile number. Please contact the store for help.'], 404);
        }

        if ($matches->count() !== 1) {
            Log::warning('Phone sign-in matched multiple customer accounts.', ['phone_suffix' => substr($phoneDigits, -4)]);

            return response()->json(['success' => false, 'message' => 'We could not safely identify one customer account. Please contact the store for help.'], 409);
        }

        $customer = $matches->first();
        Auth::guard('web')->login($customer);
        $request->session()->regenerate();
        $request->session()->put([
            'customer_phone' => $phoneDigits,
            'customer_email' => $customer->email,
        ]);

        $redirectTo = ($validated['return_to'] ?? 'my-orders') === 'checkout'
            ? route('checkout.index')
            : route('customer.my-orders');

        return response()->json(['success' => true, 'redirect_url' => $redirectTo])
            ->header('Cache-Control', 'no-store, private');
    }

    private function normalizeIndianPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) > 10 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return preg_match('/^[6-9][0-9]{9}$/', $digits) ? $digits : null;
    }
}
