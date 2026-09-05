@forelse($orders as $index => $order)
    @php
        $opsCount = $order->operations->count();
        $activeOpsCount = $order->operations->where('status', 'active')->count();
        $isEven = $index % 2 === 0;
        $accentBorder = $isEven ? 'border-warning' : 'border-dark';
    @endphp
    <div class="card border-0 rounded-3 shadow-sm mb-3 position-relative overflow-hidden order-ops-mobile-card border-start border-4 {{ $accentBorder }}" 
         style="{{ $isEven ? 'background-color: #FFFFFF;' : 'background-color: #F8FAFC; border: 1px solid #E2E8F0 !important;' }}">
        <!-- Header -->
        <div class="card-header border-bottom py-2 px-2.5 d-flex justify-content-between align-items-center" style="{{ $isEven ? 'background-color: #FFFFFF;' : 'background-color: #EDF2F7;' }}">
            <div>
                <div class="d-flex align-items-center gap-1.5">
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-warning text-decoration-none" style="font-size: 0.82rem;">
                        {{ $order->order_number }}
                    </a>
                    <span class="badge bg-{{ $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'cancelled' ? 'danger' : 'warning') }} text-capitalize" style="font-size: 0.58rem; padding: 0.15em 0.4em;">
                        {{ $order->order_status }}
                    </span>
                </div>
                <div class="text-muted mt-0.5" style="font-size: 0.68rem;">
                    <i class="fa-regular fa-calendar-check me-1 text-warning"></i>{{ ($order->sale_date ?? $order->created_at)->format('d-m-Y') }}
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold text-dark" style="font-size: 0.85rem;">₹{{ number_format($order->grand_total, 2) }}</div>
                @if($opsCount > 0)
                    <span class="badge bg-dark text-warning" style="font-size: 0.6rem;">
                        <i class="fa-solid fa-rotate-left me-1"></i>{{ $opsCount }} Adjustment(s)
                    </span>
                @else
                    <span class="badge bg-light text-muted border" style="font-size: 0.6rem;">No Adjustments</span>
                @endif
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
                <div class="text-muted fw-semibold mb-1" style="font-size: 0.68rem;">Purchased Items ({{ $order->items->count() }}):</div>
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
                                <span class="fw-bold text-dark ms-auto" style="font-size: 0.75rem;">₹{{ number_format($item->subtotal ?: ($item->unit_price * $item->quantity), 2) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Operations / Adjustments Recorded List if Any -->
            @if($opsCount > 0)
                <div class="mt-2 pt-1.5 border-top">
                    <div class="text-muted fw-semibold mb-1" style="font-size: 0.68rem;">Recorded Adjustments:</div>
                    @foreach($order->operations as $op)
                        <div class="d-flex align-items-center justify-content-between p-1 px-2 rounded mb-1 {{ $isEven ? 'bg-light' : 'bg-white border' }}" style="font-size: 0.72rem;">
                            <div>
                                <span class="badge bg-{{ $op->status === 'active' ? 'success' : 'secondary' }}" style="font-size: 0.58rem;">{{ strtoupper($op->status) }}</span>
                                <span class="fw-bold text-dark ms-1">{{ $op->operation_type_label }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                @if($op->total_financial_adjustment > 0 && $op->status === 'active')
                                    <span class="text-danger fw-bold me-1" style="font-size: 0.7rem;">-₹{{ number_format($op->total_financial_adjustment, 2) }}</span>
                                @endif
                                <a href="{{ route('admin.order-operations.show', $op->id) }}" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;" title="View"><i class="fa-solid fa-eye" style="font-size: 0.6rem;"></i></a>
                                <a href="{{ route('admin.order-operations.edit', $op->id) }}" class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;" title="Edit"><i class="fa-solid fa-pen-to-square" style="font-size: 0.6rem;"></i></a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Footer: Action Button -->
        <div class="card-footer border-top py-1.5 px-2.5" style="{{ $isEven ? 'background-color: #F8FAFC;' : 'background-color: #EDF2F7;' }}">
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted" style="font-size: 0.68rem;">Delivery: ₹{{ number_format($order->shipping, 2) }}</span>
                <a href="{{ route('admin.order-operations.create', $order->id) }}" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 py-1 shadow-sm d-inline-flex align-items-center gap-1" style="background-color: var(--qw-gold); border-color: var(--qw-gold); font-size: 0.72rem;" title="Adjust Order">
                    <i class="fa-solid fa-pen-to-square"></i> Adjust Order
                </a>
            </div>
        </div>
    </div>
@empty
    @if(!request()->get('page') || request()->get('page') == 1)
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-sliders fs-2 mb-2 d-block text-warning"></i>
            No orders or adjustments matching criteria found.
        </div>
    @endif
@endforelse
