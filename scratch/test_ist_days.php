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

$orders = Order::query()
    ->whereNotIn('id', $inactiveOrderIds)
    ->where(function ($q) {
        $q->whereNull('customer_phone')
          ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
    })
    ->whereIn('payment_status', ['paid', 'completed'])
    ->where('order_status', '!=', 'cancelled')
    ->where('is_legacy_pending', false)
    ->get();

$byDate = [];
foreach ($orders as $o) {
    // Use created_at in Asia/Kolkata timezone
    $date = Carbon::parse($o->created_at)->setTimezone('Asia/Kolkata')->toDateString();
    if (!isset($byDate[$date])) {
        $byDate[$date] = ['gross' => 0, 'count' => 0];
    }
    $byDate[$date]['gross'] += $o->grand_total;
    $byDate[$date]['count']++;
}

$days = [];
foreach ($byDate as $date => $info) {
    $ref = (float) OrderRefund::whereHas('orderOperation', fn ($query) => $query->where('status', 'active'))
        ->whereDate('refund_date', $date)->sum('refund_amount');
    $net = max(0, $info['gross'] - $ref);
    $days[] = [
        'date' => $date,
        'date_formatted' => Carbon::parse($date)->format('d M Y'),
        'gross' => $info['gross'],
        'refund' => $ref,
        'net' => $net,
        'count' => $info['count']
    ];
}

usort($days, fn($a, $b) => $b['net'] <=> $a['net']);

echo "TOP 10 HIGHEST NET PAID SALES DAYS (IST created_at, strictly paid non-cancelled orders):" . PHP_EOL;
foreach (array_slice($days, 0, 10) as $i => $d) {
    echo "#" . ($i+1) . " -> Date: {$d['date_formatted']} ({$d['date']}) | Net Paid Sales: ₹{$d['net']} (Gross: ₹{$d['gross']}, Refund: ₹{$d['refund']}) | Paid Orders: {$d['count']}" . PHP_EOL;
}
