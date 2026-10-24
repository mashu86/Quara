{{-- Book button + booked customers link. Shared by desktop rows and mobile cards. --}}
@php
    $totalStock = $product->sizes->sum('stock');
    $sizeAvailabilityData = $product->sizes->map(function ($size) use ($product) {
        $reserved = $product->bookings->where('product_size_id', $size->id)
            ->sum(function ($booking) { return $booking->remaining_quantity; });

        return [
            'id' => $size->id,
            'size' => $size->size,
            'available' => max(0, (int) $size->stock - (int) $reserved),
        ];
    });
    $typeLabels = ['business_whatsapp' => 'Business WhatsApp', 'whatsapp_customer' => 'WhatsApp', 'instagram_customer' => 'Instagram'];
    $hasLegacyBooking = $product->is_out_of_stock && (!empty($product->booked_by) || $product->booking_type);
    $bookingList = $product->bookings->map(fn ($booking) => [
        'name' => $booking->booked_by ?: 'Business WhatsApp',
        'type' => $typeLabels[$booking->booking_type] ?? 'Booked',
        'size' => optional($product->sizes->firstWhere('id', $booking->product_size_id))->size,
        'quantity' => $booking->remaining_quantity,
        'date' => $booking->booking_date?->format('d-m-Y'),
        'url' => route('admin.products.bookings.release', $booking->id),
    ])->values();
    if ($hasLegacyBooking) {
        $bookingList->prepend([
            'name' => $product->booked_by ?: 'Business WhatsApp',
            'type' => ($typeLabels[$product->booking_type] ?? 'Booked') . ' (whole product)',
            'size' => null,
            'quantity' => null,
            'date' => $product->booking_date?->format('d-m-Y'),
            'url' => route('admin.products.toggle-out-of-stock', $product->id),
            'legacy' => true,
        ]);
    }
    $canBook = !$product->is_out_of_stock && $sizeAvailabilityData->sum('available') > 0;
    $stockExhausted = $sizeAvailabilityData->sum('available') <= 0;
@endphp
<button type="button" class="btn btn-sm {{ $stockExhausted ? 'btn-outline-danger text-danger border-2' : 'btn-outline-warning text-dark' }} fw-bold rounded-pill px-3 py-0.5 book-product-btn"
        style="font-size: 0.75rem;"
        data-product-name="{{ $product->name }}"
        data-sizes='@json($sizeAvailabilityData)'
        data-url="{{ route('admin.products.bookings.store', $product->id) }}"
        title="{{ $product->is_out_of_stock ? 'Whole product is booked. Remove that booking first.' : ($canBook ? 'Book a size' : 'No unbooked stock left') }}"
        {{ $canBook ? '' : 'disabled' }}>
    <i class="fa-solid fa-user-tag me-1"></i> Book
</button>
@if($bookingList->isNotEmpty())
    <div class="mt-1">
        <button type="button" class="btn btn-link btn-sm p-0 text-primary fw-semibold text-decoration-none view-bookings-btn"
                style="font-size: 0.72rem;"
                data-product-name="{{ $product->name }}"
                data-bookings='@json($bookingList)'
                title="View booked customers">
            <i class="fa-solid fa-eye me-1"></i>{{ $bookingList->count() }} booked
        </button>
    </div>
@elseif($totalStock <= 0)
    <div class="small fw-bold text-danger mt-1" style="font-size: 0.72rem;">Sold Out</div>
@endif
