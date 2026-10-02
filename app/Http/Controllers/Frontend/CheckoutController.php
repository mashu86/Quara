<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSize;
use App\Services\CartService;
use App\Services\DistrictOfferService;
use App\Services\PincodeService;
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
        $summary['has_active_coupons'] = \App\Models\MasterCoupon::where('status', true)->where('starts_at', '<=', now())->where('ends_at', '>=', now())->exists();
        $coupon = session('master_coupon');
        if ($coupon) {
            $applied = app(\App\Services\MasterCouponService::class)->apply($coupon['code'], $cart, $summary['subtotal'], $summary['discount'], (int) $coupon['id']);
            if (!$applied['valid']) { session()->forget('master_coupon'); $coupon = null; }
            else $summary['coupon_discount'] = $applied['discount'];
        }

        $email = session('customer_email');
        $lastOrder = null;
        if ($email) {
            $lastOrder = Order::where('customer_email', $email)->latest()->first();
        }

        return view('frontend.checkout', compact('cart', 'summary', 'lastOrder'));
    }

    public function applyCoupon(Request $request)
    {
        $validated = $request->validate(['code' => 'required|string|max:80']);
        $cart = $this->cartService->getCart();
        $summary = $this->cartService->getSummary();
        $result = app(\App\Services\MasterCouponService::class)->apply($validated['code'], $cart, $summary['subtotal'], $summary['discount']);
        if (!$result['valid']) return response()->json(['success' => false, 'message' => $result['message']], 422);
        session(['master_coupon' => ['id' => $result['coupon']->id, 'code' => $result['coupon']->code]]);
        $districtDiscount = 0;
        $pin = (string) $request->input('pin_code', '');
        if (preg_match('/^[1-9][0-9]{5}$/', $pin)) {
            try {
                $location = app(PincodeService::class)->lookup($pin);
                $offer = app(DistrictOfferService::class)->eligible($location['district'], $location['state'], now('Asia/Kolkata'));
                $districtDiscount = app(DistrictOfferService::class)->snapshot($offer, $summary['subtotal'] - $summary['discount'], true)['district_offer_discount'];
            } catch (\Throwable $e) { /* Pricing is revalidated on order creation. */ }
        }
        $raw = max(0, round($summary['subtotal'] - $summary['discount'] - $districtDiscount - $result['discount'] + $summary['shipping'], 2));
        return response()->json(['success' => true, 'code' => $result['coupon']->code, 'discount' => $result['discount'], 'grand_total' => ceil($raw), 'rounding_adjustment' => round(ceil($raw) - $raw, 2), 'district_discount' => $districtDiscount, 'pin_code' => $pin, 'message' => 'Coupon applied successfully.']);
    }

    public function removeCoupon()
    {
        session()->forget('master_coupon');
        return response()->json(['success' => true, 'message' => 'Coupon removed.']);
    }

    public function districtOffer(Request $request, PincodeService $pins, DistrictOfferService $offers)
    {
        $request->validate(['pin_code' => 'required|regex:/^[1-9][0-9]{5}$/']);
        $location = $pins->lookup($request->input('pin_code'));
        $summary = $this->cartService->getSummary();
        $snapshot = $offers->snapshot($offers->eligible($location['district'], $location['state'], now('Asia/Kolkata')),
            $summary['subtotal'] - $summary['discount'], true);
        $raw = max(0, round($summary['subtotal'] - $summary['discount'] - $snapshot['district_offer_discount'] + $summary['shipping'], 2));

        return response()->json($location + [
            'offer' => $snapshot['district_offer_snapshot'],
            'discount' => $snapshot['district_offer_discount'],
            'grand_total' => ceil($raw), 'rounding_adjustment' => round(ceil($raw) - $raw, 2),
        ]);
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
            'pin_code' => 'required|regex:/^[1-9][0-9]{5}$/',
            'payment_method' => 'required|in:online',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validated['payment_method'] === 'cod') {
            return back()->withInput()->with('error', 'Cash on Delivery is disabled. Please select Online Payment.');
        }

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

        // Verify offer eligibility independently; preserve the entered delivery address.
        try {
            $location = app(PincodeService::class)->lookup($validated['pin_code']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $location = null; // Manual delivery details remain usable without a postal lookup.
        }

            try {
            $order = DB::transaction(function () use ($validated, $cart, $summary, $location) {
                $this->stockService->lockAndValidateCheckoutStock($cart);
                $orderNumber = Order::generateOrderNumber();
                $offers = app(DistrictOfferService::class);
                $snapshot = $offers->snapshot($location ? $offers->eligible($location['district'], $location['state'], now('Asia/Kolkata')) : null,
                    $summary['subtotal'] - $summary['discount'], true);
                $summary['discount'] += $snapshot['district_offer_discount'];
                $summary['district_offer_discount'] = $snapshot['district_offer_discount'];
                $couponData = null;
                $couponSession = session('master_coupon');
                if ($couponSession) {
                    $couponData = app(\App\Services\MasterCouponService::class)->apply($couponSession['code'], $cart, $summary['subtotal'], $summary['discount'] - $snapshot['district_offer_discount'], (int) $couponSession['id']);
                    if (!$couponData['valid']) throw new \RuntimeException($couponData['message']);
                    $summary['discount'] += $couponData['discount'];
                }
                $raw = max(0, round($summary['subtotal'] - $summary['discount'] + $summary['shipping'], 2));
                $summary['grand_total'] = ceil($raw);
                $summary['rounding_adjustment'] = round(ceil($raw) - $raw, 2);

                $order = Order::create([
                    ...$snapshot,
                    'order_number' => $orderNumber,
                    'user_id' => auth()->check() ? auth()->id() : null,
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_email' => $validated['customer_email'] ?? null,
                    'house_building' => $validated['house_building'],
                    'street' => $validated['street'],
                    'area' => $validated['area'],
                    'city' => $validated['city'],
                    'district' => $validated['district'],
                    'state' => $validated['state'],
                    'pin_code' => $validated['pin_code'],
                    'subtotal' => $summary['subtotal'],
                    'discount' => $summary['discount'],
                    'master_coupon_id' => $couponData['coupon']->id ?? null,
                    'coupon_code' => $couponData['coupon']->code ?? null,
                    'coupon_discount' => $couponData['discount'] ?? 0,
                    'coupon_usage_recorded' => false,
                    'shipping' => $summary['shipping'],
                    'rounding_adjustment' => $summary['rounding_adjustment'] ?? 0.00,
                    'grand_total' => $summary['grand_total'],
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $summary['grand_total'] <= 0 ? 'paid' : 'pending',
                    'order_status' => ($validated['payment_method'] === 'cod' || $summary['grand_total'] <= 0) ? 'confirmed' : 'pending',
                    'reserved_until' => ($validated['payment_method'] === 'online' && $summary['grand_total'] > 0) ? now()->addMinutes(5) : null,
                    'notes' => $validated['notes'] ?? null,
                ]);

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
                        'is_combo_offer' => $item['is_combo_offer'] ?? false,
                        'combo_category_id' => $item['combo_category_id'] ?? null,
                    ]);

                }

                // If Cash on Delivery, deduct stock immediately on order creation
                if ($validated['payment_method'] === 'cod' || $summary['grand_total'] <= 0) {
                    if ($couponData) {
                        app(\App\Services\MasterCouponService::class)->reserveForOrder($couponData['coupon']->id);
                        $order->coupon_usage_recorded = true;
                        $order->save();
                    }
                    $this->stockService->deductStockForOrder($order);
                    if ($summary['grand_total'] <= 0) {
                        \App\Models\Payment::create(['order_id' => $order->id, 'payment_method' => 'online', 'status' => 'paid', 'amount' => 0]);
                    }

                    // Create Admin Notification
                    Notification::create([
                        'title' => 'New Order Received',
                        'message' => "Order #{$order->order_number} placed by {$order->customer_name} (₹{$order->grand_total})",
                        'type' => 'new_order',
                        'order_id' => $order->id,
                        'is_read' => false,
                    ]);
                }

                session([
                    'customer_phone' => $validated['customer_phone'],
                    'customer_email' => $order->customer_email ? strtolower(trim($order->customer_email)) : session('customer_email')
                ]);

                return $order;
            }, 3);

            // Process Payment Response
            if ((float) $order->grand_total <= 0) {
                $paymentResult = [];
            } else {
                $paymentResult = $this->paymentService->initiatePayment($order, $validated['payment_method']);
            }

            if ($validated['payment_method'] === 'cod' || (float) $order->grand_total <= 0) {
                $this->cartService->clear();
                session()->forget('master_coupon');
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

        // Fast path; the payment service locks and rechecks before changing stock.
        if ($order->payment_status === 'paid' && !in_array($order->order_status, ['pending', 'cancelled'])) {
            return redirect()->route('checkout.success', ['order_number' => $order->order_number]);
        }

        try {
            $verified = $this->paymentService->verifyOnlinePayment(
                $order,
                $request->razorpay_payment_id,
                $request->razorpay_order_id,
                $request->razorpay_signature
            );

        } catch (Exception $e) {
            \Log::error('Payment verification error', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            $verified = false;
        }
        $order->refresh();
        if ($order->order_status === 'cancelled' || ($order->payment_status === 'paid' && $order->order_status === 'pending')) {
            return redirect()->route('checkout.success', ['order_number' => $order->order_number]);
        }

        if ($verified) {
            $this->cartService->clear();
            session()->forget('master_coupon');
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

        return redirect()->route('checkout.success', ['order_number' => $order->order_number]);
    }

    public function success(string $order_number)
    {
        $order = Order::where('order_number', $order_number)
            ->with(['items.product', 'payment'])
            ->firstOrFail();

        return view('frontend.order_success', compact('order'));
    }

    /**
     * Fetch previous shipping address details by email for instant autofill (Zero OTP).
     */
    public function fetchAddressByEmail(Request $request)
    {
        $email = strtolower(trim($request->input('email', '')));
        $phone = preg_replace('/[^0-9]/', '', $request->input('phone', ''));

        $query = Order::query();
        if ($email && $phone) {
            $query->where(function($q) use ($email, $phone) {
                $q->where('customer_email', $email)->orWhere('customer_phone', $phone);
            });
        } elseif ($email) {
            $query->where('customer_email', $email);
        } elseif ($phone) {
            $query->where('customer_phone', $phone);
        } else {
            return response()->json(['found' => false]);
        }

        $lastOrder = $query->latest()->first();

        if ($lastOrder) {
            return response()->json([
                'found' => true,
                'details' => [
                    'customer_name' => $lastOrder->customer_name,
                    'customer_phone' => $lastOrder->customer_phone,
                    'customer_email' => $lastOrder->customer_email,
                    'house_building' => $lastOrder->house_building,
                    'street' => $lastOrder->street,
                    'area' => $lastOrder->area,
                    'city' => $lastOrder->city,
                    'district' => $lastOrder->district,
                    'state' => $lastOrder->state,
                    'pin_code' => $lastOrder->pin_code,
                ]
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'No previous orders found.'
        ]);
    }
}
