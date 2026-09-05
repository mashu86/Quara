<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Services\StockService;
use App\Services\WhatsAppService;
use App\Mail\OrderConfirmationMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RazorpayWebhookController extends Controller
{
    protected StockService $stockService;
    protected WhatsAppService $whatsAppService;

    public function __construct(StockService $stockService, WhatsAppService $whatsAppService)
    {
        $this->stockService = $stockService;
        $this->whatsAppService = $whatsAppService;
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $webhookSecret = config('services.razorpay.webhook_secret') ?? config('services.razorpay.secret');

        Log::info('Razorpay Webhook Received', ['event' => $request->input('event')]);

        // Verify Webhook Signature if secret exists and signature present
        if (!empty($webhookSecret) && !empty($signature)) {
            $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
            if (!hash_equals($expectedSignature, $signature)) {
                Log::error('Razorpay Webhook signature verification failed');
                return response()->json(['status' => 'invalid_signature'], 400);
            }
        }

        $event = $request->input('event');
        $data = $request->input('payload.payment.entity') ?? $request->input('payload.order.entity');

        if (!$data) {
            return response()->json(['status' => 'no_payload'], 200);
        }

        $razorpayOrderId = $data['order_id'] ?? ($data['id'] ?? null);
        $razorpayPaymentId = $data['id'] ?? null;
        $orderReceipt = $data['notes']['order_number'] ?? ($data['receipt'] ?? null);

        // Find matching Order
        $order = null;
        if ($razorpayOrderId) {
            $payment = Payment::where('razorpay_order_id', $razorpayOrderId)->first();
            if ($payment) {
                $order = $payment->order;
            }
        }

        if (!$order && $orderReceipt) {
            $order = Order::where('order_number', $orderReceipt)->first();
        }

        if (!$order) {
            Log::warning('Razorpay Webhook: Matching order not found for razorpay_order_id: ' . $razorpayOrderId);
            return response()->json(['status' => 'order_not_found'], 200);
        }

        if ($event === 'payment.captured' || $event === 'order.paid') {
            if ($order->payment_status === 'paid') {
                return response()->json(['status' => 'already_processed'], 200);
            }

            // Deduct stock safely
            try {
                $itemsForDeduction = $order->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'size' => $item->size,
                        'quantity' => $item->quantity,
                    ];
                })->toArray();

                $this->stockService->deductStockForOrderItems($itemsForDeduction);
            } catch (Exception $e) {
                Log::error('Webhook Stock Deduction Exception: ' . $e->getMessage());
            }

            // Update Payment record
            if ($order->payment) {
                $order->payment->update([
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'status' => 'paid',
                    'response_payload' => array_merge((array) ($order->payment->response_payload ?? []), [
                        'webhook_event' => $event,
                        'received_at' => now()->toIso8601String(),
                    ]),
                ]);
            }

            // Confirm Order and clear stock reservation
            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
                'reserved_until' => null,
            ]);

            // Calculate fees & net revenue
            $order->calculateRazorpayCharge();

            // Admin notification
            Notification::create([
                'title' => 'New Paid Order (Webhook Verified)',
                'message' => "Order #{$order->order_number} placed by {$order->customer_name} (₹{$order->grand_total}) - Webhook Confirmed",
                'type' => 'new_order',
                'order_id' => $order->id,
                'is_read' => false,
            ]);

            // Send WhatsApp & Email confirmations
            $this->whatsAppService->sendOrderConfirmation($order);

            if ($order->customer_email) {
                try {
                    Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
                } catch (Exception $e) {
                    Log::error('Webhook Email Confirmation Error: ' . $e->getMessage());
                }
            }

            Log::info('Razorpay Webhook: Order #' . $order->order_number . ' successfully confirmed and paid!');
        } elseif ($event === 'payment.failed') {
            if ($order->payment_status === 'pending') {
                if ($order->payment) {
                    $order->payment->update(['status' => 'failed']);
                }
                $order->update([
                    'payment_status' => 'failed',
                    'reserved_until' => null, // Instantly release stock lock
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
