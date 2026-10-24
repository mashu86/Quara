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
            'tab' => ['nullable', 'in:oversold,legacy'],
        ]);
        $tab = $validated['tab'] ?? 'oversold';

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

        if ($tab === 'oversold') {
            return $this->oversoldReport($request, $validated, $applyFilters);
        }

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
            ->leftJoin('product_sizes as ps', 'ps.id', '=', 'oi.product_size_id')
            ->whereIn('oi.product_id', $productIds);
        $applyFilters($detailsQuery);
        $orderItems = $detailsQuery
            ->select('oi.id', 'oi.product_id', 'oi.product_name', 'oi.size', 'oi.quantity', 'oi.subtotal', 'oi.item_status', 'ps.size as variant_size',
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
            'tab' => $tab,
        ]);
    }

    /**
     * Sizes that have more customers promised than units ever existed:
     *   total stock = current stock + units already deducted by paid orders
     *   promised    = paid (not returned/cancelled) units + still-unsold booked units
     *   extra       = promised - total stock  (shown when > 0)
     * A paid order whose stock could not be deducted is marked "stock_review" on its payment;
     * those are the customers who did not get a piece.
     */
    private function oversoldReport(Request $request, array $validated, \Closure $applyFilters)
    {
        // Stock math needs every live sale, so the date filter is not applied here; search only.
        $itemsQuery = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('products as p', 'p.id', '=', 'oi.product_id');
        $applyFilters($itemsQuery);
        $items = $itemsQuery
            ->select('oi.id', 'oi.product_id', 'oi.product_size_id', 'oi.product_name', 'oi.size', 'oi.quantity', 'oi.subtotal',
                'o.id as order_id', 'o.order_number', 'o.customer_name', 'o.customer_phone', 'o.payment_method',
                'o.order_status', 'o.sale_date', 'o.created_at as order_created_at')
            ->orderByDesc(DB::raw('COALESCE(o.sale_date, o.created_at)'))
            ->get();

        $reviewOrderIds = \App\Models\Payment::whereIn('order_id', $items->pluck('order_id')->unique())
            ->get(['order_id', 'response_payload'])
            ->filter(fn ($payment) => !empty($payment->response_payload['stock_review']))
            ->pluck('order_id')->flip();

        // Products with live bookings are checked too, even without a paid sale.
        $bookedProductIds = \App\Models\ProductBooking::whereIn('status', ['active', 'partially_sold'])
            ->when(!empty($validated['search']), fn ($q) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', '%' . $validated['search'] . '%')))
            ->pluck('product_id');
        $productIds = $items->pluck('product_id')->merge($bookedProductIds)->unique();
        $products = Product::with(['images', 'sizes', 'bookings' => fn ($q) => $q->whereIn('status', ['active', 'partially_sold'])])
            ->whereIn('id', $productIds)->get()->keyBy('id');

        // Each order line belongs to a size: by id, else by size name, else "size not recorded".
        $sizeKey = function ($item) use ($products) {
            $product = $products->get($item->product_id);
            $size = $product?->sizes->firstWhere('id', $item->product_size_id)
                ?? $product?->sizes->first(fn ($s) => strcasecmp(trim((string) $s->size), trim((string) $item->size)) === 0 && trim((string) $item->size) !== '');
            return $item->product_id . ':' . ($size?->id ?? 'none');
        };
        $itemsBySize = $items->groupBy($sizeKey);

        $groups = collect();
        foreach ($products as $product) {
            $sizeRows = $product->sizes->map(fn ($size) => ['key' => $product->id . ':' . $size->id, 'size' => $size, 'stock' => (int) $size->stock,
                'bookings' => $product->bookings->where('product_size_id', $size->id)]);
            if ($itemsBySize->has($product->id . ':none')) {
                // Old lines without a size were taken from any size, so compare them with the whole product.
                $sizeRows->push(['key' => $product->id . ':none', 'size' => null, 'stock' => (int) $product->sizes->sum('stock'), 'bookings' => collect()]);
            }

            foreach ($sizeRows as $row) {
                $lines = $itemsBySize->get($row['key'], collect());
                $paid = (int) $lines->sum('quantity');
                $notDeducted = (int) $lines->filter(fn ($line) => $reviewOrderIds->has($line->order_id))->sum('quantity');
                $booked = (int) $row['bookings']->sum(fn ($booking) => $booking->remaining_quantity);
                $totalStock = $row['stock'] + ($paid - $notDeducted);
                $extra = ($paid + $booked) - $totalStock;
                if ($extra <= 0) {
                    continue;
                }
                $groups->push((object) [
                    'product' => $product,
                    'size_label' => $row['size']?->size ?? 'Size not recorded',
                    'total_stock' => $totalStock,
                    'paid' => $paid,
                    'booked' => $booked,
                    'extra' => $extra,
                    'lines' => $lines->map(fn ($line) => tap($line, function ($l) use ($reviewOrderIds, $row) {
                        $l->stock_review = $reviewOrderIds->has($l->order_id);
                        $l->variant_size = $row['size']?->size;
                    })),
                    'bookings' => $row['bookings']->values(),
                ]);
            }
        }
        $groups = $groups->sortByDesc('extra')->values();

        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator($groups->forPage($page, 20)->values(), $groups->count(), 20, $page,
            ['path' => $request->url(), 'query' => $request->query()]);

        return view('admin.reports.duplicate-paid-products', [
            'tab' => 'oversold',
            'oversoldGroups' => $paginated,
            'search' => $validated['search'] ?? '',
            'startDate' => $validated['start_date'] ?? '',
            'endDate' => $validated['end_date'] ?? '',
        ]);
    }
}
