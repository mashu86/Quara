<?php

namespace App\Services;

use App\Exceptions\InsufficientOrderStock;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RazorpayOrderService
{
    public function findCapturedPayment(Order $order, ?string $paymentId = null): ?array
    {
        if ($order->order_status === 'cancelled') {
            return null;
        }
        if (!config('services.razorpay.key') || !config('services.razorpay.secret')) {
            throw new \RuntimeException('Razorpay API credentials are not configured.');
        }
        $client = Http::withBasicAuth(config('services.razorpay.key'), config('services.razorpay.secret'))->timeout(15);
        $payment = $order->payment;
        $paymentId = $paymentId ?: $payment?->razorpay_payment_id;
        if ($paymentId && str_starts_with($paymentId, 'pay_')) {
            $response = $client->get('https://api.razorpay.com/v1/payments/'.rawurlencode($paymentId));
            if ($response->successful() && $this->matches($order, $response->json())) {
                return $response->json();
            }
        }
        if ($payment?->razorpay_order_id && str_starts_with($payment->razorpay_order_id, 'order_')) {
            $response = $client->get('https://api.razorpay.com/v1/orders/'.rawurlencode($payment->razorpay_order_id).'/payments');
        } else {
            $response = $client->get('https://api.razorpay.com/v1/payments', ['count' => 100]);
        }
        if ($response->successful()) {
            foreach ($response->json('items', []) as $candidate) {
                if ($this->matches($order, $candidate)) {
                    return $candidate;
                }
            }
        }
        return null;
    }

    public function matches(Order $order, array $data): bool
    {
        if (($data['status'] ?? '') !== 'captured'
            || !str_starts_with($data['id'] ?? '', 'pay_')
            || ($data['currency'] ?? '') !== 'INR'
            || (int) ($data['amount'] ?? -1) !== (int) round($order->grand_total * 100)) {
            return false;
        }
        $gatewayOrderId = $order->payment?->razorpay_order_id;
        if ($gatewayOrderId && str_starts_with($gatewayOrderId, 'order_')) {
            return $gatewayOrderId === ($data['order_id'] ?? null);
        }
        return ($data['notes']['order_number'] ?? null) === $order->order_number
            || (string) ($data['notes']['order_id'] ?? '') === (string) $order->id
            || ($data['description'] ?? '') === 'Order #'.$order->order_number.' Payment';
    }

    /** All payment entry points share this transaction and order lock. */
    public function confirm(Order $order, array $data, string $source, bool $retryStockReview = false): string
    {
        return DB::transaction(function () use ($order, $data, $source, $retryStockReview) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->order_status === 'cancelled') {
                return 'cancelled';
            }
            $payment = $order->payment;
            if ($order->payment_status === 'paid') {
                if (empty($payment?->response_payload['stock_review'])) {
                    return 'already_processed';
                }
                if (!$retryStockReview) {
                    return 'stock_review';
                }
            }
            if (!$this->matches($order, $data)) {
                return 'payment_mismatch';
            }
            if ($order->payment_status === 'refunded') {
                return 'already_processed';
            }

            $stockReview = null;
            try {
                app(StockService::class)->deductStockForOrder($order);
            } catch (InsufficientOrderStock $e) {
                // Keep the received money on record, but never promise unavailable stock.
                $stockReview = $e->getMessage();
            }
            $payment = $payment ?: Payment::create([
                'order_id' => $order->id, 'payment_method' => 'online', 'amount' => $order->grand_total,
            ]);
            $payment->update([
                'razorpay_payment_id' => $data['id'], 'razorpay_order_id' => $data['order_id'] ?? $payment->razorpay_order_id,
                'status' => 'paid',
                'response_payload' => array_merge((array) $payment->response_payload, [
                    'verified_at' => now()->toIso8601String(), 'source' => $source,
                    'razorpay_details' => $data, 'stock_review' => $stockReview,
                ]),
            ]);
            $order->update([
                'payment_status' => 'paid', 'order_status' => $stockReview ? 'pending' : 'confirmed',
                'reserved_until' => null, 'is_legacy_pending' => false,
            ]);
            $order->calculateRazorpayCharge();
            Notification::create([
                'title' => $stockReview ? 'Paid Order Needs Stock Review' : 'Order Paid ('.$source.')',
                'message' => $stockReview
                    ? 'Order #'.$order->order_number.': payment received, but stock is unavailable. Review fulfilment or refund.'
                    : 'Order #'.$order->order_number.' paid and confirmed.',
                'type' => 'new_order', 'order_id' => $order->id, 'is_read' => false,
            ]);
            return $stockReview ? 'stock_review' : 'confirmed';
        }, 3);
    }
}
