<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * The "complete local test payment" shortcut skips Razorpay entirely, so it must never
     * reach a real customer. It is allowed only when ALL of these hold:
     *  - APP_ENV is local,
     *  - RAZORPAY_LOCAL_TEST_MODE=true is set explicitly (off by default),
     *  - the site is opened as localhost / 127.0.0.1,
     *  - AND the visitor's connection really comes from this machine (loopback IP).
     * The Host header alone can be forged by a remote client; the connection IP cannot.
     */
    public static function localTestPaymentsAllowed(): bool
    {
        if (config('app.env') !== 'local' || !config('services.razorpay.local_test_mode')) {
            return false;
        }
        $request = request();
        if (!$request) {
            return false;
        }
        $loopback = ['127.0.0.1', '::1'];
        return in_array($request->getHost(), ['localhost', '127.0.0.1', '::1', '[::1]'], true)
            && in_array($request->server('REMOTE_ADDR'), $loopback, true);
    }

    /**
     * Process payment initialization based on selected method.
     */
    public function initiatePayment(Order $order, string $paymentMethod): array
    {
        if ($paymentMethod === 'cod') {
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'cod',
                'status' => 'pending',
                'amount' => $order->grand_total,
            ]);

            return [
                'status' => 'success',
                'method' => 'cod',
                'payment' => $payment,
                'redirect_url' => route('checkout.success', ['order_number' => $order->order_number]),
            ];
        }

        // Online Payment via Razorpay
        $razorpayKey = config('services.razorpay.key');
        $razorpaySecret = config('services.razorpay.secret');

        // Never contact Razorpay during local checkout. The local payment page
        // presents a clearly labelled test action that confirms the order.
        $realRazorpayOrderId = '';
        if (self::localTestPaymentsAllowed()) {
            $realRazorpayOrderId = 'order_mock_local_' . $order->id . '_' . time();
        }

        if ($realRazorpayOrderId === '' && (!is_string($razorpayKey) || (!str_starts_with($razorpayKey, 'rzp_live_') && !str_starts_with($razorpayKey, 'rzp_test_')) || empty($razorpaySecret))) {
            throw new \RuntimeException('Razorpay credentials are not configured correctly.');
        }

        $amountInPaise = (int) round($order->grand_total * 100);
        if ($realRazorpayOrderId === '') {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withBasicAuth($razorpayKey, $razorpaySecret)
                    ->post('https://api.razorpay.com/v1/orders', [
                        'amount' => $amountInPaise,
                        'currency' => 'INR',
                        'receipt' => $order->order_number,
                        'notes' => [
                            'order_id' => (string) $order->id,
                            'order_number' => $order->order_number,
                            'customer_name' => (string) $order->customer_name,
                        ]
                    ]);

                if ($response->successful()) {
                    $realRazorpayOrderId = $response->json('id');
                } else {
                    Log::warning('Razorpay API Order creation returned non-200: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('Razorpay API Order Creation Exception: ' . $e->getMessage());
            }
        }

        if (!is_string($realRazorpayOrderId) || !str_starts_with($realRazorpayOrderId, 'order_')) {
            if (self::localTestPaymentsAllowed()) {
                $realRazorpayOrderId = 'order_mock_local_' . $order->id . '_' . time();
            } else {
                $order->update(['reserved_until' => null]);
                throw new \RuntimeException('Unable to start payment. Please try checkout again.');
            }
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'online',
            'razorpay_order_id' => $realRazorpayOrderId ?: ('rzp_order_' . $order->id),
            'status' => 'pending',
            'amount' => $order->grand_total,
        ]);

        return [
            'status' => 'success',
            'method' => 'online',
            'razorpay_key' => $razorpayKey,
            'razorpay_order_id' => $realRazorpayOrderId,
            'amount' => $amountInPaise,
            'amount_in_paise' => $amountInPaise,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'payment' => $payment,
        ];
    }

    /**
     * Verify online payment callback signature strictly using Razorpay HMAC.
     */
    public function verifyOnlinePayment(Order $order, string $paymentId, string $razorpayOrderId, string $signature): bool
    {
        if ($order->order_status === 'cancelled') {
            return false;
        }

        // Local testing simulation bypass
        if (self::localTestPaymentsAllowed() && str_starts_with($razorpayOrderId, 'order_mock_local_')) {
            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'payment_method' => 'online',
                    'razorpay_order_id' => $razorpayOrderId,
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_signature' => $signature,
                    'status' => 'paid',
                    'amount' => $order->grand_total,
                ]
            );
            $stockService = app(\App\Services\StockService::class);
            $stockService->deductStockForOrder($order);
            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
                'reserved_until' => null
            ]);
            return true;
        }

        $secret = (string) config('services.razorpay.secret');
        $storedOrderId = $order->payment?->razorpay_order_id;
        if ($secret === '' || !$storedOrderId || !hash_equals($storedOrderId, $razorpayOrderId)
            || !hash_equals(hash_hmac('sha256', $storedOrderId.'|'.$paymentId, $secret), $signature)) {
            return false;
        }
        $service = app(RazorpayOrderService::class);
        $payment = $service->findCapturedPayment($order, $paymentId);
        if (!$payment) {
            return false;
        }
        return in_array($service->confirm($order, $payment, 'Checkout'), ['confirmed', 'already_processed']);
    }
}
