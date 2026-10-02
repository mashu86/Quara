@forelse($products as $product)
    @php
        $isBooked = (bool) $product->is_out_of_stock && (!empty($product->booked_by) || !empty($product->booking_type));
        $isSoldOut = !$isBooked && ((bool) $product->is_out_of_stock || $product->total_stock <= 0);
        $availability = $isSoldOut ? 'sold-out' : ($isBooked ? 'booked' : 'available');
        $availabilityLabel = $isSoldOut ? 'Sold out' : ($isBooked ? 'Booked' : 'Available');
        $availabilityColor = $isSoldOut ? '#dc3545' : ($isBooked ? '#fd7e14' : '#198754');
    @endphp
    <div class="list-group-item px-0 py-3 d-flex align-items-center gap-3 category-product-row" draggable="true" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-url="{{ route($side === 'inside' ? 'admin.categories.products.detach' : 'admin.categories.products.attach', [$category, $product]) }}" data-method="{{ $side === 'inside' ? 'DELETE' : 'POST' }}" style="cursor:grab">
        <img src="{{ $product->primary_image_url }}" alt="" class="rounded-3 border" style="width:56px;height:68px;object-fit:cover" loading="lazy">
        <div class="flex-grow-1 min-w-0">
            <div class="fw-semibold text-truncate">{{ $product->name }}</div>
            <div class="small text-muted">₹{{ number_format($product->final_price, 2) }} · Stock: {{ $product->total_stock }}</div>
        </div>
        <span class="d-inline-flex align-items-center gap-2 small fw-semibold text-nowrap" title="{{ $availabilityLabel }}" aria-label="{{ $availabilityLabel }}">
            <span class="rounded-circle d-inline-block" style="width:12px;height:12px;background-color:{{ $availabilityColor }};box-shadow:0 0 0 3px {{ $availabilityColor }}22"></span>
            <span class="d-none d-sm-inline">{{ $availabilityLabel }}</span>
        </span>
        @if($side === 'inside')
            <button type="button" class="btn btn-outline-danger rounded-circle product-category-action" style="width:38px;height:38px" title="Remove from category" aria-label="Remove {{ $product->name }} from category" data-url="{{ route('admin.categories.products.detach', [$category, $product]) }}" data-method="DELETE">
                <i class="fa-solid fa-xmark"></i>
            </button>
        @else
            <button type="button" class="btn btn-outline-success rounded-circle product-category-action" style="width:38px;height:38px" title="Add to category" aria-label="Add {{ $product->name }} to category" data-url="{{ route('admin.categories.products.attach', [$category, $product]) }}" data-method="POST">
                <i class="fa-solid fa-plus"></i>
            </button>
        @endif
    </div>
@empty
    <div class="text-center text-muted py-5">No products match this filter.</div>
@endforelse
