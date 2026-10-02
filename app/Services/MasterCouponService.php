<?php

namespace App\Services;

use App\Models\MasterCoupon;
use Illuminate\Support\Facades\DB;

class MasterCouponService
{
    public function apply(string $code, array $cart, float $subtotal, float $existingDiscount, ?int $expectedId = null): array
    {
        $coupon = MasterCoupon::with('categories')->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))])->first();
        if (!$coupon || !$coupon->status || ($expectedId && $coupon->id !== $expectedId)) return ['valid' => false, 'message' => 'Coupon code is invalid or inactive.'];
        $now = now();
        if ($now->lt($coupon->starts_at) || $now->gt($coupon->ends_at)) return ['valid' => false, 'message' => 'Coupon is not currently valid.'];
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) return ['valid' => false, 'message' => 'Coupon usage limit has been reached.'];

        $eligible = 0.0;
        $eligibleExistingDiscount = 0.0;
        $hasOffer = false;
        foreach ($cart as $item) {
            $product = \App\Models\Product::with(['category', 'categories'])->find($item['product_id']);
            if (!$product) continue;
            $categories = collect([$product->category_id, ...$product->categories->pluck('id')->all()])->filter()->unique();
            if (!$coupon->all_categories && $categories->intersect($coupon->categories->pluck('id'))->isEmpty()) continue;
            $itemAmount = (float) $item['final_price'] * (int) $item['quantity'];
            $eligible += $itemAmount;
            $eligibleExistingDiscount += (float) ($item['discount_amount'] ?? 0) * (int) $item['quantity'];
            $hasOffer = $hasOffer || (float) ($item['discount_amount'] ?? 0) > 0 || !empty($item['is_combo_offer']);
        }
        $base = max(0, round($eligible - $eligibleExistingDiscount, 2));
        if ($base <= (float) $coupon->minimum_purchase) return ['valid' => false, 'message' => 'Eligible purchase amount must be greater than ₹'.number_format($coupon->minimum_purchase, 2).'.'];
        if ($hasOffer && !$coupon->allow_with_offer) return ['valid' => false, 'message' => 'This coupon cannot be combined with existing offers.'];
        $discount = $coupon->discount_type === 'percentage' ? round($base * (float) $coupon->discount_value / 100, 2) : (float) $coupon->discount_value;
        return ['valid' => true, 'coupon' => $coupon, 'eligible_amount' => $base, 'discount' => min($base, $discount)];
    }

    public function reserveUsage(int $couponId, bool $alreadyApplied = false): void
    {
        $coupon = MasterCoupon::whereKey($couponId)->lockForUpdate()->firstOrFail();
        if (!$coupon->status || now()->lt($coupon->starts_at) || now()->gt($coupon->ends_at) || (!$alreadyApplied && $coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit)) {
            throw new \RuntimeException('Coupon usage limit has been reached. Please retry checkout.');
        }
        $coupon->increment('used_count');
    }

    public function reserveForOrder(int $couponId): void
    {
        $coupon = MasterCoupon::whereKey($couponId)->lockForUpdate()->firstOrFail();
        if (!$coupon->status || now()->lt($coupon->starts_at) || now()->gt($coupon->ends_at) || ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit)) {
            throw new \RuntimeException('Coupon usage limit has been reached. Please retry checkout.');
        }
        $coupon->increment('used_count');
    }
}
