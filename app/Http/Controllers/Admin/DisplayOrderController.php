<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class DisplayOrderController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])
        ->orderBy('sort_order', 'asc')
        ->orderBy('id', 'desc')
        ->get();

        $products = Product::with(['category', 'images'])
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        $defaultOrderBy = Setting::get('default_display_order_by', 'category');
        $categoryDisplayStyle = Setting::get('category_display_style', 'grid');

        return view('admin.display_order.index', compact('categories', 'products', 'defaultOrderBy', 'categoryDisplayStyle'));
    }

    public function updatePreference(Request $request)
    {
        $validated = $request->validate([
            'default_display_order_by' => ['required', 'in:category,product'],
            'category_display_style' => ['nullable', 'in:grid,drawer,horizontal_scroll'],
        ]);

        Setting::set('default_display_order_by', $validated['default_display_order_by'], 'general');
        if (isset($validated['category_display_style'])) {
            Setting::set('category_display_style', $validated['category_display_style'], 'general');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Default order preference updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Default order preference updated successfully.');
    }

    public function updateCategoryOrder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:categories,id'],
        ]);

        foreach ($validated['order'] as $index => $categoryId) {
            Category::where('id', $categoryId)->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category position order updated successfully!',
        ]);
    }

    public function updateProductOrder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:products,id'],
        ]);

        foreach ($validated['order'] as $index => $productId) {
            Product::where('id', $productId)->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product position order updated successfully!',
        ]);
    }
}
