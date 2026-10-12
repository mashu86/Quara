<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DuplicatePaidProductsReportController extends Controller
{
    private const NORMALIZED_PHONE_SQL = "SUBSTR(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(o.customer_phone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', ''), '.', ''), -10)";

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $applyFilters = function ($query) use ($validated) {
            $query->where('o.payment_status', 'paid')
                ->where('o.order_status', '!=', 'cancelled')
                ->whereNotNull('oi.product_id')
                // Ignore the admin's own test purchases, even when stored with +91 or separators.
                ->whereRaw(self::NORMALIZED_PHONE_SQL . ' <> ?', ['9544832975'])
                ->where(function ($items) {
                    $items->whereNull('oi.item_status')->orWhereNotIn('oi.item_status', ['returned', 'cancelled']);
                })
                ->whereNotExists(function ($operations) {
                    $operations->selectRaw('1')
                        ->from('order_operations as op')
                        ->whereColumn('op.order_id', 'oi.order_id')
                        ->where('op.status', 'active')
                        ->whereIn('op.operation_type', ['product_returned', 'customer_return', 'wrong_product_sent', 'product_damaged', 'product_lost'])
                        ->where(function ($match) {
                            $match->whereColumn('op.order_item_id', 'oi.id')
                                ->orWhere(function ($legacyMatch) {
                                    $legacyMatch->whereNull('op.order_item_id')
                                        ->whereColumn('op.product_id', 'oi.product_id');
                                });
                        });
                });

            if (!empty($validated['start_date'])) {
                $query->whereRaw('DATE(COALESCE(o.sale_date, o.created_at)) >= ?', [$validated['start_date']]);
            }
            if (!empty($validated['end_date'])) {
                $query->whereRaw('DATE(COALESCE(o.sale_date, o.created_at)) <= ?', [$validated['end_date']]);
            }
            if (!empty($validated['search'])) {
                $query->where(function ($search) use ($validated) {
                    $term = '%' . $validated['search'] . '%';
                    $search->where('oi.product_name', 'like', $term)->orWhere('p.name', 'like', $term);
                });
            }
        };

        $groupsQuery = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('products as p', 'p.id', '=', 'oi.product_id');
        $applyFilters($groupsQuery);
        $groups = $groupsQuery
            ->select('oi.product_id', DB::raw('COALESCE(p.name, MAX(oi.product_name)) as product_name'))
            ->selectRaw('COUNT(DISTINCT oi.order_id) as paid_order_count')
            ->selectRaw('COUNT(DISTINCT ' . self::NORMALIZED_PHONE_SQL . ') as buyer_count')
            ->selectRaw('SUM(oi.quantity) as total_quantity')
            ->groupBy('oi.product_id', 'p.name')
            // Repeated paid orders count even when they belong to the same customer.
            ->havingRaw('COUNT(DISTINCT oi.order_id) >= 2')
            ->orderByDesc('paid_order_count')
            ->orderBy('product_name')
            ->paginate(20)
            ->withQueryString();

        $productIds = $groups->getCollection()->pluck('product_id');
        $detailsQuery = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('products as p', 'p.id', '=', 'oi.product_id')
            ->whereIn('oi.product_id', $productIds);
        $applyFilters($detailsQuery);
        $orderItems = $detailsQuery
            ->select('oi.id', 'oi.product_id', 'oi.product_name', 'oi.size', 'oi.quantity', 'oi.subtotal', 'oi.item_status',
                'o.id as order_id', 'o.order_number', 'o.customer_name', 'o.customer_phone', 'o.payment_method',
                'o.order_status', 'o.sale_date', 'o.created_at as order_created_at')
            ->orderBy('oi.product_id')
            ->orderByDesc(DB::raw('COALESCE(o.sale_date, o.created_at)'))
            ->get()
            ->groupBy('product_id');
        $productsById = Product::with('images')->whereIn('id', $productIds)->get()->keyBy('id');

        return view('admin.reports.duplicate-paid-products', [
            'groups' => $groups,
            'orderItemsByProduct' => $orderItems,
            'productsById' => $productsById,
            'search' => $validated['search'] ?? '',
            'startDate' => $validated['start_date'] ?? '',
            'endDate' => $validated['end_date'] ?? '',
        ]);
    }
}
