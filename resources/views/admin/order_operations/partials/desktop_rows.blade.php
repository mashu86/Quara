@foreach($orders as $order)
    @php
        $opsCount = $order->operations->count();
        $activeOpsCount = $order->operations->where('status', 'active')->count();
        $canAdjustOrder = ($order->order_status !== 'pending' && $order->order_status !== 'cancelled')
            && ($order->payment_status === 'paid' || $order->payment_status === 'offline_sale' || $order->payment_method === 'offline_sale' || $order->payment_method === 'manual' || $opsCount > 0);
    @endphp
    <tr>
        <td class="ps-3">
            <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-warning text-decoration-none">
                {{ $order->order_number }}
            </a>
            <div class="small text-muted" style="font-size: 0.7rem;">{{ ($order->sale_date ?? $order->created_at)->format('d-m-Y') }}</div>
        </td>
        <td>
            <div class="fw-bold text-dark">{{ $order->customer_name }}</div>
            @if($order->customer_phone)
                <a href="tel:{{ $order->customer_phone }}" class="small text-muted text-decoration-none" style="font-size: 0.7rem;">
                    <i class="fa-solid fa-phone text-success me-1"></i>{{ $order->customer_phone }}
                </a>
            @endif
        </td>
        <td>
            @foreach($order->items as $item)
                @php
                    $itemProd = $item->product;
                    $itemImg = $itemProd ? $itemProd->primary_image_url : \App\Models\Setting::logoUrl();
                @endphp
                <div class="d-flex align-items-center gap-2 my-1">
                    <div class="p-0.5 bg-white border rounded flex-shrink-0" style="cursor: pointer;" onclick="openImagePreviewModal('{{ addslashes($itemImg) }}', '{{ addslashes($item->product_name) }}')" title="Click to view image">
                        <img src="{{ $itemImg }}" alt="{{ $item->product_name }}" 
                             class="rounded d-block" 
                             style="width: 36px; height: 44px; object-fit: cover;">
                    </div>
                    <div class="overflow-hidden">
                        <div class="fw-bold text-dark text-truncate" style="max-width: 190px;" title="{{ $item->product_name }}">{{ $item->product_name }}</div>
                        <div class="small text-muted" style="font-size: 0.7rem;">
                            <span class="badge bg-secondary" style="font-size: 0.62rem;">Size: {{ $item->size }}</span> &times; {{ $item->quantity }}
                        </div>
                    </div>
                </div>
            @endforeach
        </td>
        <td>
            <div class="fw-bold text-dark">₹{{ number_format($order->grand_total, 2) }}</div>
            <div class="small text-muted" style="font-size: 0.68rem;">Delivery: ₹{{ number_format($order->shipping, 2) }}</div>
        </td>
        <td>
            <span class="badge bg-{{ $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'cancelled' ? 'danger' : 'warning') }} text-capitalize">
                {{ $order->order_status }}
            </span>
        </td>
        <td>
            @if($opsCount > 0)
                <div>
                    @foreach($order->operations as $op)
                        <div class="mb-1 text-nowrap">
                            <span class="badge bg-{{ $op->status === 'active' ? 'success' : 'secondary' }} text-uppercase" style="font-size: 0.62rem;">
                                {{ $op->status }}
                            </span>
                            <span class="fw-semibold ms-1" style="font-size: 0.72rem;">{{ $op->operation_type_label }}</span>
                            @if($op->total_financial_adjustment > 0 && $op->status === 'active')
                                <span class="text-danger fw-bold ms-1" style="font-size: 0.7rem;">(-₹{{ number_format($op->total_financial_adjustment, 2) }})</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <span class="badge bg-light text-muted border">No Adjustments</span>
            @endif
        </td>
        <td class="text-end pe-3">
            <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                @if($canAdjustOrder)
                    <a href="{{ route('admin.order-operations.create', $order->id) }}" 
                       class="btn btn-sm btn-warning text-dark fw-bold rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 28px; height: 28px; background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Adjust Order">
                        <i class="fa-solid fa-sliders" style="font-size: 0.75rem;"></i>
                    </a>
                @endif
                @if($opsCount === 1)
                    @php $singleOp = $order->operations->first(); @endphp
                    <a href="{{ route('admin.order-operations.show', $singleOp->id) }}" 
                       class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 28px; height: 28px;" title="View Adjustment Details">
                        <i class="fa-solid fa-eye" style="font-size: 0.7rem;"></i>
                    </a>
                    <a href="{{ route('admin.order-operations.edit', $singleOp->id) }}" 
                       class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 28px; height: 28px;" title="Edit Adjustment">
                        <i class="fa-solid fa-pen-to-square" style="font-size: 0.7rem;"></i>
                    </a>
                @elseif($opsCount > 1)
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-outline-dark dropdown-toggle rounded-pill px-2.5 py-1 fw-bold shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.72rem;">
                            Adjustments ({{ $opsCount }})
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2" style="min-width: 220px; font-size: 0.78rem;">
                            <li class="dropdown-header text-uppercase fw-bold text-dark border-bottom pb-1 mb-1" style="font-size: 0.68rem;">Recorded Adjustments</li>
                            @foreach($order->operations as $op)
                                <li class="mb-1">
                                    <div class="d-flex justify-content-between align-items-center p-1.5 rounded bg-light">
                                        <div class="pe-2 text-truncate" style="max-width: 130px;">
                                            <span class="badge bg-{{ $op->status === 'active' ? 'success' : 'secondary' }}" style="font-size: 0.6rem;">{{ strtoupper($op->status) }}</span>
                                            <div class="fw-bold text-dark text-truncate" title="{{ $op->operation_type_label }}">{{ $op->operation_type_label }}</div>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.order-operations.show', $op->id) }}" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" title="View"><i class="fa-solid fa-eye" style="font-size: 0.65rem;"></i></a>
                                            <a href="{{ route('admin.order-operations.edit', $op->id) }}" class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" title="Edit"><i class="fa-solid fa-pen-to-square" style="font-size: 0.65rem;"></i></a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </td>
    </tr>
@endforeach
