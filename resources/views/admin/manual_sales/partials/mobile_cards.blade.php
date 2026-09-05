@forelse($manualOrders as $index => $order)
    @php
        $isEven = $index % 2 === 0;
        $accentBorder = $isEven ? 'border-warning' : 'border-dark';
    @endphp
    <div class="card border-0 rounded-3 shadow-sm mb-3 position-relative overflow-hidden manual-sales-mobile-card border-start border-4 {{ $accentBorder }}"
         style="{{ $isEven ? 'background-color: #FFFFFF;' : 'background-color: #F8FAFC; border: 1px solid #E2E8F0 !important;' }}">
        <!-- Header -->
        <div class="card-header border-bottom py-2 px-2.5 d-flex justify-content-between align-items-center" style="{{ $isEven ? 'background-color: #FFFFFF;' : 'background-color: #EDF2F7;' }}">
            <div>
                <div class="d-flex align-items-center gap-1.5">
                    <span class="fw-bold text-warning" style="font-size: 0.82rem;">{{ $order->order_number }}</span>
                    <span class="badge bg-dark text-warning border border-warning" style="font-size: 0.58rem; padding: 0.15em 0.4em;">
                        <i class="fa-solid fa-user-pen me-1"></i> MANUAL SALE
                    </span>
                </div>
                <div class="text-muted mt-0.5" style="font-size: 0.68rem;">
                    <i class="fa-regular fa-calendar-check me-1 text-warning"></i>{{ ($order->sale_date ?? $order->created_at)->format('d-m-Y') }}
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold text-dark" style="font-size: 0.85rem;">₹{{ number_format($order->grand_total, 2) }}</div>
                <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} text-capitalize" style="font-size: 0.6rem;">
                    {{ $order->payment_status }}
                </span>
            </div>
        </div>

        <!-- Body: Customer & Items -->
        <div class="card-body p-2.5">
            <!-- Customer Info -->
            <div class="d-flex justify-content-between align-items-start mb-2 pb-1.5 border-bottom">
                <div>
                    <div class="fw-bold text-dark" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-user text-muted me-1"></i>{{ $order->customer_name }}
                    </div>
                    @if($order->customer_phone)
                        <a href="tel:{{ $order->customer_phone }}" class="text-muted text-decoration-none" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-phone text-success me-1"></i>{{ $order->customer_phone }}
                        </a>
                    @endif
                </div>
                <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.6rem;">
                    {{ str_replace('_', ' ', $order->payment_method) }}
                </span>
            </div>

            <!-- Purchased Items -->
            <div class="mb-1">
                <div class="text-muted fw-semibold mb-1" style="font-size: 0.68rem;">Purchased Items:</div>
                @foreach($order->items as $item)
                    @php
                        $itemProd = $item->product;
                        $itemImg = $itemProd ? $itemProd->primary_image_url : \App\Models\Setting::logoUrl();
                        $itemRowBg = $isEven ? 'bg-light' : 'bg-white border';
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-1.5 p-1.5 rounded-2 {{ $itemRowBg }}">
                        <div class="p-0.5 bg-white border rounded flex-shrink-0" style="cursor: pointer;" onclick="openImagePreviewModal('{{ addslashes($itemImg) }}', '{{ addslashes($item->product_name) }}')" title="Click to view image">
                            <img src="{{ $itemImg }}" alt="{{ $item->product_name }}" 
                                 class="rounded d-block" 
                                 style="width: 38px; height: 46px; object-fit: cover;">
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.78rem;">{{ $item->product_name }}</div>
                            <div class="d-flex align-items-center gap-1 mt-0.5">
                                <span class="badge bg-dark" style="font-size: 0.62rem;">Size: {{ $item->size }}</span>
                                <span class="text-muted" style="font-size: 0.7rem;">× {{ $item->quantity }}</span>
                                <span class="fw-bold text-dark ms-auto" style="font-size: 0.75rem;">₹{{ number_format($item->subtotal ?: ($item->price * $item->quantity), 2) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Footer: Actions -->
        <div class="card-footer border-top py-1.5 px-2.5" style="{{ $isEven ? 'background-color: #F8FAFC;' : 'background-color: #EDF2F7;' }}">
            <div class="d-flex align-items-center justify-content-end gap-2">
                <a href="{{ route('admin.order-operations.create', $order->id) }}" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2.5 py-1 d-inline-flex align-items-center justify-content-center shadow-sm fw-bold" style="font-size: 0.7rem;" title="Adjust / Exchange Order Items">
                    <i class="fa-solid fa-sliders me-1 text-warning"></i> Adjust
                </a>
                <a href="{{ route('admin.manual-sales.edit', $order->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-2.5 py-1 d-inline-flex align-items-center justify-content-center shadow-sm fw-bold" style="font-size: 0.7rem;" title="Edit Customer & Sale Details">
                    <i class="fa-solid fa-user-pen me-1"></i> Edit Details
                </a>
            </div>
        </div>
    </div>
@empty
    @if(!request()->get('page') || request()->get('page') == 1)
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-receipt fs-2 mb-2 d-block text-warning"></i>
            No manual offline sales recorded yet. Click "Record New Offline Sale" to add one.
        </div>
    @endif
@endforelse
