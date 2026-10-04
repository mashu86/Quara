<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use Illuminate\Support\Facades\DB;

echo "ORDER & PAYMENT STATUS COMBINATIONS IN DB:" . PHP_EOL;
$rows = Order::select('payment_status', 'order_status', 'order_source', 'payment_method', DB::raw('count(*) as count'), DB::raw('sum(grand_total) as total'))
    ->groupBy('payment_status', 'order_status', 'order_source', 'payment_method')
    ->get();

foreach ($rows as $r) {
    echo "payment_status: {$r->payment_status} | order_status: {$r->order_status} | source: {$r->order_source} | method: {$r->payment_method} => Count: {$r->count} | Total: ₹{$r->total}" . PHP_EOL;
}
