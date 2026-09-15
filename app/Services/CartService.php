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

    public function addComboItems(array $items, \App\Models\Category $comboCategory): array
    {
        $minCount = (int) $comboCategory->min_count;
        $comboPrice = (float) $comboCategory->combo_price;

        // Ensure every product in the combo offer is unique
        $productIds = array_map(fn($itm) => (int)$itm['product_id'], $items);
        if (count($productIds) !== count(array_unique($productIds))) {
            return [
                'success' => false,
                'message' => 'Each product in a combo offer package must be unique. Duplicate products cannot be added.'
            ];
        }

        $totalQty = 0;
        foreach ($items as $itm) {
            $totalQty += (int) ($itm['quantity'] ?? 1);
        }

        if ($totalQty < $minCount) {
            return [
                'success' => false,
                'message' => "Minimum {$minCount} items required for {$comboCategory->name} combo offer. You selected {$totalQty}."
            ];
        }

        // Calculate target combo total for totalQty (rounded UP using ceil to nearest whole rupee for extra items)
        $rawComboTotal = ($comboPrice / $minCount) * $totalQty;
        $targetComboTotal = (float) ceil($rawComboTotal);

        // Calculate exact per-item price allocation so total sum equals $targetComboTotal exactly without rounding loss
        $baseUnitPrice = floor(($targetComboTotal / $totalQty) * 100) / 100;
        $remainderCents = (int) round(($targetComboTotal - ($baseUnitPrice * $totalQty)) * 100);

        $cart = $this->getCart();
        $itemIndex = 0;

        foreach ($items as $itm) {
            $productId = (int) $itm['product_id'];
            $size = (string) $itm['size'];
            $qty = (int) ($itm['quantity'] ?? 1);

            $product = Product::active()->with(['images', 'sizes'])->find($productId);
            if (!$product) {
                return ['success' => false, 'message' => 'Selected product is currently unavailable.'];
            }

            $stockCheck = $this->stockService->checkStock($productId, $size, $qty);
            if (!$stockCheck['available']) {
                return ['success' => false, 'message' => "{$product->name} (Size: {$size}): {$stockCheck['message']}"];
            }

            // Distribute 1 paisa (0.01) to first N items to absorb remainder cents
            $unitComboPrice = $baseUnitPrice;
            if ($itemIndex < $remainderCents) {
                $unitComboPrice = round($baseUnitPrice + 0.01, 2);
            }
            $itemIndex++;

            $cartKey = "combo_{$comboCategory->id}_{$productId}_{$size}";
            $existingQty = isset($cart[$cartKey]) ? $cart[$cartKey]['quantity'] : 0;
            $newQty = $existingQty + $qty;

            $cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'size' => $size,
                'price' => (float) $product->price,
                'discount_amount' => max(0, (float) ($product->price - $unitComboPrice)),
                'final_price' => $unitComboPrice,
                'quantity' => $newQty,
                'image' => $product->primary_image_url,
                'subtotal' => round($unitComboPrice * $newQty, 2),
                'is_combo_offer' => true,
                'combo_category_id' => $comboCategory->id,
                'combo_delivery_charge' => (float) $comboCategory->delivery_charge,
            ];
        }

        Session::put('cart', $cart);

        return [
            'success' => true,
            'message' => 'Offer Combo package added to cart successfully!',
            'cart_count' => $this->getCartCount(),
            'cart' => $cart
        ];
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

        // Check if any combo items in cart have explicit free delivery
        $comboFreeShipping = false;
        foreach ($cart as $item) {
            if (!empty($item['is_combo_offer']) && isset($item['combo_delivery_charge']) && (float)$item['combo_delivery_charge'] === 0.0) {
                $comboFreeShipping = true;
                break;
            }
        }

        if ($comboFreeShipping) {
            $shipping = 0.00;
            $matchedPolicyName = 'Free Combo Offer Delivery';
        } else if ($cartCount > 0) {
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

        $rawGrandTotal = max(0, $cartSubtotal + $shipping);
        $grandTotal = (float) ceil($rawGrandTotal);
        $roundingAdjustment = round($grandTotal - $rawGrandTotal, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($totalDiscount, 2),
            'shipping' => round($shipping, 2),
            'raw_grand_total' => round($rawGrandTotal, 2),
            'rounding_adjustment' => round($roundingAdjustment, 2),
            'grand_total' => round($grandTotal, 2),
            'item_count' => $cartCount,
            'matched_policy' => $comboFreeShipping ? $matchedPolicyName : ($matchedPolicy ? $matchedPolicy->name : null),
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
