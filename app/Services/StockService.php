<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSize;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Exception;

class StockService
{
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
                    $newStock = max(0, $prevStock - $remainder);
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
}
