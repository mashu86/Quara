<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSize;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'house_building' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pin_code' => 'nullable|string|max:10',
            
            'items' => 'required|array|min:1',
            'items.*.product_size_id' => 'required|exists:product_sizes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',

            'delivery_charge' => 'nullable|numeric|min:0',
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

        $shipping = (float) ($validated['delivery_charge'] ?? 0.00);
        $grandTotal = $calculatedSubtotal + $shipping;
        $orderNumber = 'QW-MAN-' . strtoupper(str_shuffle(substr(uniqid(), -5)));
        $saleDate = !empty($validated['sale_date']) ? \Carbon\Carbon::parse($validated['sale_date']) : now();

        DB::transaction(function () use ($validated, $orderItemsData, $affectedProducts, $calculatedSubtotal, $shipping, $grandTotal, $orderNumber, $saleDate) {
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
                'discount' => 0.00,
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

            // Unblock reserved/out_of_stock status if product was previously reserved and is now sold
            foreach ($affectedProducts as $prod) {
                if ($prod->is_out_of_stock) {
                    $prod->update([
                        'is_out_of_stock' => false,
                        'booked_by' => null,
                    ]);
                }
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
            'house_building' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pin_code' => 'nullable|string|max:10',

            'items' => 'required|array|min:1',
            'items.*.product_size_id' => 'required|exists:product_sizes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',

            'delivery_charge' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,upi,bank_transfer',
            'payment_status' => 'required|in:paid,pending',
            'sale_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $oldItems = $order->items;
        $saleDate = !empty($validated['sale_date']) ? \Carbon\Carbon::parse($validated['sale_date']) : ($order->sale_date ?? $order->created_at);

        try {
            DB::transaction(function () use ($order, $oldItems, $validated, $saleDate) {
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

                foreach ($affectedProducts as $prod) {
                    if ($prod->is_out_of_stock) {
                        $prod->update([
                        'is_out_of_stock' => false,
                        'booked_by' => null,
                    ]);
                    }
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
}
