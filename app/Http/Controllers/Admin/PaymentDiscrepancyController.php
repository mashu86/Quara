<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            ->where('order_status', '!=', 'cancelled')
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
                        if (in_array($pItem['status'] ?? '', ['captured'])) {
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
                $issueTitle = 'Paid on Razorpay, but not marked Paid here';
                $issueDescription = "Razorpay shows a captured payment of ₹{$order->grand_total} (Payment ID: " . ($rzpDetails['id'] ?? 'N/A') . "), but this order has not been marked Paid in the system.";
            }
            // Type 2: DB order is marked Paid, but NO captured payment found on Razorpay & no razorpay_payment_id
            elseif ($order->payment_status === 'paid' && !$hasCapturedOnRzp && empty($rzpPaymentId)) {
                $type = 'paid_without_razorpay_id';
                $issueTitle = 'Marked Paid without a Razorpay Payment ID';
                $issueDescription = 'This order is marked Paid in the system, but no Razorpay Payment ID is recorded.';
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
        if ($order->order_status === 'cancelled') {
            return back()->with('info', 'Cancelled orders are excluded from Razorpay checks.');
        }
        $payment = $order->payment;
        try {
            $service = app(\App\Services\RazorpayOrderService::class);
            $capturedPayment = $service->findCapturedPayment($order);
            if ($capturedPayment) {
                $result = $service->confirm($order, $capturedPayment, 'Reconciliation', true);
                return back()->with(in_array($result, ['confirmed', 'already_processed']) ? 'success' : 'warning',
                    'Reconciliation: '.str_replace('_', ' ', $result).'.');
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
