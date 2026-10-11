<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $this->restoreCartAfterAbandonedSelectionCheckout();
        if (session('buy_now_mode')) {
            $this->restoreCartAfterBuyNow();
        }

        $cart = $this->cartService->getCart();
        $summary = $this->cartService->getSummary();
        $discountBreakdown = $this->cartService->getDiscountBreakdown();
        $stockValidation = $this->cartService->validateCartStock();

        return view('frontend.cart', compact('cart', 'summary', 'discountBreakdown', 'stockValidation'));
    }

    public function add(Request $request)
    {
        $this->restoreCartAfterAbandonedSelectionCheckout();
        if ($request->input('purchase_action') === 'buy_now') {
            return $this->buyNow($request);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
        ]);

        $result = $this->cartService->add(
            (int) $validated['product_id'],
            $validated['size'] ?? '',
            (int) $validated['quantity']
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if (!$result['success']) {
            return back()->with('error', $result['message'])->withInput();
        }

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    public function addCombo(Request $request)
    {
        $this->restoreCartAfterAbandonedSelectionCheckout();
        $request->validate([
            'combo_category_id' => 'required|exists:categories,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.size' => 'nullable|string|max:100',
            'items.*.quantity' => 'nullable|integer|min:1',
        ]);

        $comboCategory = \App\Models\Category::findOrFail($request->combo_category_id);
        $result = $this->cartService->addComboItems($request->items, $comboCategory);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if (!$result['success']) {
            return back()->with('error', $result['message'])->withInput();
        }

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    public function addMinimumCategory(Request $request)
    {
        $this->restoreCartAfterAbandonedSelectionCheckout();
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.size' => 'nullable|string|max:100',
        ]);

        $category = \App\Models\Category::findOrFail($request->category_id);
        $result = $this->cartService->addMinimumCategoryItems($request->items, $category);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if (!$result['success']) return back()->with('error', $result['message']);
        return redirect()->route('checkout.index');
    }

    public function update(Request $request, string $cartKey)
    {
        $this->restoreCartAfterAbandonedSelectionCheckout();
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $result = $this->cartService->update($cartKey, (int) $request->quantity);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    public function remove(string $cartKey)
    {
        $this->restoreCartAfterAbandonedSelectionCheckout();
        $result = $this->cartService->remove($cartKey);

        if (request()->wantsJson() || request()->ajax()) {
            $result['stock_validation'] = $this->cartService->validateCartStock();
            $cart = $this->cartService->getCart();
            $result['cart_items_count'] = count($cart);
            $result['cart_cards_html'] = view('frontend.partials.cart_cards', ['cart' => $cart])->render();
            $discountBreakdown = $this->cartService->getDiscountBreakdown();
            $result['summary_html'] = view('frontend.partials.cart_summary', [
                'summary' => $result['summary'],
                'discountBreakdown' => $discountBreakdown,
                'stockValidation' => $result['stock_validation'],
            ])->render();
            return response()->json($result);
        }

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    public function removeSelected(Request $request)
    {
        $this->restoreCartAfterAbandonedSelectionCheckout();
        $validated = $request->validate([
            'cart_keys' => 'required|array|min:1',
            'cart_keys.*' => 'required|string|max:255',
        ]);

        $cart = $this->cartService->getCart();
        $keys = array_intersect($validated['cart_keys'], array_keys($cart));
        if (!$keys) {
            return redirect()->route('cart.index')->with('error', 'The selected items could not be found in your cart.');
        }

        foreach ($keys as $key) {
            $this->cartService->remove((string) $key);
        }

        return redirect()->route('cart.index')->with('success', count($keys) . ' selected item(s) removed from your cart.');
    }

    public function clear()
    {
        $this->cartService->clear();
        session()->forget(['checkout_original_cart', 'checkout_remaining_cart']);

        return redirect()->route('cart.index')->with('success', 'All items have been removed from your cart.');
    }

    public function previewSelection(Request $request)
    {
        $validated = $request->validate([
            'cart_keys' => 'nullable|array',
            'cart_keys.*' => 'required|string|max:255',
        ]);

        $originalCart = $this->cartService->getCart();
        $selectedKeys = collect($validated['cart_keys'] ?? [])->unique()->all();
        $selectedCart = $selectedKeys
            ? array_intersect_key($originalCart, array_flip($selectedKeys))
            : $originalCart;

        if ($selectedKeys && empty($selectedCart)) {
            return response()->json(['success' => false, 'message' => 'The selected items could not be found in your cart.'], 422);
        }

        try {
            session(['cart' => $selectedCart]);
            $selectedCart = $this->cartService->getCart();
            $summary = $this->cartService->getSummary();
            $discountBreakdown = $this->cartService->getDiscountBreakdown();
            $stockValidation = $this->cartService->validateCartStock();

            return response()->json([
                'success' => true,
                'checkout_ready' => (bool) $stockValidation['valid'],
                'calculation_complete' => true,
                'summary_html' => view('frontend.partials.cart_summary', compact('summary', 'discountBreakdown', 'stockValidation'))->render(),
            ]);
        } finally {
            session(['cart' => $originalCart]);
        }
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'cart_keys' => 'nullable|array',
            'cart_keys.*' => 'required|string|max:255',
        ]);

        // Restore the full source cart if the shopper resubmits from browser history.
        if (session()->has('checkout_original_cart')) {
            session(['cart' => session('checkout_original_cart', [])]);
            session()->forget(['checkout_original_cart', 'checkout_remaining_cart']);
        }

        $cart = $this->cartService->getCart();
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $selectedKeys = collect($validated['cart_keys'] ?? [])->unique()->all();
        // With no selections, the shopper intends to check out the whole cart.
        $selectedCart = $selectedKeys
            ? array_intersect_key($cart, array_flip($selectedKeys))
            : $cart;

        if (empty($selectedCart)) {
            return redirect()->route('cart.index')->with('error', 'The selected items could not be found in your cart.');
        }

        $remainingCart = array_diff_key($cart, $selectedCart);
        session([
            'checkout_original_cart' => $cart,
            'checkout_remaining_cart' => $remainingCart,
            'cart' => $selectedCart,
        ]);

        return redirect()->route('checkout.index');
    }

    /** Start an isolated checkout for one selected product without discarding the cart. */
    public function buyNow(Request $request)
    {
        $this->restoreCartAfterAbandonedSelectionCheckout();
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
        ], [
            'product_id.required' => 'This product could not be identified. Please reload the page and try again.',
        ]);

        if (!session('buy_now_mode')) {
            session([
                'buy_now_original_cart' => session('cart', []),
                'buy_now_original_coupon' => session('master_coupon'),
            ]);
        }
        session()->forget('master_coupon');
        session(['cart' => []]);

        $result = $this->cartService->add(
            (int) $validated['product_id'],
            $validated['size'] ?? '',
            (int) $validated['quantity']
        );

        if (!$result['success']) {
            $this->restoreCartAfterBuyNow();
            return back()->with('error', $result['message'])->withInput();
        }

        session(['buy_now_mode' => true]);
        return redirect()->route('checkout.index');
    }

    private function restoreCartAfterBuyNow(): void
    {
        if (!session('buy_now_mode') && !session()->has('buy_now_original_cart')) return;

        session(['cart' => session('buy_now_original_cart', [])]);
        if (session()->has('buy_now_original_coupon')) {
            session(['master_coupon' => session('buy_now_original_coupon')]);
        } else {
            session()->forget('master_coupon');
        }
        session()->forget(['buy_now_mode', 'buy_now_original_cart', 'buy_now_original_coupon']);
    }

    private function restoreCartAfterAbandonedSelectionCheckout(): void
    {
        if (!session()->has('checkout_original_cart')) return;

        session(['cart' => session('checkout_original_cart', [])]);
        session()->forget(['checkout_original_cart', 'checkout_remaining_cart']);
    }
}
