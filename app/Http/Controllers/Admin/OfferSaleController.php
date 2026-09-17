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

        // 1. Available Products (Products not currently in the selected offer category).
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
        $availableProductsQuery
            ->where(function($q) use ($selectedCategoryId) {
                $q->whereNull('combo_category_id')
                  ->orWhere('combo_category_id', '!=', $selectedCategoryId);
            });

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
            'selectedCategoryId',
            'selectedCategory',
            'productFilterCategories',
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

        $products = Product::with('sizes')->whereIn('id', $productIds)->get();

        foreach ($products as $product) {
            $totalStock = (int) $product->sizes->sum('stock');
            $isSoldOut = ($product->is_out_of_stock && empty($product->booked_by)) || ($totalStock <= 0 && empty($product->booked_by));

            if ($action === 'add') {
                if ($isSoldOut) continue;

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
                // Action: remove
                // If sold out, preserve its offer price & category!
                if ($isSoldOut) {
                    continue;
                }

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
        // Past Sales Protection: Only clear offer assignments for Available & Booked products.
        // DO NOT touch Sold Out products (is_out_of_stock = 1 OR physical size stock <= 0).
        $assignedProducts = Product::with('sizes')
            ->where(function($q) {
                $q->whereNotNull('combo_category_id')
                  ->orWhere('discount_type', '!=', 'none');
            })->get();

        $updatedCount = 0;
        foreach ($assignedProducts as $prod) {
            $totalStock = (int) $prod->sizes->sum('stock');
            $isSoldOut = ($prod->is_out_of_stock && empty($prod->booked_by)) || ($totalStock <= 0 && empty($prod->booked_by));

            // CRITICAL: Skip sold out products so their offer purchase history and offer price remain 100% untouched!
            if ($isSoldOut) {
                continue;
            }

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
