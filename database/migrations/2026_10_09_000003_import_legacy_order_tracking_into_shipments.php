<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('orders')
            ->whereNotNull('tracking_number')
            ->where('tracking_number', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($orders) {
                foreach ($orders as $order) {
                    // Legacy duplicate numbers cannot satisfy the new unique shipment index.
                    if (DB::table('orders')->where('tracking_number', $order->tracking_number)->count() !== 1) {
                        continue;
                    }
                    if (DB::table('order_shipments')->where('order_id', $order->id)->exists()) {
                        continue;
                    }

                    DB::table('order_shipments')->insert([
                        'order_id' => $order->id,
                        'article_number' => $order->tracking_number,
                        'tracking_number' => $order->tracking_number,
                        'courier_name' => $order->courier_partner,
                        'receiver_name' => $order->customer_name,
                        'receiver_address' => collect([
                            $order->house_building,
                            $order->street,
                            $order->area,
                            $order->city,
                            $order->district,
                            $order->state,
                            $order->pin_code,
                        ])->filter()->implode(', '),
                        'weight_unit' => 'gram',
                        'shipment_date' => $order->dispatched_at
                            ? substr($order->dispatched_at, 0, 10)
                            : substr($order->sale_date ?: $order->created_at, 0, 10),
                        'shipment_status' => 'Generated',
                        'created_by' => null,
                        'updated_by' => null,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Imported legacy records are retained to avoid deleting admin shipment edits.
    }
};
