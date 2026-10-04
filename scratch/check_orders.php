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

echo "INACTIVE ORDER IDS: " . implode(', ', $inactiveOrderIds) . PHP_EOL . PHP_EOL;

$allOrders = Order::all();
echo "Total Orders Count: " . $allOrders->count() . PHP_EOL;

$byDate = [];
foreach ($allOrders as $o) {
    $date = $o->sale_date ? Carbon::parse($o->sale_date)->toDateString() : Carbon::parse($o->created_at)->toDateString();
    if (!isset($byDate[$date])) {
        $byDate[$date] = [
            'all' => [],
            'valid_paid' => [],
            'pending' => [],
            'cancelled' => [],
        ];
    }
    $byDate[$date]['all'][] = $o;
    
    $isTest = strpos((string)$o->customer_phone, '9544832975') !== false;
    $isInactive = in_array($o->id, $inactiveOrderIds);
    $isLegacyPending = (bool) $o->is_legacy_pending;
    
    if (!$isTest && !$isInactive && !$isLegacyPending) {
        if (in_array($o->payment_status, ['paid', 'completed']) && $o->order_status !== 'cancelled') {
            $byDate[$date]['valid_paid'][] = $o;
        } elseif ($o->order_status === 'cancelled') {
            $byDate[$date]['cancelled'][] = $o;
        } else {
            $byDate[$date]['pending'][] = $o;
        }
    }
}

ksort($byDate);

foreach ($byDate as $date => $data) {
    $paidSum = array_sum(array_column($data['valid_paid'], 'grand_total'));
    $paidCount = count($data['valid_paid']);
    $pendingSum = array_sum(array_column($data['pending'], 'grand_total'));
    $pendingCount = count($data['pending']);
    $refundSum = OrderRefund::whereHas('orderOperation', fn ($query) => $query->where('status', 'active'))
        ->whereDate('refund_date', $date)->sum('refund_amount');
    $netSales = max(0, $paidSum - $refundSum);

    echo "DATE: $date" . PHP_EOL;
    echo "  Total Orders in DB on this date: " . count($data['all']) . PHP_EOL;
    echo "  VALID PAID ORDERS: Count=$paidCount | Gross Amount=₹$paidSum | Refunds=₹$refundSum | NET PAID SALES=₹$netSales" . PHP_EOL;
    echo "  PENDING / UNPAID ORDERS: Count=$pendingCount | Amount=₹$pendingSum" . PHP_EOL;
    foreach ($data['valid_paid'] as $p) {
        echo "    -> Paid Order #{$p->order_number} (ID {$p->id}): ₹{$p->grand_total} | Payment Status: {$p->payment_status} | Order Status: {$p->order_status} | Method: {$p->payment_method}" . PHP_EOL;
    }
    foreach ($data['pending'] as $p) {
        echo "    -> Pending Order #{$p->order_number} (ID {$p->id}): ₹{$p->grand_total} | Payment Status: {$p->payment_status} | Order Status: {$p->order_status} | Method: {$p->payment_method}" . PHP_EOL;
    }
    echo "--------------------------------------------------------" . PHP_EOL;
}
