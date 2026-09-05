<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderOperation;
use App\Models\OrderOperationExpense;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderOperationController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $hasOperationFilter = $request->get('has_operation'); // all, with_ops, without_ops
        $operationStatusFilter = $request->get('operation_status'); // active, inactive
        $operationTypeFilter = $request->get('operation_type');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        // Query eligible orders: Paid, completed, delivered, or offline sales
        $query = Order::with(['items.product.images', 'operations.expenses'])
            ->orderBy('id', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                  ->orWhereHas('items', function ($itemQ) use ($search) {
                      $itemQ->where('product_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($hasOperationFilter === 'with_ops') {
            $query->has('operations');
        } elseif ($hasOperationFilter === 'without_ops') {
            $query->doesntHave('operations');
        }

        if (!empty($operationStatusFilter)) {
            $query->whereHas('operations', function ($opQ) use ($operationStatusFilter) {
                $opQ->where('status', $operationStatusFilter);
            });
        }

        if (!empty($operationTypeFilter)) {
            $query->whereHas('operations', function ($opQ) use ($operationTypeFilter) {
                $opQ->where('operation_type', $operationTypeFilter);
            });
        }

        if (!empty($fromDate)) {
            $query->whereDate(DB::raw('COALESCE(sale_date, created_at)'), '>=', $fromDate);
        }

        if (!empty($toDate)) {
            $query->whereDate(DB::raw('COALESCE(sale_date, created_at)'), '<=', $toDate);
        }

        $orders = $query->paginate(15)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $desktopHtml = view('admin.order_operations.partials.desktop_rows', compact('orders'))->render();
            $mobileHtml = view('admin.order_operations.partials.mobile_cards', compact('orders'))->render();

            return response()->json([
                'desktop_html' => $desktopHtml,
                'mobile_html' => $mobileHtml,
                'next_page_url' => $orders->nextPageUrl(),
                'has_more' => $orders->hasMorePages(),
                'total' => $orders->total(),
            ]);
        }

        return view('admin.order_operations.index', compact(
            'orders',
            'search',
            'hasOperationFilter',
            'operationStatusFilter',
            'operationTypeFilter',
            'fromDate',
            'toDate'
        ));
    }

    public function create(Order $order)
    {
        $order->load(['items.product.images', 'operations.expenses', 'operations.replacementProduct', 'operations.replacementProductSize', 'operations.product']);
        $categories = \App\Models\Category::where('status', 'active')->orderBy('name', 'asc')->get();
        $allProducts = Product::where('status', 'active')
            ->orderBy('name', 'asc')
            ->with(['category', 'categories', 'sizes', 'images'])
            ->get();

        $operationTypes = [
            'order_cancelled' => 'Order / Item Cancelled',
            'product_returned' => 'Product Returned',
        ];

        return view('admin.order_operations.create', compact('order', 'allProducts', 'categories', 'operationTypes'));
    }

    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'operation_type' => 'nullable|string|in:order_cancelled,product_returned,product_exchange',
            'inventory_condition' => 'required|in:return_to_stock,do_not_restock',
            'return_date' => 'nullable|date',
            'refund_option' => 'required|in:no_refund,refund',
            'refund_amount' => 'nullable|numeric|min:0',
            'refund_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $order, $request) {
            $orderItem = OrderItem::findOrFail($validated['order_item_id']);
            
            // Enforce single adjustment rule: prevent re-adjusting an item that is already returned/exchanged/adjusted
            $hasActiveOp = OrderOperation::where('order_id', $order->id)
                ->where('order_item_id', $orderItem->id)
                ->where('status', 'active')
                ->exists();

            if ($orderItem->item_status === 'returned' || $orderItem->item_status === 'exchanged' || $orderItem->item_status === 'cancelled' || $hasActiveOp) {
                throw new Exception("This product ({$orderItem->product_name}) has already been adjusted/returned and cannot be adjusted again.");
            }

            $productId = $orderItem->product_id;
            $qtyToAdjust = (int) $orderItem->quantity;

            $actionType = $validated['operation_type'] ?? 'product_returned';
            $invCondition = $validated['inventory_condition'];
            $returnDate = !empty($validated['return_date']) ? \Carbon\Carbon::parse($validated['return_date'])->toDateString() : now()->toDateString();
            
            $refundOption = $validated['refund_option'];
            $refundAmount = ($refundOption === 'refund') ? (float) ($validated['refund_amount'] ?? 0) : 0.00;
            $refundDate = ($refundAmount > 0 && !empty($validated['refund_date'])) 
                ? \Carbon\Carbon::parse($validated['refund_date'])->toDateString() 
                : ($refundAmount > 0 ? now()->toDateString() : null);

            // 1. Update OrderItem Status & Fields
            $newItemStatus = 'returned';

            $orderItem->update([
                'item_status' => $newItemStatus,
                'inventory_condition' => $invCondition,
                'return_date' => $returnDate,
                'refund_date' => $refundDate,
                'refund_amount' => $refundAmount,
            ]);

            // 2. Handle Inventory Condition for Original Product
            $pSize = ProductSize::find($orderItem->product_size_id);
            if ($invCondition === 'return_to_stock' && $pSize) {
                $prevStock = $pSize->stock;
                $newStock = $prevStock + $qtyToAdjust;
                $pSize->update(['stock' => $newStock]);

                StockMovement::create([
                    'product_id' => $pSize->product_id,
                    'product_size_id' => $pSize->id,
                    'size' => $pSize->size,
                    'previous_stock' => $prevStock,
                    'new_stock' => $newStock,
                    'difference' => $qtyToAdjust,
                    'reason' => "Order Adjustment #{$order->order_number} Item Return ({$orderItem->product_name})",
                    'admin_name' => auth()->check() ? auth()->user()->name : 'Admin',
                ]);
            } else if ($invCondition === 'do_not_restock' && $pSize) {
                StockMovement::create([
                    'product_id' => $pSize->product_id,
                    'product_size_id' => $pSize->id,
                    'size' => $pSize->size,
                    'previous_stock' => $pSize->stock,
                    'new_stock' => $pSize->stock,
                    'difference' => 0,
                    'reason' => "Order Adjustment #{$order->order_number} Item Frozen/Not Restocked ({$orderItem->product_name})",
                    'admin_name' => auth()->check() ? auth()->user()->name : 'Admin',
                ]);
            }

            // 3. Create OrderOperation Record
            $opData = [
                'order_id' => $order->id,
                'product_id' => $orderItem->product_id,
                'order_item_id' => $orderItem->id,
                'operation_type' => 'product_returned',
                'status' => 'active',
                'quantity' => $qtyToAdjust,
                'is_product_restored' => ($invCondition === 'return_to_stock'),
                'inventory_condition' => $invCondition,
                'return_date' => $returnDate,
                'refund_date' => $refundDate,
                'is_money_refunded' => ($refundAmount > 0),
                'product_refund_amount' => $refundAmount,
                'delivery_refund_amount' => 0.00,
                'other_refund_amount' => 0.00,
                'total_refund_amount' => $refundAmount,
                'additional_expense_total' => 0.00,
                'total_financial_adjustment' => $refundAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->check() ? auth()->user()->name : 'Admin',
                'updated_by' => auth()->check() ? auth()->user()->name : 'Admin',
            ];

            $operation = OrderOperation::create($opData);

            // 4. Create Immutable OrderRefund record if refund amount > 0
            if ($refundAmount > 0 && $refundDate) {
                \App\Models\OrderRefund::create([
                    'order_id' => $order->id,
                    'order_operation_id' => $operation->id,
                    'order_item_id' => $orderItem->id,
                    'refund_amount' => $refundAmount,
                    'refund_date' => $refundDate,
                    'notes' => $validated['notes'] ?? 'Product Return Refund',
                    'created_by' => auth()->check() ? auth()->user()->name : 'Admin',
                ]);
            }

            // 5. Recalculate Order Subtotal & Grand Total
            $orderSubtotal = (float) OrderItem::where('order_id', $order->id)->sum('subtotal');
            $order->update(['subtotal' => $orderSubtotal]);
            $order->recalculateTotals();
        });

        return redirect()->route('admin.order-operations.create', $order->id)
            ->with('success', 'Product return processed successfully!');
    }

    /**
     * Record a refund given at a later date for an order or returned item.
     */
    public function addOrderRefund(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_item_id' => 'nullable|exists:order_items,id',
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_date' => 'required|date',
            'payment_method' => 'nullable|string',
            'transaction_reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $order) {
            $refundDate = \Carbon\Carbon::parse($validated['refund_date'])->toDateString();
            $refundAmount = (float) $validated['refund_amount'];

            $orderItem = !empty($validated['order_item_id']) 
                ? OrderItem::find($validated['order_item_id']) 
                : null;

            $operation = null;
            if ($orderItem) {
                $operation = OrderOperation::where('order_id', $order->id)
                    ->where('order_item_id', $orderItem->id)
                    ->where('status', 'active')
                    ->first();
            }

            if (!$operation) {
                $operation = OrderOperation::where('order_id', $order->id)
                    ->where('status', 'active')
                    ->latest()
                    ->first();
            }

            // Create Immutable OrderRefund record
            \App\Models\OrderRefund::create([
                'order_id' => $order->id,
                'order_operation_id' => $operation?->id,
                'order_item_id' => $orderItem?->id ?: $operation?->order_item_id,
                'refund_amount' => $refundAmount,
                'refund_date' => $refundDate,
                'payment_method' => $validated['payment_method'] ?? 'manual',
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'notes' => $validated['notes'] ?? 'Customer Refund',
                'created_by' => auth()->check() ? auth()->user()->name : 'Admin',
            ]);

            if ($orderItem) {
                $newCumulativeRefund = (float) $orderItem->refund_amount + $refundAmount;
                $orderItem->update([
                    'refund_amount' => $newCumulativeRefund,
                    'refund_date' => $refundDate,
                ]);
            }

            if ($operation) {
                $newOpRefund = (float) $operation->total_refund_amount + $refundAmount;
                $operation->update([
                    'is_money_refunded' => true,
                    'refund_date' => $refundDate,
                    'product_refund_amount' => $newOpRefund,
                    'total_refund_amount' => $newOpRefund,
                    'total_financial_adjustment' => $newOpRefund + (float) $operation->additional_expense_total,
                ]);
            }
        });

        return redirect()->back()
            ->with('success', 'Refund of ₹' . number_format($validated['refund_amount'], 2) . ' recorded successfully!');
    }

    public function addOrderItem(Request $request, Order $order)
    {
        $validated = $request->validate([
            'add_product_size_id' => 'required|exists:product_sizes,id',
            'add_quantity' => 'required|integer|min:1',
            'add_unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $order) {
            $pSize = ProductSize::with('product')->findOrFail($validated['add_product_size_id']);
            $qty = (int) $validated['add_quantity'];
            $unitPrice = (float) $validated['add_unit_price'];
            $subtotal = $unitPrice * $qty;

            if ($pSize->stock < $qty) {
                throw new Exception("Insufficient stock for {$pSize->product->name} (Size {$pSize->size}). Available: {$pSize->stock}.");
            }

            // Deduct stock
            $prevStock = $pSize->stock;
            $newStock = $prevStock - $qty;
            $pSize->update(['stock' => $newStock]);

            StockMovement::create([
                'product_id' => $pSize->product_id,
                'product_size_id' => $pSize->id,
                'size' => $pSize->size,
                'previous_stock' => $prevStock,
                'new_stock' => $newStock,
                'difference' => -$qty,
                'reason' => "Added to Order #{$order->order_number} by Admin",
                'admin_name' => auth()->check() ? auth()->user()->name : 'Admin',
            ]);

            // Create OrderItem
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $pSize->product_id,
                'product_size_id' => $pSize->id,
                'product_name' => $pSize->product->name,
                'size' => $pSize->size,
                'unit_price' => $unitPrice,
                'discount_amount' => 0.00,
                'final_unit_price' => $unitPrice,
                'quantity' => $qty,
                'subtotal' => $subtotal,
                'item_status' => 'active',
            ]);

            $fullSubtotal = (float) OrderItem::where('order_id', $order->id)->sum('subtotal');

            $order->update(['subtotal' => $fullSubtotal]);
            $order->recalculateTotals();
        });

        return redirect()->route('admin.order-operations.create', $order->id)
            ->with('success', 'New product added to order successfully!');
    }

    public function updateShipping(Request $request, Order $order)
    {
        $validated = $request->validate([
            'shipping' => 'required|numeric|min:0',
        ]);

        $order->recalculateTotals((float) $validated['shipping']);

        return redirect()->route('admin.order-operations.create', $order->id)
            ->with('success', 'Order shipping charge updated successfully!');
    }

    public function addOrderExpense(Request $request, Order $order)
    {
        $validated = $request->validate([
            'expense_title' => 'required|string|max:255',
            'expense_amount' => 'required|numeric|min:0.01',
            'expense_notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $order) {
            $amount = (float) $validated['expense_amount'];

            \App\Models\Expense::create([
                'title' => "Order #{$order->order_number} Expense: {$validated['expense_title']}",
                'amount' => $amount,
                'expense_date' => now()->toDateString(),
                'category' => 'Order Operational Expense',
                'notes' => $validated['expense_notes'] ?? "Logged for Order #{$order->order_number}",
            ]);

            OrderOperation::create([
                'order_id' => $order->id,
                'product_id' => null,
                'order_item_id' => null,
                'operation_type' => 'other',
                'other_description' => "Order Expense: {$validated['expense_title']}",
                'status' => 'active',
                'quantity' => 1,
                'is_product_restored' => false,
                'inventory_condition' => 'do_not_restock',
                'is_money_refunded' => false,
                'additional_expense_total' => $amount,
                'total_financial_adjustment' => $amount,
                'notes' => $validated['expense_notes'] ?? null,
                'created_by' => auth()->check() ? auth()->user()->name : 'Admin',
            ]);
        });

        return redirect()->route('admin.order-operations.create', $order->id)
            ->with('success', 'Additional expense recorded successfully and added to Profit & Loss!');
    }

    public function addOrderIncome(Request $request, Order $order)
    {
        $validated = $request->validate([
            'income_title' => 'required|string|max:255',
            'income_amount' => 'required|numeric|min:0.01',
            'income_notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $order) {
            $amount = (float) $validated['income_amount'];

            \App\Models\Income::create([
                'income_name' => "Order #{$order->order_number} Income: {$validated['income_title']}",
                'income_price' => $amount,
                'selling_pieces' => 1,
                'total_income_amount' => $amount,
                'income_date' => now()->toDateString(),
                'type' => 'other',
                'status' => 'active',
                'notes' => $validated['income_notes'] ?? "Logged for Order #{$order->order_number}",
            ]);

            OrderOperation::create([
                'order_id' => $order->id,
                'product_id' => null,
                'order_item_id' => null,
                'operation_type' => 'other',
                'other_description' => "Order Additional Income: {$validated['income_title']}",
                'status' => 'active',
                'quantity' => 1,
                'is_product_restored' => false,
                'inventory_condition' => 'do_not_restock',
                'is_money_refunded' => false,
                'total_financial_adjustment' => -$amount,
                'notes' => $validated['income_notes'] ?? null,
                'created_by' => auth()->check() ? auth()->user()->name : 'Admin',
            ]);
        });

        return redirect()->route('admin.order-operations.create', $order->id)
            ->with('success', 'Additional income recorded successfully and added to Profit & Loss!');
    }

    public function removeOrderItem(Order $order, OrderItem $item)
    {
        if ($item->order_id !== $order->id) {
            return redirect()->back()->with('error', 'Item does not belong to this order.');
        }

        DB::transaction(function () use ($order, $item) {
            if ($item->item_status === 'active' && $item->product_size_id) {
                $pSize = ProductSize::find($item->product_size_id);
                if ($pSize) {
                    $prevStock = $pSize->stock;
                    $newStock = $prevStock + $item->quantity;
                    $pSize->update(['stock' => $newStock]);

                    StockMovement::create([
                        'product_id' => $pSize->product_id,
                        'product_size_id' => $pSize->id,
                        'size' => $pSize->size,
                        'previous_stock' => $prevStock,
                        'new_stock' => $newStock,
                        'difference' => $item->quantity,
                        'reason' => "Item Removed from Order #{$order->order_number}",
                        'admin_name' => auth()->check() ? auth()->user()->name : 'Admin',
                    ]);
                }
            }

            $item->delete();

            $activeSubtotal = OrderItem::where('order_id', $order->id)
                ->whereIn('item_status', ['active', 'exchanged'])
                ->sum('subtotal');

            $order->update(['subtotal' => $activeSubtotal]);
            $order->recalculateTotals();
        });

        return redirect()->route('admin.order-operations.create', $order->id)
            ->with('success', 'Item removed from order successfully!');
    }

    public function show(OrderOperation $operation)
    {
        $operation->load(['order.items.product', 'product.images', 'orderItem', 'replacementProduct', 'replacementProductSize']);
        return view('admin.order_operations.show', compact('operation'));
    }

    public function edit(OrderOperation $operation)
    {
        return redirect()->route('admin.order-operations.create', $operation->order_id)
            ->with('error', 'Order adjustments are finalized and locked. Editing is not permitted.');
    }

    public function update(Request $request, OrderOperation $operation)
    {
        return redirect()->route('admin.order-operations.create', $operation->order_id)
            ->with('error', 'Order adjustments are finalized and locked. Editing is not permitted.');
    }

    public function toggleStatus(Request $request, OrderOperation $operation)
    {
        $newStatus = $operation->status === 'active' ? 'inactive' : 'active';
        $operation->update([
            'status' => $newStatus,
            'updated_by' => auth()->check() ? auth()->user()->name : 'Admin',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'message' => 'Adjustment status updated to ' . strtoupper($newStatus),
            ]);
        }

        return redirect()->back()->with('success', 'Adjustment status toggled to ' . strtoupper($newStatus) . '!');
    }

    public function destroy(OrderOperation $operation)
    {
        $orderId = $operation->order_id;

        DB::transaction(function () use ($operation) {
            if ($operation->is_product_restored && $operation->product_id && $operation->orderItem && $operation->orderItem->product_size_id) {
                $pSize = ProductSize::find($operation->orderItem->product_size_id);
                if ($pSize) {
                    $prevStock = $pSize->stock;
                    $newStock = max(0, $prevStock - $operation->quantity);
                    $pSize->update(['stock' => $newStock]);

                    StockMovement::create([
                        'product_id' => $operation->product_id,
                        'product_size_id' => $pSize->id,
                        'size' => $operation->orderItem->size,
                        'previous_stock' => $prevStock,
                        'new_stock' => $newStock,
                        'difference' => -$operation->quantity,
                        'reason' => "Order Operation #{$operation->id} Deleted (Restoration Reverted)",
                        'admin_name' => auth()->check() ? auth()->user()->name : 'Admin',
                    ]);
                }
            }

            if ($operation->orderItem) {
                $operation->orderItem->update([
                    'item_status' => 'active',
                    'inventory_condition' => null,
                    'refund_amount' => 0.00,
                ]);
            }

            $operation->delete();
        });

        return redirect()->route('admin.order-operations.create', $orderId)->with('success', 'Adjustment record removed successfully.');
    }
}
