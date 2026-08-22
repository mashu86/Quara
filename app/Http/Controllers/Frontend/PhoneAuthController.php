<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PhoneAuthController extends Controller
{
    /**
     * Store verified phone number in session and return past address if available.
     */
    public function verifySession(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|min:10|max:20',
        ]);

        $rawPhone = preg_replace('/[^0-9]/', '', $validated['phone']);
        // Store last 10 digits
        $phone10 = substr($rawPhone, -10);

        session(['customer_phone' => $phone10]);

        // Find most recent past order for this customer to prefill address
        $lastOrder = Order::where(function ($q) use ($phone10, $validated) {
            $q->where('customer_phone', 'LIKE', "%{$phone10}")
              ->orWhere('customer_phone', $validated['phone']);
        })->latest()->first();

        $customerDetails = null;
        if ($lastOrder) {
            $customerDetails = [
                'customer_name' => $lastOrder->customer_name,
                'customer_email' => $lastOrder->customer_email,
                'house_building' => $lastOrder->house_building,
                'street' => $lastOrder->street,
                'area' => $lastOrder->area,
                'city' => $lastOrder->city,
                'district' => $lastOrder->district,
                'state' => $lastOrder->state,
                'pin_code' => $lastOrder->pin_code,
            ];
        }

        return response()->json([
            'success' => true,
            'phone' => $phone10,
            'message' => 'Phone number verified successfully.',
            'previous_details' => $customerDetails,
        ]);
    }

    /**
     * Customer My Orders Dashboard view based on verified phone session.
     */
    public function myOrders(Request $request)
    {
        $phone = session('customer_phone');

        if (!$phone && $request->filled('phone')) {
            $phone = substr(preg_replace('/[^0-9]/', '', $request->phone), -10);
        }

        $orders = collect();
        if ($phone) {
            $orders = Order::where('customer_phone', 'LIKE', "%{$phone}")
                ->with(['items.product', 'payment'])
                ->latest()
                ->get();
        }

        return view('frontend.my_orders', compact('orders', 'phone'));
    }

    /**
     * Logout customer phone session.
     */
    public function logout(Request $request)
    {
        session()->forget('customer_phone');
        return redirect()->route('home')->with('success', 'You have been logged out.');
    }
}
