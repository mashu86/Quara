@extends('layouts.app')

@section('title', 'Order Confirmation - ' . $siteName)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border text-center">
                @if($order->payment_status === 'paid' || $order->payment_method === 'cod')
                    <div class="mb-4">
                        <span class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 40px;">
                            <i class="fa-solid fa-check"></i>
                        </span>
                    </div>

                    <h1 class="font-serif fw-bold display-6 mb-2">THANK YOU FOR YOUR ORDER!</h1>
                    <p class="text-muted fs-5 mb-4">Your order has been placed successfully and is being prepared with care.</p>
                @elseif($order->payment_status === 'failed')
                    <div class="mb-4">
                        <span class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 40px;">
                            <i class="fa-solid fa-xmark"></i>
                        </span>
                    </div>

                    <h1 class="font-serif fw-bold display-6 mb-2 text-danger">PAYMENT UNSUCCESSFUL</h1>
                    <p class="text-muted fs-5 mb-4">The online payment for this order failed or was cancelled by the bank. No money was charged.</p>
                @else
                    <div class="mb-4">
                        <span class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 40px;">
                            <i class="fa-solid fa-clock"></i>
                        </span>
                    </div>

                    <h1 class="font-serif fw-bold display-6 mb-2">PAYMENT PENDING</h1>
                    <p class="text-muted fs-5 mb-4">Your payment is currently pending verification.</p>
                @endif

                <div class="alert alert-light border rounded-3 p-4 text-start mb-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase font-bold">Order Number</span>
                            <h5 class="fw-bold text-gold mb-0">{{ $order->order_number }}</h5>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase font-bold">Order Date</span>
                            <h6 class="fw-bold mb-0">{{ $order->created_at->format('M d, Y - h:i A') }}</h6>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase font-bold">Payment Method</span>
                            <h6 class="fw-bold text-uppercase mb-0">
                                {{ $order->payment_method }} 
                                <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : ($order->payment_status === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </h6>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase font-bold">Total Amount</span>
                            <h5 class="fw-bold text-gold mb-0">₹{{ number_format($order->grand_total, 2) }}</h5>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address Summary -->
                <div class="card border-0 bg-light rounded-3 p-3 text-start mb-4">
                    <h6 class="font-serif fw-bold mb-2"><i class="fa-solid fa-location-dot text-gold me-2"></i> Shipping To:</h6>
                    <p class="mb-1 fw-bold">{{ $order->customer_name }} ({{ $order->customer_phone }})</p>
                    <p class="mb-0 text-muted small">
                        {{ $order->house_building }}, {{ $order->street }}, {{ $order->area }}, {{ $order->city }}, {{ $order->district }}, {{ $order->state }} - {{ $order->pin_code }}
                    </p>
                </div>

                <!-- Items Ordered Table -->
                <div class="table-responsive text-start mb-4">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Size</th>
                                <th>Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $item['product_name'] }}</div>
                                    </td>
                                    <td><span class="badge bg-dark">{{ $item['size'] }}</span></td>
                                    <td>{{ $item['quantity'] }}</td>
                                    <td class="text-end fw-bold">₹{{ number_format($item['subtotal'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="{{ route('order.tracking', ['order_number' => $order->order_number, 'phone' => $order->customer_phone]) }}" class="btn btn-qw-gold rounded-pill px-4">
                        <i class="fa-solid fa-truck-fast me-2"></i> TRACK YOUR ORDER
                    </a>
                    <a href="{{ route('shop') }}" class="btn btn-qw-outline rounded-pill px-4">
                        CONTINUE SHOPPING
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
