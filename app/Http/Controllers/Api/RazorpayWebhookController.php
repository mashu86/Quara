<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\RazorpayOrderService;
use App\Services\WhatsAppService;
use App\Mail\OrderConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.razorpay.webhook_secret') ?: config('services.razorpay.secret');
        $signature = (string) $request->header('X-Razorpay-Signature');
        if (!$secret || !$signature || !hash_equals(hash_hmac('sha256', $request->getContent(), $secret), $signature)) {
            return response()->json(['status' => 'invalid_signature'], 400);
        }
        $event = $request->input('event');
        if (!in_array($event, ['payment.authorized', 'payment.captured', 'order.paid', 'payment.failed'])) {
            return response()->json(['status' => 'ignored']);
        }
        $data = $request->input('payload.payment.entity') ?? $request->input('payload.order.entity');
        if (!$data) {
            return response()->json(['status' => 'no_payload']);
        }
        $gatewayOrderId = $data['order_id'] ?? $data['id'] ?? null;
        $order = Payment::where('razorpay_order_id', $gatewayOrderId)->first()?->order;
        if (!$order && ($number = $data['notes']['order_number'] ?? $data['receipt'] ?? null)) {
            $order = Order::where('order_number', $number)->first();
        }
        if (!$order) {
            return response()->json(['status' => 'order_not_found']);
        }
        if ($order->order_status === 'cancelled') {
            return response()->json(['status' => 'cancelled']);
        }
        if ($event === 'payment.failed') {
            // A failed attempt does not end an order: Checkout can retry another payment.
            DB::transaction(function () use ($order, $data) {
                $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                if ($order->payment_status === 'pending' && $order->order_status !== 'cancelled' && $order->payment) {
                    $order->payment->update([
                        'status' => 'failed',
                        'response_payload' => array_merge((array) $order->payment->response_payload, [
                            'failed_attempt' => $data['id'] ?? null,
                            'failed_at' => now()->toIso8601String(),
                        ]),
                    ]);
                    $order->update(['reserved_until' => null]);
                }
            });
            return response()->json(['status' => 'success']);
        }
        if ($event === 'payment.authorized') {
            DB::transaction(function () use ($order, $data) {
                $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                if ($order->payment_status === 'pending' && $order->order_status === 'pending') {
                    $payment = $order->payment ?: Payment::create([
                        'order_id' => $order->id, 'payment_method' => 'online', 'amount' => $order->grand_total,
                    ]);
                    $payment->update([
                        'status' => 'pending',
                        'razorpay_payment_id' => $data['id'] ?? $payment->razorpay_payment_id,
                        'response_payload' => array_merge((array) $payment->response_payload, [
                            'authorized_at' => now()->toIso8601String(),
                            'razorpay_details' => $data,
                        ]),
                    ]);
                    // Repeated authorization webhooks do not extend an active hold.
                    if (!$order->reserved_until || $order->reserved_until->isPast()) {
                        $order->update(['reserved_until' => now()->addMinutes(2)]);
                    }
                }
            });
            return response()->json(['status' => 'authorized_pending_capture']);
        }
        $service = app(RazorpayOrderService::class);
        if (!str_starts_with($data['id'] ?? '', 'pay_')) {
            $data = $service->findCapturedPayment($order);
            if (!$data) {
                return response()->json(['status' => 'payment_not_available'], 503);
            }
        }
        $result = $service->confirm($order, $data, 'Webhook');
        if ($result === 'confirmed') {
            $order->refresh();
            try {
                app(WhatsAppService::class)->sendOrderConfirmation($order);
                if ($order->customer_email) {
                    Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
                }
            } catch (\Exception $e) {
                Log::error('Webhook confirmation message failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }
        return response()->json(['status' => $result === 'confirmed' ? 'success' : $result]);
    }
}
