<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $manualOrders = $query->paginate(15)->withQueryString();

        return view('admin.manual_sales.index', compact('manualOrders'));
    }

    public function create()
    {
        $products = Product::where('status', 'active')->with(['sizes', 'images'])->get();
        return view('admin.manual_sales.create', compact('products'));
    }

    public function store(Request $request)
    {
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
            'product_size_id' => 'required|exists:product_sizes,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'delivery_charge' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,upi,bank_transfer',
            'payment_status' => 'required|in:paid,pending',
            'notes' => 'nullable|string',
        ]);

        $pSize = ProductSize::with('product')->findOrFail($validated['product_size_id']);
        $product = $pSize->product;

        if ($pSize->stock < $validated['quantity']) {
            return back()->withInput()->with('error', "Insufficient stock for {$product->name} (Size {$pSize->size}). Available: {$pSize->stock} pcs.");
        }

        $subtotal = $validated['unit_price'] * $validated['quantity'];
        $shipping = (float) ($validated['delivery_charge'] ?? 0.00);
        $grandTotal = $subtotal + $shipping;

        $orderNumber = 'QW-MAN-' . strtoupper(str_shuffle(substr(uniqid(), -5)));

        DB::transaction(function () use ($validated, $product, $pSize, $subtotal, $shipping, $grandTotal, $orderNumber) {
            // Create Manual Order
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
                'subtotal' => $subtotal,
                'discount' => 0.00,
                'shipping' => $shipping,
                'grand_total' => $grandTotal,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'order_status' => 'delivered',
                'order_source' => 'manual',
                'notes' => '[Manual Sale Entry] ' . ($validated['notes'] ?? ''),
            ]);

            // Add Order Item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_size_id' => $pSize->id,
                'product_name' => $product->name,
                'size' => $pSize->size,
                'price' => $validated['unit_price'],
                'quantity' => $validated['quantity'],
                'subtotal' => $subtotal,
            ]);

            // Deduct Stock
            $this->stockService->deductStock(
                $pSize->id,
                $validated['quantity'],
                'Manual Offline Sale (' . $orderNumber . ')',
                auth()->user()->name
            );
        });

        return redirect()->route('admin.manual-sales.index')->with('success', "Manual Sale #{$orderNumber} recorded successfully!");
    }
}
