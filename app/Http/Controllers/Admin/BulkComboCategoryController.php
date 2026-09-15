<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class BulkComboCategoryController extends Controller
{
    public function index(Request $request)
    {
        $comboCategories = Category::where('status', 'active')
            ->where('is_combo_offer', true)
            ->orderBy('name', 'asc')
            ->get();

        $selectedCategoryId = $request->input('combo_category_id');
        if (!$selectedCategoryId && $comboCategories->isNotEmpty()) {
            $selectedCategoryId = $comboCategories->first()->id;
        }

        $selectedCategory = $selectedCategoryId ? $comboCategories->firstWhere('id', $selectedCategoryId) : null;

        // Base query for valid products (Not sold out & Not booked)
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

        // 1. Available Products (Products not currently in the selected combo category)
        $availableProducts = (clone $validProductsQuery)
            ->where(function($q) use ($selectedCategoryId) {
                $q->whereNull('combo_category_id')
                  ->orWhere('combo_category_id', '!=', $selectedCategoryId);
            })
            ->orderBy('id', 'desc')
            ->get();

        // 2. Assigned Products (Products currently in the selected combo category)
        $assignedProducts = collect();
        if ($selectedCategoryId) {
            $assignedProducts = (clone $validProductsQuery)
                ->where('combo_category_id', $selectedCategoryId)
                ->orderBy('combo_sort_order', 'asc')
                ->orderBy('id', 'desc')
                ->get();
        }

        return view('admin.bulk_combo.index', compact(
            'comboCategories',
            'selectedCategoryId',
            'selectedCategory',
            'availableProducts',
            'assignedProducts'
        ));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'combo_category_id' => 'required|exists:categories,id',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'action' => 'required|in:add,remove'
        ]);

        $comboCategoryId = $request->input('combo_category_id');
        $productIds = $request->input('product_ids', []);
        $action = $request->input('action');

        // Only affect products that are not booked and not sold out
        $productsQuery = Product::whereIn('id', $productIds)
            ->where('is_out_of_stock', false)
            ->where(function($q) {
                $q->whereNull('booked_by')->orWhere('booked_by', '');
            });

        if ($action === 'add') {
            // Overwrite existing combo category assignment (1 combo category rule)
            $productsQuery->update(['combo_category_id' => $comboCategoryId]);
            $message = count($productIds) . ' product(s) added to this combo category.';
        } else {
            $productsQuery->where('combo_category_id', $comboCategoryId)
                ->update(['combo_category_id' => null]);
            $message = count($productIds) . ' product(s) removed from this combo category.';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return back()->with('success', $message);
    }
}
