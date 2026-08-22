<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SocialMedia;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function track(Request $request)
    {
        $order = null;
        if ($request->filled('order_number') && $request->filled('phone')) {
            $orderNumber = trim($request->order_number);
            $phone = trim($request->phone);

            $order = Order::where('order_number', $orderNumber)
                ->where('customer_phone', $phone)
                ->with(['items.product', 'payment'])
                ->first();
        }

        $whatsapp = SocialMedia::where('type', 'whatsapp')->where('status', 'active')->first();

        return view('frontend.order_tracking', compact('order', 'whatsapp'));
    }
}
