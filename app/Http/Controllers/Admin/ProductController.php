<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GeminiApiKey;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Models\Setting;
use App\Models\SizeMaster;
use App\Services\ImageOptimizerService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class ProductController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function sizeGuide()
    {
        $siteName = Setting::get('site_name', config('app.name', 'Quara'));
        $logoUrl = Setting::logoUrl();
        return view('admin.products.size_guide', compact('siteName', 'logoUrl'));
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
                    $q->where(function ($soldOut) {
                        $soldOut->where('is_out_of_stock', true)
                            ->orWhereDoesntHave('sizes', function ($sq) {
                                $sq->where('stock', '>', 0);
                            });
                    })->where(function ($notBooked) {
                        $notBooked->whereNull('booked_by')
                            ->orWhereRaw("TRIM(booked_by) = ''");
                    })->where(function ($notBusinessBooking) {
                        $notBusinessBooking->whereNull('booking_type')
                            ->orWhere('booking_type', '<>', 'business_whatsapp');
                    });
                });
            } elseif ($request->stock_status === 'reserved') {
                $query->where('is_out_of_stock', true)->where(function ($booked) {
                    $booked->where(function ($byCustomer) {
                        $byCustomer->whereNotNull('booked_by')->whereRaw("TRIM(booked_by) <> ''");
                    })->orWhere('booking_type', 'business_whatsapp');
                });
            } elseif (in_array($request->stock_status, ['return_to_stock', 'do_not_restock'], true)) {
                $query->whereHas('orderItems', function ($itemQuery) use ($request) {
                    $itemQuery->where('item_status', 'returned')
                        ->where('inventory_condition', $request->stock_status);
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

    public function bookedProducts(Request $request)
    {
        $query = Product::with(['category', 'sizes', 'images'])
            ->where('is_out_of_stock', true)
            ->where(function ($booked) {
                $booked->where(function ($legacyOrCustomer) {
                    $legacyOrCustomer->whereNotNull('booked_by')->whereRaw("TRIM(booked_by) <> ''");
                })->orWhere('booking_type', 'business_whatsapp');
            });

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('booked_by', 'like', '%'.$search.'%')
                    ->orWhere('booking_type', 'like', '%'.$search.'%');
            });
        }

        $products = $query->orderByDesc('updated_at')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.products.booked', compact('products', 'search'));
    }

    public function bulkUnbook(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|integer|distinct|exists:products,id',
        ]);

        $count = Product::whereIn('id', $validated['product_ids'])
            ->where('is_out_of_stock', true)
            ->where(function ($booked) {
                $booked->where(function ($legacyOrCustomer) {
                    $legacyOrCustomer->whereNotNull('booked_by')->whereRaw("TRIM(booked_by) <> ''");
                })->orWhereNotNull('booking_type');
            })
            ->update([
                'is_out_of_stock' => false,
                'booked_by' => null,
                'booking_type' => null,
                'booking_date' => null,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.products.booked', $request->only('search', 'page'))
            ->with('success', "{$count} booked product(s) unbooked successfully.");
    }

    public function toggleOutOfStock(Request $request, Product $product)
    {
        if ($request->has('is_out_of_stock')) {
            $isOutOfStock = $request->boolean('is_out_of_stock');
        } else {
            $isOutOfStock = !$product->is_out_of_stock;
        }

        $bookedBy = trim($request->input('booked_by', ''));
        $validated = $request->validate([
            'booked_by' => 'nullable|string|max:255',
            'booking_type' => 'nullable|in:business_whatsapp,whatsapp_customer,instagram_customer',
            'booking_date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($isOutOfStock && $request->input('booking_type') !== 'business_whatsapp' && empty($bookedBy)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booked By is mandatory when marking a product as Booked.'
                ], 422);
            }
            return back()->withErrors(['booked_by' => 'Booked By is mandatory when marking a product as Booked.'])->withInput();
        }

        $product->is_out_of_stock = $isOutOfStock;
        $product->booked_by = $isOutOfStock ? $bookedBy : null;
        $product->booking_type = $isOutOfStock ? ($validated['booking_type'] ?? null) : null;
        $product->booking_date = $isOutOfStock ? ($validated['booking_date'] ?? now()->toDateString()) : null;
        $product->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_out_of_stock' => $product->is_out_of_stock,
                'booked_by' => $product->booked_by,
                'booking_type' => $product->booking_type,
                'booking_date' => $product->booking_date?->format('Y-m-d'),
                'message' => $product->is_out_of_stock ? "Product marked as Booked." : "Product restored to normal stock behavior.",
            ]);
        }

        $statusMsg = $product->is_out_of_stock ? "Product marked as Booked." : "Product restored to normal stock behavior.";
        return back()->with('success', $statusMsg);
    }

    public function toggleStatus(Product $product)
    {
        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();

        return response()->json([
            'success' => true,
            'status' => $product->status,
            'message' => 'Product status updated to ' . ucfirst($product->status) . '.',
        ]);
    }

    public function create(Request $request)
    {
        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();
        $comboCategories = Category::where('status', 'active')
            ->where(function ($q) {
                $q->where('is_offer_category', true)->orWhere('is_combo_offer', true);
            })
            ->orderBy('name', 'asc')
            ->get();
        $sizeMasters = \App\Models\SizeMaster::with('rows')->orderBy('sort_order', 'asc')->get();
        $retainedCategoryIds = (array) $request->input('category_ids', []);
        $geminiApiKeys = GeminiApiKey::query()->orderByDesc('is_active')->orderByDesc('id')->get();
        return view('admin.products.create', compact('categories', 'comboCategories', 'sizeMasters', 'retainedCategoryIds', 'geminiApiKeys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'action' => 'nullable|string|in:save_and_add_another,save_and_close',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'category_id' => 'nullable|exists:categories,id',
            'measurement_type' => 'nullable|in:up,down',
            'combo_category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01',
            'discount_type' => 'required|in:none,fixed,percentage,flat',
            'discount_value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'is_out_of_stock' => 'nullable|boolean',
            'booked_by' => 'nullable|string|max:255',
            'ai_whatsapp_booked' => 'nullable|boolean',
            'booking_type' => 'nullable|in:business_whatsapp,whatsapp_customer,instagram_customer',
            'booking_date' => 'nullable|date_format:Y-m-d',
            'display_size_chart' => 'nullable|boolean',
            'size_master_id' => 'nullable|exists:size_masters,id',
            'delivery_charge_type' => 'nullable|in:include,exclude',
            'weight_kg' => 'nullable|numeric|min:0.01',
            'main_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:12288',
            'sub_images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:12288',
            'sizes' => 'required|array',
            'sizes.*' => 'nullable|string|max:50',
            'stocks' => 'required|array',
        ]);

        $categoryIds = array_values(array_filter($request->input('category_ids', [])));
        if (empty($categoryIds) && $request->filled('category_id')) {
            $categoryIds = [$request->category_id];
        }

        $validated['category_id'] = $categoryIds[0] ?? null;
        $validated['measurement_type'] = $validated['measurement_type'] ?? 'up';
        $validated['is_out_of_stock'] = $request->boolean('is_out_of_stock');
        $isAiBusinessBooking = $validated['is_out_of_stock'] && $request->boolean('ai_whatsapp_booked');
        $validated['booked_by'] = ($validated['is_out_of_stock'] && ! $isAiBusinessBooking) ? (trim($request->input('booked_by', '')) ?: null) : null;
        $validated['booking_type'] = $validated['is_out_of_stock'] ? ($isAiBusinessBooking ? 'business_whatsapp' : ($validated['booking_type'] ?? null)) : null;
        $validated['booking_date'] = $validated['is_out_of_stock'] ? ($isAiBusinessBooking ? ($validated['booking_date'] ?? now()->toDateString()) : ($validated['booking_date'] ?? null)) : null;
        unset($validated['ai_whatsapp_booked']);
        $validated['display_size_chart'] = $request->boolean('display_size_chart');
        $validated['size_master_id'] = $request->filled('size_master_id') ? (int) $request->input('size_master_id') : null;

        if ($validated['is_out_of_stock'] && $validated['booking_type'] !== 'business_whatsapp' && empty($validated['booked_by'])) {
            return back()->withErrors(['booked_by' => 'Booked By is mandatory when marking a product as Booked.'])->withInput();
        }
        if ($validated['is_out_of_stock'] && empty($validated['booking_type'])) {
            return back()->withErrors(['booking_type' => 'Select a booking type.'])->withInput();
        }
        if ($validated['is_out_of_stock'] && empty($validated['booking_date'])) {
            return back()->withErrors(['booking_date' => 'Select the booked date.'])->withInput();
        }
        $validated['discount_value'] = $validated['discount_value'] ?? 0.00;
        $validated['delivery_charge_type'] = $validated['delivery_charge_type'] ?? 'exclude';
        $validated['weight_kg'] = $validated['weight_kg'] ?? 0.30;
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);

        if (! empty($validated['combo_category_id'])) {
            $offerCategory = Category::find($validated['combo_category_id']);
            if ($offerCategory && $offerCategory->offer_type === 'discount' && $offerCategory->discount_value > 0) {
                $validated['discount_type'] = $offerCategory->discount_type ?? 'percentage';
                $validated['discount_value'] = $offerCategory->discount_value;
                $validated['final_price'] = Product::calculateFinalPrice(
                    $validated['price'],
                    $validated['discount_type'],
                    $validated['discount_value']
                );
            }
            if ($offerCategory && ! in_array((int) $offerCategory->id, array_map('intval', $categoryIds), true)) {
                $categoryIds[] = (int) $offerCategory->id;
            }
        }
        $validated['collection_visible'] = ! Category::whereIn('id', $categoryIds)
            ->where('show_in_collection', false)
            ->exists();

        DB::transaction(function () use ($validated, $request, $categoryIds) {
            $product = Product::create($validated);
            $product->categories()->sync($categoryIds);

            // Handle Sizes, Stock and Measurements (Chest, Waist, Length)
            $chests = $request->input('chests', []);
            $waists = $request->input('waists', []);
            $hips = $request->input('hips', []);
            $lengths = $request->input('lengths', []);

            foreach ($validated['sizes'] as $index => $sizeName) {
                $stockQty = max(0, (int) ($validated['stocks'][$index] ?? 0));
                $pSize = ProductSize::create([
                    'product_id' => $product->id,
                    'size' => trim($sizeName ?? ''),
                    'stock' => $stockQty,
                    'chest' => !empty($chests[$index]) ? trim($chests[$index]) : null,
                    'waist' => !empty($waists[$index]) ? trim($waists[$index]) : null,
                    'hip' => !empty($hips[$index]) ? trim($hips[$index]) : null,
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
            return redirect()->route('admin.products.create')
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
        $product->load(['category', 'categories', 'sizes', 'images', 'stockMovements', 'comboCategory', 'sizeMaster']);

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
        $comboCategories = Category::where('status', 'active')
            ->where(function ($q) {
                $q->where('is_offer_category', true)->orWhere('is_combo_offer', true);
            })
            ->orderBy('name', 'asc')
            ->get();
        $sizeMasters = \App\Models\SizeMaster::with('rows')->orderBy('sort_order', 'asc')->get();
        $geminiApiKeys = GeminiApiKey::query()->orderByDesc('is_active')->orderByDesc('id')->get();
        return view('admin.products.edit', compact('product', 'categories', 'comboCategories', 'sizeMasters', 'geminiApiKeys'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'category_id' => 'nullable|exists:categories,id',
            'measurement_type' => 'nullable|in:up,down',
            'combo_category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01',
            'discount_type' => 'required|in:none,fixed,percentage,flat',
            'discount_value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'is_out_of_stock' => 'nullable|boolean',
            'booked_by' => 'nullable|string|max:255',
            'ai_whatsapp_booked' => 'nullable|boolean',
            'booking_type' => 'nullable|in:business_whatsapp,whatsapp_customer,instagram_customer',
            'booking_date' => 'nullable|date_format:Y-m-d',
            'display_size_chart' => 'nullable|boolean',
            'size_master_id' => 'nullable|exists:size_masters,id',
            'delivery_charge_type' => 'nullable|in:include,exclude',
            'weight_kg' => 'nullable|numeric|min:0.01',
            'new_images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:12288',
            'existing_sizes' => 'nullable|array',
            'existing_sizes.*' => 'nullable|string|max:50',
            'clear_size_labels' => 'nullable|array',
            'clear_size_labels.*' => 'in:0,1',
            'existing_stocks' => 'nullable|array',
            'existing_stocks.*' => 'required|integer|min:0',
            'new_sizes' => 'nullable|array',
            'new_sizes.*' => 'nullable|string|max:50',
            'new_stocks' => 'nullable|array',
            'new_stocks.*' => 'nullable|integer|min:0',
            'stock_adjustment_reason' => 'nullable|string|max:255',
        ]);

        $categoryIds = array_values(array_filter($request->input('category_ids', [])));
        if (empty($categoryIds) && $request->filled('category_id')) {
            $categoryIds = [$request->category_id];
        }

        $validated['category_id'] = $categoryIds[0] ?? null;
        $validated['measurement_type'] = $validated['measurement_type'] ?? $product->measurement_type ?? 'up';
        $validated['is_out_of_stock'] = $request->boolean('is_out_of_stock');
        $isAiBusinessBooking = $validated['is_out_of_stock'] && $request->boolean('ai_whatsapp_booked');
        $validated['booked_by'] = ($validated['is_out_of_stock'] && ! $isAiBusinessBooking) ? (trim($request->input('booked_by', '')) ?: null) : null;
        $validated['booking_type'] = $validated['is_out_of_stock'] ? ($isAiBusinessBooking ? 'business_whatsapp' : ($validated['booking_type'] ?? $product->booking_type)) : null;
        $validated['booking_date'] = $validated['is_out_of_stock'] ? ($isAiBusinessBooking ? ($validated['booking_date'] ?? now()->toDateString()) : ($validated['booking_date'] ?? $product->booking_date?->format('Y-m-d'))) : null;
        unset($validated['ai_whatsapp_booked']);
        $validated['display_size_chart'] = $request->boolean('display_size_chart');
        $validated['size_master_id'] = $request->filled('size_master_id') ? (int) $request->input('size_master_id') : null;

        // If product was originally sold out or booked, do not allow changing combo_category_id
        $wasSoldOutOrBooked = ($product->is_out_of_stock || !empty($product->booked_by) || $product->sizes->sum('stock') <= 0);
        if ($wasSoldOutOrBooked) {
            $validated['combo_category_id'] = $product->combo_category_id;
        }

        if ($validated['is_out_of_stock'] && $validated['booking_type'] !== 'business_whatsapp' && empty($validated['booked_by'])) {
            return back()->withErrors(['booked_by' => 'Booked By is mandatory when marking a product as Booked.'])->withInput();
        }
        $isLegacyBookingWithoutDetails = $product->is_out_of_stock
            && empty($product->booking_type)
            && ! empty($product->booked_by)
            && empty($request->input('booking_type'));
        if ($validated['is_out_of_stock'] && empty($validated['booking_type']) && ! $isLegacyBookingWithoutDetails) {
            return back()->withErrors(['booking_type' => 'Select a booking type.'])->withInput();
        }
        if ($validated['is_out_of_stock'] && empty($validated['booking_date']) && ! $isLegacyBookingWithoutDetails) {
            return back()->withErrors(['booking_date' => 'Select the booked date.'])->withInput();
        }
        $validated['discount_value'] = $validated['discount_value'] ?? 0.00;
        $validated['delivery_charge_type'] = $validated['delivery_charge_type'] ?? 'exclude';
        $validated['weight_kg'] = $validated['weight_kg'] ?? 0.30;

        $newComboCatId = $validated['combo_category_id'] ?? null;
        if (! empty($newComboCatId)) {
            $offerCategory = Category::find($newComboCatId);
            if ($offerCategory && $offerCategory->offer_type === 'discount' && $offerCategory->discount_value > 0) {
                $validated['discount_type'] = $offerCategory->discount_type ?? 'percentage';
                $validated['discount_value'] = $offerCategory->discount_value;
                $validated['final_price'] = Product::calculateFinalPrice(
                    $validated['price'],
                    $validated['discount_type'],
                    $validated['discount_value']
                );
            }
            if ($offerCategory && ! in_array((int) $offerCategory->id, array_map('intval', $categoryIds), true)) {
                $categoryIds[] = (int) $offerCategory->id;
            }
        } else {
            // If combo_category_id was set to null and product was previously in a discount offer category (and not sold out/booked)
            if (! $wasSoldOutOrBooked && $product->combo_category_id) {
                $oldOfferCat = Category::find($product->combo_category_id);
                if ($oldOfferCat && $oldOfferCat->offer_type === 'discount') {
                    $validated['discount_type'] = 'none';
                    $validated['discount_value'] = 0;
                    $validated['final_price'] = $validated['price'];
                }
            }
        }
        $validated['collection_visible'] = ! Category::whereIn('id', $categoryIds)
            ->where('show_in_collection', false)
            ->exists();

        DB::transaction(function () use ($validated, $request, $product, $categoryIds) {
            $product->update($validated);
            $product->categories()->sync($categoryIds);

            $reason = $request->get('stock_adjustment_reason') ?? 'Admin Product Edit Adjustment';

            // Update existing size names, stock & measurements
            // Read the validated form field directly so an intentionally blank label
            // remains distinguishable from an omitted existing-size row.
            $existingSizes = $request->input('existing_sizes', []);
            $clearSizeLabelIds = array_map(
                'intval',
                array_keys(array_filter($request->input('clear_size_labels', []), fn ($value) => (string) $value === '1'))
            );
            if ($clearSizeLabelIds !== []) {
                ProductSize::where('product_id', $product->id)
                    ->whereIn('id', $clearSizeLabelIds)
                    ->update(['size' => '']);
            }
            $existingStocks = $validated['existing_stocks'] ?? [];
            $existingChests = $request->input('existing_chests', []);
            $existingWaists = $request->input('existing_waists', []);
            $existingHips = $request->input('existing_hips', []);
            $existingLengths = $request->input('existing_lengths', []);

            $requestedSizeIds = array_unique(array_merge(array_keys($existingSizes), array_keys($existingStocks), array_keys($existingChests), $clearSizeLabelIds));

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

                if (in_array((int) $sizeId, $clearSizeLabelIds, true)) {
                    $updateData['size'] = '';
                } elseif (array_key_exists($sizeId, $existingSizes)) {
                    $newSizeName = trim((string) ($existingSizes[$sizeId] ?? ''));
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

                if (array_key_exists($sizeId, $existingHips)) {
                    $hVal = !empty($existingHips[$sizeId]) ? trim($existingHips[$sizeId]) : null;
                    if ($pSize->hip !== $hVal) $updateData['hip'] = $hVal;
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
            $newHips = $request->input('new_hips', []);
            $newLengths = $request->input('new_lengths', []);

            foreach ($newSizes as $i => $nSize) {
                $nStock = max(0, (int) ($newStocks[$i] ?? 0));
                $pSize = ProductSize::create([
                    'product_id' => $product->id,
                    'size' => trim($nSize ?? ''),
                    'stock' => $nStock,
                    'chest' => !empty($newChests[$i]) ? trim($newChests[$i]) : null,
                    'waist' => !empty($newWaists[$i]) ? trim($newWaists[$i]) : null,
                    'hip' => !empty($newHips[$i]) ? trim($newHips[$i]) : null,
                    'length' => !empty($newLengths[$i]) ? trim($newLengths[$i]) : null,
                ]);
                if ($nStock > 0) {
                    $this->stockService->adjustStock($pSize->id, $nStock, 'New Size Stock', auth()->user()->name);
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

    public function clearSizeLabel(Product $product, ProductSize $size)
    {
        abort_unless((int) $size->product_id === (int) $product->id, 404);

        ProductSize::where('product_id', $product->id)
            ->whereKey($size->id)
            ->update(['size' => '']);
        session()->forget('_old_input.existing_sizes.' . $size->id);
        session()->forget('_old_input.clear_size_labels.' . $size->id);

        return response()->json(['success' => true, 'size_id' => $size->id, 'label' => '']);
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

    public function bookedConflicts(Request $request)
    {
        $search = trim($request->input('search', ''));
        
        // Show products that have CONFIRMED/PAID sales AND (have booked_by set OR stock > 0 while flagged out of stock)
        $query = Product::with(['primaryImage', 'categories', 'sizes'])
            ->where(function ($q) {
                $q->whereNotNull('booked_by')
                  ->orWhere(function ($subQ) {
                      $subQ->where('is_out_of_stock', true)
                           ->whereHas('sizes', function ($sQ) {
                               $sQ->where('stock', '>', 0);
                           });
                  });
            })
            ->whereHas('orderItems', function ($itemQ) {
                $itemQ->whereNotIn('item_status', ['returned', 'cancelled'])
                      ->whereHas('order', function ($orderQ) {
                          $orderQ->whereNotIn('order_status', ['cancelled'])
                                 ->where(function ($q) {
                                     $q->where('payment_status', 'paid')
                                       ->orWhereIn('order_status', ['processing', 'completed', 'delivered']);
                                 });
                      });
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%")
                  ->orWhere('booked_by', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        return view('admin.products.booked_conflicts', compact('products', 'search'));
    }

    public function resolveConflict(Request $request, Product $product)
    {
        $request->validate([
            'resolution' => 'required|in:already_sold,not_sold_back_to_stock,keep_booked',
            'booked_by' => 'nullable|string|max:255',
        ]);

        $resolution = $request->input('resolution');

        if ($resolution === 'already_sold') {
            // Option 1: Already sold -> Set is_out_of_stock = false, clear booked_by, and set physical stock = 0
            $product->update([
                'is_out_of_stock' => false,
                'booked_by' => null,
                'booking_type' => null,
                'booking_date' => null,
                'booked_by_admin_id' => null,
                'booked_at' => null,
            ]);

            // Ensure physical size stock is zeroed out for sold item
            $product->sizes()->update(['stock' => 0]);

            return back()->with('success', "Product #{$product->id} '{$product->name}' marked as Sold Out & Booked status removed successfully.");
        } elseif ($resolution === 'not_sold_back_to_stock') {
            // Option 2: Not sold -> Put back to Available Stock (is_out_of_stock = false) & Clear booked_by
            $product->update([
                'is_out_of_stock' => false,
                'booked_by' => null,
                'booking_type' => null,
                'booking_date' => null,
                'booked_by_admin_id' => null,
                'booked_at' => null,
            ]);

            return back()->with('success', "Product #{$product->id} '{$product->name}' returned to Available Stock successfully.");
        } elseif ($resolution === 'keep_booked') {
            // Option 3: Keep in Booked status
            $bookedBy = trim($request->input('booked_by', '')) ?: ($product->booked_by ?: 'Booked Customer');

            $product->update([
                'is_out_of_stock' => true,
                'booked_by' => $bookedBy,
            ]);

            return back()->with('success', "Product #{$product->id} '{$product->name}' maintained and marked as Booked ({$bookedBy}).");
        }

        return back()->with('error', 'Invalid resolution selected.');
    }

    public function aiAutoFill(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        $geminiKeys = Setting::getGeminiKeysOrdered();

        if (empty($geminiKeys)) {
            $activeStoredKey = GeminiApiKey::where('is_active', true)->first();
            $message = $activeStoredKey && !empty($activeStoredKey->api_key)
                ? 'The active Google Gemini API key is saved but cannot be decrypted on this installation. Re-enter the key in Admin > Gemini API Keys and save it as active, then retry Auto Fill Product.'
                : 'Google Gemini API Key is not configured. Please enter your Gemini API Key under Master Settings (/admin/settings) or Gemini API Keys (/admin/gemini-keys).';
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        try {
            $filePath = $request->file('image')->getRealPath();
            $mimeType = mime_content_type($filePath) ?: 'image/jpeg';
            $fileData = file_get_contents($filePath);

            if ($fileData === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to read uploaded dress image.'
                ], 400);
            }

            // Downscale image in memory to max 800px for super-fast base64 upload & instant Gemini Vision processing
            if (function_exists('imagecreatefromstring')) {
                $srcImg = @imagecreatefromstring($fileData);
                if ($srcImg !== false) {
                    $width = imagesx($srcImg);
                    $height = imagesy($srcImg);
                    $maxDim = 800;

                    if ($width > $maxDim || $height > $maxDim) {
                        $ratio = min($maxDim / $width, $maxDim / $height);
                        $newW = max(1, (int) ($width * $ratio));
                        $newH = max(1, (int) ($height * $ratio));

                        $dstImg = imagecreatetruecolor($newW, $newH);
                        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $width, $height);
                        imagedestroy($srcImg);
                        $srcImg = $dstImg;
                    }

                    ob_start();
                    imagejpeg($srcImg, null, 82);
                    $compressedData = ob_get_clean();
                    imagedestroy($srcImg);

                    if (!empty($compressedData)) {
                        $fileData = $compressedData;
                        $mimeType = 'image/jpeg';
                    }
                }
            }

            $base64Data = base64_encode($fileData);

            $sizeMasterOptions = SizeMaster::whereHas('rows')->orderBy('sort_order')->get(['id', 'name']);

            $prompt = implode("\n", [
                'You are an expert e-commerce fashion copywriter for a ladies fashion shop ("Quara Wardrobe").',
                'Examine this uploaded dress image in detail.',
                'Always generate both a non-empty product name and a non-empty description from the clothing visible in this image. No price or measurement notes are needed.',
                'Describe observable details only. Do not invent fabric composition, brand, measurements, care instructions or other properties that cannot be confirmed from the image.',
                'Detect garment type (e.g. Abaya, Maxi Dress, Kurti, Salwar Set, Kaftan, Gown, Tops, Saree), color, visible texture, neckline, sleeve style, pattern (floral, printed, embroidered, solid), silhouette & embellishments.',
                'Return ONLY a valid JSON object strictly matching this format:',
                '{"name": "Short e-commerce title (2 TO 4 WORDS ONLY, e.g. Floral Maxi Dress)", "description": "An attractive 3-4 sentence paragraph highlighting the visible garment type, color, pattern, sleeves and silhouette. Do not use bullet points, list items, section headers or unsupported claims."}',
            ]);

            $prompt .= "\nAlso include a size_master_id field in the JSON object. Identify the garment directly from the image and select the most appropriate size master ID from this catalog: "
                . $sizeMasterOptions->toJson()
                . '. Category selection is required whenever a suitable catalog entry exists. Use the actual garment type, not just words in the short product title. Return an integer ID from this catalog, never an invented ID. Return null only when no category fits. Do not estimate body measurements or a size label from the photograph.';

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64Data,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 2048,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'name' => ['type' => 'STRING', 'description' => 'Non-empty product title, 2 to 4 words.'],
                            'description' => ['type' => 'STRING', 'description' => 'Non-empty description of the visible garment.'],
                            'size_master_id' => ['type' => 'INTEGER', 'nullable' => true],
                        ],
                        'required' => ['name', 'description', 'size_master_id'],
                    ],
                ],
            ];

            @set_time_limit(180);

            $modelsToTry = [
                // Confirmed to support the active key's image + JSON request.
                'gemini-3.5-flash-lite',
                'gemini-3-flash-preview',
                'gemini-3.6-flash',
                'gemini-3.5-flash',
                'gemini-3.1-flash-lite',
            ];

            $response = null;
            $status = 0;
            $connectionFailed = false;
            $decoded = null;
            $hadIncompleteResponse = false;
            $successfulModel = null;
            $loopStartTime = microtime(true);
            $lastStatus = 0;

            foreach ($geminiKeys as $currentKey) {
                $currentKey = trim((string) $currentKey);
                if (empty($currentKey)) continue;

                // Reuse the most recently successful model for this active key.
                // This avoids repeatedly trying models that Google has retired.
                $modelCacheKey = 'gemini_working_model_' . hash('sha256', $currentKey);
                $cachedModel = Cache::get($modelCacheKey);
                if (is_string($cachedModel) && in_array($cachedModel, $modelsToTry, true)) {
                    $modelsToTry = array_values(array_unique([$cachedModel, ...$modelsToTry]));
                }

                foreach ($modelsToTry as $model) {
                    // Vision responses can take longer than a text-only Gemini call.
                    if ((microtime(true) - $loopStartTime) > 120) {
                        break 2;
                    }

                    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

                    // Retry incomplete output once on the working model before trying alternatives.
                    for ($attempt = 0; $attempt < 2; $attempt++) {
                        $remaining = 120 - (microtime(true) - $loopStartTime);
                        if ($remaining < 1) break 3;
                        $attemptPayload = $payload;
                        if ($attempt > 0) $attemptPayload['generationConfig']['maxOutputTokens'] = 4096;
                        try {
                            $httpResponse = Http::withHeaders(['x-goog-api-key' => $currentKey])
                                ->connectTimeout(min(4, $remaining))
                                ->timeout(min(70, $remaining))
                                ->post($url, $attemptPayload);
                            $response = $httpResponse->body();
                            $status = $httpResponse->status();
                            $connectionFailed = false;
                        } catch (ConnectionException $e) {
                            $response = null;
                            $status = 0;
                            $connectionFailed = true;
                        }
                        $lastStatus = $status;

                        if (is_string($response) && $status >= 200 && $status < 300) {
                            $responseData = json_decode($response, true);
                            $decoded = $this->parseProductImageResponse($responseData);
                            if ($decoded !== null) {
                                $successfulModel = $model;
                                Cache::put($modelCacheKey, $model, now()->addDay());
                                break 3;
                            }
                            $hadIncompleteResponse = true;
                            Log::warning('AI image response missing complete product details', [
                                'model' => $model,
                                'attempt' => $attempt + 1,
                                'finish_reason' => $responseData['candidates'][0]['finishReason'] ?? null,
                            ]);
                            continue;
                        }

                        Log::warning('AI image request failed', ['model' => $model, 'status' => $status]);
                        break;
                    }

                    // If rate limited (429), break model loop for this key immediately and try next key
                    if ($status === 429) {
                        break;
                    }
                }
            }

            if (!$successfulModel || !is_string($response)) {
                Log::error('AI auto fill Gemini all models/keys failed', ['status' => $status, 'connection_failed' => $connectionFailed]);

                $errMsg = 'Unable to analyze image with Google Gemini AI. Please check your API key.';
                if ($lastStatus === 429) {
                    $errMsg = 'Google Gemini AI quota has been reached for the active key. Please wait for its quota to reset or select a key with available quota.';
                } elseif ($hadIncompleteResponse) {
                    $errMsg = 'The image service returned incomplete product details after retrying. Your other fields are filled; retry Auto Fill Product or enter the name manually.';
                } elseif ($lastStatus === 0) {
                    $errMsg = 'The image service could not be reached. Your other fields are filled; please retry Auto Fill Product.';
                } elseif ($lastStatus === 503) {
                    $errMsg = 'Google Gemini AI is temporarily busy. Please try again in a moment.';
                }

                return response()->json([
                    'success' => false,
                    'message' => $errMsg
                ], in_array($lastStatus, [401, 403, 429, 503], true) ? $lastStatus : 502);
            }

            $productName = trim($decoded['name']);
            $productName = Str::words($productName, 4, '');

            return response()->json([
                'success' => true,
                'name' => $productName,
                'description' => trim($decoded['description'] ?? ''),
                'size_master_id' => $sizeMasterOptions->firstWhere('id', $decoded['size_master_id'] ?? null)?->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('AI auto fill error', ['exception' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during AI analysis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Read the final answer across all text parts, excluding thought summaries.
     * An HTTP 200 alone does not mean generation finished or required fields exist.
     */
    private function parseProductImageResponse(mixed $response): ?array
    {
        if (!is_array($response) || !empty($response['promptFeedback']['blockReason'])) return null;
        $candidate = $response['candidates'][0] ?? null;
        if (!is_array($candidate) || (isset($candidate['finishReason']) && $candidate['finishReason'] !== 'STOP')) return null;
        $parts = $candidate['content']['parts'] ?? [];
        if (!is_array($parts)) return null;
        $text = '';
        foreach ($parts as $part) {
            if (is_array($part) && empty($part['thought']) && is_string($part['text'] ?? null)) {
                $text .= $part['text'];
            }
        }
        $text = trim($text);
        if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/i', $text, $matches)) {
            $text = $matches[1];
        }
        $decoded = json_decode($text, true);
        if (!is_array($decoded)) return null;
        foreach (['name', 'description'] as $field) {
            if (!is_string($decoded[$field] ?? null)) return null;
            $decoded[$field] = trim(preg_replace('/[\s\p{Z}\x{200B}\x{FEFF}]+/u', ' ', $decoded[$field]) ?? '');
            if ($decoded[$field] === '') return null;
        }
        return $decoded;
    }
}
