<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderOperation;
use App\Models\OrderRefund;
use Illuminate\Support\Carbon;

$inactiveOrderIds = OrderOperation::where('status', 'inactive')->pluck('order_id')->toArray();

$dailySalesData = Order::query()
    ->selectRaw("DATE(COALESCE(sale_date, created_at)) as sale_day, SUM(grand_total) as gross_sales, COUNT(id) as orders_count")
    ->whereNotIn('id', $inactiveOrderIds)
    ->where(function ($q) {
        $q->whereNull('customer_phone')
          ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
    })
    ->whereIn('payment_status', ['paid', 'completed'])
    ->where('order_status', '!=', 'cancelled')
    ->groupBy(\Illuminate\Support\Facades\DB::raw('DATE(COALESCE(sale_date, created_at))'))
    ->get();

$dailyRefundsData = OrderRefund::whereHas('orderOperation', fn ($query) => $query->where('status', 'active'))
    ->selectRaw("DATE(refund_date) as refund_day, SUM(refund_amount) as total_refund")
    ->groupBy(\Illuminate\Support\Facades\DB::raw('DATE(refund_date)'))
    ->pluck('total_refund', 'refund_day');

$days = [];
foreach ($dailySalesData as $row) {
    $dStr = $row->sale_day;
    $ref = (float) ($dailyRefundsData[$dStr] ?? 0);
    $net = max(0, (float)$row->gross_sales - $ref);
    $days[] = [
        'date' => $dStr,
        'gross' => (float)$row->gross_sales,
        'refund' => $ref,
        'net' => $net,
        'count' => $row->orders_count
    ];
}

usort($days, fn($a, $b) => $b['net'] <=> $a['net']);

echo "TOP 10 HIGHEST NET PAID SALES DAYS:" . PHP_EOL;
foreach (array_slice($days, 0, 10) as $i => $d) {
    echo "#" . ($i+1) . " -> Date: {$d['date']} | Net Paid Sales: ₹{$d['net']} (Gross: ₹{$d['gross']}, Refund: ₹{$d['refund']}) | Paid Orders: {$d['count']}" . PHP_EOL;
}

echo PHP_EOL . "WHAT ABOUT 11 SEP 2026?" . PHP_EOL;
$sep11 = Order::whereDate(\DB::raw('COALESCE(sale_date, created_at)'), '2026-09-11')->get();
echo "Total 11 Sep orders count in DB: " . $sep11->count() . PHP_EOL;
foreach ($sep11 as $o) {
    echo "  Order #{$o->order_number} (ID {$o->id}): ₹{$o->grand_total} | Payment Status: {$o->payment_status} | Order Status: {$o->order_status} | sale_date: {$o->sale_date} | created_at: {$o->created_at} | Phone: {$o->customer_phone}" . PHP_EOL;
}
