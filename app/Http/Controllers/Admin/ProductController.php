<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Services\ImageOptimizerService;
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
                $query->where('is_out_of_stock', false)
                    ->whereHas('sizes', function ($q) {
                        $q->where('stock', '>', 0);
                    });
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->where(function ($q) {
                    $q->where('is_out_of_stock', true)
                      ->orWhereDoesntHave('sizes', function ($sq) {
                          $sq->where('stock', '>', 0);
                      });
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

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();

        if ($request->ajax() || $request->wantsJson()) {
            $desktopHtml = view('admin.products.partials.desktop_rows', compact('products'))->render();

            return response()->json([
                'desktop_html' => $desktopHtml,
                'next_page_url' => $products->nextPageUrl(),
                'has_more' => $products->hasMorePages(),
                'total' => $products->total(),
            ]);
        }

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function toggleOutOfStock(Request $request, Product $product)
    {
        if ($request->has('is_out_of_stock')) {
            $product->is_out_of_stock = $request->boolean('is_out_of_stock');
        } else {
            $product->is_out_of_stock = !$product->is_out_of_stock;
        }

        $product->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_out_of_stock' => $product->is_out_of_stock,
                'message' => $product->is_out_of_stock ? "Product marked as Out of Stock." : "Product restored to normal stock behavior.",
            ]);
        }

        $statusMsg = $product->is_out_of_stock ? "Product marked as Out of Stock." : "Product restored to normal stock behavior.";
        return back()->with('success', $statusMsg);
    }

    public function create(Request $request)
    {
        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();
        $retainedCategoryIds = (array) $request->input('category_ids', []);
        return view('admin.products.create', compact('categories', 'retainedCategoryIds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'action' => 'nullable|string|in:save_and_add_another,save_and_close',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01',
            'discount_type' => 'required|in:none,fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'is_out_of_stock' => 'nullable|boolean',
            'delivery_charge_type' => 'nullable|in:include,exclude',
            'weight_kg' => 'nullable|numeric|min:0.01',
            'main_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:12288',
            'sub_images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:12288',
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
        $validated['is_out_of_stock'] = $request->boolean('is_out_of_stock');
        $validated['discount_value'] = $validated['discount_value'] ?? 0.00;
        $validated['delivery_charge_type'] = $validated['delivery_charge_type'] ?? 'exclude';
        $validated['weight_kg'] = $validated['weight_kg'] ?? 0.30;
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);

        DB::transaction(function () use ($validated, $request, $categoryIds) {
            $product = Product::create($validated);
            $product->categories()->sync($categoryIds);

            // Handle Sizes, Stock and Measurements (Chest, Waist, Length)
            $chests = $request->input('chests', []);
            $waists = $request->input('waists', []);
            $lengths = $request->input('lengths', []);

            foreach ($validated['sizes'] as $index => $sizeName) {
                if (!empty($sizeName)) {
                    $stockQty = max(0, (int) ($validated['stocks'][$index] ?? 0));
                    $pSize = ProductSize::create([
                        'product_id' => $product->id,
                        'size' => trim($sizeName),
                        'stock' => $stockQty,
                        'chest' => !empty($chests[$index]) ? trim($chests[$index]) : null,
                        'waist' => !empty($waists[$index]) ? trim($waists[$index]) : null,
                        'length' => !empty($lengths[$index]) ? trim($lengths[$index]) : null,
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
            $mainPath = ImageOptimizerService::optimizeAndStore($request->file('main_image'), 'products', 'public');
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'storage/' . $mainPath,
                'is_primary' => true,
                'sort_order' => 0,
            ]);

            foreach ($request->file('sub_images', []) as $i => $imageFile) {
                $path = ImageOptimizerService::optimizeAndStore($imageFile, 'products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'storage/' . $path,
                    'is_primary' => false,
                    'sort_order' => $i + 1,
                ]);
            }
        });

        $action = $request->input('action', 'save_and_close');
        if ($action === 'save_and_add_another') {
            return redirect()->route('admin.products.create', ['category_ids' => $categoryIds])
                ->with('success', 'Product "' . $validated['name'] . '" saved successfully! Ready to add your next product.');
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
    }

    public function show(Product $product)
    {
        return redirect()->route('admin.products.edit', $product);
    }

    public function edit(Product $product)
    {
        $product->load(['category', 'categories', 'sizes', 'images', 'stockMovements']);

        // Auto-ensure single primary image if images exist
        if ($product->images->isNotEmpty()) {
            $hasPrimary = $product->images->contains('is_primary', true);
            if (!$hasPrimary) {
                $firstImg = $product->images->first();
                $firstImg->update(['is_primary' => true]);
                $product->load('images');
            }
        }

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
            'is_out_of_stock' => 'nullable|boolean',
            'delivery_charge_type' => 'nullable|in:include,exclude',
            'weight_kg' => 'nullable|numeric|min:0.01',
            'new_images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:12288',
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
        $validated['is_out_of_stock'] = $request->boolean('is_out_of_stock');
        $validated['discount_value'] = $validated['discount_value'] ?? 0.00;
        $validated['delivery_charge_type'] = $validated['delivery_charge_type'] ?? 'exclude';
        $validated['weight_kg'] = $validated['weight_kg'] ?? 0.30;

        DB::transaction(function () use ($validated, $request, $product, $categoryIds) {
            $product->update($validated);
            $product->categories()->sync($categoryIds);

            $reason = $request->get('stock_adjustment_reason') ?? 'Admin Product Edit Adjustment';

            // Update existing size names, stock & measurements
            $existingSizes = $validated['existing_sizes'] ?? [];
            $existingStocks = $validated['existing_stocks'] ?? [];
            $existingChests = $request->input('existing_chests', []);
            $existingWaists = $request->input('existing_waists', []);
            $existingLengths = $request->input('existing_lengths', []);

            $requestedSizeIds = array_unique(array_merge(array_keys($existingSizes), array_keys($existingStocks), array_keys($existingChests)));

            $productSizes = ProductSize::where('product_id', $product->id)
                ->whereIn('id', $requestedSizeIds)
                ->get()
                ->keyBy('id');

            foreach ($requestedSizeIds as $sizeId) {
                $pSize = $productSizes->get((int) $sizeId);
                if (!$pSize) {
                    continue;
                }

                $updateData = [];

                if (array_key_exists($sizeId, $existingSizes)) {
                    $newSizeName = trim($existingSizes[$sizeId]);
                    if ($pSize->size !== $newSizeName) {
                        $updateData['size'] = $newSizeName;
                    }
                }

                if (array_key_exists($sizeId, $existingChests)) {
                    $cVal = !empty($existingChests[$sizeId]) ? trim($existingChests[$sizeId]) : null;
                    if ($pSize->chest !== $cVal) $updateData['chest'] = $cVal;
                }

                if (array_key_exists($sizeId, $existingWaists)) {
                    $wVal = !empty($existingWaists[$sizeId]) ? trim($existingWaists[$sizeId]) : null;
                    if ($pSize->waist !== $wVal) $updateData['waist'] = $wVal;
                }

                if (array_key_exists($sizeId, $existingLengths)) {
                    $lVal = !empty($existingLengths[$sizeId]) ? trim($existingLengths[$sizeId]) : null;
                    if ($pSize->length !== $lVal) $updateData['length'] = $lVal;
                }

                if (!empty($updateData)) {
                    $pSize->update($updateData);
                }

                if (array_key_exists($sizeId, $existingStocks)) {
                    $newStock = max(0, (int) $existingStocks[$sizeId]);
                    $oldStock = (int) $pSize->stock;
                    $diff = $newStock - $oldStock;

                    if ($diff !== 0) {
                        $this->stockService->adjustStock(
                            $pSize->id,
                            $diff,
                            $reason,
                            auth()->user()->name
                        );
                    }
                }
            }

            // Create new sizes
            $newSizes = $validated['new_sizes'] ?? [];
            $newStocks = $validated['new_stocks'] ?? [];
            $newChests = $request->input('new_chests', []);
            $newWaists = $request->input('new_waists', []);
            $newLengths = $request->input('new_lengths', []);

            foreach ($newSizes as $i => $nSize) {
                if (!empty($nSize)) {
                    $nStock = max(0, (int) ($newStocks[$i] ?? 0));
                    $pSize = ProductSize::create([
                        'product_id' => $product->id,
                        'size' => trim($nSize),
                        'stock' => $nStock,
                        'chest' => !empty($newChests[$i]) ? trim($newChests[$i]) : null,
                        'waist' => !empty($newWaists[$i]) ? trim($newWaists[$i]) : null,
                        'length' => !empty($newLengths[$i]) ? trim($newLengths[$i]) : null,
                    ]);
                    if ($nStock > 0) {
                        $this->stockService->adjustStock($pSize->id, $nStock, 'New Size Stock', auth()->user()->name);
                    }
                }
            }

            // Upload new images
            if ($request->hasFile('new_images')) {
                $maxSort = ProductImage::where('product_id', $product->id)->max('sort_order') ?? 0;
                $hasPrimary = ProductImage::where('product_id', $product->id)->where('is_primary', true)->exists();

                foreach ($request->file('new_images') as $i => $imageFile) {
                    $path = ImageOptimizerService::optimizeAndStore($imageFile, 'products', 'public');
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

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Primary display image updated successfully.'
            ]);
        }

        return back()->with('success', 'Primary display image updated successfully.');
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
