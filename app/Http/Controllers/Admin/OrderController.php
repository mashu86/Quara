<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderCancelledMail;
use App\Models\Notification;
use App\Models\Order;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['items', 'payment']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('customer_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('customer_phone', 'LIKE', '%' . $search . '%');
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $sort = $request->get('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('id', 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'payment', 'notifications']);

        // Mark unread notifications for this order as read
        Notification::where('order_id', $order->id)->where('is_read', false)->update(['is_read' => true]);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => 'required|in:pending,confirmed,processing,packed,shipped,delivered,cancelled',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $prevOrderStatus = $order->order_status;
        $newOrderStatus = $validated['order_status'];

        // Enforce Cancellation Restriction: If order cancellation is locked by admin
        if ($order->is_cancellation_disabled && $newOrderStatus === 'cancelled') {
            return back()->withErrors(['order_status' => "This order (#{$order->order_number}) is locked and CANNOT be cancelled."])->withInput();
        }

        // If order is changed to cancelled and was previously confirmed/processing, restore stock
        if ($newOrderStatus === 'cancelled' && $prevOrderStatus !== 'cancelled') {
            $items = $order->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'size' => $item->size,
                    'quantity' => $item->quantity,
                ];
            })->toArray();

            $this->stockService->restoreStockForOrderItems($items, "Order #{$order->order_number} Cancelled by Admin");

            // Dispatch Order Cancellation Email if email is present
            if ($order->customer_email) {
                try {
                    Mail::to($order->customer_email)->send(new OrderCancelledMail($order));
                } catch (Exception $e) {
                    \Log::error('Order Cancelled Email Error: ' . $e->getMessage());
                }
            }
        }

        $order->update($validated);

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Order status updated successfully!');
    }

    public function updateCourierDispatch(Request $request, Order $order)
    {
        $validated = $request->validate([
            'is_dispatched_to_courier' => 'nullable|boolean',
            'courier_partner' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
        ]);

        $isDispatched = $request->has('is_dispatched_to_courier');

        $order->update([
            'is_dispatched_to_courier' => $isDispatched,
            'courier_partner' => $validated['courier_partner'] ?? null,
            'tracking_number' => $validated['tracking_number'] ?? null,
            'dispatched_at' => $isDispatched ? ($order->dispatched_at ?? now()) : null,
            'order_status' => $isDispatched ? 'shipped' : $order->order_status,
        ]);

        $statusMsg = $isDispatched
            ? 'Order status updated: Handed Over to Courier Partner!'
            : 'Order status updated: Packing Process.';

        return redirect()->route('admin.orders.show', $order->id)->with('success', $statusMsg);
    }

    public function toggleCancellationLock(Request $request, Order $order)
    {
        $order->is_cancellation_disabled = !$order->is_cancellation_disabled;
        $order->save();

        $statusMsg = $order->is_cancellation_disabled ? 'Order cancellation has been LOCKED (Prohibited).' : 'Order cancellation lock has been UNLOCKED.';

        return back()->with('success', $statusMsg);
    }

    public function invoice(Order $order)
    {
        $order->load(['items.product', 'payment']);
        return view('admin.orders.invoice', compact('order'));
    }

    public function sendFollowupEmail(Request $request, Order $order)
    {
        if (!$order->customer_email) {
            return response()->json(['success' => false, 'message' => 'No customer email provided.']);
        }

        try {
            Mail::to($order->customer_email)->send(new \App\Mail\OrderConfirmationMail($order));
            return response()->json(['success' => true, 'message' => 'Follow-up email dispatched.']);
        } catch (Exception $e) {
            \Log::error('Order Followup Email Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function incrementWaCount(Request $request, Order $order)
    {
        $type = $request->input('type', 'thank_you');
        if ($type === 'couriered') {
            $order->increment('wa_couriered_count');
        } else {
            $order->increment('wa_thank_you_count');
        }

        $order->refresh();

        return response()->json([
            'success' => true,
            'wa_thank_you_count' => $order->wa_thank_you_count,
            'wa_couriered_count' => $order->wa_couriered_count,
        ]);
    }
}
