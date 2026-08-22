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
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ], [
            'size.required' => 'Please select a size before adding to cart.',
        ]);

        $result = $this->cartService->add(
            (int) $request->product_id,
            $request->size,
            (int) $request->quantity
        );

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
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ], [
            'size.required' => 'Please select a size before proceeding to Buy Now.',
        ]);

        $this->cartService->clear();
        $result = $this->cartService->add(
            (int) $request->product_id,
            $request->size,
            (int) $request->quantity
        );

        if (!$result['success']) {
            return back()->with('error', $result['message'])->withInput();
        }

        return redirect()->route('checkout.index');
    }
}
