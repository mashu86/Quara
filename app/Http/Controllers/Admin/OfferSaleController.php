<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfferSaleController extends Controller
{
    public function index(Request $request)
    {
        $offerCategories = Category::where('is_offer_category', true)
            ->orderBy('name', 'asc')
            ->get();

        $comboCategories = $offerCategories->where('offer_type', 'combo');
        $discountCategories = $offerCategories->where('offer_type', 'discount');

        $activeOfferCategory = $offerCategories->firstWhere('is_active_offer', true);

        $selectedCategoryId = $request->input('offer_category_id');
        if (!$selectedCategoryId && $offerCategories->isNotEmpty()) {
            $selectedCategoryId = $activeOfferCategory ? $activeOfferCategory->id : $offerCategories->first()->id;
        }

        $selectedCategory = $selectedCategoryId ? $offerCategories->firstWhere('id', $selectedCategoryId) : null;

        // Base query for valid products (Not sold out)
        $validProductsQuery = Product::with(['sizes', 'category', 'comboCategory', 'images'])
            ->where('is_out_of_stock', false)
            ->where(function($q) {
                $q->whereNull('booked_by')->orWhere('booked_by', '');
            })
            ->whereHas('sizes', function($q) {
                $q->where('stock', '>', 0);
            });

        if ($request->filled('search')) {
            $search = trim($request->search);
            $validProductsQuery->where('name', 'LIKE', "%{$search}%");
        }

        // 1. Available Products (Products not currently in the selected offer category)
        $availableProducts = (clone $validProductsQuery)
            ->where(function($q) use ($selectedCategoryId) {
                $q->whereNull('combo_category_id')
                  ->orWhere('combo_category_id', '!=', $selectedCategoryId);
            })
            ->orderBy('id', 'desc')
            ->get();

        // 2. Assigned Products (Products currently in the selected offer category)
        $assignedProducts = collect();
        if ($selectedCategoryId) {
            $assignedProducts = (clone $validProductsQuery)
                ->where('combo_category_id', $selectedCategoryId)
                ->orderBy('combo_sort_order', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            // Auto-sync assigned products for discount categories or pivot table
            if ($selectedCategory) {
                foreach ($assignedProducts as $prod) {
                    if ($selectedCategory->offer_type === 'discount' && $selectedCategory->discount_value > 0) {
                        $discType = $selectedCategory->discount_type ?? 'percentage';
                        $discVal = $selectedCategory->discount_value;
                        $expectedFinal = Product::calculateFinalPrice($prod->price, $discType, $discVal);
                        
                        if ($prod->discount_type !== $discType || (float)$prod->discount_value !== (float)$discVal || (float)$prod->final_price !== $expectedFinal) {
                            $prod->update([
                                'discount_type' => $discType,
                                'discount_value' => $discVal,
                                'final_price' => $expectedFinal
                            ]);
                        }
                    }
                    if (!$prod->categories()->where('categories.id', $selectedCategoryId)->exists()) {
                        $prod->categories()->attach($selectedCategoryId);
                    }
                }
            }
        }

        return view('admin.offer_sale.index', compact(
            'offerCategories',
            'comboCategories',
            'discountCategories',
            'activeOfferCategory',
            'selectedCategoryId',
            'selectedCategory',
            'availableProducts',
            'assignedProducts'
        ));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'offer_category_id' => 'required|exists:categories,id',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'action' => 'required|in:add,remove'
        ]);

        $offerCategoryId = $request->input('offer_category_id');
        $productIds = $request->input('product_ids', []);
        $action = $request->input('action');

        $offerCategory = Category::find($offerCategoryId);

        // Only affect products that are not booked and not sold out
        $products = Product::whereIn('id', $productIds)
            ->where('is_out_of_stock', false)
            ->where(function($q) {
                $q->whereNull('booked_by')->orWhere('booked_by', '');
            })->get();

        foreach ($products as $product) {
            if ($action === 'add') {
                $updateData = ['combo_category_id' => $offerCategoryId];

                if ($offerCategory && $offerCategory->offer_type === 'discount' && $offerCategory->discount_value > 0) {
                    $updateData['discount_type'] = $offerCategory->discount_type ?? 'percentage';
                    $updateData['discount_value'] = $offerCategory->discount_value;
                    $updateData['final_price'] = Product::calculateFinalPrice(
                        $product->price,
                        $updateData['discount_type'],
                        $updateData['discount_value']
                    );
                }

                $product->update($updateData);

                // Sync pivot table
                if ($offerCategory && !$product->categories()->where('categories.id', $offerCategoryId)->exists()) {
                    $product->categories()->attach($offerCategoryId);
                }
            } else {
                $updateData = ['combo_category_id' => null];

                if ($offerCategory && $offerCategory->offer_type === 'discount') {
                    $updateData['discount_type'] = 'none';
                    $updateData['discount_value'] = 0;
                    $updateData['final_price'] = $product->price;
                }

                $product->update($updateData);

                // Detach pivot table
                if ($offerCategory) {
                    $product->categories()->detach($offerCategoryId);
                }
            }
        }

        $message = count($productIds) . ' product(s) ' . ($action === 'add' ? 'added to' : 'removed from') . ' this offer category.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return back()->with('success', $message);
    }

    public function activateOfferCategory(Request $request)
    {
        $request->validate([
            'offer_category_id' => 'required|integer',
        ]);

        $categoryId = (int) $request->input('offer_category_id');

        DB::transaction(function () use ($categoryId) {
            // Deactivate all offer categories
            Category::where('is_offer_category', true)->update([
                'is_active_offer' => false,
                'status' => 'inactive'
            ]);

            // If a specific category was selected (not 0 / None)
            if ($categoryId > 0) {
                $activeCat = Category::find($categoryId);
                if ($activeCat) {
                    $activeCat->update([
                        'is_active_offer' => true,
                        'status' => 'active'
                    ]);

                    // Sync all assigned products for this active category
                    $assignedProducts = Product::where('combo_category_id', $categoryId)
                        ->where('is_out_of_stock', false)
                        ->where(function($q) {
                            $q->whereNull('booked_by')->orWhere('booked_by', '');
                        })->get();

                    foreach ($assignedProducts as $prod) {
                        if ($activeCat->offer_type === 'discount' && $activeCat->discount_value > 0) {
                            $discType = $activeCat->discount_type ?? 'percentage';
                            $discVal = $activeCat->discount_value;
                            $finalPrice = Product::calculateFinalPrice($prod->price, $discType, $discVal);
                            $prod->update([
                                'discount_type' => $discType,
                                'discount_value' => $discVal,
                                'final_price' => $finalPrice
                            ]);
                        }
                        if (!$prod->categories()->where('categories.id', $categoryId)->exists()) {
                            $prod->categories()->attach($categoryId);
                        }
                    }
                }
            }
        });

        $activeCat = $categoryId > 0 ? Category::find($categoryId) : null;
        $msg = $activeCat
            ? "Offer Category '{$activeCat->name}' is now ACTIVE. (All other offer categories deactivated)"
            : "No Offer Category is active now. Standard store mode active.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'active_category_id' => $categoryId
            ]);
        }

        return back()->with('success', $msg);
    }

    public function removeOfferFromAllAvailableProducts(Request $request)
    {
        // Past Sales Protection: Only clear combo_category_id for unsold available products
        $assignedProducts = Product::whereNotNull('combo_category_id')
            ->where('is_out_of_stock', false)
            ->where(function($q) {
                $q->whereNull('booked_by')->orWhere('booked_by', '');
            })->get();

        $updatedCount = 0;
        foreach ($assignedProducts as $prod) {
            $catId = $prod->combo_category_id;
            $prod->update([
                'combo_category_id' => null,
                'discount_type' => 'none',
                'discount_value' => 0,
                'final_price' => $prod->price
            ]);
            if ($catId) {
                $prod->categories()->detach($catId);
            }
            $updatedCount++;
        }

        $msg = "Offer assignment removed from {$updatedCount} available product(s). Past sold orders remain 100% untouched.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg
            ]);
        }

        return back()->with('success', $msg);
    }
}
