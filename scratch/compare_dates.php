<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderOperation;
use App\Models\OrderRefund;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

$inactiveOrderIds = OrderOperation::where('status', 'inactive')->pluck('order_id')->toArray();

echo "=== GROUP BY DATE(created_at) ===" . PHP_EOL;
$byCreatedAt = Order::query()
    ->selectRaw("DATE(created_at) as sale_day, SUM(grand_total) as gross_sales, COUNT(id) as orders_count")
    ->whereNotIn('id', $inactiveOrderIds)
    ->where(function ($q) {
        $q->whereNull('customer_phone')
          ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
    })
    ->whereIn('payment_status', ['paid', 'completed'])
    ->where('order_status', '!=', 'cancelled')
    ->groupBy(DB::raw('DATE(created_at)'))
    ->orderByDesc('gross_sales')
    ->take(10)
    ->get();

foreach ($byCreatedAt as $i => $row) {
    echo "#" . ($i+1) . " -> Date: {$row->sale_day} | Gross Sales: ₹{$row->gross_sales} | Orders: {$row->orders_count}" . PHP_EOL;
}

echo PHP_EOL . "=== GROUP BY DATE(COALESCE(sale_date, created_at)) ===" . PHP_EOL;
$byCoalesce = Order::query()
    ->selectRaw("DATE(COALESCE(sale_date, created_at)) as sale_day, SUM(grand_total) as gross_sales, COUNT(id) as orders_count")
    ->whereNotIn('id', $inactiveOrderIds)
    ->where(function ($q) {
        $q->whereNull('customer_phone')
          ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
    })
    ->whereIn('payment_status', ['paid', 'completed'])
    ->where('order_status', '!=', 'cancelled')
    ->groupBy(DB::raw('DATE(COALESCE(sale_date, created_at))'))
    ->orderByDesc('gross_sales')
    ->take(10)
    ->get();

foreach ($byCoalesce as $i => $row) {
    echo "#" . ($i+1) . " -> Date: {$row->sale_day} | Gross Sales: ₹{$row->gross_sales} | Orders: {$row->orders_count}" . PHP_EOL;
}
