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

                if (!$productSize || $productSize->stock < $qty) {
                    throw new Exception("Stock validation failed for product ID {$productId} size {$size}. Available: " . ($productSize ? $productSize->stock : 0));
                }

                $prevStock = $productSize->stock;
                $newStock = $prevStock - $qty;

                $productSize->update(['stock' => $newStock]);

                // Record movement history
                StockMovement::create([
                    'product_id' => $productId,
                    'product_size_id' => $productSize->id,
                    'size' => $size,
                    'previous_stock' => $prevStock,
                    'new_stock' => $newStock,
                    'difference' => -$qty,
                    'reason' => 'Customer Order Purchase',
                    'admin_name' => 'System (Order Processing)',
                ]);
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
