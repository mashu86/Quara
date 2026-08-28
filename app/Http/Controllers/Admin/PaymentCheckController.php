<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentCheckController extends Controller
{
    private const CURRENCY = 'INR';

    public function index()
    {
        $key = (string) config('services.razorpay.key', '');
        $isConfigured = $this->hasValidCredentials();

        return view('admin.payment_check.index', [
            'isConfigured' => $isConfigured,
            'razorpayMode' => str_starts_with($key, 'rzp_live_') ? 'live' : 'test',
            'maskedKey' => $key === '' ? 'Not configured' : Str::mask($key, '*', 9, max(strlen($key) - 13, 0)),
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:100'],
        ]);

        if (! $this->hasValidCredentials()) {
            return response()->json([
                'message' => 'Razorpay Key ID or Secret Key is not configured correctly. Save both in Master Settings first.',
            ], 422);
        }

        $key = (string) config('services.razorpay.key');
        $secret = (string) config('services.razorpay.secret');
        $amountInPaise = (int) round(((float) $validated['amount']) * 100);

        try {
            $response = Http::acceptJson()
                ->withBasicAuth($key, $secret)
                ->timeout(15)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => $amountInPaise,
                    'currency' => self::CURRENCY,
                    'receipt' => 'admin_test_'.now()->format('YmdHis').'_'.Str::lower(Str::random(6)),
                    'notes' => [
                        'purpose' => 'Admin Razorpay configuration check',
                        'admin_id' => (string) $request->user()->getAuthIdentifier(),
                    ],
                ]);
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Could not connect to Razorpay. Check the server internet connection and try again.',
            ], 502);
        }

        $razorpayOrderId = $response->json('id');

        if (! $response->successful() || ! is_string($razorpayOrderId) || ! str_starts_with($razorpayOrderId, 'order_')) {
            return response()->json([
                'message' => $this->razorpayError($response->json('error.description')),
            ], 422);
        }

        $request->session()->put('razorpay_payment_check', [
            'order_id' => $razorpayOrderId,
            'amount' => $amountInPaise,
            'created_at' => now()->timestamp,
        ]);

        return response()->json([
            'key' => $key,
            'order_id' => $razorpayOrderId,
            'amount' => $amountInPaise,
            'currency' => self::CURRENCY,
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'razorpay_payment_id' => ['required', 'string', 'max:255'],
            'razorpay_order_id' => ['required', 'string', 'max:255'],
            'razorpay_signature' => ['required', 'string', 'max:255'],
        ]);

        $testOrder = $request->session()->get('razorpay_payment_check');

        if (! is_array($testOrder)
            || ! hash_equals((string) ($testOrder['order_id'] ?? ''), $validated['razorpay_order_id'])
            || now()->timestamp - (int) ($testOrder['created_at'] ?? 0) > 1800) {
            return response()->json([
                'message' => 'This test payment session is invalid or expired. Start a new ₹1 test.',
            ], 422);
        }

        $secret = (string) config('services.razorpay.secret', '');
        $expectedSignature = hash_hmac(
            'sha256',
            $validated['razorpay_order_id'].'|'.$validated['razorpay_payment_id'],
            $secret
        );

        if ($secret === '' || ! hash_equals($expectedSignature, $validated['razorpay_signature'])) {
            $request->session()->forget('razorpay_payment_check');

            return response()->json([
                'message' => 'Payment signature verification failed. The Razorpay credentials may not match.',
            ], 422);
        }

        try {
            $paymentResponse = Http::acceptJson()
                ->withBasicAuth((string) config('services.razorpay.key'), $secret)
                ->timeout(15)
                ->get('https://api.razorpay.com/v1/payments/'.rawurlencode($validated['razorpay_payment_id']));
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Signature is valid, but the server could not fetch the final payment status from Razorpay.',
            ], 502);
        }

        $payment = $paymentResponse->json();
        $isMatchingPayment = $paymentResponse->successful()
            && ($payment['order_id'] ?? null) === $testOrder['order_id']
            && (int) ($payment['amount'] ?? 0) === (int) $testOrder['amount']
            && ($payment['currency'] ?? null) === self::CURRENCY
            && in_array($payment['status'] ?? null, ['authorized', 'captured'], true);

        if (! $isMatchingPayment) {
            return response()->json([
                'message' => 'The payment callback was signed, but Razorpay did not confirm a matching successful payment.',
            ], 422);
        }

        $request->session()->forget('razorpay_payment_check');

        return response()->json([
            'message' => 'Razorpay is working correctly. The test payment was verified successfully.',
            'payment_id' => $validated['razorpay_payment_id'],
            'status' => $payment['status'],
            'amount' => (int) $testOrder['amount'],
        ]);
    }

    private function hasValidCredentials(): bool
    {
        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        return is_string($key)
            && preg_match('/^rzp_(test|live)_[A-Za-z0-9]+$/', $key) === 1
            && is_string($secret)
            && trim($secret) !== '';
    }

    private function razorpayError(mixed $description): string
    {
        if (is_string($description) && trim($description) !== '') {
            return 'Razorpay rejected the test order: '.$description;
        }

        return 'Razorpay rejected the test order. Check whether the Key ID and Secret Key belong to the same mode.';
    }
}
