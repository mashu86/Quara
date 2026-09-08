<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentDiscrepancyController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $razorpayKey = config('services.razorpay.key');
        $razorpaySecret = config('services.razorpay.secret');
        $isConfigured = !empty($razorpayKey) && !empty($razorpaySecret);

        // Fetch online orders
        $orders = Order::with(['items.product', 'payment'])
            ->where('payment_method', 'online')
            ->orderBy('id', 'desc')
            ->get();

        $capturedPaymentsMap = [];
        if ($isConfigured) {
            try {
                // Fetch recent captured payments from Razorpay API
                $response = Http::withoutVerifying()
                    ->withBasicAuth($razorpayKey, $razorpaySecret)
                    ->get('https://api.razorpay.com/v1/payments', [
                        'count' => 100,
                    ]);

                if ($response->successful()) {
                    foreach ($response->json('items', []) as $pItem) {
                        if (in_array($pItem['status'] ?? '', ['captured', 'authorized'])) {
                            $notes = $pItem['notes'] ?? [];
                            $orderNum = $notes['order_number'] ?? ($notes['order_id'] ?? ($pItem['description'] ?? ''));
                            $rzpOrdId = $pItem['order_id'] ?? null;

                            if ($rzpOrdId) {
                                $capturedPaymentsMap['rzp_order_' . $rzpOrdId] = $pItem;
                            }
                            if ($orderNum) {
                                $capturedPaymentsMap['order_num_' . trim($orderNum)] = $pItem;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // Ignore API connection issues for list rendering
            }
        }

        $discrepancies = [];

        foreach ($orders as $order) {
            $payment = $order->payment;
            $rzpPaymentId = $payment ? $payment->razorpay_payment_id : null;
            $rzpOrderId = $payment ? $payment->razorpay_order_id : null;

            $hasCapturedOnRzp = false;
            $rzpDetails = null;

            // Check if captured payment exists on Razorpay map
            if ($rzpOrderId && isset($capturedPaymentsMap['rzp_order_' . $rzpOrderId])) {
                $hasCapturedOnRzp = true;
                $rzpDetails = $capturedPaymentsMap['rzp_order_' . $rzpOrderId];
            } elseif (isset($capturedPaymentsMap['order_num_' . $order->order_number])) {
                $hasCapturedOnRzp = true;
                $rzpDetails = $capturedPaymentsMap['order_num_' . $order->order_number];
            }

            $type = null;
            $issueTitle = '';
            $issueDescription = '';

            // Type 1: Razorpay has Captured payment, but DB order is Pending
            if ($hasCapturedOnRzp && $order->payment_status !== 'paid') {
                $type = 'captured_but_pending';
                $issueTitle = 'Razorpay-ൽ Paid ആണ്, പക്ഷെ System-ൽ Pending';
                $issueDescription = "Customer Razorpay-ൽ ₹{$order->grand_total} വിജയകരമായി അടച്ചു (Payment ID: " . ($rzpDetails['id'] ?? 'N/A') . "), പക്ഷെ System-ൽ ഇനിയും Pending ആയി നിൽക്കുന്നു.";
            }
            // Type 2: DB order is marked Paid, but NO captured payment found on Razorpay & no razorpay_payment_id
            elseif ($order->payment_status === 'paid' && !$hasCapturedOnRzp && empty($rzpPaymentId)) {
                $type = 'paid_without_razorpay_id';
                $issueTitle = 'System-ൽ Paid ആണ്, Razorpay Payment ID ഇല്ല';
                $issueDescription = 'ഓർഡർ system-ൽ Paid ആയി മാർക്ക് ചെയ്തിട്ടുണ്ട്, പക്ഷെ Razorpay Payment ID കാണുന്നില്ല.';
            }

            if ($type) {
                $discrepancies[] = [
                    'order' => $order,
                    'type' => $type,
                    'issue_title' => $issueTitle,
                    'issue_description' => $issueDescription,
                    'rzp_details' => $rzpDetails,
                ];
            }
        }

        $totalDiscrepancyCount = count($discrepancies);
        $capturedPendingCount = count(array_filter($discrepancies, fn($d) => $d['type'] === 'captured_but_pending'));
        $paidWithoutIdCount = count(array_filter($discrepancies, fn($d) => $d['type'] === 'paid_without_razorpay_id'));

        return view('admin.payment_discrepancies.index', compact(
            'discrepancies',
            'totalDiscrepancyCount',
            'capturedPendingCount',
            'paidWithoutIdCount',
            'isConfigured'
        ));
    }

    public function reconcile(Request $request, Order $order)
    {
        $razorpayKey = config('services.razorpay.key');
        $razorpaySecret = config('services.razorpay.secret');

        if (empty($razorpayKey) || empty($razorpaySecret)) {
            return back()->with('error', 'Razorpay API credentials are not configured.');
        }

        $payment = $order->payment;
        $capturedPayment = null;

        try {
            // 1. Check by explicit payment ID if set
            $paymentIdToCheck = $payment ? $payment->razorpay_payment_id : null;
            if ($paymentIdToCheck && str_starts_with($paymentIdToCheck, 'pay_')) {
                $response = Http::withoutVerifying()
                    ->withBasicAuth($razorpayKey, $razorpaySecret)
                    ->get("https://api.razorpay.com/v1/payments/{$paymentIdToCheck}");

                if ($response->successful() && in_array($response->json('status'), ['captured', 'authorized'])) {
                    $capturedPayment = $response->json();
                }
            }

            // 2. Check by Razorpay Order ID
            if (!$capturedPayment && $payment && !empty($payment->razorpay_order_id) && !str_starts_with($payment->razorpay_order_id, 'rzp_order_')) {
                $response = Http::withoutVerifying()
                    ->withBasicAuth($razorpayKey, $razorpaySecret)
                    ->get("https://api.razorpay.com/v1/orders/{$payment->razorpay_order_id}/payments");

                if ($response->successful()) {
                    foreach ($response->json('items', []) as $item) {
                        if (in_array($item['status'] ?? '', ['captured', 'authorized'])) {
                            $capturedPayment = $item;
                            break;
                        }
                    }
                }
            }

            // 3. Check by exact Order Number in notes/receipt
            if (!$capturedPayment) {
                $response = Http::withoutVerifying()
                    ->withBasicAuth($razorpayKey, $razorpaySecret)
                    ->get('https://api.razorpay.com/v1/payments', [
                        'count' => 50,
                    ]);

                if ($response->successful()) {
                    $orderNum = trim($order->order_number ?? '');

                    foreach ($response->json('items', []) as $item) {
                        if (!in_array($item['status'] ?? '', ['captured', 'authorized'])) {
                            continue;
                        }

                        $notes = $item['notes'] ?? [];
                        $receipt = $notes['order_number'] ?? ($notes['order_id'] ?? ($item['description'] ?? ''));

                        if (!empty($orderNum) && str_contains($receipt, $orderNum)) {
                            $capturedPayment = $item;
                            break;
                        }
                    }
                }
            }

            if ($capturedPayment) {
                // Deduct stock if marking as paid from pending
                if ($order->payment_status !== 'paid') {
                    $itemsForDeduction = $order->items->map(fn($item) => [
                        'product_id' => $item->product_id,
                        'size' => $item->size,
                        'quantity' => $item->quantity,
                    ])->toArray();

                    $this->stockService->deductStockForOrderItems($itemsForDeduction);
                }

                if (!$payment) {
                    $payment = Payment::create([
                        'order_id' => $order->id,
                        'payment_method' => 'online',
                        'amount' => $order->grand_total,
                    ]);
                }

                $payment->update([
                    'razorpay_payment_id' => $capturedPayment['id'],
                    'razorpay_order_id' => $capturedPayment['order_id'] ?? $payment->razorpay_order_id,
                    'status' => 'paid',
                    'response_payload' => array_merge((array) ($payment->response_payload ?? []), [
                        'reconciled_at' => now()->toIso8601String(),
                        'razorpay_details' => $capturedPayment,
                    ]),
                ]);

                $order->update([
                    'payment_status' => 'paid',
                    'order_status' => 'confirmed',
                    'reserved_until' => null,
                    'is_legacy_pending' => false,
                ]);

                $order->calculateRazorpayCharge();

                Notification::create([
                    'title' => 'Order Reconciled (Paid)',
                    'message' => "Order #{$order->order_number} placed by {$order->customer_name} (₹{$order->grand_total}) - Reconciled with Razorpay",
                    'type' => 'new_order',
                    'order_id' => $order->id,
                    'is_read' => false,
                ]);

                return back()->with('success', "Order #{$order->order_number} successfully reconciled with Razorpay! Marked as Paid & Confirmed (Payment ID: {$capturedPayment['id']}).");
            } else {
                // If not paid on Razorpay and currently pending, confirm it stays pending
                if ($order->payment_status === 'pending') {
                    return back()->with('info', "Checked Razorpay API. No captured payment found for Order #{$order->order_number}. Order remains Pending.");
                }

                // If marked paid in system by mistake, reset to pending & move stock to internal reserved pool
                if ($order->payment_status === 'paid' && $request->boolean('force_reset_pending')) {
                    $itemsToReserve = $order->items->map(fn($item) => [
                        'product_id' => $item->product_id,
                        'size' => $item->size,
                        'quantity' => $item->quantity,
                    ])->toArray();

                    $this->stockService->reserveStockForOrderItems($itemsToReserve, "Order #{$order->order_number} Reset to Pending (Moved to Internal Reserved Stock)");

                    if ($payment) {
                        $payment->update(['status' => 'pending', 'razorpay_payment_id' => null]);
                    }

                    $order->update([
                        'payment_status' => 'pending',
                        'order_status' => 'pending',
                    ]);

                    return back()->with('success', "Order #{$order->order_number} reset to Pending & stock moved to Internal Reserved Stock (hidden from public storefront).");
                }

                return back()->with('warning', "No captured payment found on Razorpay for Order #{$order->order_number}.");
            }
        } catch (Exception $e) {
            return back()->with('error', 'Reconciliation Error: ' . $e->getMessage());
        }
    }

    public function reconcileAll(Request $request)
    {
        $orders = Order::where('payment_method', 'online')
            ->where('payment_status', 'pending')
            ->get();

        $fixedCount = 0;
        foreach ($orders as $order) {
            try {
                $subRequest = new Request();
                $res = $this->reconcile($subRequest, $order);
                if (session('success')) {
                    $fixedCount++;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return back()->with('success', "Reconciliation completed. Fixed {$fixedCount} mismatched orders!");
    }
}
