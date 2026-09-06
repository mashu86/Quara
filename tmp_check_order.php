<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\Payment;

$orders = Order::where('order_source', '!=', 'manual')
    ->orWhere('payment_method', 'online')
    ->orderBy('id', 'desc')
    ->get();

$out = "Total online orders: " . $orders->count() . "\n";
foreach ($orders as $o) {
    $p = Payment::where('order_id', $o->id)->first();
    $pInfo = $p ? "PayID: {$p->razorpay_payment_id} | Status: {$p->status}" : "NO PAYMENT RECORD";
    $out .= "ID: {$o->id} | Num: {$o->order_number} | Customer: {$o->customer_name} | Phone: {$o->customer_phone} | Amt: ₹{$o->grand_total} | PayStatus: {$o->payment_status} | OrderStatus: {$o->order_status} | {$pInfo}\n";
}
file_put_contents(__DIR__ . '/orders_out.txt', $out);
echo "Wrote to orders_out.txt\n";


