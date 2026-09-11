<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\ProductSize;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Exception;

class StockService
{
    /** Called inside the checkout transaction, before creating any order/items. */
    public function lockAndValidateCheckoutStock(array $items): void
    {
        $items = collect($items)->sortBy(fn ($item) => sprintf('%020d:%s', $item['product_id'], $item['size']));
        $requested = [];
        foreach ($items as $item) {
            $size = ProductSize::where('product_id', $item['product_id'])
                ->where('size', $item['size'])->lockForUpdate()->first();
            $key = $item['product_id'].':'.$item['size'];
            $requested[$key] = ($requested[$key] ?? 0) + (int) $item['quantity'];
            if (!$size || $item['quantity'] < 1 || $size->available_stock < $requested[$key]) {
                throw new Exception('Stock validation failed: this item is sold out or reserved by another customer.');
            }
        }
    }

    /** Deduct physical stock without consuming another order's reservation. */
    public function deductStockForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items()->orderBy('product_id')->orderBy('size')->get() as $item) {
                $size = ProductSize::where('product_id', $item->product_id)
                    ->where('size', $item->size)->lockForUpdate()->first();
                if (!$size || $size->availableStockForOrder($order->id) < $item->quantity) {
                    throw new \App\Exceptions\InsufficientOrderStock('Stock validation failed: '.$item->product_name.' ('.$item->size.') is unavailable.');
                }
                $previous = (int) $size->stock;
                $size->update(['stock' => $previous - $item->quantity]);
                StockMovement::create([
                    'product_id' => $item->product_id, 'product_size_id' => $size->id,
                    'size' => $item->size, 'previous_stock' => $previous,
                    'new_stock' => $size->stock, 'difference' => -$item->quantity,
                    'reason' => 'Order #'.$order->order_number.' purchase', 'admin_name' => 'System',
                ]);
                Product::whereKey($item->product_id)->update([
                    'is_out_of_stock' => ProductSize::where('product_id', $item->product_id)->sum('stock') <= 0,
                ]);
            }
        });
    }

    /**
     * Revalidate item availability against current stock.
     */
    public function checkStock(int $productId, ?string $size, int $requestedQty): array
    {
        $product = Product::with('category')->find($productId);
        if (!$product || $product->status !== 'active' || !$product->category || $product->category->status !== 'active') {
            return ['available' => false, 'message' => 'Product is currently unavailable.'];
        }

        if ($product->is_out_of_stock) {
            return ['available' => false, 'message' => 'Selected item is currently out of stock.', 'available_stock' => 0];
        }

        if ($size) {
            $productSize = ProductSize::where('product_id', $productId)->where('size', $size)->first();
            if (!$productSize) {
                return ['available' => false, 'message' => "Selected size ({$size}) is not available for this product."];
            }
            $availableStock = $productSize->available_stock;
        } else {
            $availableStock = $product->sizes->sum(function ($s) { return $s->available_stock; });
        }

        if ($availableStock <= 0) {
            return ['available' => false, 'message' => 'Selected item is out of stock.', 'available_stock' => 0];
        }

        if ($requestedQty > $availableStock) {
            return [
                'available' => false,
                'message' => "Only {$availableStock} item(s) available in size {$size}.",
                'available_stock' => $availableStock
            ];
        }

        return ['available' => true, 'available_stock' => $availableStock];
    }

    /**
     * Deduct stock for a single ProductSize with movement audit logging.
     */
    public function deductStock(int $productSizeId, int $qty, string $reason = 'Manual Sale', ?string $adminName = null): bool
    {
        return DB::transaction(function () use ($productSizeId, $qty, $reason, $adminName) {
            $productSize = ProductSize::where('id', $productSizeId)->lockForUpdate()->firstOrFail();
            $prevStock = $productSize->stock;
            $newStock = max(0, $prevStock - $qty);

            $productSize->update(['stock' => $newStock]);

            StockMovement::create([
                'product_id' => $productSize->product_id,
                'product_size_id' => $productSize->id,
                'size' => $productSize->size,
                'previous_stock' => $prevStock,
                'new_stock' => $newStock,
                'difference' => -$qty,
                'reason' => $reason,
                'admin_name' => $adminName ?? (auth()->check() ? auth()->user()->name : 'Admin'),
            ]);

            return true;
        });
    }

    /**
     * Deduct stock inside a database transaction with pessimistic locking.
     * Prioritizes deducting from reserved_stock first, then public stock.
     */
    public function deductStockForOrderItems(array $items): bool
    {
        return DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $productId = $item['product_id'];
                $size = $item['size'];
                $qty = $item['quantity'];

                // Pessimistic locking on size stock
                $productSize = ProductSize::where('product_id', $productId)
                    ->where('size', $size)
                    ->lockForUpdate()
                    ->first();

                if (!$productSize) {
                    throw new Exception("Product size variant not found for product ID {$productId} size {$size}.");
                }

                $prevStock = $productSize->stock;
                $prevReserved = $productSize->reserved_stock ?? 0;

                // 1. Deduct from reserved_stock if available
                if ($prevReserved >= $qty) {
                    $newReserved = $prevReserved - $qty;
                    $productSize->update(['reserved_stock' => $newReserved]);

                    StockMovement::create([
                        'product_id' => $productId,
                        'product_size_id' => $productSize->id,
                        'size' => $size,
                        'previous_stock' => $prevStock,
                        'new_stock' => $prevStock,
                        'difference' => 0,
                        'reason' => 'Customer Paid Order (Fulfilled from Reserved Stock)',
                        'admin_name' => 'System (Payment Reconciliation)',
                    ]);
                }
                // 2. Otherwise deduct from public stock
                else {
                    $remainder = $qty - $prevReserved;
                    if ($prevReserved > 0) {
                        $productSize->update(['reserved_stock' => 0]);
                    }
                    if ($productSize->available_stock < $remainder) {
                        throw new Exception('Stock validation failed: insufficient stock for this order.');
                    }
                    $newStock = $prevStock - $remainder;
                    $productSize->update(['stock' => $newStock]);

                    StockMovement::create([
                        'product_id' => $productId,
                        'product_size_id' => $productSize->id,
                        'size' => $size,
                        'previous_stock' => $prevStock,
                        'new_stock' => $newStock,
                        'difference' => -$remainder,
                        'reason' => 'Customer Paid Order Purchase',
                        'admin_name' => 'System (Order Processing)',
                    ]);
                }

                // Automatically update out of stock flag if total stock is 0
                $product = Product::find($productId);
                if ($product) {
                    $totalStockRemaining = ProductSize::where('product_id', $productId)->sum('stock');
                    $product->update([
                        'is_out_of_stock' => $totalStockRemaining <= 0,
                        'booked_by' => null,
                        'booked_by_admin_id' => null,
                        'booked_at' => null,
                    ]);
                }
            }
            return true;
        });
    }

    /**
     * Move item stock into Internal Reserved Pool (hidden from public shop).
     */
    public function reserveStockForOrderItems(array $items, string $reason = 'Internal Payment Reservation'): bool
    {
        return DB::transaction(function () use ($items, $reason) {
            foreach ($items as $item) {
                $productId = $item['product_id'];
                $size = $item['size'];
                $qty = $item['quantity'];

                $productSize = ProductSize::where('product_id', $productId)
                    ->where('size', $size)
                    ->lockForUpdate()
                    ->first();

                if ($productSize) {
                    $prevReserved = $productSize->reserved_stock ?? 0;
                    $newReserved = $prevReserved + $qty;

                    $productSize->update(['reserved_stock' => $newReserved]);

                    StockMovement::create([
                        'product_id' => $productId,
                        'product_size_id' => $productSize->id,
                        'size' => $size,
                        'previous_stock' => $productSize->stock,
                        'new_stock' => $productSize->stock,
                        'difference' => $qty,
                        'reason' => $reason,
                        'admin_name' => auth()->check() ? auth()->user()->name : 'System (Internal Stock Reservation)',
                    ]);
                }
            }
            return true;
        });
    }

    /**
     * Restore stock if order is cancelled or payment fails.
     */
    public function restoreStockForOrderItems(array $items, string $reason = 'Order Cancelled'): bool
    {
        return DB::transaction(function () use ($items, $reason) {
            foreach ($items as $item) {
                $productId = $item['product_id'];
                $size = $item['size'];
                $qty = $item['quantity'];

                $productSize = ProductSize::where('product_id', $productId)
                    ->where('size', $size)
                    ->lockForUpdate()
                    ->first();

                if ($productSize) {
                    $prevStock = $productSize->stock;
                    $newStock = $prevStock + $qty;

                    $productSize->update(['stock' => $newStock]);

                    StockMovement::create([
                        'product_id' => $productId,
                        'product_size_id' => $productSize->id,
                        'size' => $size,
                        'previous_stock' => $prevStock,
                        'new_stock' => $newStock,
                        'difference' => $qty,
                        'reason' => $reason,
                        'admin_name' => auth()->check() ? auth()->user()->name : 'System (Order Cancellation)',
                    ]);
                    Product::whereKey($productId)->update(['is_out_of_stock' => false]);
                }
            }
            return true;
        });
    }

    /**
     * Manual stock adjustment by Admin.
     */
    public function adjustStock(int $productSizeId, int $newStock, string $reason, string $adminName): ProductSize
    {
        return DB::transaction(function () use ($productSizeId, $newStock, $reason, $adminName) {
            $productSize = ProductSize::where('id', $productSizeId)->lockForUpdate()->firstOrFail();
            $prevStock = $productSize->stock;
            $diff = $newStock - $prevStock;

            $productSize->update(['stock' => max(0, $newStock)]);

            StockMovement::create([
                'product_id' => $productSize->product_id,
                'product_size_id' => $productSize->id,
                'size' => $productSize->size,
                'previous_stock' => $prevStock,
                'new_stock' => $productSize->stock,
                'difference' => $diff,
                'reason' => $reason,
                'admin_name' => $adminName,
            ]);

            return $productSize;
        });
    }

    /**
     * Release/Unbook reserved stock for a product size back into available public inventory.
     */
    public function releaseReservedStockForProductSize(int $productSizeId, string $reason = 'Manual Admin Release / Unbook'): bool
    {
        return DB::transaction(function () use ($productSizeId, $reason) {
            $productSize = ProductSize::where('id', $productSizeId)->lockForUpdate()->first();
            if (!$productSize) {
                return false;
            }

            $prevReserved = $productSize->reserved_stock ?? 0;
            $productSize->update(['reserved_stock' => 0]);

            $product = Product::find($productSize->product_id);
            if ($product) {
                $totalAvailable = ProductSize::where('product_id', $product->id)
                    ->get()
                    ->sum('available_stock');

                $product->update([
                    'is_out_of_stock' => $totalAvailable <= 0,
                    'booked_by' => null,
                    'booked_by_admin_id' => null,
                    'booked_at' => null,
                ]);
            }

            StockMovement::create([
                'product_id' => $productSize->product_id,
                'product_size_id' => $productSize->id,
                'size' => $productSize->size,
                'previous_stock' => $productSize->stock,
                'new_stock' => $productSize->stock,
                'difference' => 0,
                'reason' => $reason . " (Released {$prevReserved} reserved pcs)",
                'admin_name' => auth()->check() ? auth()->user()->name : 'Admin',
            ]);

            return true;
        });
    }
}
