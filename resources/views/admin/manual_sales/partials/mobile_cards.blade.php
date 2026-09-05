@forelse($manualOrders as $order)
    <article class="manual-sales-mobile-card card border-0 rounded-3 shadow-sm mb-3">
        <div class="manual-sale-header">
            <div class="manual-sale-reference">
                <a href="{{ route('admin.orders.show', $order->id) }}" class="manual-sale-order-number">{{ $order->order_number }}</a>
                <span class="manual-sale-date"><i class="fa-regular fa-calendar-check me-1" aria-hidden="true"></i>{{ ($order->sale_date ?? $order->created_at)->format('d M Y') }}</span>
            </div>
            <span class="badge bg-dark text-warning manual-sale-source"><i class="fa-solid fa-user-pen me-1" aria-hidden="true"></i>Manual sale</span>
        </div>
        <div class="manual-sale-body">
            <div class="manual-sale-customer">
                <div class="manual-sale-customer-details">
                    <div class="manual-sale-customer-name">{{ $order->customer_name }}</div>
                    @if($order->customer_phone)
                        <a href="tel:{{ $order->customer_phone }}" class="manual-sale-phone"><i class="fa-solid fa-phone text-success me-1" aria-hidden="true"></i>{{ $order->customer_phone }}</a>
                    @endif
                </div>
                <span class="badge bg-light text-dark border manual-sale-method">{{ str_replace('_', ' ', $order->payment_method) }}</span>
            </div>
            <div class="manual-sale-items">
                <div class="manual-sale-section-label">Purchased items <span>({{ $order->items->sum('quantity') }} pcs)</span></div>
                @foreach($order->items as $item)
                    @php
                        $itemImg = $item->product ? $item->product->primary_image_url : \App\Models\Setting::logoUrl();
                    @endphp
                    <div class="manual-sale-item">
                        <button type="button" class="manual-sale-image" data-image-url="{{ $itemImg }}" data-image-title="{{ $item->product_name }}" onclick="openImagePreviewModal(this.dataset.imageUrl, this.dataset.imageTitle)" aria-label="Preview image: {{ $item->product_name }}">
                            <img src="{{ $itemImg }}" alt="{{ $item->product_name }}" loading="lazy" width="48" height="60">
                        </button>
                        <div class="manual-sale-item-details">
                            <div class="manual-sale-product-name">{{ $item->product_name }}</div>
                            <div class="manual-sale-item-meta">
                                <span class="badge bg-dark">Size: {{ $item->size }}</span>
                                <span class="manual-sale-quantity">Qty: {{ $item->quantity }}</span>
                                <strong class="manual-sale-item-price">&#8377;{{ number_format($item->subtotal ?? (($item->final_unit_price ?? $item->unit_price ?? $item->price ?? 0) * $item->quantity), 2) }}</strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="manual-sale-total-row">
                <div><span class="manual-sale-section-label">Total amount</span><strong class="manual-sale-total">&#8377;{{ number_format($order->grand_total, 2) }}</strong></div>
                <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} manual-sale-payment">{{ ucfirst($order->payment_status) }}</span>
            </div>
        </div>
        <div class="manual-sale-actions">
            <a href="{{ route('admin.order-operations.create', $order->id) }}" class="btn btn-outline-warning text-dark" title="Adjust / Exchange Order Items"><i class="fa-solid fa-sliders me-1" aria-hidden="true"></i>Adjust</a>
            <a href="{{ route('admin.manual-sales.edit', $order->id) }}" class="btn btn-outline-dark" title="Edit Customer & Sale Details"><i class="fa-solid fa-user-pen me-1" aria-hidden="true"></i>Edit Details</a>
        </div>
    </article>
@empty
    @if(!request()->get('page') || request()->get('page') == 1)
        <div class="text-center py-5 px-3 text-muted">
            <i class="fa-solid fa-receipt fs-2 mb-2 d-block text-warning" aria-hidden="true"></i>
            <p class="small mb-0">No offline sales found. Use New Sale to record a purchase, or clear your filters.</p>
        </div>
    @endif
@endforelse
