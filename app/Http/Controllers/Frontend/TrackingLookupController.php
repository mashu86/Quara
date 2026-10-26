<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class TrackingLookupController extends Controller
{
    /**
     * Customer tracking lookup by phone number. Only order ID, date, status and the tracking
     * number are returned, so a phone number never reveals names or addresses.
     */
    public function __invoke(Request $request)
    {
        $digits = preg_replace('/\D+/', '', (string) $request->input('phone'));
        $phone = substr($digits, -10);
        if (strlen($phone) !== 10) {
            return response()->json(['message' => 'Please enter a valid 10 digit phone number.'], 422);
        }

        $orders = Order::with('shipment')
            // Stored numbers may carry +91, spaces or dashes; compare on the digits only.
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(customer_phone, ' ', ''), '-', ''), '+', ''), '.', '') LIKE ?", ['%'.$phone])
            ->where('payment_status', 'paid')
            ->orderByDesc('created_at')->limit(20)->get()
            ->map(function (Order $order) {
                $tracking = $order->shipment?->tracking_number ?: ($order->shipment?->article_number ?: $order->tracking_number);

                return [
                    'order_number' => $order->order_number,
                    'date' => optional($order->sale_date ?? $order->created_at)->format('d M Y'),
                    'status' => ucfirst((string) $order->order_status),
                    'tracking_number' => $tracking ?: null,
                ];
            });

        return response()->json(['orders' => $orders]);
    }
}
