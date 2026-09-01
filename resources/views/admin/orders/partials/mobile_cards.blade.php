@forelse($orders as $order)
    <div class="card border-0 rounded-3 shadow-sm mb-2 position-relative overflow-hidden bg-white orders-mobile-card">
        <!-- Mobile Card Header -->
        <div class="card-header bg-white border-bottom py-2 px-2.5 d-flex justify-content-between align-items-center">
            <div>
                <div class="d-flex align-items-center gap-1">
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-warning text-decoration-none" style="font-size: 0.8rem;">
                        {{ $order->order_number }}
                    </a>
                    @if($order->notifications && $order->notifications->where('is_read', false)->count() > 0)
                        <span class="badge bg-danger text-white align-middle" style="font-size: 0.55rem; padding: 0.15em 0.35em;" title="Unread Order Notification">NEW</span>
                    @endif
                </div>
                <div class="mt-0.5 mb-0.5">
                    @if($order->order_source === 'manual' || $order->payment_method === 'offline_sale')
                        <span class="badge bg-dark text-warning border border-warning" style="font-size: 0.58rem; padding: 0.15em 0.4em;">
                            <i class="fa-solid fa-user-pen me-1"></i> MANUAL SALE
                        </span>
                    @else
                        <span class="badge bg-primary text-white" style="font-size: 0.58rem; padding: 0.15em 0.4em;">
                            <i class="fa-solid fa-globe me-1"></i> WEBSITE SALE
                        </span>
                    @endif
                </div>
                <div class="text-muted" style="font-size: 0.68rem;">
                    <i class="fa-regular fa-clock me-1"></i>{{ $order->created_at->format('M d, Y • h:i A') }}
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold text-dark" style="font-size: 0.85rem;">₹{{ number_format($order->grand_total, 2) }}</div>
                <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.6rem; padding: 0.15em 0.4em;">
                    {{ $order->items->sum('quantity') }} Pcs
                </span>
            </div>
        </div>

        <!-- Mobile Card Body -->
        <div class="card-body p-2.5">
            <!-- Customer Info -->
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="fw-bold text-dark" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-user text-muted me-1"></i>{{ $order->customer_name }}
                    </div>
                    <a href="tel:{{ $order->customer_phone }}" class="text-muted text-decoration-none" style="font-size: 0.72rem;">
                        <i class="fa-solid fa-phone text-success me-1"></i>{{ $order->customer_phone }}
                    </a>
                </div>
                <div class="text-end">
                    <div class="mb-1">
                        @if($order->order_source === 'manual' || $order->payment_method === 'offline_sale')
                            <span class="badge bg-dark text-warning border border-warning" style="font-size: 0.6rem;">
                                <i class="fa-solid fa-user-pen me-1"></i> MANUAL
                            </span>
                        @else
                            <span class="badge bg-primary text-white" style="font-size: 0.6rem;">
                                <i class="fa-solid fa-globe me-1"></i> WEBSITE
                            </span>
                        @endif
                    </div>
                    <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.6rem;">
                        {{ str_replace('_', ' ', $order->payment_method) }}
                    </span>
                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} text-capitalize ms-0.5" style="font-size: 0.6rem;">
                        {{ $order->payment_status }}
                    </span>
                </div>
            </div>

            <!-- Order Status -->
            <div class="d-flex align-items-center justify-content-between pt-1.5 border-top">
                <span class="text-muted" style="font-size: 0.7rem;">Status:</span>
                <div>
                    <span class="badge bg-{{ $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'cancelled' ? 'danger' : 'warning') }} text-capitalize" style="font-size: 0.62rem; padding: 0.2em 0.5em;">
                        {{ $order->order_status }}
                    </span>
                    @if($order->operations && $order->operations->where('status', 'active')->count() > 0)
                        <span class="badge text-white ms-1" style="background-color: #6f42c1; font-size: 0.62rem; padding: 0.2em 0.5em;" title="Has active return/operation">
                            <i class="fa-solid fa-rotate-left me-1"></i> Returned ({{ $order->operations->where('status', 'active')->count() }})
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Mobile Card Actions -->
        <div class="card-footer bg-light border-top py-1.5 px-2.5">
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted fw-semibold" style="font-size: 0.68rem;">Actions:</span>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" onclick="openIndexEditPaymentModal({{ json_encode($order) }})" class="btn btn-sm btn-outline-primary rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm btn-circle-mobile" title="Edit Payment Details">
                        <i class="fa-solid fa-credit-card"></i>
                    </button>
                    <button type="button" onclick="openWhatsappModal({{ json_encode($order) }})" class="btn btn-sm btn-success text-white rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm btn-circle-mobile" title="WhatsApp Follow-up">
                        <i class="fa-brands fa-whatsapp"></i>
                    </button>
                    <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-sm btn-outline-warning text-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm btn-circle-mobile" title="Edit Order Details">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm btn-circle-mobile" title="View Details">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.order-operations.create', $order->id) }}" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm btn-circle-mobile" title="Order Operation / Return">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                    <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-warning text-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm btn-circle-mobile" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Print Invoice">
                        <i class="fa-solid fa-print"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@empty
    @if(!request()->get('page') || request()->get('page') == 1)
        <div class="text-center py-4 text-muted">
            <i class="fa-solid fa-box-open fs-1 text-muted mb-2 d-block"></i>
            No real sales orders found for the selected date / filters.
        </div>
    @endif
@endforelse
