@extends('layouts.app')

@section('title', 'My Orders - ' . $siteName)
@section('meta_robots', 'noindex, nofollow')

@section('styles')
<style>
    #myOrderImageModal .modal-dialog { max-width: min(900px, calc(100vw - 2rem)); }
    #myOrderImageModal .modal-content { max-height: calc(100dvh - 2rem); }
    #myOrderImageModal .modal-body { min-height: 0; display: flex; align-items: center; justify-content: center; overflow: auto; overscroll-behavior: contain; }
    #myOrderImageModalImage { display: block; max-width: 100%; max-height: calc(100dvh - 8rem); width: auto; height: auto; object-fit: contain; margin: 0 auto; }
    .my-order-product-image { cursor: zoom-in; }
    @media (max-width: 575.98px) {
        #myOrderImageModal .modal-dialog { width: 100%; max-width: none; height: 100dvh; margin: 0; }
        #myOrderImageModal .modal-content { height: 100%; max-height: 100%; border-radius: 0 !important; }
        #myOrderImageModal .modal-body { min-height: 0; flex: 1 1 auto; padding: .5rem !important; overflow: auto; overscroll-behavior: contain; }
        #myOrderImageModalImage { flex: 0 0 auto; width: auto; max-width: 100%; height: auto; max-height: calc(100dvh - 4.5rem); object-fit: contain; object-position: center; }
    }
</style>
@endsection

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
                                                    <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product_name }}" class="rounded-3 border my-order-product-image" style="width: 54px; height: 54px; object-fit: cover;" role="button" tabindex="0" data-order-image="{{ $item->product->primary_image_url }}" data-order-image-alt="{{ $item->product_name }}" aria-label="View image of {{ $item->product_name }}">
                                                @else
                                                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center border" style="width: 54px; height: 54px;">
                                                        <i class="fa-solid fa-shirt text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark">
                                                        {{ $item->product_name }}
                                                        @if($item->is_combo_offer)
                                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 0.68rem;">👑 Offer Combo Sale</span>
                                                        @endif
                                                    </h6>
                                                    <span class="small text-muted">Size: <strong class="text-dark">{{ $item->size }}</strong> &bull; Qty: {{ $item->quantity }}@if(!$item->is_combo_offer) &bull; Price: ₹{{ number_format($item->final_unit_price, 2) }}@endif</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="col-md-4 text-md-end border-top border-md-0 pt-3 pt-md-0">
                                    <div class="text-muted small mb-1">Total Paid Amount</div>
                                    <div class="fs-4 fw-bold text-gold mb-3">₹{{ number_format($order->grand_total, 2) }}</div>
                            @include('partials.district_offer_summary')
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

<div class="modal fade" id="myOrderImageModal" tabindex="-1" aria-labelledby="myOrderImageModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
            <div class="modal-header py-2">
                <h2 class="modal-title fs-6 fw-bold text-truncate" id="myOrderImageModalTitle">Product image</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="myOrderImageModalImage" src="" alt="Product image">
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const modalElement = document.getElementById('myOrderImageModal');
        const previewImage = document.getElementById('myOrderImageModalImage');
        const title = document.getElementById('myOrderImageModalTitle');
        if (!modalElement || !window.bootstrap?.Modal) return;
        const modalBody = modalElement.querySelector('.modal-body');

        modalElement.addEventListener('shown.bs.modal', () => {
            // Reset any previous image's scroll position when opening on mobile.
            if (modalBody) modalBody.scrollTop = 0;
        });

        const openPreview = (thumbnail) => {
            previewImage.src = thumbnail.dataset.orderImage;
            previewImage.alt = thumbnail.dataset.orderImageAlt || 'Product image';
            title.textContent = previewImage.alt;
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        };

        document.querySelectorAll('[data-order-image]').forEach(thumbnail => {
            thumbnail.addEventListener('click', () => openPreview(thumbnail));
            thumbnail.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openPreview(thumbnail);
                }
            });
        });
    })();
</script>

<!-- Include Reusable Email OTP Modal -->
@include('frontend.partials.email_otp_modal')
@endsection
