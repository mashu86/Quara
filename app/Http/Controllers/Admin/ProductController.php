<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'categories', 'sizes', 'images']);

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $catId = $request->category_id;
            $query->where(function ($q) use ($catId) {
                $q->where('category_id', $catId)
                  ->orWhereHas('categories', function ($cq) use ($catId) {
                      $cq->where('categories.id', $catId);
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $query->whereHas('sizes', function ($q) {
                    $q->where('stock', '>', 0);
                });
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->whereDoesntHave('sizes', function ($q) {
                    $q->where('stock', '>', 0);
                });
            }
        }

        $sort = $request->get('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('id', 'asc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('final_price', 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy('final_price', 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $products = $query->paginate(10)->withQueryString();
        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01',
            'discount_type' => 'required|in:none,fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'delivery_charge_type' => 'nullable|in:include,exclude',
            'weight_kg' => 'nullable|numeric|min:0.01',
            'main_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:3072',
            'sub_images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
            'sizes' => 'required|array',
            'stocks' => 'required|array',
        ]);

        $categoryIds = $request->input('category_ids', []);
        if (empty($categoryIds) && $request->filled('category_id')) {
            $categoryIds = [$request->category_id];
        }

        if (empty($categoryIds)) {
            return back()->withErrors(['category_ids' => 'Please select at least one category.'])->withInput();
        }

        $validated['category_id'] = $categoryIds[0];
        $validated['discount_value'] = $validated['discount_value'] ?? 0.00;
        $validated['delivery_charge_type'] = $validated['delivery_charge_type'] ?? 'exclude';
        $validated['weight_kg'] = $validated['weight_kg'] ?? 0.30;
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);

        DB::transaction(function () use ($validated, $request, $categoryIds) {
            $product = Product::create($validated);
            $product->categories()->sync($categoryIds);

            // Handle Sizes and Stock
            foreach ($validated['sizes'] as $index => $sizeName) {
                if (!empty($sizeName)) {
                    $stockQty = max(0, (int) ($validated['stocks'][$index] ?? 0));
                    $pSize = ProductSize::create([
                        'product_id' => $product->id,
                        'size' => trim($sizeName),
                        'stock' => $stockQty,
                    ]);

                    if ($stockQty > 0) {
                        $this->stockService->adjustStock(
                            $pSize->id,
                            $stockQty,
                            'Initial Stock Addition',
                            auth()->user()->name
                        );
                    }
                }
            }

            // Handle Images
            $mainPath = $request->file('main_image')->store('products', 'public');
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'storage/' . $mainPath,
                'is_primary' => true,
                'sort_order' => 0,
            ]);

            foreach ($request->file('sub_images', []) as $i => $imageFile) {
                $path = $imageFile->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'storage/' . $path,
                    'is_primary' => false,
                    'sort_order' => $i + 1,
                ]);
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
    }

    public function edit(Product $product)
    {
        $product->load(['category', 'categories', 'sizes', 'images', 'stockMovements']);
        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01',
            'discount_type' => 'required|in:none,fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'delivery_charge_type' => 'nullable|in:include,exclude',
            'weight_kg' => 'nullable|numeric|min:0.01',
            'new_images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
            'existing_sizes' => 'nullable|array',
            'existing_sizes.*' => 'required|string|max:50',
            'existing_stocks' => 'nullable|array',
            'existing_stocks.*' => 'required|integer|min:0',
            'new_sizes' => 'nullable|array',
            'new_sizes.*' => 'nullable|string|max:50',
            'new_stocks' => 'nullable|array',
            'new_stocks.*' => 'nullable|integer|min:0',
            'stock_adjustment_reason' => 'nullable|string|max:255',
        ]);

        $categoryIds = $request->input('category_ids', []);
        if (empty($categoryIds) && $request->filled('category_id')) {
            $categoryIds = [$request->category_id];
        }

        if (empty($categoryIds)) {
            return back()->withErrors(['category_ids' => 'Please select at least one category.'])->withInput();
        }

        $validated['category_id'] = $categoryIds[0];
        $validated['discount_value'] = $validated['discount_value'] ?? 0.00;
        $validated['delivery_charge_type'] = $validated['delivery_charge_type'] ?? 'exclude';
        $validated['weight_kg'] = $validated['weight_kg'] ?? 0.30;

        DB::transaction(function () use ($validated, $request, $product, $categoryIds) {
            $product->update($validated);
            $product->categories()->sync($categoryIds);

            $reason = $request->get('stock_adjustment_reason') ?? 'Admin Product Edit Adjustment';

            // Update existing size names and stock for this product only.
            $existingSizes = $validated['existing_sizes'] ?? [];
            $existingStocks = $validated['existing_stocks'] ?? [];
            $requestedSizeIds = array_unique(array_merge(array_keys($existingSizes), array_keys($existingStocks)));

            $productSizes = ProductSize::where('product_id', $product->id)
                ->whereIn('id', $requestedSizeIds)
                ->get()
                ->keyBy('id');

            foreach ($requestedSizeIds as $sizeId) {
                $pSize = $productSizes->get((int) $sizeId);
                if (!$pSize) {
                    continue;
                }

                if (array_key_exists($sizeId, $existingSizes)) {
                    $newSizeName = trim($existingSizes[$sizeId]);
                    if ($pSize->size !== $newSizeName) {
                        $pSize->update(['size' => $newSizeName]);
                    }
                }

                if (array_key_exists($sizeId, $existingStocks)) {
                    $newStock = (int) $existingStocks[$sizeId];
                    if ($pSize->stock !== $newStock) {
                        $this->stockService->adjustStock($pSize->id, $newStock, $reason, auth()->user()->name);
                    }
                }
            }

            // Add new sizes
            if ($request->has('new_sizes')) {
                foreach ($request->new_sizes as $i => $nSize) {
                    if (!empty($nSize)) {
                        $nStock = max(0, (int)($request->new_stocks[$i] ?? 0));
                        $pSize = ProductSize::create([
                            'product_id' => $product->id,
                            'size' => trim($nSize),
                            'stock' => $nStock,
                        ]);
                        if ($nStock > 0) {
                            $this->stockService->adjustStock($pSize->id, $nStock, 'New Size Stock', auth()->user()->name);
                        }
                    }
                }
            }

            // Upload new images
            if ($request->hasFile('new_images')) {
                $maxSort = ProductImage::where('product_id', $product->id)->max('sort_order') ?? 0;
                $hasPrimary = ProductImage::where('product_id', $product->id)->where('is_primary', true)->exists();

                foreach ($request->file('new_images') as $i => $imageFile) {
                    $path = $imageFile->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => 'storage/' . $path,
                        'is_primary' => (!$hasPrimary && $i === 0),
                        'sort_order' => $maxSort + $i + 1,
                    ]);
                }
            }
        });

        return redirect()->route('admin.products.edit', $product->id)->with('success', 'Product updated successfully!');
    }

    public function addStockBatch(Request $request, Product $product)
    {
        $validated = $request->validate([
            'stock_date' => 'required|date',
            'product_size_id' => 'required|exists:product_sizes,id',
            'quantity_to_add' => 'required|integer|min:1',
            'reason_note' => 'required|string|max:255',
        ]);

        $pSize = ProductSize::findOrFail($validated['product_size_id']);
        $newTotal = $pSize->stock + (int) $validated['quantity_to_add'];

        $reason = '[Batch Arrival ' . $validated['stock_date'] . '] ' . $validated['reason_note'];

        $this->stockService->adjustStock(
            $pSize->id,
            $newTotal,
            $reason,
            auth()->user()->name
        );

        return redirect()->route('admin.products.edit', $product->id)
            ->with('success', "Successfully added {$validated['quantity_to_add']} pcs to Size {$pSize->size}!");
    }

    public function setPrimaryImage(ProductImage $image)
    {
        ProductImage::where('product_id', $image->product_id)->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Primary image updated.');
    }

    public function deleteImage(ProductImage $image)
    {
        $productId = $image->product_id;
        if (str_contains($image->image_path, 'storage/')) {
            $oldPath = str_replace('storage/', '', $image->image_path);
            Storage::disk('public')->delete($oldPath);
        }

        $image->delete();

        // Ensure at least one primary image exists
        if (!ProductImage::where('product_id', $productId)->where('is_primary', true)->exists()) {
            $first = ProductImage::where('product_id', $productId)->first();
            if ($first) {
                $first->update(['is_primary' => true]);
            }
        }

        return back()->with('success', 'Image deleted.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $img) {
            if (str_contains($img->image_path, 'storage/')) {
                $oldPath = str_replace('storage/', '', $img->image_path);
                Storage::disk('public')->delete($oldPath);
            }
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully!');
    }
}
