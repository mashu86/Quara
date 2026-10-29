<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class AvailableStockReportController extends Controller
{
    public function index()
    {
        // Same rule as the shop's SOLD OUT badge: stock left after reserved and booked pieces are set aside.
        $products = Product::query()
            ->where('status', 'active')
            ->where('is_out_of_stock', false)
            ->select('products.id', 'products.category_id')
            ->selectRaw("
                (SELECT COALESCE(SUM(stock - COALESCE(reserved_stock, 0)), 0) FROM product_sizes WHERE product_sizes.product_id = products.id)
                - (SELECT COALESCE(SUM(quantity - sold_quantity), 0) FROM product_bookings
                   WHERE product_bookings.product_id = products.id AND product_bookings.status IN ('active', 'partially_sold'))
                AS available_units
            ")
            ->having('available_units', '>', 0)
            ->get();

        $pivotCategories = DB::table('category_product')
            ->whereIn('product_id', $products->pluck('id'))
            ->get(['product_id', 'category_id'])
            ->groupBy('product_id');

        // A product listed in several categories counts once in each of them.
        $stats = [];
        foreach ($products as $product) {
            $categoryIds = collect($pivotCategories->get($product->id, []))->pluck('category_id')
                ->push($product->category_id)->filter()->unique();

            foreach ($categoryIds->isEmpty() ? [0] : $categoryIds as $categoryId) {
                $stats[$categoryId]['products'] = ($stats[$categoryId]['products'] ?? 0) + 1;
                $stats[$categoryId]['units'] = ($stats[$categoryId]['units'] ?? 0) + (int) $product->available_units;
            }
        }

        $categories = Category::whereIn('id', array_keys($stats))->pluck('name', 'id');
        $rows = collect($stats)->map(fn ($stat, $categoryId) => [
            'id' => $categoryId ?: null,
            'name' => $categories[$categoryId] ?? 'Uncategorized',
            'products' => $stat['products'],
            'units' => $stat['units'],
        ])->sortByDesc('products')->values();

        return view('admin.reports.available-stock', [
            'rows' => $rows,
            'totalProducts' => $products->count(),
            'totalUnits' => (int) $products->sum('available_units'),
        ]);
    }
}
