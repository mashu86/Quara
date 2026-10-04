<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductCategoryAssignmentService
{
    /**
     * Assign either normal categories or one offer category to an unsold product.
     * Sold-out products are deliberately left untouched to protect historical sales.
     */
    public function assign(Product $product, array $normalCategoryIds = [], ?int $offerCategoryId = null): bool
    {
        return DB::transaction(function () use ($product, $normalCategoryIds, $offerCategoryId) {
            $product = Product::query()->lockForUpdate()->with('sizes')->findOrFail($product->id);
            if ($this->isSoldOut($product)) {
                return false;
            }

            $normalCategoryIds = collect($normalCategoryIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
            $requestedOfferIds = $normalCategoryIds->filter(function ($id) {
                $category = Category::find($id);
                return $category && ($category->is_offer_category || $category->is_combo_offer);
            })->values();
            if (!$offerCategoryId && $requestedOfferIds->count() > 1) {
                throw ValidationException::withMessages(['category_ids' => 'Select only one Offer Category.']);
            }
            if (!$offerCategoryId && $requestedOfferIds->isNotEmpty()) {
                $offerCategoryId = (int) $requestedOfferIds->first();
            }
            $offerCategory = null;
            if ($offerCategoryId) {
                $offerCategory = Category::query()->whereKey($offerCategoryId)
                    ->where(fn ($q) => $q->where('is_offer_category', true)->orWhere('is_combo_offer', true))
                    ->first();
                if (!$offerCategory) {
                    throw ValidationException::withMessages(['combo_category_id' => 'Select a valid Offer Category.']);
                }
                $otherOfferIds = $normalCategoryIds->reject(fn ($id) => (int) $id === (int) $offerCategoryId)
                    ->filter(function ($id) {
                        $category = Category::find($id);
                        return $category && ($category->is_offer_category || $category->is_combo_offer);
                    });
                if ($otherOfferIds->isNotEmpty()) {
                    throw ValidationException::withMessages(['category_ids' => 'Select only one Offer Category.']);
                }
                // An offer category is exclusive, even if old rows contain stale assignments.
                $normalCategoryIds = collect();
            }

            $allIds = $offerCategory ? collect([$offerCategory->id]) : $normalCategoryIds;
            $product->category_id = $allIds->first();
            $product->combo_category_id = $offerCategory?->id;
            $product->final_price = $offerCategory?->offer_type === 'discount' && (float) $offerCategory->discount_value > 0
                ? Product::calculateFinalPrice($product->price, $offerCategory->discount_type ?? 'percentage', $offerCategory->discount_value)
                : Product::calculateFinalPrice($product->price, $product->discount_type, $product->discount_value);

            if ($offerCategory?->offer_type === 'discount' && (float) $offerCategory->discount_value > 0) {
                $product->discount_type = $offerCategory->discount_type ?? 'percentage';
                $product->discount_value = $offerCategory->discount_value;
            } elseif ($product->getOriginal('combo_category_id')) {
                $oldOffer = Category::find($product->getOriginal('combo_category_id'));
                if ($oldOffer?->offer_type === 'discount') {
                    $product->discount_type = 'none';
                    $product->discount_value = 0;
                }
            }
            $product->collection_visible = $allIds->isEmpty()
                || ! Category::whereIn('id', $allIds)->where('show_in_collection', false)->exists();
            $product->save();
            $product->categories()->sync($allIds->all());

            return true;
        });
    }

    public function isSoldOut(Product $product): bool
    {
        $totalStock = (int) $product->sizes->sum('stock');
        // Booked products are reserved inventory and remain editable by the offer manager.
        // Only actual sold-out items (with no reservation owner) retain historical assignment.
        return empty($product->booked_by)
            && ($product->is_out_of_stock || $totalStock <= 0);
    }
}
