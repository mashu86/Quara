<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SocialMedia;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function track(Request $request)
    {
        $order = null;
        $trackingNumber = null;
        $trackingDetailsAvailable = false;

        if ($request->filled('order_number') && $request->filled('phone')) {
            $orderNumber = trim($request->order_number);
            $phone = trim($request->phone);

            $order = Order::where('order_number', $orderNumber)
                ->where('customer_phone', $phone)
                ->with(['items.product', 'payment', 'shipment'])
                ->first();

            if ($order) {
                // India Post's article number is the consignment ID customers need to track.
                $trackingNumber = collect([
                    $order->shipment?->article_number,
                    $order->shipment?->tracking_number,
                    $order->tracking_number,
                ])->first(fn ($number) => filled(trim((string) $number)));

                $orderDate = $order->sale_date ?? $order->created_at;
                $trackingDetailsAvailable = $orderDate
                    ? CarbonImmutable::parse($orderDate)->setTimezone('Asia/Kolkata')->startOfDay()->greaterThanOrEqualTo(CarbonImmutable::parse('2026-10-18', 'Asia/Kolkata'))
                    : false;
            }
        }

        $whatsapp = SocialMedia::where('type', 'whatsapp')->where('status', 'active')->first();

        return view('frontend.order_tracking', compact('order', 'whatsapp', 'trackingNumber', 'trackingDetailsAvailable'));
    }
}
