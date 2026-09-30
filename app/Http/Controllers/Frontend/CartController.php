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
        $cart = $this->cartService->getCart();
        $summary = $this->cartService->getSummary();
        $stockValidation = $this->cartService->validateCartStock();

        return view('frontend.cart', compact('cart', 'summary', 'stockValidation'));
    }

    public function add(Request $request)
    {
        if ($request->input('purchase_action') === 'buy_now') {
            return $this->buyNow($request);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
        ], [
            'size.required' => 'Please select a size before adding to cart.',
        ]);

        $product = \App\Models\Product::with('selectableSizes')->findOrFail($validated['product_id']);
        if ($product->selectableSizes->isNotEmpty() && empty($validated['size'])) {
            return back()->withErrors(['size' => 'Please select a size before adding to cart.'])->withInput();
        }

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
        $request->validate([
            'combo_category_id' => 'required|exists:categories,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.size' => 'required|string',
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

    public function update(Request $request, string $cartKey)
    {
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
        $result = $this->cartService->remove($cartKey);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($result);
        }

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    /**
     * Buy Now flow: clears cart, adds selected item, and redirects directly to checkout.
     */
    public function buyNow(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
        ], [
            'product_id.required' => 'This product could not be identified. Please reload the page and try again.',
        ]);

        $product = \App\Models\Product::with('selectableSizes')->findOrFail($validated['product_id']);
        if ($product->selectableSizes->isNotEmpty() && empty($validated['size'])) {
            return back()->withErrors(['size' => 'Please select a size before proceeding to Buy Now.'])->withInput();
        }

        $this->cartService->clear();
        $result = $this->cartService->add(
            (int) $validated['product_id'],
            $validated['size'] ?? '',
            (int) $validated['quantity']
        );

        if (!$result['success']) {
            return back()->with('error', $result['message'])->withInput();
        }

        return redirect()->route('checkout.index');
    }
}
