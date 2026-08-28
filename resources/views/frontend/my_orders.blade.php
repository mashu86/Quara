@extends('layouts.app')

@section('title', 'My Orders - ' . $siteName)
@section('meta_robots', 'noindex, nofollow')

@section('content')
<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-center text-md-start mb-4 pb-2 border-bottom">
        <div>
            <h3 class="font-serif fw-bold mb-1 fs-4">My Orders & History</h3>
            <p class="text-muted small mb-0">
                Viewing past orders linked to: 
                <strong class="text-dark">{{ $email ?: ($phone ? ('Phone: ' . $phone) : 'Not Verified') }}</strong>
            </p>
        </div>

        <div class="mt-3 mt-md-0 d-flex justify-content-center gap-2">
            @if(session('customer_email') || session('customer_phone'))
                <form action="{{ route('customer.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger rounded-pill btn-sm px-3 fw-bold">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> Logout Session
                    </button>
                </form>
            @else
                <button type="button" onclick="showOtpModal()" class="btn btn-warning rounded-pill btn-sm px-3 fw-bold shadow-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold); color: #fff;">
                    <i class="fa-solid fa-envelope me-1"></i> Verify Email to View Orders
                </button>
            @endif
        </div>
    </div>

    @if(!$email && !$phone)
        <!-- Prompt / Quick Lookup by Mobile or Email -->
        <div class="card border-0 rounded-4 shadow-sm text-center py-4 py-md-5">
            <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center">
                <i class="fa-solid fa-boxes-packing text-warning display-4 mb-3"></i>
                <h5 class="font-serif fw-bold mb-2 text-center">Find Your Past Orders</h5>
                <p class="text-muted small col-md-7 mx-auto mb-4 text-center">
                    Enter your 10-digit Mobile Phone Number or Email Address below to view all your past orders and live tracking updates.
                </p>

                <form action="{{ route('customer.my-orders') }}" method="GET" class="col-md-6 col-lg-5 mx-auto mb-3">
                    <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                        <input type="text" name="contact" class="form-control border-0 px-4 fs-6" placeholder="Mobile Number or Email..." required>
                        <button type="submit" class="btn btn-qw-gold px-4 fw-bold">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> VIEW ORDERS
                        </button>
                    </div>
                </form>

                <div class="d-flex align-items-center justify-content-center gap-2 text-muted small mt-2">
                    <span>Or prefer OTP email verification?</span>
                    <button type="button" onclick="showOtpModal()" class="btn btn-link text-gold p-0 text-decoration-none fw-bold">
                        Verify Email with OTP <i class="fa-solid fa-arrow-right small"></i>
                    </button>
                </div>
            </div>
        </div>
    @else
        <!-- Orders List -->
        <div class="row g-4">
            @forelse($orders as $order)
                <div class="col-12">
                    <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
                        <div class="card-header bg-light border-0 p-3 p-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <span class="badge bg-gold text-dark fw-bold me-2 mb-1">Order #{{ $order->order_number }}</span>
                                <span class="text-muted small d-block d-md-inline-block">Placed on {{ $order->created_at->format('M d, Y h:i A') }}</span>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-warning text-dark',
                                        'confirmed' => 'bg-info text-dark',
                                        'processing' => 'bg-primary text-white',
                                        'packed' => 'bg-secondary text-white',
                                        'shipped' => 'bg-info text-dark',
                                        'delivered' => 'bg-success text-white',
                                        'cancelled' => 'bg-danger text-white',
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$order->order_status] ?? 'bg-dark' }} px-3 py-2 rounded-pill font-bold">
                                    <i class="fa-solid fa-truck-fast me-1"></i> {{ strtoupper($order->order_status) }}
                                </span>
                                <span class="badge bg-outline-dark border text-dark px-3 py-2 rounded-pill small">
                                    {{ strtoupper($order->payment_method) }} ({{ strtoupper($order->payment_status) }})
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-3 p-md-4">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($order->items as $item)
                                            <div class="d-flex align-items-center gap-3">
                                                @if($item->product && $item->product->primary_image_url)
                                                    <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product_name }}" class="rounded-3 border" style="width: 54px; height: 54px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center border" style="width: 54px; height: 54px;">
                                                        <i class="fa-solid fa-shirt text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark">{{ $item->product_name }}</h6>
                                                    <span class="small text-muted">Size: <strong class="text-dark">{{ $item->size }}</strong> &bull; Qty: {{ $item->quantity }} &bull; Price: ₹{{ number_format($item->final_unit_price, 2) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="col-md-4 text-md-end border-top border-md-0 pt-3 pt-md-0">
                                    <div class="text-muted small mb-1">Total Paid Amount</div>
                                    <div class="fs-4 fw-bold text-gold mb-3">₹{{ number_format($order->grand_total, 2) }}</div>
                                    <a href="{{ route('order.tracking', ['order_number' => $order->order_number, 'phone' => $order->customer_phone]) }}" class="btn btn-dark rounded-pill btn-sm px-4 fw-bold">
                                        <i class="fa-solid fa-location-dot me-1"></i> Track Live Status
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="p-5 bg-white rounded-4 shadow-sm border col-md-6 mx-auto">
                        <i class="fa-solid fa-box-open text-muted fs-1 mb-3"></i>
                        <h5 class="fw-bold">No orders found for this Email address.</h5>
                        <p class="text-muted small">You haven't placed any orders yet with {{ $email }}.</p>
                        <a href="{{ route('shop') }}" class="btn btn-gold rounded-pill px-4 fw-bold mt-2">SHOP NEW ARRIVALS</a>
                    </div>
                </div>
            @endforelse
        </div>
    @endif
</div>

<!-- Include Reusable Email OTP Modal -->
@include('frontend.partials.email_otp_modal')
@endsection
