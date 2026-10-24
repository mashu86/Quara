<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBooking;
use App\Models\ProductSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductBookingController extends Controller
{
    public function index()
    {
        $bookings = ProductBooking::with(['product.images', 'productSize'])
            ->whereIn('status', ['active', 'partially_sold'])->latest()->paginate(30);
        return view('admin.products.size_bookings', compact('bookings'));
    }

    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'product_size_id' => 'required|integer|exists:product_sizes,id',
            'quantity' => 'required|integer|min:1',
            'booked_by' => 'nullable|string|max:255',
            'booking_type' => 'required|in:business_whatsapp,whatsapp_customer,instagram_customer',
            'booking_date' => 'required|date_format:Y-m-d',
        ]);

        $booking = DB::transaction(function () use ($data, $product, $request) {
            $size = ProductSize::where('product_id', $product->id)->whereKey($data['product_size_id'])->lockForUpdate()->firstOrFail();
            if ($product->is_out_of_stock) {
                throw ValidationException::withMessages(['product_size_id' => 'This product uses the existing product-level booking. Clear that legacy booking before creating size bookings.']);
            }
            if ($size->availableStockForOrder() < $data['quantity']) {
                throw ValidationException::withMessages(['quantity' => "Only {$size->availableStockForOrder()} unreserved unit(s) of this size are available."]);
            }
            if ($data['booking_type'] !== 'business_whatsapp' && blank($data['booked_by'])) {
                throw ValidationException::withMessages(['booked_by' => 'Customer name or phone is required for customer bookings.']);
            }

            return ProductBooking::create([
                'product_id' => $product->id,
                'product_size_id' => $size->id,
                'quantity' => $data['quantity'],
                'booked_by' => $data['booking_type'] === 'business_whatsapp' ? null : trim($data['booked_by']),
                'booking_type' => $data['booking_type'],
                'booking_date' => $data['booking_date'],
                'status' => 'active',
                'created_by' => $request->user()?->id,
            ]);
        });

        return response()->json(['success' => true, 'booking_id' => $booking->id, 'message' => 'Size booking saved. Reserved units are now unavailable for online orders.']);
    }

    public function release(ProductBooking $booking)
    {
        if (!in_array($booking->status, ['active', 'partially_sold'], true)) {
            return response()->json(['success' => false, 'message' => 'This booking is already closed.'], 422);
        }
        // Nothing sold yet: drop the row. Partly sold bookings are linked to order items, so keep them as released.
        if ((int) $booking->sold_quantity === 0) {
            $booking->delete();
        } else {
            $booking->update(['status' => 'released']);
        }
        return response()->json(['success' => true, 'message' => 'Remaining booked units are available again.']);
    }
}
