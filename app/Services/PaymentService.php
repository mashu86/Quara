<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentService
{
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

        if (!is_string($razorpayKey) || (!str_starts_with($razorpayKey, 'rzp_live_') && !str_starts_with($razorpayKey, 'rzp_test_')) || empty($razorpaySecret)) {
            throw new \RuntimeException('Razorpay credentials are not configured correctly.');
        }

        $amountInPaise = (int) round($order->grand_total * 100);
        $realRazorpayOrderId = '';

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

        if (!is_string($realRazorpayOrderId) || !str_starts_with($realRazorpayOrderId, 'order_')) {
            $order->update(['reserved_until' => null]);
            throw new \RuntimeException('Unable to start payment. Please try checkout again.');
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
