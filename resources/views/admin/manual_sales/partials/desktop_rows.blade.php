@foreach($manualOrders as $order)
    <tr>
        <td class="fw-bold text-warning">{{ $order->order_number }}</td>
        <td class="fw-semibold">{{ $order->customer_name }}</td>
        <td>{{ $order->customer_phone }}</td>
        <td>
            @foreach($order->items as $item)
                @php
                    $itemProd = $item->product;
                    $itemImg = $itemProd ? $itemProd->primary_image_url : \App\Models\Setting::logoUrl();
                @endphp
                <div class="d-flex align-items-center gap-2 my-1">
                    <img src="{{ $itemImg }}" alt="{{ $item->product_name }}" 
                         class="rounded border shadow-sm flex-shrink-0" 
                         style="width: 38px; height: 44px; object-fit: cover; cursor: pointer;" 
                         onclick="openImagePreviewModal('{{ addslashes($itemImg) }}', '{{ addslashes($item->product_name) }}')" 
                         title="Click to view image">
                    <div>
                        <span class="fw-bold text-dark">{{ $item->product_name }}</span> 
                        <div class="small text-muted">(Size: {{ $item->size }}) &times; {{ $item->quantity }}</div>
                    </div>
                </div>
            @endforeach
        </td>
        <td class="fw-bold text-dark">₹{{ number_format($order->grand_total, 2) }}</td>
        <td>
            <span class="badge bg-uppercase bg-{{ $order->payment_method === 'cash' ? 'success' : 'info' }} me-1">{{ $order->payment_method }}</span>
            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($order->payment_status) }}</span>
        </td>
        <td class="small text-muted" title="Recorded: {{ $order->created_at->format('d-m-Y h:i A') }}">
            <i class="fa-regular fa-calendar-check me-1 text-warning"></i>{{ ($order->sale_date ?? $order->created_at)->format('d-m-Y') }}
        </td>
        <td class="text-end pe-3">
            <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                <a href="{{ route('admin.order-operations.create', $order->id) }}" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Record Operation / Return">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
                <a href="{{ route('admin.manual-sales.edit', $order->id) }}" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Edit Offline Sale">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>
            </div>
        </td>
    </tr>
@endforeach
