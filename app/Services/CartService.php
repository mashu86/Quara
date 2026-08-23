<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function getCart(): array
    {
        return Session::get('cart', []);
    }

    public function add(int $productId, string $size, int $quantity): array
    {
        $product = Product::active()->with(['images', 'sizes'])->find($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Product is currently unavailable.'];
        }
        $stockCheck = $this->stockService->checkStock($productId, $size, $quantity);

        if (!$stockCheck['available']) {
            return ['success' => false, 'message' => $stockCheck['message']];
        }

        $cart = $this->getCart();
        $cartKey = "{$productId}_{$size}";

        $currentQty = isset($cart[$cartKey]) ? $cart[$cartKey]['quantity'] : 0;
        $newQty = $currentQty + $quantity;

        // Verify total requested quantity against stock
        $recheck = $this->stockService->checkStock($productId, $size, $newQty);
        if (!$recheck['available']) {
            return ['success' => false, 'message' => "Cannot add {$quantity} more. Maximum available stock for size {$size} is {$recheck['available_stock']}."];
        }

        $cart[$cartKey] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'size' => $size,
            'price' => (float) $product->price,
            'discount_amount' => (float) ($product->price - $product->final_price),
            'final_price' => (float) $product->final_price,
            'quantity' => $newQty,
            'image' => $product->primary_image_url,
            'subtotal' => round($product->final_price * $newQty, 2),
        ];

        Session::put('cart', $cart);

        return [
            'success' => true,
            'message' => 'Item added to cart successfully!',
            'cart_count' => $this->getCartCount(),
            'cart' => $cart
        ];
    }

    public function update(string $cartKey, int $quantity): array
    {
        $cart = $this->getCart();

        if (!isset($cart[$cartKey])) {
            return ['success' => false, 'message' => 'Cart item not found.'];
        }

        if ($quantity <= 0) {
            return $this->remove($cartKey);
        }

        $item = $cart[$cartKey];
        $stockCheck = $this->stockService->checkStock($item['product_id'], $item['size'], $quantity);

        if (!$stockCheck['available']) {
            return ['success' => false, 'message' => $stockCheck['message']];
        }

        $cart[$cartKey]['quantity'] = $quantity;
        $cart[$cartKey]['subtotal'] = round($cart[$cartKey]['final_price'] * $quantity, 2);

        Session::put('cart', $cart);

        return [
            'success' => true,
            'message' => 'Cart updated successfully!',
            'cart_count' => $this->getCartCount(),
            'summary' => $this->getSummary()
        ];
    }

    public function remove(string $cartKey): array
    {
        $cart = $this->getCart();
        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            Session::put('cart', $cart);
        }

        return [
            'success' => true,
            'message' => 'Item removed from cart.',
            'cart_count' => $this->getCartCount(),
            'summary' => $this->getSummary()
        ];
    }

    public function clear(): void
    {
        Session::forget('cart');
    }

    public function getCartCount(): int
    {
        $cart = $this->getCart();
        return array_reduce($cart, function ($total, $item) {
            return $total + $item['quantity'];
        }, 0);
    }

    public function getSummary(): array
    {
        $cart = $this->getCart();
        $subtotal = 0;
        $totalDiscount = 0;

        foreach ($cart as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
            $totalDiscount += ($item['discount_amount'] * $item['quantity']);
        }

        $cartSubtotal = $subtotal - $totalDiscount;
        $cartCount = $this->getCartCount();
        $shipping = 0.00;
        $matchedPolicy = null;

        if ($cartCount > 0) {
            $policies = \App\Models\ShippingPolicy::where('status', 'active')
                ->orderBy('priority', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            foreach ($policies as $policy) {
                $evalVal = ($policy->criteria_type === 'cart_count') ? (float) $cartCount : (float) $cartSubtotal;
                if ($policy->matches($evalVal)) {
                    $matchedPolicy = $policy;
                    break;
                }
            }

            if ($matchedPolicy) {
                $shipping = ($matchedPolicy->delivery_type === 'free') ? 0.00 : (float) $matchedPolicy->charge_amount;
            }
        }

        $grandTotal = $cartSubtotal + $shipping;

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($totalDiscount, 2),
            'shipping' => round($shipping, 2),
            'grand_total' => round($grandTotal, 2),
            'item_count' => $cartCount,
            'matched_policy' => $matchedPolicy ? $matchedPolicy->name : null,
        ];
    }

    /**
     * Revalidate whole cart before checkout against real-time DB stock.
     */
    public function validateCartStock(): array
    {
        $cart = $this->getCart();
        $errors = [];

        foreach ($cart as $key => $item) {
            $check = $this->stockService->checkStock($item['product_id'], $item['size'], $item['quantity']);
            if (!$check['available']) {
                $errors[] = "{$item['name']} ({$item['size']}): " . $check['message'];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
