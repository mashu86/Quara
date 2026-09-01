@foreach($orders as $order)
    <tr>
        <td>
            <span class="fw-semibold">{{ $order->created_at->format('M d, Y') }}</span>
            <small class="d-block text-muted">{{ $order->created_at->format('h:i A') }}</small>
        </td>
        <td>
            <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-gold text-decoration-none">
                {{ $order->order_number }}
            </a>
        </td>
        <td>
            <span class="fw-semibold text-dark">{{ $order->customer_name }}</span>
            <small class="d-block text-muted">{{ $order->customer_phone }}</small>
        </td>
        <td class="fw-bold text-dark">₹{{ number_format($order->grand_total, 2) }}</td>
        <td class="text-secondary">₹{{ number_format($order->razorpay_base_fee, 2) }}</td>
        <td class="text-muted">₹{{ number_format($order->razorpay_gst_fee, 2) }}</td>
        <td class="fw-bold text-danger">₹{{ number_format($order->razorpay_total_charge, 2) }}</td>
        <td class="fw-bold text-success">₹{{ number_format($order->razorpay_net_amount, 2) }}</td>
        <td>
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-light btn-sm rounded-pill border">
                <i class="fa-solid fa-eye me-1"></i> Details
            </a>
        </td>
    </tr>
@endforeach
