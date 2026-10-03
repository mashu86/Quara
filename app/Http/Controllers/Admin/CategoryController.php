<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageOptimizerService;
use App\Services\ProductCategoryAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

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

        if ($request->boolean('ajax')) {
            return response()->json([
                'html' => view('admin.categories.index', compact('categories'))->render(),
                'next_page_url' => $categories->nextPageUrl(),
                'has_more' => $categories->hasMorePages(),
            ]);
        }

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
            'combo_price' => [$isOfferCategory && $offerType === 'combo' ? 'required_without:unit_offer_price' : 'nullable', 'numeric', 'min:0'],
            'unit_offer_price' => [$isOfferCategory && $offerType === 'combo' ? 'required_without:combo_price' : 'nullable', 'numeric', 'min:0'],
            'discount_value' => [$isOfferCategory && $offerType === 'discount' ? 'required' : 'nullable', 'numeric', 'min:0'],
            'discount_type' => [$isOfferCategory && $offerType === 'discount' ? 'required' : 'nullable', 'in:percentage,flat'],
            'delivery_charge_mode' => ['required_if:is_offer_category,1', 'nullable', 'in:free,custom,master'],
            'delivery_charge' => ['nullable', 'numeric', 'min:0', 'required_if:delivery_charge_mode,custom'],
            'allow_pre_min_purchase' => 'nullable|boolean',
            'pre_min_purchase_offer_price' => 'nullable|boolean',
            'minimum_purchase_required' => 'nullable|boolean',
            'minimum_purchase_count' => [$request->boolean('minimum_purchase_required') && !$isOfferCategory ? 'required' : 'nullable', 'integer', 'min:1'],
            'show_in_collection' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        if ($isOfferCategory && $offerType === 'combo' && (int) $request->input('min_count') > 0) {
            $validated['combo_price'] = $request->filled('unit_offer_price')
                ? round((float) $request->input('unit_offer_price') * (int) $request->input('min_count'), 2)
                : (float) $request->input('combo_price');
        }
        $validated['is_offer_category'] = $request->has('is_offer_category') ? (bool) $request->is_offer_category : false;
        $validated['show_in_collection'] = $request->boolean('show_in_collection', true);
        
        if ($validated['is_offer_category']) {
            $validated['offer_type'] = $request->get('offer_type', 'combo');
            $validated['is_combo_offer'] = ($validated['offer_type'] === 'combo');
            $validated['min_count'] = ($validated['offer_type'] === 'combo') ? ($request->min_count ?? null) : null;
            $validated['combo_price'] = ($validated['offer_type'] === 'combo') ? ($validated['combo_price'] ?? null) : null;
            unset($validated['unit_offer_price']);
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
            $validated['delivery_charge_mode'] = 'master';
        }

        $validated['delivery_charge_mode'] = $isOfferCategory ? $request->input('delivery_charge_mode', 'free') : 'master';
        $validated['delivery_charge'] = $validated['delivery_charge_mode'] === 'custom' ? (float) $request->input('delivery_charge', 0) : 0.00;
        $validated['allow_pre_min_purchase'] = $isOfferCategory && $offerType === 'combo' && $request->boolean('allow_pre_min_purchase');
        $validated['pre_min_purchase_offer_price'] = $validated['allow_pre_min_purchase'] && $request->boolean('pre_min_purchase_offer_price');
        $validated['minimum_purchase_required'] = !$isOfferCategory && $request->boolean('minimum_purchase_required');
        $validated['minimum_purchase_count'] = $validated['minimum_purchase_required'] ? (int) $request->input('minimum_purchase_count') : null;

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

    public function products(Request $request, Category $category)
    {
        $validated = $request->validate([
            'filter' => ['nullable', Rule::in(['all', 'not_sold_out', 'available'])],
            'side' => ['nullable', Rule::in(['inside', 'outside'])],
            'search' => ['nullable', 'string', 'max:100'],
            'search_side' => ['nullable', Rule::in(['inside', 'outside'])],
            'side_search' => ['nullable', 'string', 'max:100'],
        ]);

        $filter = $validated['filter'] ?? 'all';
        $side = $validated['side'] ?? null;
        $isAjax = $request->boolean('ajax');
        $baseQuery = Product::query()->where('products.status', 'active')
            ->with(['sizes', 'category', 'categories']);

        $insideQuery = (clone $baseQuery)->where(function ($query) use ($category) {
            $query->where('products.category_id', $category->id)
                ->orWhereHas('categories', fn ($categories) => $categories->where('categories.id', $category->id));
        });

        $outsideQuery = (clone $baseQuery)->where(function ($query) use ($category) {
            $query->whereNull('products.category_id')->orWhere('products.category_id', '!=', $category->id);
        })->whereDoesntHave('categories', fn ($categories) => $categories->where('categories.id', $category->id));

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $insideQuery->where('products.name', 'like', '%' . $search . '%');
            $outsideQuery->where('products.name', 'like', '%' . $search . '%');
        }
        if (!empty($validated['side_search'])) {
            $searchQuery = $validated['search_side'] === 'inside' ? $insideQuery : $outsideQuery;
            $searchQuery->where('products.name', 'like', '%' . $validated['side_search'] . '%');
        }

        foreach ([$insideQuery, $outsideQuery] as $query) {
            if ($filter === 'not_sold_out') {
                $query->where('products.is_out_of_stock', false)
                    ->whereHas('sizes', fn ($sizes) => $sizes->whereRaw('product_sizes.stock > COALESCE(product_sizes.reserved_stock, 0)'));
            } elseif ($filter === 'available') {
                $query->where('products.is_out_of_stock', false)
                    ->whereHas('sizes', fn ($sizes) => $sizes->whereRaw('product_sizes.stock > COALESCE(product_sizes.reserved_stock, 0)'))
                    ->whereDoesntHave('sizes', fn ($sizes) => $sizes->where('product_sizes.reserved_stock', '>', 0));
            }
        }

        $insideProducts = $insideQuery->orderBy('products.name')->paginate(20, ['products.*'], 'inside_page')->withQueryString();
        $outsideProducts = $outsideQuery->orderBy('products.name')->paginate(20, ['products.*'], 'outside_page')->withQueryString();

        if ($isAjax) {
            $products = $side === 'inside' ? $insideProducts : $outsideProducts;
            return response()->json([
                'html' => view('admin.categories.partials.product_items', [
                    'products' => $products,
                    'category' => $category,
                    'side' => $side,
                ])->render(),
                'next_page_url' => $products->nextPageUrl(),
                'has_more' => $products->hasMorePages(),
                'total' => $products->total(),
            ]);
        }

        if ($side === 'inside') {
            $outsideProducts = collect();
        } elseif ($side === 'outside') {
            $insideProducts = collect();
        }

        return view('admin.categories.products', compact('category', 'insideProducts', 'outsideProducts', 'filter', 'side'));
    }

    public function attachProduct(Request $request, Category $category, Product $product, ProductCategoryAssignmentService $categoryAssignments)
    {
        abort_unless($product->status === 'active', 422, 'Only active products can be assigned to a category.');
        $categoryAssignments->assign($product, $category->is_offer_category || $category->is_combo_offer ? [] : [$category->id], $category->is_offer_category || $category->is_combo_offer ? (int) $category->id : null);

        return response()->json(['success' => true, 'message' => 'Product added to category.']);
    }

    public function detachProduct(Request $request, Category $category, Product $product, ProductCategoryAssignmentService $categoryAssignments)
    {
        DB::transaction(function () use ($category, $product, $categoryAssignments) {
            if ((int) $product->combo_category_id === (int) $category->id) {
                $categoryAssignments->assign($product, []);
            }
            $category->products()->detach($product->id);
            if ((int) $product->category_id === (int) $category->id) {
                $nextCategoryId = DB::table('category_product')->where('product_id', $product->id)->value('category_id');
                $product->category_id = $nextCategoryId;
                $product->save();
            }
            $this->syncProductCollectionVisibility($product->fresh());
        });

        return response()->json(['success' => true, 'message' => 'Product removed from category.']);
    }

    private function syncProductCollectionVisibility(Product $product): void
    {
        $categoryIds = collect([$product->category_id])
            ->merge($product->categories()->pluck('categories.id'))
            ->filter()
            ->unique();
        $visible = $categoryIds->isEmpty()
            || !Category::whereIn('id', $categoryIds)->where('show_in_collection', false)->exists();
        $product->update(['collection_visible' => $visible]);
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
            'combo_price' => [$isOfferCategory && $offerType === 'combo' ? 'required_without:unit_offer_price' : 'nullable', 'numeric', 'min:0'],
            'unit_offer_price' => [$isOfferCategory && $offerType === 'combo' ? 'required_without:combo_price' : 'nullable', 'numeric', 'min:0'],
            'discount_value' => [$isOfferCategory && $offerType === 'discount' ? 'required' : 'nullable', 'numeric', 'min:0'],
            'discount_type' => [$isOfferCategory && $offerType === 'discount' ? 'required' : 'nullable', 'in:percentage,flat'],
            'delivery_charge_mode' => ['required_if:is_offer_category,1', 'nullable', 'in:free,custom,master'],
            'delivery_charge' => ['nullable', 'numeric', 'min:0', 'required_if:delivery_charge_mode,custom'],
            'allow_pre_min_purchase' => 'nullable|boolean',
            'pre_min_purchase_offer_price' => 'nullable|boolean',
            'minimum_purchase_required' => 'nullable|boolean',
            'minimum_purchase_count' => [$request->boolean('minimum_purchase_required') && !$isOfferCategory ? 'required' : 'nullable', 'integer', 'min:1'],
            'show_in_collection' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        if ($isOfferCategory && $offerType === 'combo' && (int) $request->input('min_count') > 0) {
            $validated['combo_price'] = $request->filled('unit_offer_price')
                ? round((float) $request->input('unit_offer_price') * (int) $request->input('min_count'), 2)
                : (float) $request->input('combo_price');
        }
        $validated['is_offer_category'] = $request->has('is_offer_category') ? (bool) $request->is_offer_category : false;
        $validated['show_in_collection'] = $request->has('show_in_collection')
            ? $request->boolean('show_in_collection')
            : (bool) $category->show_in_collection;

        if ($validated['is_offer_category']) {
            $validated['offer_type'] = $request->get('offer_type', 'combo');
            $validated['is_combo_offer'] = ($validated['offer_type'] === 'combo');
            $validated['min_count'] = ($validated['offer_type'] === 'combo') ? ($request->min_count ?? null) : null;
            $validated['combo_price'] = ($validated['offer_type'] === 'combo') ? ($validated['combo_price'] ?? null) : null;
            unset($validated['unit_offer_price']);
            $validated['discount_value'] = ($validated['offer_type'] === 'discount') ? ($request->discount_value ?? null) : null;
            $validated['discount_type'] = ($validated['offer_type'] === 'discount') ? ($request->discount_type ?? 'percentage') : 'percentage';
        } else {
            $validated['offer_type'] = 'combo';
            $validated['is_combo_offer'] = false;
            $validated['min_count'] = null;
            $validated['combo_price'] = null;
            $validated['discount_value'] = null;
            $validated['is_active_offer'] = false;
            $validated['delivery_charge_mode'] = 'master';
            if ($request->filled('status')) {
                $validated['status'] = $request->status;
            }
        }

        $validated['delivery_charge_mode'] = $isOfferCategory ? $request->input('delivery_charge_mode', 'free') : 'master';
        $validated['delivery_charge'] = $validated['delivery_charge_mode'] === 'custom' ? (float) $request->input('delivery_charge', 0) : 0.00;
        $validated['allow_pre_min_purchase'] = $isOfferCategory && $offerType === 'combo' && $request->boolean('allow_pre_min_purchase');
        $validated['pre_min_purchase_offer_price'] = $validated['allow_pre_min_purchase'] && $request->boolean('pre_min_purchase_offer_price');
        $validated['minimum_purchase_required'] = !$isOfferCategory && $request->boolean('minimum_purchase_required');
        $validated['minimum_purchase_count'] = $validated['minimum_purchase_required'] ? (int) $request->input('minimum_purchase_count') : null;

        if ($request->hasFile('background_image')) {
            if ($category->background_image && str_contains($category->background_image, 'storage/')) {
                $oldPath = str_replace('storage/', '', $category->background_image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = ImageOptimizerService::optimizeAndStore($request->file('background_image'), 'categories', 'public');
            $validated['background_image'] = 'storage/' . $path;
        }

        $deactivatingCategory = $category->status === 'active'
            && ($validated['status'] ?? $category->status) === 'inactive';

        DB::transaction(function () use ($category, $validated, $deactivatingCategory) {
            $category->update($validated);
            if ($deactivatingCategory) {
                $this->detachProductsFromCategory($category);
            }
        });

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

        $newStatus = ($category->status === 'active') ? 'inactive' : 'active';
        DB::transaction(function () use ($category, $newStatus) {
            $category->status = $newStatus;
            $category->save();

            if ($newStatus === 'inactive') {
                $this->detachProductsFromCategory($category);
            }
        });

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

    private function detachProductsFromCategory(Category $category): void
    {
        $affectedProductIds = Product::where('category_id', $category->id)
            ->orWhereHas('categories', fn ($query) => $query->where('categories.id', $category->id))
            ->pluck('id');

        if (Schema::hasColumn('products', 'combo_category_id')) {
            $affectedProductIds = $affectedProductIds->merge(
                Product::where('combo_category_id', $category->id)->pluck('id')
            )->unique()->values();
        }

        Product::where('category_id', $category->id)->update(['category_id' => null]);

        if (Schema::hasColumn('products', 'combo_category_id')) {
            Product::where('combo_category_id', $category->id)->update(['combo_category_id' => null]);
        }

        DB::table('category_product')->where('category_id', $category->id)->delete();

        foreach ($affectedProductIds as $productId) {
            $remainingCategoryIds = Product::whereKey($productId)->whereNotNull('category_id')->pluck('category_id');
            $remainingCategoryIds = $remainingCategoryIds->merge(
                DB::table('category_product')->where('product_id', $productId)->pluck('category_id')
            );

            if (Schema::hasColumn('products', 'combo_category_id')) {
                $comboCategoryId = Product::whereKey($productId)->value('combo_category_id');
                if ($comboCategoryId) {
                    $remainingCategoryIds->push($comboCategoryId);
                }
            }

            $isCollectionVisible = $remainingCategoryIds->isEmpty()
                ? (bool) $category->show_in_collection
                : ! Category::whereIn('id', $remainingCategoryIds->unique())
                    ->where('show_in_collection', false)
                    ->exists();

            Product::whereKey($productId)->update(['collection_visible' => $isCollectionVisible]);
        }
    }
}
