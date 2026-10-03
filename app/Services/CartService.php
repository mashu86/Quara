<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Category;
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
        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return [];
        }

        $updatedCart = [];
        $hasChanges = false;

        foreach ($cart as $key => $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $size = (string) ($item['size'] ?? '');
            $quantity = (int) ($item['quantity'] ?? 1);

            $product = Product::with(['sizes', 'category', 'categories', 'comboCategory'])->find($productId);

            // 1. If product doesn't exist, is inactive, or category is inactive -> REMOVE FROM CART
            if (!$product || $product->status !== 'active') {
                $hasChanges = true;
                continue;
            }

            // Products without size variants use one stable cart/order label.
            $selectableSizes = $product->sizes->filter(fn ($variant) => trim((string) $variant->size) !== '');
            if ($selectableSizes->isEmpty()) {
                if ($size !== '') {
                    $size = '';
                    $hasChanges = true;
                }
            }

            // 2. Check stock for this specific size, or product availability when no variants exist.
            if ($selectableSizes->isEmpty()) {
                $productSize = $product->sizes->first(fn ($variant) => trim((string) $variant->size) === '');
                $availableStock = $productSize ? (int) $productSize->available_stock : 0;
            } elseif ($size === '') {
                $productSize = null;
                $availableStock = (int) $selectableSizes->sum(fn ($variant) => $variant->available_stock);
            } else {
                $productSize = $selectableSizes->firstWhere('size', $size);
                $availableStock = $productSize ? (int) $productSize->available_stock : 0;
            }

            // If product is marked out of stock or size stock is 0 -> REMOVE FROM CART (Sold out)
            if ($product->is_out_of_stock || $availableStock <= 0) {
                $hasChanges = true;
                continue;
            }

            // Adjust quantity if user requested more than available stock
            if ($quantity > $availableStock) {
                $quantity = $availableStock;
                $hasChanges = true;
            }

            // 3. Handle Combo Offer vs Regular Product Price Sync
            if (!empty($item['is_combo_offer'])) {
                $comboCategoryId = (int) ($item['combo_category_id'] ?? 0);
                $comboCategory = \App\Models\Category::find($comboCategoryId);

                // If combo category was deleted, disabled or offer turned off -> revert to regular price
                if (!$comboCategory || $comboCategory->status !== 'active' || !$comboCategory->is_combo_offer) {
                    $effectivePrice = (float) $product->effective_final_price;
                    $updatedCart[$key] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'size' => $size,
                        'price' => (float) $product->price,
                        'discount_amount' => max(0, (float) ($product->price - $effectivePrice)),
                        'final_price' => $effectivePrice,
                        'quantity' => $quantity,
                        'available_stock' => $availableStock,
                        'image' => $product->primary_image_url,
                        'subtotal' => round($effectivePrice * $quantity, 2),
                        'is_combo_offer' => false,
                    ];
                    $hasChanges = true;
                } else {
                    $updatedCart[$key] = array_merge($item, [
                        'name' => $product->name,
                        'image' => $product->primary_image_url,
                        'quantity' => $quantity,
                        'available_stock' => $availableStock,
                        'subtotal' => round($item['final_price'] * $quantity, 2),
                    ]);
                }
            } else {
                // Regular Product: Dynamically re-verify effective price (reflecting active offer vs removed offer)
                $effectivePrice = (float) $product->effective_final_price;
                $originalPrice = (float) $product->price;
                $discountAmount = max(0, round($originalPrice - $effectivePrice, 2));

                if (($item['final_price'] ?? 0) != $effectivePrice || ($item['quantity'] ?? 0) != $quantity) {
                    $hasChanges = true;
                }

                $updatedCart[$key] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'size' => $size,
                    'price' => $originalPrice,
                    'discount_amount' => $discountAmount,
                    'final_price' => $effectivePrice,
                    'quantity' => $quantity,
                    'available_stock' => $availableStock,
                    'image' => $product->primary_image_url,
                    'subtotal' => round($effectivePrice * $quantity, 2),
                    'is_combo_offer' => false,
                ];
            }
        }

        // Apply each active combo category's price to regular cart items after its minimum is met.
        $updatedCart = $this->applyCategoryComboPrices($updatedCart, $hasChanges);
        if ($hasChanges) {
            Session::put('cart', $updatedCart);
        }

        return $updatedCart;
    }

    public function add(int $productId, string $size, int $quantity): array
    {
        $product = Product::active()->with(['images', 'sizes', 'category', 'categories', 'comboCategory'])->find($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Product is currently unavailable.'];
        }
        if ($product->sizes->filter(fn ($variant) => trim((string) $variant->size) !== '')->isEmpty()) {
            $size = '';
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
            $stockMessage = $size !== ''
                ? "Cannot add {$quantity} more. Maximum available stock for size {$size} is {$recheck['available_stock']}."
                : "Cannot add {$quantity} more. Only {$recheck['available_stock']} item(s) are available.";
            return ['success' => false, 'message' => $stockMessage];
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
            'available_stock' => $recheck['available_stock'],
            'image' => $product->primary_image_url,
            'subtotal' => round($product->final_price * $newQty, 2),
        ];

        $changed = false;
        $cart = $this->applyCategoryComboPrices($cart, $changed);

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
        $cart[$cartKey]['available_stock'] = $stockCheck['available_stock'];
        $cart[$cartKey]['subtotal'] = round($cart[$cartKey]['final_price'] * $quantity, 2);

        $changed = false;
        $cart = $this->applyCategoryComboPrices($cart, $changed);

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
            $changed = false;
            $cart = $this->applyCategoryComboPrices($cart, $changed);
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
        Session::forget('master_coupon');
    }

    public function getCartCount(): int
    {
        $cart = $this->getCart();
        return array_reduce($cart, function ($total, $item) {
            return $total + $item['quantity'];
        }, 0);
    }

    /** Price eligible regular products at the active combo category's per-item offer rate. */
    private function applyCategoryComboPrices(array $cart, bool &$changed): array
    {
        $offerCategories = Category::where('status', 'active')
            ->where(function ($query) {
                $query->where('is_combo_offer', true)
                    ->orWhere(function ($offerQuery) {
                        $offerQuery->where('is_offer_category', true)->where('offer_type', 'combo');
                    });
            })
            ->where('is_active_offer', true)
            ->where('min_count', '>', 0)
            ->get();

        if ($offerCategories->isEmpty()) {
            return $cart;
        }

        $products = Product::with(['category', 'categories', 'comboCategory'])->findMany(
            collect($cart)->pluck('product_id')->unique()->all()
        )->keyBy('id');

        foreach ($offerCategories as $category) {
            $eligibleKeys = [];
            $eligibleQty = 0;
            foreach ($cart as $key => $item) {
                if (!empty($item['is_combo_offer'])) {
                    continue;
                }
                $product = $products->get((int) ($item['product_id'] ?? 0));
                if (!$product) {
                    continue;
                }
                $matches = (int) $product->combo_category_id === (int) $category->id
                    || (int) $product->category_id === (int) $category->id
                    || $product->categories->contains('id', $category->id);
                if ($matches) {
                    $eligibleKeys[] = $key;
                    $eligibleQty += (int) ($item['quantity'] ?? 0);
                }
            }

            if (!$eligibleKeys) {
                continue;
            }

            $offerApplies = $eligibleQty >= (int) $category->min_count;
            $useComboPrice = $offerApplies || (bool) $category->pre_min_purchase_offer_price;

            $targetTotal = $useComboPrice
                ? (float) ceil(((float) $category->combo_price / (int) $category->min_count) * $eligibleQty)
                : 0.0;
            $baseUnitPrice = $useComboPrice ? floor(($targetTotal / $eligibleQty) * 100) / 100 : 0.0;
            $remainderCents = $useComboPrice ? (int) round(($targetTotal - ($baseUnitPrice * $eligibleQty)) * 100) : 0;
            $unitIndex = 0;

            foreach ($eligibleKeys as $key) {
                $item = $cart[$key];
                $qty = (int) $item['quantity'];
                $basePrice = (float) ($item['price'] ?? 0);
                $unitPrice = $useComboPrice ? $baseUnitPrice : (float) $product->effective_final_price;
                if ($useComboPrice && $unitIndex < $remainderCents) {
                    $unitPrice = round($unitPrice + 0.01, 2);
                }
                $unitIndex += $qty;

                if ((float) ($item['final_price'] ?? 0) !== $unitPrice
                    || ($offerApplies && (int) ($item['combo_category_id'] ?? 0) !== (int) $category->id)
                    || (!$offerApplies && (int) ($item['combo_category_id'] ?? 0) === (int) $category->id)) {
                    $changed = true;
                }
                $cart[$key]['final_price'] = $unitPrice;
                $cart[$key]['discount_amount'] = max(0, round($basePrice - $unitPrice, 2));
                $cart[$key]['subtotal'] = round($unitPrice * $qty, 2);
                if ($offerApplies) {
                    $cart[$key]['combo_category_id'] = $category->id;
                } else {
                    unset($cart[$key]['combo_category_id']);
                }
            }
        }

        return $cart;
    }

    public function addComboItems(array $items, \App\Models\Category $comboCategory): array
    {
        if (!$comboCategory->is_combo_offer || $comboCategory->status !== 'active') {
            return ['success' => false, 'message' => 'This combo offer is currently unavailable.'];
        }
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

        if ($minCount < 1 || $comboPrice < 0) {
            return ['success' => false, 'message' => 'This combo offer is not configured correctly.'];
        }
        if ($totalQty < $minCount) {
            if (!$comboCategory->allow_pre_min_purchase) {
                return [
                    'success' => false,
                    'message' => "Minimum {$minCount} items required for {$comboCategory->name} combo offer. You selected {$totalQty}."
                ];
            }
        }

        $useComboPrice = $totalQty >= $minCount || $comboCategory->pre_min_purchase_offer_price;
        // Before the minimum is met, ordinary product prices remain in effect unless opted in.
        $rawComboTotal = $minCount > 0 ? ($comboPrice / $minCount) * $totalQty : 0;
        $targetComboTotal = $useComboPrice ? (float) ceil($rawComboTotal) : 0.0;

        // Calculate exact per-item price allocation so total sum equals $targetComboTotal exactly without rounding loss
        $baseUnitPrice = floor(($targetComboTotal / $totalQty) * 100) / 100;
        $remainderCents = (int) round(($targetComboTotal - ($baseUnitPrice * $totalQty)) * 100);

        $cart = $this->getCart();
        $itemIndex = 0;

        foreach ($items as $itm) {
            $productId = (int) $itm['product_id'];
            $size = (string) ($itm['size'] ?? '');
            $qty = (int) ($itm['quantity'] ?? 1);

            $product = Product::active()->with(['images', 'sizes'])->find($productId);
            if (!$product) {
                return ['success' => false, 'message' => 'Selected product is currently unavailable.'];
            }
            $selectableSizes = $product->sizes->filter(fn ($variant) => trim((string) $variant->size) !== '');
            if ($selectableSizes->isEmpty()) {
                $size = '';
            } elseif ($size !== '' && !$selectableSizes->contains('size', $size)) {
                return ['success' => false, 'message' => "Selected size is not available for {$product->name}."];
            }

            $stockCheck = $this->stockService->checkStock($productId, $size, $qty);
            if (!$stockCheck['available']) {
                $sizeText = $size !== '' ? " (Size: {$size})" : '';
                return ['success' => false, 'message' => "{$product->name}{$sizeText}: {$stockCheck['message']}"];
            }

            // Distribute 1 paisa (0.01) to first N items to absorb remainder cents
            $unitComboPrice = $useComboPrice ? $baseUnitPrice : (float) $product->price;
            if ($useComboPrice && $itemIndex < $remainderCents) {
                $unitComboPrice = round($baseUnitPrice + 0.01, 2);
            }
            $itemIndex += $qty;

            $cartKey = "combo_{$comboCategory->id}_{$productId}_{$size}";
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'size' => $size,
                'price' => (float) $product->price,
                'discount_amount' => max(0, (float) ($product->price - $unitComboPrice)),
                'final_price' => $unitComboPrice,
                'quantity' => $qty,
                'available_stock' => $stockCheck['available_stock'],
                'image' => $product->primary_image_url,
                'subtotal' => round($unitComboPrice * $qty, 2),
                'is_combo_offer' => true,
                'combo_category_id' => $comboCategory->id,
                'combo_delivery_charge' => (float) $comboCategory->delivery_charge,
                'combo_delivery_charge_mode' => $comboCategory->delivery_charge_mode ?? 'free',
                'combo_min_count' => $minCount,
                'combo_pre_minimum' => $totalQty < $minCount,
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

    public function addMinimumCategoryItems(array $items, \App\Models\Category $category): array
    {
        if ($category->is_offer_category || $category->is_combo_offer || !$category->minimum_purchase_required || $category->status !== 'active') {
            return ['success' => false, 'message' => 'This category does not use minimum-purchase selection.'];
        }

        $minimum = (int) $category->minimum_purchase_count;
        if ($minimum < 1 || count($items) < $minimum) {
            return ['success' => false, 'message' => "Please select at least {$minimum} products from {$category->name}."];
        }

        $productIds = array_map(fn ($item) => (int) $item['product_id'], $items);
        if (count($productIds) !== count(array_unique($productIds))) {
            return ['success' => false, 'message' => 'Choose different products from this category.'];
        }

        $oldCart = Session::get('cart', []);
        foreach ($items as $item) {
            $product = Product::active()->with('categories')->find((int) $item['product_id']);
            if (!$product || ((int) $product->category_id !== (int) $category->id && !$product->categories->contains('id', $category->id))) {
                Session::put('cart', $oldCart);
                return ['success' => false, 'message' => 'A selected product is no longer available in this category. Please choose another product.'];
            }

            $size = (string) ($item['size'] ?? '');
            $stock = $this->stockService->checkStock($product->id, $size, 1);
            if (!$stock['available']) {
                Session::put('cart', $oldCart);
                return ['success' => false, 'message' => "{$product->name} may have been purchased by another customer. Please choose a different product from {$category->name}."];
            }

            $result = $this->add($product->id, $size, 1);
            if (!$result['success']) {
                Session::put('cart', $oldCart);
                return ['success' => false, 'message' => "{$product->name} is no longer available. Please choose a different product from {$category->name}."];
            }
        }

        return ['success' => true, 'message' => 'Selected products added to your cart.', 'cart_count' => $this->getCartCount()];
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

        // Combo categories can provide free, custom or master-policy delivery.
        $comboDeliveryMode = null;
        $comboDeliveryCharge = 0.0;
        foreach ($cart as $item) {
            if (!empty($item['is_combo_offer'])) {
                $cat = \App\Models\Category::find($item['combo_category_id'] ?? 0);
                $categoryQty = 0;
                foreach ($cart as $comboItem) if (!empty($comboItem['is_combo_offer']) && (int) ($comboItem['combo_category_id'] ?? 0) === (int) ($item['combo_category_id'] ?? 0)) $categoryQty += (int) ($comboItem['quantity'] ?? 0);
                if (!$cat && $categoryQty < (int) ($item['combo_min_count'] ?? PHP_INT_MAX)) continue;
                if ($cat && $categoryQty < (int) $cat->min_count) {
                    continue; // Use Website Delivery Price Master before the category minimum is reached.
                }
                $mode = $item['combo_delivery_charge_mode'] ?? ((float) ($item['combo_delivery_charge'] ?? 0) === 0.0 ? 'free' : 'custom');
                if ($mode === 'free' || $mode === 'custom') {
                    $comboDeliveryMode = $mode;
                    $comboDeliveryCharge = (float) ($item['combo_delivery_charge'] ?? 0);
                    break;
                }
            }
        }

        if ($comboDeliveryMode === 'free') {
            $shipping = 0.00;
            $matchedPolicyName = 'Free Combo Offer Delivery';
        } elseif ($comboDeliveryMode === 'custom') {
            $shipping = $comboDeliveryCharge;
            $matchedPolicyName = 'Combo Offer Delivery Charge';
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
            'matched_policy' => $matchedPolicyName ?? ($matchedPolicy ? $matchedPolicy->name : null),
        ];
    }

    /**
     * Revalidate whole cart before checkout against real-time DB stock.
     */
    public function validateCartStock(): array
    {
        $cart = $this->getCart();
        $errors = [];
        $categoryCounts = [];
        $minimums = [];

        foreach ($cart as $key => $item) {
            $check = $this->stockService->checkStock($item['product_id'], $item['size'], $item['quantity']);
            if (!$check['available']) {
                $sizeText = !empty($item['size']) ? " ({$item['size']})" : '';
                $errors[] = "{$item['name']}{$sizeText}: " . $check['message'];
            }

            if (!empty($item['is_combo_offer'])) continue;
            $product = Product::with(['category', 'categories'])->find($item['product_id']);
            if (!$product) continue;
            $productCategories = collect([$product->category])->merge($product->categories)->filter()->unique('id');
            foreach ($productCategories as $category) {
                if ($category->is_offer_category || $category->is_combo_offer || !$category->minimum_purchase_required) continue;
                $minimums[$category->id] = $category;
                $categoryCounts[$category->id] = ($categoryCounts[$category->id] ?? 0) + (int) $item['quantity'];
            }
        }

        foreach ($minimums as $categoryId => $category) {
            $required = (int) $category->minimum_purchase_count;
            $selected = (int) ($categoryCounts[$categoryId] ?? 0);
            if ($required > 0 && $selected < $required) {
                $errors[] = "{$category->name} category requires at least {$required} products. Your cart currently has {$selected}.";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
