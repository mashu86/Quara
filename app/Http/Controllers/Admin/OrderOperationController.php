<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderOperation;
use App\Models\OrderOperationExpense;
use App\Models\ProductSize;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderOperationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $hasOperationFilter = $request->get('has_operation'); // all, with_ops, without_ops
        $operationStatusFilter = $request->get('operation_status'); // active, inactive
        $operationTypeFilter = $request->get('operation_type');

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

        $orders = $query->paginate(15)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $desktopHtml = view('admin.order_operations.partials.desktop_rows', compact('orders'))->render();

            return response()->json([
                'desktop_html' => $desktopHtml,
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
            'operationTypeFilter'
        ));
    }

    public function create(Order $order)
    {
        $order->load(['items.product.images', 'operations.expenses']);
        $operationTypes = OrderOperation::OPERATION_TYPES;

        return view('admin.order_operations.create', compact('order', 'operationTypes'));
    }

    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'operation_type' => 'required|string',
            'other_description' => 'nullable|required_if:operation_type,other|string|max:255',
            'status' => 'required|in:active,inactive',
            'order_item_id' => 'nullable|exists:order_items,id',
            'quantity' => 'required|integer|min:1',
            'is_product_restored' => 'required|boolean',
            'is_money_refunded' => 'required|boolean',
            'product_refund_amount' => 'nullable|numeric|min:0',
            'delivery_refund_amount' => 'nullable|numeric|min:0',
            'other_refund_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'expenses' => 'nullable|array',
            'expenses.*.description' => 'required_with:expenses.*.amount|string|max:255',
            'expenses.*.amount' => 'required_with:expenses.*.description|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($validated, $order, $request) {
            $orderItem = null;
            $productId = null;

            if (!empty($validated['order_item_id'])) {
                $orderItem = OrderItem::find($validated['order_item_id']);
                if ($orderItem) {
                    $productId = $orderItem->product_id;
                }
            } else {
                $firstItem = $order->items->first();
                if ($firstItem) {
                    $orderItem = $firstItem;
                    $productId = $firstItem->product_id;
                }
            }

            $productRefund = $validated['is_money_refunded'] ? (float) ($validated['product_refund_amount'] ?? 0) : 0;
            $deliveryRefund = $validated['is_money_refunded'] ? (float) ($validated['delivery_refund_amount'] ?? 0) : 0;
            $otherRefund = $validated['is_money_refunded'] ? (float) ($validated['other_refund_amount'] ?? 0) : 0;
            $totalRefund = $productRefund + $deliveryRefund + $otherRefund;

            $operation = OrderOperation::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'order_item_id' => $orderItem ? $orderItem->id : null,
                'operation_type' => $validated['operation_type'],
                'other_description' => $validated['other_description'] ?? null,
                'status' => $validated['status'],
                'quantity' => $validated['quantity'],
                'is_product_restored' => (bool) $validated['is_product_restored'],
                'is_money_refunded' => (bool) $validated['is_money_refunded'],
                'product_refund_amount' => $productRefund,
                'delivery_refund_amount' => $deliveryRefund,
                'other_refund_amount' => $otherRefund,
                'total_refund_amount' => $totalRefund,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->check() ? auth()->user()->name : 'Admin',
            ]);

            // Save additional expenses
            $additionalExpenseTotal = 0.00;
            if (!empty($validated['expenses']) && is_array($validated['expenses'])) {
                foreach ($validated['expenses'] as $exp) {
                    if (!empty($exp['description']) && !empty($exp['amount'])) {
                        $amt = (float) $exp['amount'];
                        OrderOperationExpense::create([
                            'order_operation_id' => $operation->id,
                            'description' => trim($exp['description']),
                            'amount' => $amt,
                        ]);
                        $additionalExpenseTotal += $amt;
                    }
                }
            }

            $operation->additional_expense_total = $additionalExpenseTotal;
            $operation->total_financial_adjustment = $totalRefund + $additionalExpenseTotal;
            $operation->save();

            // Handle inventory restoration if requested
            if ($operation->is_product_restored && $productId && $orderItem && $orderItem->size) {
                $productSize = ProductSize::where('product_id', $productId)
                    ->where('size', $orderItem->size)
                    ->first();

                if ($productSize) {
                    $prevStock = $productSize->stock;
                    $qtyToRestore = $operation->quantity;
                    $newStock = $prevStock + $qtyToRestore;

                    $productSize->update(['stock' => $newStock]);

                    StockMovement::create([
                        'product_id' => $productId,
                        'product_size_id' => $productSize->id,
                        'size' => $orderItem->size,
                        'previous_stock' => $prevStock,
                        'new_stock' => $newStock,
                        'difference' => $qtyToRestore,
                        'reason' => "Order Operation #{$operation->id} Inventory Return ({$operation->operation_type_label})",
                        'admin_name' => auth()->check() ? auth()->user()->name : 'Admin',
                    ]);
                }
            }
        });

        return redirect()->route('admin.order-operations.index')->with('success', 'Order operation recorded successfully!');
    }

    public function show(OrderOperation $operation)
    {
        $operation->load(['order.items.product', 'product.images', 'orderItem', 'expenses']);
        return view('admin.order_operations.show', compact('operation'));
    }

    public function edit(OrderOperation $operation)
    {
        $operation->load(['order.items.product', 'expenses']);
        $operationTypes = OrderOperation::OPERATION_TYPES;

        return view('admin.order_operations.edit', compact('operation', 'operationTypes'));
    }

    public function update(Request $request, OrderOperation $operation)
    {
        $validated = $request->validate([
            'operation_type' => 'required|string',
            'other_description' => 'nullable|required_if:operation_type,other|string|max:255',
            'status' => 'required|in:active,inactive',
            'quantity' => 'required|integer|min:1',
            'is_product_restored' => 'required|boolean',
            'is_money_refunded' => 'required|boolean',
            'product_refund_amount' => 'nullable|numeric|min:0',
            'delivery_refund_amount' => 'nullable|numeric|min:0',
            'other_refund_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'expenses' => 'nullable|array',
            'expenses.*.description' => 'required_with:expenses.*.amount|string|max:255',
            'expenses.*.amount' => 'required_with:expenses.*.description|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($validated, $operation) {
            $prevRestored = $operation->is_product_restored;
            $newRestored = (bool) $validated['is_product_restored'];

            $productRefund = $validated['is_money_refunded'] ? (float) ($validated['product_refund_amount'] ?? 0) : 0;
            $deliveryRefund = $validated['is_money_refunded'] ? (float) ($validated['delivery_refund_amount'] ?? 0) : 0;
            $otherRefund = $validated['is_money_refunded'] ? (float) ($validated['other_refund_amount'] ?? 0) : 0;
            $totalRefund = $productRefund + $deliveryRefund + $otherRefund;

            // Handle inventory adjustment toggle
            $orderItem = $operation->orderItem;
            $productId = $operation->product_id;

            if ($productId && $orderItem && $orderItem->size) {
                $productSize = ProductSize::where('product_id', $productId)
                    ->where('size', $orderItem->size)
                    ->first();

                if ($productSize) {
                    if (!$prevRestored && $newRestored) {
                        // Stock was not restored before, now user set to Yes -> increment stock
                        $prevStock = $productSize->stock;
                        $qtyToRestore = $validated['quantity'];
                        $newStock = $prevStock + $qtyToRestore;

                        $productSize->update(['stock' => $newStock]);

                        StockMovement::create([
                            'product_id' => $productId,
                            'product_size_id' => $productSize->id,
                            'size' => $orderItem->size,
                            'previous_stock' => $prevStock,
                            'new_stock' => $newStock,
                            'difference' => $qtyToRestore,
                            'reason' => "Order Operation #{$operation->id} Inventory Return Enabled",
                            'admin_name' => auth()->check() ? auth()->user()->name : 'Admin',
                        ]);
                    } elseif ($prevRestored && !$newRestored) {
                        // Stock was restored before, now user set to No -> deduct stock back
                        $prevStock = $productSize->stock;
                        $qtyToDeduct = $operation->quantity;
                        $newStock = max(0, $prevStock - $qtyToDeduct);

                        $productSize->update(['stock' => $newStock]);

                        StockMovement::create([
                            'product_id' => $productId,
                            'product_size_id' => $productSize->id,
                            'size' => $orderItem->size,
                            'previous_stock' => $prevStock,
                            'new_stock' => $newStock,
                            'difference' => -$qtyToDeduct,
                            'reason' => "Order Operation #{$operation->id} Inventory Return Reverted",
                            'admin_name' => auth()->check() ? auth()->user()->name : 'Admin',
                        ]);
                    }
                }
            }

            $operation->update([
                'operation_type' => $validated['operation_type'],
                'other_description' => $validated['other_description'] ?? null,
                'status' => $validated['status'],
                'quantity' => $validated['quantity'],
                'is_product_restored' => $newRestored,
                'is_money_refunded' => (bool) $validated['is_money_refunded'],
                'product_refund_amount' => $productRefund,
                'delivery_refund_amount' => $deliveryRefund,
                'other_refund_amount' => $otherRefund,
                'total_refund_amount' => $totalRefund,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => auth()->check() ? auth()->user()->name : 'Admin',
            ]);

            // Sync expenses
            $operation->expenses()->delete();
            $additionalExpenseTotal = 0.00;

            if (!empty($validated['expenses']) && is_array($validated['expenses'])) {
                foreach ($validated['expenses'] as $exp) {
                    if (!empty($exp['description']) && !empty($exp['amount'])) {
                        $amt = (float) $exp['amount'];
                        OrderOperationExpense::create([
                            'order_operation_id' => $operation->id,
                            'description' => trim($exp['description']),
                            'amount' => $amt,
                        ]);
                        $additionalExpenseTotal += $amt;
                    }
                }
            }

            $operation->additional_expense_total = $additionalExpenseTotal;
            $operation->total_financial_adjustment = $totalRefund + $additionalExpenseTotal;
            $operation->save();
        });

        return redirect()->route('admin.order-operations.index')->with('success', 'Order operation updated successfully!');
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
                'message' => 'Operation status updated to ' . strtoupper($newStatus),
            ]);
        }

        return redirect()->back()->with('success', 'Operation status toggled to ' . strtoupper($newStatus) . '!');
    }

    public function destroy(OrderOperation $operation)
    {
        DB::transaction(function () use ($operation) {
            // Revert inventory if restored
            if ($operation->is_product_restored && $operation->product_id && $operation->orderItem && $operation->orderItem->size) {
                $productSize = ProductSize::where('product_id', $operation->product_id)
                    ->where('size', $operation->orderItem->size)
                    ->first();

                if ($productSize) {
                    $prevStock = $productSize->stock;
                    $newStock = max(0, $prevStock - $operation->quantity);
                    $productSize->update(['stock' => $newStock]);

                    StockMovement::create([
                        'product_id' => $operation->product_id,
                        'product_size_id' => $productSize->id,
                        'size' => $operation->orderItem->size,
                        'previous_stock' => $prevStock,
                        'new_stock' => $newStock,
                        'difference' => -$operation->quantity,
                        'reason' => "Order Operation #{$operation->id} Deleted (Restoration Reverted)",
                        'admin_name' => auth()->check() ? auth()->user()->name : 'Admin',
                    ]);
                }
            }

            $operation->delete();
        });

        return redirect()->route('admin.order-operations.index')->with('success', 'Order operation record removed.');
    }
}
