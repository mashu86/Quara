@extends('layouts.app')

@section('title', 'My Orders - QUARA WALDROP')

@section('content')
<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="font-serif fw-bold mb-1">My Orders & Purchase History</h2>
            <p class="text-muted small mb-0">
                Viewing past orders linked to Phone: 
                <strong class="text-dark">+91 {{ $phone ?? 'Not Verified' }}</strong>
            </p>
        </div>

        <div class="mt-3 mt-md-0 d-flex gap-2">
            @if(session('customer_phone'))
                <form action="{{ route('customer.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger rounded-pill btn-sm px-3 fw-bold">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> Logout Phone Session
                    </button>
                </form>
            @else
                <button type="button" onclick="showOtpModal()" class="btn btn-warning rounded-pill btn-sm px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-mobile-screen-button me-1"></i> Verify Phone to View Orders
                </button>
            @endif
        </div>
    </div>

    @if(!$phone)
        <!-- Prompt to Verify Phone -->
        <div class="card border-0 rounded-4 shadow-sm text-center py-5">
            <div class="card-body p-4">
                <i class="fa-solid fa-shield-cat text-warning display-4 mb-3"></i>
                <h4 class="font-serif fw-bold">Enter Mobile Number to View Orders</h4>
                <p class="text-muted col-md-6 mx-auto mb-4">
                    നിങ്ങളുടെ മുൻപത്തെ ഓർഡറുകളും തത്സമയ ട്രാക്കിംഗ് വിവരങ്ങളും കാണാൻ മൊബൈൽ നമ്പർ വെരിഫൈ ചെയ്യുക.
                </p>
                <button type="button" onclick="showOtpModal()" class="btn btn-dark rounded-pill px-5 py-3 fw-bold shadow">
                    VERIFY PHONE NUMBER NOW <i class="fa-solid fa-arrow-right ms-2"></i>
                </button>
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
                        <h5 class="fw-bold">No orders found for this phone number.</h5>
                        <p class="text-muted small">You haven't placed any orders yet with +91 {{ $phone }}.</p>
                        <a href="{{ route('shop') }}" class="btn btn-gold rounded-pill px-4 fw-bold mt-2">SHOP NEW ARRIVALS</a>
                    </div>
                </div>
            @endforelse
        </div>
    @endif
</div>

<!-- Include Reusable Phone OTP Modal -->
@include('frontend.partials.phone_otp_modal')
@endsection
