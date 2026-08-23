<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSize;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Services\StockService;
use App\Services\WhatsAppService;
use App\Mail\OrderConfirmationMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    protected CartService $cartService;

    protected StockService $stockService;

    protected PaymentService $paymentService;

    protected WhatsAppService $whatsAppService;

    public function __construct(
        CartService $cartService,
        StockService $stockService,
        PaymentService $paymentService,
        WhatsAppService $whatsAppService
    ) {
        $this->cartService = $cartService;
        $this->stockService = $stockService;
        $this->paymentService = $paymentService;
        $this->whatsAppService = $whatsAppService;
    }

    public function index()
    {
        $cart = $this->cartService->getCart();
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Please add products before checking out.');
        }

        $stockCheck = $this->cartService->validateCartStock();
        if (! $stockCheck['valid']) {
            return redirect()->route('cart.index')->with('error', implode(' ', $stockCheck['errors']));
        }

        $summary = $this->cartService->getSummary();

        $email = session('customer_email');
        $lastOrder = null;
        if ($email) {
            $lastOrder = Order::where('customer_email', $email)->latest()->first();
        }

        return view('frontend.checkout', compact('cart', 'summary', 'lastOrder'));
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'house_building' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pin_code' => 'required|string|max:20',
            'payment_method' => 'required|in:cod,online',
            'notes' => 'nullable|string|max:500',
        ]);

        $cart = $this->cartService->getCart();
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Final revalidation of stock right before order creation
        $stockCheck = $this->cartService->validateCartStock();
        if (! $stockCheck['valid']) {
            return redirect()->route('cart.index')->with('error', implode(' ', $stockCheck['errors']));
        }

        $summary = $this->cartService->getSummary();

        try {
            $order = DB::transaction(function () use ($validated, $cart, $summary) {
                $orderNumber = Order::generateOrderNumber();

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'user_id' => auth()->check() ? auth()->id() : null,
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_email' => $validated['customer_email'],
                    'house_building' => $validated['house_building'],
                    'street' => $validated['street'],
                    'area' => $validated['area'],
                    'city' => $validated['city'],
                    'district' => $validated['district'],
                    'state' => $validated['state'],
                    'pin_code' => $validated['pin_code'],
                    'subtotal' => $summary['subtotal'],
                    'discount' => $summary['discount'],
                    'shipping' => $summary['shipping'],
                    'grand_total' => $summary['grand_total'],
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => ($validated['payment_method'] === 'cod') ? 'pending' : 'pending',
                    'order_status' => ($validated['payment_method'] === 'cod') ? 'confirmed' : 'pending',
                    'notes' => $validated['notes'] ?? null,
                ]);

                $itemsForDeduction = [];

                foreach ($cart as $item) {
                    $productSize = ProductSize::where('product_id', $item['product_id'])
                        ->where('size', $item['size'])
                        ->first();

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_size_id' => $productSize ? $productSize->id : null,
                        'product_name' => $item['name'],
                        'size' => $item['size'],
                        'unit_price' => $item['price'],
                        'discount_amount' => $item['discount_amount'],
                        'final_unit_price' => $item['final_price'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    $itemsForDeduction[] = [
                        'product_id' => $item['product_id'],
                        'size' => $item['size'],
                        'quantity' => $item['quantity'],
                    ];
                }

                // If Cash on Delivery, deduct stock immediately on order creation
                if ($validated['payment_method'] === 'cod') {
                    $this->stockService->deductStockForOrderItems($itemsForDeduction);

                    // Create Admin Notification
                    Notification::create([
                        'title' => 'New Order Received',
                        'message' => "Order #{$order->order_number} placed by {$order->customer_name} (₹{$order->grand_total}) - COD",
                        'type' => 'new_order',
                        'order_id' => $order->id,
                        'is_read' => false,
                    ]);
                }

                return $order;
            });

            // Process Payment Response
            $paymentResult = $this->paymentService->initiatePayment($order, $validated['payment_method']);

            if ($validated['payment_method'] === 'cod') {
                $this->cartService->clear();
                $this->whatsAppService->sendOrderConfirmation($order);

                if ($order->customer_email) {
                    try {
                        Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
                    } catch (Exception $e) {
                        \Log::error('Order Confirmation Email Error: '.$e->getMessage());
                    }
                }

                return redirect()->route('checkout.success', ['order_number' => $order->order_number])
                    ->with('success', 'Order placed successfully!');
            }

            // Online Payment: Return Razorpay modal details
            return view('frontend.checkout_online_payment', compact('order', 'paymentResult'));

        } catch (Exception $e) {
            return back()->with('error', 'Order creation failed: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Verify online payment callback signature from frontend or webhook.
     */
    public function verifyOnlinePayment(Request $request)
    {
        $request->validate([
            'order_number' => 'required|exists:orders,order_number',
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $order = Order::where('order_number', $request->order_number)->firstOrFail();

        // Lock order to prevent duplicate processing
        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success', ['order_number' => $order->order_number]);
        }

        $verified = $this->paymentService->verifyOnlinePayment(
            $order,
            $request->razorpay_payment_id,
            $request->razorpay_order_id,
            $request->razorpay_signature
        );

        if ($verified) {
            // Deduct stock after successful payment verification
            $itemsForDeduction = $order->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'size' => $item->size,
                    'quantity' => $item->quantity,
                ];
            })->toArray();

            $this->stockService->deductStockForOrderItems($itemsForDeduction);

            // Create Admin Notification
            Notification::create([
                'title' => 'New Paid Order',
                'message' => "Order #{$order->order_number} placed by {$order->customer_name} (₹{$order->grand_total}) - Online Paid",
                'type' => 'new_order',
                'order_id' => $order->id,
                'is_read' => false,
            ]);

            $this->cartService->clear();
            $this->whatsAppService->sendOrderConfirmation($order);

            if ($order->customer_email) {
                try {
                    Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
                } catch (Exception $e) {
                    \Log::error('Order Confirmation Email Error: '.$e->getMessage());
                }
            }

            return redirect()->route('checkout.success', ['order_number' => $order->order_number])
                ->with('success', 'Payment successful! Your order has been placed.');
        }

        return redirect()->route('checkout.index')->with('error', 'Payment verification failed. Please try again or choose COD.');
    }

    public function success(string $order_number)
    {
        $order = Order::where('order_number', $order_number)
            ->with(['items.product', 'payment'])
            ->firstOrFail();

        return view('frontend.order_success', compact('order'));
    }
}
