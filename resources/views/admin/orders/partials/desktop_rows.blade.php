@forelse($orders as $order)
    <tr>
        <td class="ps-3">
            <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-warning text-decoration-none">
                {{ $order->order_number }}
            </a>
        </td>
        <td>
            <div class="fw-bold text-dark">{{ $order->customer_name }}</div>
            <div class="small text-muted"><i class="fa-solid fa-phone me-1"></i> {{ $order->customer_phone }}</div>
        </td>
        <td class="small">{{ $order->created_at->format('M d, Y') }}<br><span class="text-muted">{{ $order->created_at->format('h:i A') }}</span></td>
        <td>
            <span class="badge bg-light text-dark border small fw-bold">
                {{ $order->items->sum('quantity') }} Pcs
            </span>
        </td>
        <td>
            @if($order->payment_method === 'offline_sale')
                <span class="badge bg-purple text-white text-uppercase me-1" style="background-color: #6f42c1;">OFFLINE</span>
            @elseif($order->payment_method === 'online')
                <span class="badge bg-info text-dark text-uppercase me-1">ONLINE</span>
            @else
                <span class="badge bg-light text-dark border text-uppercase me-1">{{ $order->payment_method }}</span>
            @endif
            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} text-capitalize">
                {{ $order->payment_status }}
            </span>
        </td>
        <td class="fw-bold">₹{{ number_format($order->grand_total, 2) }}</td>
        <td>
            <span class="badge bg-{{ $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'cancelled' ? 'danger' : 'warning') }} text-capitalize px-3 py-2">
                {{ $order->order_status }}
            </span>
            @if($order->operations && $order->operations->where('status', 'active')->count() > 0)
                <div class="mt-1">
                    <span class="badge text-white px-2 py-1" style="background-color: #6f42c1;" title="Has active return/operation">
                        <i class="fa-solid fa-rotate-left me-1"></i> Returned ({{ $order->operations->where('status', 'active')->count() }})
                    </span>
                </div>
            @endif
        </td>
        <td class="text-end pe-3">
            <div class="d-flex flex-nowrap justify-content-end align-items-center gap-2" style="gap: 8px;">
                <button type="button" onclick="openIndexEditPaymentModal({{ json_encode($order) }})" class="btn btn-sm btn-outline-primary rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;" title="Edit Payment Details">
                    <i class="fa-solid fa-credit-card fs-6"></i>
                </button>
                <button type="button" onclick="openWhatsappModal({{ json_encode($order) }})" class="btn btn-sm btn-success text-white rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;" title="WhatsApp Follow-up">
                    <i class="fa-brands fa-whatsapp fs-6"></i>
                </button>
                <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-sm btn-outline-warning text-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;" title="Edit Order Details">
                    <i class="fa-solid fa-pen-to-square fs-6"></i>
                </a>
                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;" title="View Details">
                    <i class="fa-solid fa-eye fs-6"></i>
                </a>
                <a href="{{ route('admin.order-operations.create', $order->id) }}" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;" title="Order Operation / Return">
                    <i class="fa-solid fa-rotate-left fs-6"></i>
                </a>
                <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-warning text-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px; background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Print Invoice">
                    <i class="fa-solid fa-print fs-6"></i>
                </a>
            </div>
        </td>
    </tr>
@empty
    @if(!request()->get('page') || request()->get('page') == 1)
        <tr>
            <td colspan="8" class="text-center py-5 text-muted">
                <i class="fa-solid fa-box-open fs-1 text-muted mb-2 d-block"></i>
                No real sales orders found for the selected date / filters.
            </td>
        </tr>
    @endif
@endforelse
