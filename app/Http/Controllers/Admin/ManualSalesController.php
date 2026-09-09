<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Setting;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ManualSalesController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $query = Order::where('order_source', 'manual')->with('items')->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate(DB::raw('COALESCE(sale_date, created_at)'), '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate(DB::raw('COALESCE(sale_date, created_at)'), '<=', $request->to_date);
        }

        $manualOrders = $query->paginate(15)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $desktopHtml = view('admin.manual_sales.partials.desktop_rows', compact('manualOrders'))->render();
            $mobileHtml = view('admin.manual_sales.partials.mobile_cards', compact('manualOrders'))->render();

            return response()->json([
                'desktop_html' => $desktopHtml,
                'mobile_html' => $mobileHtml,
                'next_page_url' => $manualOrders->nextPageUrl(),
                'has_more' => $manualOrders->hasMorePages(),
                'total' => $manualOrders->total(),
            ]);
        }

        return view('admin.manual_sales.index', compact('manualOrders'));
    }

    public function create()
    {
        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();
        $products = Product::where('status', 'active')->inStockFirst()->orderBy('name', 'asc')->with(['category', 'categories', 'sizes', 'images'])->get();
        return view('admin.manual_sales.create', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        if (!$request->has('items') && $request->filled('product_size_id')) {
            $request->merge([
                'items' => [
                    [
                        'product_size_id' => $request->product_size_id,
                        'quantity' => $request->quantity,
                        'unit_price' => $request->unit_price,
                    ]
                ]
            ]);
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'house_building' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'area' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'pin_code' => 'required|string|max:10',
            
            'items' => 'required|array|min:1',
            'items.*.product_size_id' => 'required|exists:product_sizes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',

            'delivery_charge' => 'nullable|numeric|min:0',
            'discount_option' => 'nullable|string|in:none,set_total,calculate_discount',
            'desired_subtotal' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,upi,bank_transfer',
            'payment_status' => 'required|in:paid,pending',
            'sale_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $qtyPerSize = [];
        foreach ($validated['items'] as $item) {
            $szId = $item['product_size_id'];
            $qtyPerSize[$szId] = ($qtyPerSize[$szId] ?? 0) + (int) $item['quantity'];
        }

        foreach ($qtyPerSize as $szId => $reqQty) {
            $pSize = ProductSize::with('product')->findOrFail($szId);
            if ($pSize->stock < $reqQty) {
                return back()->withInput()->with('error', "Insufficient stock for {$pSize->product->name} (Size {$pSize->size}). Requested: {$reqQty} pcs, Available: {$pSize->stock} pcs.");
            }
        }

        $calculatedSubtotal = 0;
        $orderItemsData = [];
        $affectedProducts = [];
        foreach ($validated['items'] as $item) {
            $pSize = ProductSize::with('product')->findOrFail($item['product_size_id']);
            $product = $pSize->product;
            $unitPrice = (float) $item['unit_price'];
            $qty = (int) $item['quantity'];
            $itemSubtotal = $unitPrice * $qty;
            $calculatedSubtotal += $itemSubtotal;

            $orderItemsData[] = [
                'product' => $product,
                'productSize' => $pSize,
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'subtotal' => $itemSubtotal,
            ];
            $affectedProducts[$product->id] = $product;
        }

        $discountOption = $request->input('discount_option', 'none');
        $discountAmount = 0.00;

        if ($discountOption === 'set_total' && $request->filled('desired_subtotal')) {
            $desiredSubtotal = (float) $request->input('desired_subtotal');
            $discountAmount = max(0, $calculatedSubtotal - $desiredSubtotal);
        } elseif ($discountOption === 'calculate_discount' && $request->filled('discount_value')) {
            $discVal = (float) $request->input('discount_value');
            $discType = $request->input('discount_type', 'fixed');
            if ($discType === 'percentage') {
                $discountAmount = round($calculatedSubtotal * ($discVal / 100), 2);
            } else {
                $discountAmount = $discVal;
            }
            $discountAmount = min($calculatedSubtotal, max(0, $discountAmount));
        } elseif ($request->filled('discount')) {
            $discountAmount = max(0, (float) $request->input('discount'));
        }

        $shipping = (float) ($validated['delivery_charge'] ?? 0.00);
        $grandTotal = max(0, round($calculatedSubtotal - $discountAmount + $shipping, 2));
        $orderNumber = 'QW-MAN-' . strtoupper(str_shuffle(substr(uniqid(), -5)));

        $nowInIst = \Carbon\Carbon::now('Asia/Kolkata');
        if (!empty($validated['sale_date'])) {
            $parsedDate = \Carbon\Carbon::parse($validated['sale_date'], 'Asia/Kolkata');
            $saleDate = $parsedDate->setTime($nowInIst->hour, $nowInIst->minute, $nowInIst->second);
        } else {
            $saleDate = $nowInIst;
        }

        DB::transaction(function () use ($validated, $orderItemsData, $affectedProducts, $calculatedSubtotal, $discountAmount, $shipping, $grandTotal, $orderNumber, $saleDate) {
            $order = Order::create([
                'user_id' => null,
                'order_number' => $orderNumber,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'house_building' => $validated['house_building'] ?? 'Offline Store',
                'street' => $validated['street'] ?? 'Direct Purchase',
                'area' => $validated['area'] ?? 'Counter Sale',
                'city' => $validated['city'] ?? 'Naduvil',
                'district' => $validated['district'] ?? 'Kannur',
                'state' => $validated['state'] ?? 'Kerala',
                'pin_code' => $validated['pin_code'] ?? '670582',
                'subtotal' => $calculatedSubtotal,
                'discount' => $discountAmount,
                'shipping' => $shipping,
                'grand_total' => $grandTotal,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'order_status' => 'delivered',
                'order_source' => 'manual',
                'sale_date' => $saleDate,
                'notes' => '[Manual Sale Entry] ' . ($validated['notes'] ?? ''),
            ]);

            foreach ($orderItemsData as $it) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $it['product']->id,
                    'product_size_id' => $it['productSize']->id,
                    'product_name' => $it['product']->name,
                    'size' => $it['productSize']->size,
                    'unit_price' => $it['unit_price'],
                    'discount_amount' => 0.00,
                    'final_unit_price' => $it['unit_price'],
                    'quantity' => $it['quantity'],
                    'subtotal' => $it['subtotal'],
                ]);

                $this->stockService->deductStock(
                    $it['productSize']->id,
                    $it['quantity'],
                    'Manual Offline Sale (' . $orderNumber . ')',
                    auth()->user()->name
                );
            }

            // Unblock reserved/out_of_stock status for all products involved in this sale
            $productIdsToUnbook = array_keys($affectedProducts);
            if (!empty($productIdsToUnbook)) {
                \App\Models\Product::whereIn('id', $productIdsToUnbook)->update([
                    'is_out_of_stock' => false,
                    'booked_by' => null,
                ]);
            }
        });

        return redirect()->route('admin.manual-sales.index')->with('success', "Manual Sale #{$orderNumber} recorded successfully!");
    }

    public function edit(Order $order)
    {
        if ($order->order_source !== 'manual') {
            return redirect()->route('admin.manual-sales.index')->with('error', 'Only manual offline sales can be edited here.');
        }

        $order->load(['items.product', 'items.productSize']);
        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();
        $products = Product::where('status', 'active')->inStockFirst()->orderBy('name', 'asc')->with(['category', 'categories', 'sizes', 'images'])->get();

        return view('admin.manual_sales.edit', compact('order', 'products', 'categories'));
    }

    public function update(Request $request, Order $order)
    {
        if ($order->order_source !== 'manual') {
            return redirect()->route('admin.manual-sales.index')->with('error', 'Only manual offline sales can be edited here.');
        }

        if (!$request->has('items') && $request->filled('product_size_id')) {
            $request->merge([
                'items' => [
                    [
                        'product_size_id' => $request->product_size_id,
                        'quantity' => $request->quantity,
                        'unit_price' => $request->unit_price,
                    ]
                ]
            ]);
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'house_building' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'area' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'pin_code' => 'required|string|max:10',

            'items' => 'required|array|min:1',
            'items.*.product_size_id' => 'required|exists:product_sizes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',

            'delivery_charge' => 'nullable|numeric|min:0',
            'discount_option' => 'nullable|string|in:none,set_total,calculate_discount',
            'desired_subtotal' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,upi,bank_transfer',
            'payment_status' => 'required|in:paid,pending',
            'sale_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $oldItems = $order->items;
        
        $nowInIst = \Carbon\Carbon::now('Asia/Kolkata');
        if (!empty($validated['sale_date'])) {
            $parsedDate = \Carbon\Carbon::parse($validated['sale_date'], 'Asia/Kolkata');
            $saleDate = $parsedDate->setTime($nowInIst->hour, $nowInIst->minute, $nowInIst->second);
        } else {
            $saleDate = $order->sale_date ?? $nowInIst;
        }

        try {
            DB::transaction(function () use ($request, $order, $oldItems, $validated, $saleDate) {
                foreach ($oldItems as $oldItem) {
                    if ($oldItem->product_size_id) {
                        $pSize = ProductSize::find($oldItem->product_size_id);
                        if ($pSize) {
                            $this->stockService->adjustStock(
                                $pSize->id,
                                $pSize->stock + $oldItem->quantity,
                                "Manual Sale Edit Restore (#{$order->order_number})",
                                auth()->user()->name
                            );
                        }
                    }
                }

                $qtyPerSize = [];
                foreach ($validated['items'] as $item) {
                    $szId = $item['product_size_id'];
                    $qtyPerSize[$szId] = ($qtyPerSize[$szId] ?? 0) + (int) $item['quantity'];
                }

                foreach ($qtyPerSize as $szId => $reqQty) {
                    $pSize = ProductSize::with('product')->findOrFail($szId);
                    if ($pSize->stock < $reqQty) {
                        throw new \Exception("Insufficient stock for {$pSize->product->name} (Size {$pSize->size}). Required: {$reqQty} pcs, Available: {$pSize->stock} pcs.");
                    }
                }

                $order->items()->delete();

                $calculatedSubtotal = 0;
                $affectedProducts = [];
                foreach ($validated['items'] as $item) {
                    $pSize = ProductSize::with('product')->findOrFail($item['product_size_id']);
                    $product = $pSize->product;
                    $unitPrice = (float) $item['unit_price'];
                    $qty = (int) $item['quantity'];
                    $itemSubtotal = $unitPrice * $qty;
                    $calculatedSubtotal += $itemSubtotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_size_id' => $pSize->id,
                        'product_name' => $product->name,
                        'size' => $pSize->size,
                        'unit_price' => $unitPrice,
                        'discount_amount' => 0.00,
                        'final_unit_price' => $unitPrice,
                        'quantity' => $qty,
                        'subtotal' => $itemSubtotal,
                    ]);

                    $this->stockService->deductStock(
                        $pSize->id,
                        $qty,
                        "Manual Sale Edit (#{$order->order_number})",
                        auth()->user()->name
                    );

                    $affectedProducts[$product->id] = $product;
                }

                $productIdsToUnbook = array_keys($affectedProducts);
                if (!empty($productIdsToUnbook)) {
                    \App\Models\Product::whereIn('id', $productIdsToUnbook)->update([
                        'is_out_of_stock' => false,
                        'booked_by' => null,
                    ]);
                }

                $discountOption = $request->input('discount_option', 'none');
                $discountAmount = 0.00;

                if ($discountOption === 'set_total' && $request->filled('desired_subtotal')) {
                    $desiredSubtotal = (float) $request->input('desired_subtotal');
                    $discountAmount = max(0, $calculatedSubtotal - $desiredSubtotal);
                } elseif ($discountOption === 'calculate_discount' && $request->filled('discount_value')) {
                    $discVal = (float) $request->input('discount_value');
                    $discType = $request->input('discount_type', 'fixed');
                    if ($discType === 'percentage') {
                        $discountAmount = round($calculatedSubtotal * ($discVal / 100), 2);
                    } else {
                        $discountAmount = $discVal;
                    }
                    $discountAmount = min($calculatedSubtotal, max(0, $discountAmount));
                } elseif ($request->filled('discount')) {
                    $discountAmount = max(0, (float) $request->input('discount'));
                }

                $shipping = (float) ($validated['delivery_charge'] ?? 0.00);

                $order->update([
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_email' => $validated['customer_email'] ?? null,
                    'house_building' => $validated['house_building'] ?? 'Offline Store',
                    'street' => $validated['street'] ?? 'Direct Purchase',
                    'area' => $validated['area'] ?? 'Counter Sale',
                    'city' => $validated['city'] ?? 'Naduvil',
                    'district' => $validated['district'] ?? 'Kannur',
                    'state' => $validated['state'] ?? 'Kerala',
                    'pin_code' => $validated['pin_code'] ?? '670582',
                    'subtotal' => $calculatedSubtotal,
                    'discount' => $discountAmount,
                    'shipping' => $shipping,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $validated['payment_status'],
                    'sale_date' => $saleDate,
                    'notes' => $validated['notes'] ?? $order->notes,
                ]);

                $order->recalculateTotals($shipping);
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.manual-sales.index')->with('success', "Manual Sale #{$order->order_number} updated successfully!");
    }

    public function parseAddress(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:12288',
        ]);

        $rawKey = Setting::get('gemini_api_key');
        $geminiKey = is_string($rawKey) ? Setting::decryptSecret($rawKey) : null;
        if (empty($geminiKey)) {
            $geminiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        }
        $geminiKey = trim((string) $geminiKey);

        if (empty($geminiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Google Gemini API Key is not configured. Please enter your Gemini API Key under Master Settings (/admin/settings) and click Save.'
            ], 422);
        }

        try {
            $filePath = $request->file('image')->getRealPath();
            $mimeType = mime_content_type($filePath) ?: 'image/jpeg';
            $fileData = file_get_contents($filePath);

            if ($fileData === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to read uploaded address screenshot.'
                ], 400);
            }

            // Downscale image in memory to max 1000px for super-fast base64 upload & sharp OCR
            if (function_exists('imagecreatefromstring')) {
                $srcImg = @imagecreatefromstring($fileData);
                if ($srcImg !== false) {
                    $width = imagesx($srcImg);
                    $height = imagesy($srcImg);
                    $maxDim = 1000;

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
                    imagejpeg($srcImg, null, 85);
                    $compressedData = ob_get_clean();
                    imagedestroy($srcImg);

                    if (!empty($compressedData)) {
                        $fileData = $compressedData;
                        $mimeType = 'image/jpeg';
                    }
                }
            }

            $base64Data = base64_encode($fileData);

            $prompt = implode("\n", [
                'You are an expert OCR and delivery address parsing AI system.',
                'Analyze this uploaded screenshot or photo (which could be a WhatsApp chat screenshot, Instagram message, order label, paper receipt, or typed address text image) to extract customer delivery address & contact details.',
                'Parse and clean all details carefully:',
                '- customer_name: Full name of the customer (person receiving the order). Do NOT include titles like Mr/Mrs unless part of name.',
                '- customer_phone: Mobile/contact phone number with 10 digits (or standard Indian phone number). Remove spaces, dashes, or +91 prefix if clean 10-digit number can be extracted.',
                '- customer_email: Email address if present in text, else empty string.',
                '- house_building: House name, villa name, building/apartment name, flat number, door number, or building details.',
                '- street: Street name, road name, area name, landmark, or locality (e.g. Near Bus Stand, Main Road).',
                '- city: City, town, post office name, or village.',
                '- district: District (e.g. Kannur, Kozhikode, Ernakulam, Wayanad, Thiruvananthapuram, Palakkad, Thrissur, Malappuram, Kasaragod, Kottayam, Alappuzha, Pathanamthitta, Idukki, Kollam, etc.).',
                '- state: Indian State (e.g. Kerala, Tamil Nadu, Karnataka, etc. Default to "Kerala" if inside Kerala context or district/Pincode).',
                '- pin_code: 6-digit Indian PIN Code.',
                '- raw_text: Concise summary of raw address text extracted from image.',
                'Return ONLY a valid JSON object strictly matching this format:',
                '{"customer_name": "", "customer_phone": "", "customer_email": "", "house_building": "", "street": "", "city": "", "district": "", "state": "Kerala", "pin_code": "", "raw_text": ""}'
            ]);

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
                    'temperature' => 0.1,
                    'maxOutputTokens' => 800,
                    'responseMimeType' => 'application/json',
                ],
            ];

            $modelsToTry = [
                'gemini-1.5-flash',
                'gemini-2.0-flash',
                'gemini-1.5-pro',
                'gemini-2.0-flash-lite',
                'gemini-3.5-flash-lite',
                'gemini-3.7-flash',
                'gemini-3.6-flash',
                'gemini-flash-latest',
            ];

            $cachedModel = Cache::get('gemini_working_model_' . md5($geminiKey));
            if ($cachedModel && in_array($cachedModel, $modelsToTry, true)) {
                $modelsToTry = array_unique(array_merge([$cachedModel], $modelsToTry));
            }

            $response = null;
            $status = 0;
            $curlError = '';
            $successfulModel = null;

            foreach ($modelsToTry as $model) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($geminiKey);

                $curl = curl_init($url);
                curl_setopt_array($curl, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_CONNECTTIMEOUT => 4,
                    CURLOPT_TIMEOUT => 14,
                ]);

                $response = curl_exec($curl);
                $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $curlError = curl_error($curl);
                curl_close($curl);

                if (is_string($response) && $status >= 200 && $status < 300) {
                    $successfulModel = $model;
                    Cache::put('gemini_working_model_' . md5($geminiKey), $model, 86400);
                    break;
                }

                Log::warning("Gemini AI address parse model {$model} failed with HTTP {$status}", ['response' => mb_substr((string)$response, 0, 200)]);
            }

            if (!$successfulModel || !is_string($response)) {
                Log::error('AI address parse Gemini models failed', ['status' => $status, 'error' => $curlError, 'response' => mb_substr((string)$response, 0, 300)]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to analyze image with AI. Please check your Gemini API key under Master Settings.'
                ], 500);
            }

            $responseData = json_decode($response, true);
            $rawText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!is_string($rawText)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid response format received from AI Vision service.'
                ], 500);
            }

            $cleanJson = $rawText;
            if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $rawText, $matches)) {
                $cleanJson = $matches[1];
            } else {
                $cleanJson = trim($cleanJson, "` \t\n\r\0\x0B");
            }

            $decoded = json_decode($cleanJson, true);

            if (!is_array($decoded)) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI could not find readable address details in this screenshot. Please try a clearer screenshot.'
                ], 422);
            }

            // Clean phone number if present
            if (!empty($decoded['customer_phone'])) {
                $phoneClean = preg_replace('/[^\d]/', '', (string)$decoded['customer_phone']);
                if (strlen($phoneClean) > 10 && str_starts_with($phoneClean, '91')) {
                    $phoneClean = substr($phoneClean, 2);
                }
                $decoded['customer_phone'] = $phoneClean;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'customer_name' => trim($decoded['customer_name'] ?? ''),
                    'customer_phone' => trim($decoded['customer_phone'] ?? ''),
                    'customer_email' => trim($decoded['customer_email'] ?? ''),
                    'house_building' => trim($decoded['house_building'] ?? ''),
                    'street' => trim($decoded['street'] ?? ''),
                    'city' => trim($decoded['city'] ?? ''),
                    'district' => trim($decoded['district'] ?? ''),
                    'state' => trim($decoded['state'] ?? 'Kerala'),
                    'pin_code' => trim($decoded['pin_code'] ?? ''),
                    'raw_text' => trim($decoded['raw_text'] ?? ''),
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('AI address parse error', ['exception' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during AI analysis: ' . $e->getMessage()
            ], 500);
        }
    }
}
