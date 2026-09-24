<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sort = $request->get('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('id', 'asc');
        } elseif ($sort === 'name') {
            $query->orderBy('name', 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $categories = $query->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $isOfferCategory = $request->boolean('is_offer_category');
        $offerType = $request->input('offer_type');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'background_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:12288',
            'text_color' => 'required|string|max:10',
            'status' => 'nullable|in:active,inactive',
            'is_offer_category' => 'nullable|boolean',
            'offer_type' => 'required_if:is_offer_category,1|nullable|in:combo,discount',
            'min_count' => [$isOfferCategory && $offerType === 'combo' ? 'required' : 'nullable', 'integer', 'min:1'],
            'combo_price' => [$isOfferCategory && $offerType === 'combo' ? 'required' : 'nullable', 'numeric', 'min:0'],
            'discount_value' => [$isOfferCategory && $offerType === 'discount' ? 'required' : 'nullable', 'numeric', 'min:0'],
            'discount_type' => [$isOfferCategory && $offerType === 'discount' ? 'required' : 'nullable', 'in:percentage,flat'],
            'delivery_charge' => 'nullable|numeric|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_offer_category'] = $request->has('is_offer_category') ? (bool) $request->is_offer_category : false;
        
        if ($validated['is_offer_category']) {
            $validated['offer_type'] = $request->get('offer_type', 'combo');
            $validated['is_combo_offer'] = ($validated['offer_type'] === 'combo');
            $validated['min_count'] = ($validated['offer_type'] === 'combo') ? ($request->min_count ?? null) : null;
            $validated['combo_price'] = ($validated['offer_type'] === 'combo') ? ($request->combo_price ?? null) : null;
            $validated['discount_value'] = ($validated['offer_type'] === 'discount') ? ($request->discount_value ?? null) : null;
            $validated['discount_type'] = ($validated['offer_type'] === 'discount') ? ($request->discount_type ?? 'percentage') : 'percentage';
            // Offer categories have status managed via Offer Sale Manager
            $validated['status'] = 'inactive';
        } else {
            $validated['offer_type'] = 'combo';
            $validated['is_combo_offer'] = false;
            $validated['min_count'] = null;
            $validated['combo_price'] = null;
            $validated['discount_value'] = null;
            $validated['status'] = $request->status ?? 'active';
        }

        $validated['delivery_charge'] = $request->filled('delivery_charge') ? (float) $request->delivery_charge : 0.00;

        if ($request->hasFile('background_image')) {
            $path = ImageOptimizerService::optimizeAndStore($request->file('background_image'), 'categories', 'public');
            $validated['background_image'] = 'storage/' . $path;
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully!');
    }

    public function show(Category $category)
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $isOfferCategory = $request->boolean('is_offer_category');
        $offerType = $request->input('offer_type');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'background_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:12288',
            'text_color' => 'required|string|max:10',
            'status' => 'nullable|in:active,inactive',
            'is_offer_category' => 'nullable|boolean',
            'offer_type' => 'required_if:is_offer_category,1|nullable|in:combo,discount',
            'min_count' => [$isOfferCategory && $offerType === 'combo' ? 'required' : 'nullable', 'integer', 'min:1'],
            'combo_price' => [$isOfferCategory && $offerType === 'combo' ? 'required' : 'nullable', 'numeric', 'min:0'],
            'discount_value' => [$isOfferCategory && $offerType === 'discount' ? 'required' : 'nullable', 'numeric', 'min:0'],
            'discount_type' => [$isOfferCategory && $offerType === 'discount' ? 'required' : 'nullable', 'in:percentage,flat'],
            'delivery_charge' => 'nullable|numeric|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_offer_category'] = $request->has('is_offer_category') ? (bool) $request->is_offer_category : false;

        if ($validated['is_offer_category']) {
            $validated['offer_type'] = $request->get('offer_type', 'combo');
            $validated['is_combo_offer'] = ($validated['offer_type'] === 'combo');
            $validated['min_count'] = ($validated['offer_type'] === 'combo') ? ($request->min_count ?? null) : null;
            $validated['combo_price'] = ($validated['offer_type'] === 'combo') ? ($request->combo_price ?? null) : null;
            $validated['discount_value'] = ($validated['offer_type'] === 'discount') ? ($request->discount_value ?? null) : null;
            $validated['discount_type'] = ($validated['offer_type'] === 'discount') ? ($request->discount_type ?? 'percentage') : 'percentage';
        } else {
            $validated['offer_type'] = 'combo';
            $validated['is_combo_offer'] = false;
            $validated['min_count'] = null;
            $validated['combo_price'] = null;
            $validated['discount_value'] = null;
            $validated['is_active_offer'] = false;
            if ($request->filled('status')) {
                $validated['status'] = $request->status;
            }
        }

        $validated['delivery_charge'] = $request->filled('delivery_charge') ? (float) $request->delivery_charge : 0.00;

        if ($request->hasFile('background_image')) {
            if ($category->background_image && str_contains($category->background_image, 'storage/')) {
                $oldPath = str_replace('storage/', '', $category->background_image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = ImageOptimizerService::optimizeAndStore($request->file('background_image'), 'categories', 'public');
            $validated['background_image'] = 'storage/' . $path;
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    public function toggleStatus(Request $request, Category $category)
    {
        if ($category->is_offer_category || $category->is_combo_offer) {
            return response()->json([
                'success' => false,
                'message' => 'Status for offer categories is managed centrally in Offer Sale Manager.'
            ], 422);
        }

        $category->status = ($category->status === 'active') ? 'inactive' : 'active';
        $category->save();

        return response()->json([
            'success' => true,
            'status' => $category->status,
            'message' => 'Category status updated successfully to ' . ucfirst($category->status)
        ]);
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category that contains existing products.');
        }

        if ($category->background_image && str_contains($category->background_image, 'storage/')) {
            $oldPath = str_replace('storage/', '', $category->background_image);
            Storage::disk('public')->delete($oldPath);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully!');
    }
}
