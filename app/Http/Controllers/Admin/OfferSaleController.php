<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ProductCategoryAssignmentService;
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

        $activeOfferCategories = $offerCategories->where('is_active_offer', true);
        $offerStoreEnabled = (bool) filter_var(Setting::get('offer_store_enabled', $activeOfferCategories->isNotEmpty() ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
        if (!$offerStoreEnabled) $activeOfferCategories = collect();
        $activeOfferCategory = $activeOfferCategories->first();

        $selectedCategoryId = $request->input('offer_category_id');
        if (!$selectedCategoryId && $activeOfferCategories->isNotEmpty()) {
            $selectedCategoryId = $activeOfferCategory->id;
        } elseif (!$selectedCategoryId && $offerCategories->isNotEmpty()) {
            $selectedCategoryId = $offerCategories->first()->id;
        }

        $selectedCategory = $selectedCategoryId ? $offerCategories->firstWhere('id', $selectedCategoryId) : null;

        // These are store/product categories only. Offer categories are deliberately excluded
        // so the Available Products filter stays meaningful while assigning an offer.
        $productFilterCategories = Category::where('is_offer_category', false)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        // Base query for normal available products (not sold out and not booked).
        // Assigned products continue to use this query, preserving the existing workflow.
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

        // 1. Available Products include products assigned to other offers too. The assignment
        // service will move an eligible product from its old offer when the admin adds it here.
        // Booked products can optionally be shown here so the admin can intentionally decide
        // whether to include them in an offer. Products which are merely sold out remain hidden.
        $bookedFilter = $request->input('booked_filter', 'without');
        if (!in_array($bookedFilter, ['without', 'include', 'only'], true)) {
            $bookedFilter = 'without';
        }

        if ($bookedFilter === 'without') {
            $availableProductsQuery = clone $validProductsQuery;
        } else {
            $availableProductsQuery = Product::with(['sizes', 'category', 'comboCategory', 'images']);

            if ($bookedFilter === 'only') {
                $availableProductsQuery->whereNotNull('booked_by')->where('booked_by', '!=', '');
            } else {
                $availableProductsQuery->where(function ($query) {
                    $query->where(function ($availableQuery) {
                        $availableQuery->where('is_out_of_stock', false)
                            ->where(function ($bookedQuery) {
                                $bookedQuery->whereNull('booked_by')->orWhere('booked_by', '');
                            })
                            ->whereHas('sizes', function ($sizeQuery) {
                                $sizeQuery->where('stock', '>', 0);
                            });
                    })->orWhere(function ($bookedQuery) {
                        $bookedQuery->whereNotNull('booked_by')->where('booked_by', '');
                    });
                });
            }

            if ($request->filled('search')) {
                $search = trim($request->search);
                $availableProductsQuery->where('name', 'LIKE', "%{$search}%");
            }
        }

        // Category and price filters intentionally apply only here; assigned products must
        // remain visible so the current drag/remove workflow is never hidden by a filter.
        $productCategoryIds = collect($request->input('product_category_ids', []))
            ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($productCategoryIds->isNotEmpty()) {
            $availableProductsQuery->where(function ($query) use ($productCategoryIds) {
                $query->whereIn('category_id', $productCategoryIds->all())
                    ->orWhereHas('categories', function ($categoryQuery) use ($productCategoryIds) {
                        $categoryQuery->whereIn('categories.id', $productCategoryIds->all());
                    });
            });
        }

        if ($request->filled('min_price') && is_numeric($request->input('min_price'))) {
            $availableProductsQuery->where('final_price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price') && is_numeric($request->input('max_price'))) {
            $availableProductsQuery->where('final_price', '<=', (float) $request->input('max_price'));
        }

        $availableProducts = $availableProductsQuery
            ->orderBy('id', 'desc')
            ->get();

        // 2. Assigned Products (Products currently in the selected offer category)
        $assignedProducts = collect();
        if ($selectedCategoryId) {
            // Keep booked products visible after they are assigned. Otherwise a booked item
            // added from the Available list disappears from both columns after page reload.
            $assignedProductsQuery = Product::with(['sizes', 'category', 'comboCategory', 'images'])
                ->where('combo_category_id', $selectedCategoryId)
                ->where(function ($query) {
                    $query->where(function ($availableQuery) {
                        $availableQuery->where('is_out_of_stock', false)
                            ->where(function ($bookedQuery) {
                                $bookedQuery->whereNull('booked_by')->orWhere('booked_by', '');
                            })
                            ->whereHas('sizes', function ($sizeQuery) {
                                $sizeQuery->where('stock', '>', 0);
                            });
                    })->orWhere(function ($bookedQuery) {
                        $bookedQuery->whereNotNull('booked_by')->where('booked_by', '');
                    });
                });

            if ($request->filled('search')) {
                $search = trim($request->search);
                $assignedProductsQuery->where('name', 'LIKE', "%{$search}%");
            }

            $assignedProducts = $assignedProductsQuery
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
            'activeOfferCategories',
            'offerStoreEnabled',
            'selectedCategoryId',
            'selectedCategory',
            'productFilterCategories',
            'availableProducts',
            'assignedProducts'
        ));
    }

    public function assign(Request $request, ProductCategoryAssignmentService $categoryAssignments)
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

        $offerCategory = Category::whereKey($offerCategoryId)->where('is_offer_category', true)->firstOrFail();

        $products = Product::with('sizes')->whereIn('id', $productIds)->get();

        foreach ($products as $product) {
            if ($action === 'add') {
                $categoryAssignments->assign($product, [], (int) $offerCategoryId);
            } else {
                $categoryAssignments->assign($product, []);
            }
        }

        $message = count($productIds) . ' product(s) ' . ($action === 'add' ? 'added to' : 'removed from') . ' this offer category.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'offer_category_name' => $action === 'add' ? $offerCategory->name : null,
            ]);
        }

        return back()->with('success', $message);
    }

    public function activateOfferCategory(Request $request)
    {
        $request->validate([
            'offer_category_id' => 'required|integer',
            'action' => 'nullable|in:toggle,none',
        ]);

        $categoryId = (int) $request->input('offer_category_id');

        $settings = Setting::values();
        $requestedCategory = $categoryId > 0 ? Category::where('is_offer_category', true)->findOrFail($categoryId) : null;
        $isActive = $requestedCategory && $requestedCategory->is_active_offer;
        DB::transaction(function () use ($categoryId, $requestedCategory, $isActive) {
            if ($categoryId === 0) {
                Category::where('is_offer_category', true)->update(['is_active_offer' => false, 'status' => 'inactive']);
                Setting::set('offer_store_enabled', '0', 'offers');
                return;
            }

            Setting::set('offer_store_enabled', '1', 'offers');

            // Each offer type has its own switch; Combo and Discount may both be active.
            $enable = !$isActive;
            Category::where('is_offer_category', true)->where('offer_type', $requestedCategory->offer_type)
                ->update(['is_active_offer' => false, 'status' => 'inactive']);
            $requestedCategory->update(['is_active_offer' => $enable, 'status' => $enable ? 'active' : 'inactive']);
        });

        $msg = $categoryId === 0
            ? 'Offer Store is OFF. Offer Categories are inactive.'
            : ($requestedCategory->fresh()->is_active_offer
                ? "Offer Category '{$requestedCategory->name}' is now ACTIVE."
                : "Offer Category '{$requestedCategory->name}' is now INACTIVE.");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'active_category_id' => $categoryId,
                'is_active' => (bool) ($requestedCategory?->fresh()->is_active_offer ?? false),
            ]);
        }

        return back()->with('success', $msg);
    }

    public function removeOfferFromAllAvailableProducts(Request $request, ProductCategoryAssignmentService $categoryAssignments)
    {
        // Past Sales Protection: Only clear offer assignments for Available & Booked products.
        // DO NOT touch Sold Out products (is_out_of_stock = 1 OR physical size stock <= 0).
        $assignedProducts = Product::with('sizes')->whereNotNull('combo_category_id')->get();

        $updatedCount = 0;
        foreach ($assignedProducts as $prod) {
            if ($categoryAssignments->assign($prod, [])) $updatedCount++;
        }

        $msg = "Offer assignment removed from {$updatedCount} available/booked product(s). Past sold out products remain 100% untouched with their offer prices preserved.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg
            ]);
        }

        return back()->with('success', $msg);
    }
}
