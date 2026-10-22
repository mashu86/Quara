@extends('layouts.app')

@section('title', 'My Orders - ' . $siteName)
@section('meta_robots', 'noindex, nofollow')

@section('styles')
<style>
    .my-orders-page { max-width: 1120px; }
    .my-orders-heading { border-bottom: 1px solid #eee8d8; }
    .my-orders-icon { width: 46px; height: 46px; border-radius: 15px; background: rgba(212, 175, 55, .13); }
    .my-order-card { border: 1.5px solid var(--qw-gold) !important; box-shadow: 0 6px 20px rgba(31, 27, 17, .055) !important; transition: transform .18s ease, box-shadow .18s ease; }
    .my-order-card::before { content: ""; display: block; height: 3px; background: linear-gradient(90deg, var(--qw-gold-dark), var(--qw-gold-light), var(--qw-gold)); }
    .my-order-card:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(31, 27, 17, .09) !important; }
    .my-order-card-returned { border: 1.5px solid #dc3545 !important; }
    .my-order-card-returned::before { background: linear-gradient(90deg, #dc3545, #f4a5ad); }
    .my-order-card-header { background: linear-gradient(110deg, #fffdf7 0%, #faf8f1 100%); border-bottom: 1px solid #f0ecdf !important; }
    .my-order-number { background: #fff; border: 1px solid var(--qw-gold); color: #4b3c12; }
    .my-order-status { font-size: .69rem; letter-spacing: .035em; }
    .my-order-item { padding: 12px; border: 1px solid #f0eee8; border-radius: 14px; background: #fff; }
    .my-order-summary { height: 100%; padding: 16px; border: 1px solid #eee9dc; border-radius: 16px; background: #fcfbf7; }
    .my-order-tracking { border-color: #ebe5d6 !important; background: #fff; }
    #myOrderImageModal .modal-dialog { max-width: min(900px, calc(100vw - 2rem)); }
    #myOrderImageModal .modal-content { max-height: calc(100dvh - 2rem); }
    #myOrderImageModal .modal-body { min-height: 0; display: flex; align-items: center; justify-content: center; overflow: auto; overscroll-behavior: contain; }
    #myOrderImageModalImage { display: block; max-width: 100%; max-height: calc(100dvh - 8rem); width: auto; height: auto; object-fit: contain; margin: 0 auto; }
    .my-order-product-image { cursor: zoom-in; }
    @media (max-width: 575.98px) {
        #myOrderImageModal .modal-dialog { width: 100%; max-width: none; min-height: 100dvh; height: auto; margin: 0; }
        #myOrderImageModal .modal-content { min-height: 100dvh; height: auto; max-height: none; border-radius: 0 !important; }
        #myOrderImageModal .modal-body { min-height: 0; flex: 1 0 auto; padding: .5rem !important; overflow-x: hidden; overflow-y: auto; overscroll-behavior-y: contain; }
        #myOrderImageModalImage { display: block; flex: 0 0 auto; width: auto; max-width: 100%; height: auto; max-height: none; object-fit: unset; object-position: center; margin: 0 auto; }
    }
</style>
@endsection

@section('content')
<div class="container py-5 my-orders-page">
    <div class="my-orders-heading mb-4 pb-3">
        <div class="d-flex align-items-center gap-3">
            <span class="my-orders-icon d-inline-flex align-items-center justify-content-center flex-shrink-0">
                <i class="fa-solid fa-bag-shopping text-gold fs-5" aria-hidden="true"></i>
            </span>
            <div>
                <h3 class="font-serif fw-bold mb-1 fs-4">My Orders</h3>
                <p class="text-muted small mb-0">View your purchases and track each order.</p>
            </div>
        </div>
    </div>

    @if(!$isCustomer && $orders->isEmpty())
        <!-- Guest visitors can only see orders stored in their current session. -->
        <div class="card border rounded-4 shadow-sm text-center py-4 py-md-5" style="border-color: #eee9dc !important;">
            <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center">
                <i class="fa-solid fa-boxes-packing text-warning display-4 mb-3"></i>
                <h5 class="font-serif fw-bold mb-2 text-center">View Your Orders</h5>
                <p class="text-muted small col-md-7 mx-auto mb-4 text-center">
                    Sign in to see orders linked to your account. Orders from your current guest session will also be linked after email verification.
                </p>
                <button type="button" onclick="showCustomerLoginModal()" class="btn btn-qw-gold rounded-pill px-4 fw-bold">SIGN IN TO VIEW ORDERS</button>
            </div>
        </div>
    @else
        <!-- Orders List -->
        <div class="row g-4">
            @forelse($orders as $order)
                <div class="col-12">
                    @php
                        $hasReturn = $order->items->contains(fn ($item) => $item->item_status === 'returned')
                            || $order->operations->contains(fn ($operation) => $operation->status === 'active' && in_array($operation->operation_type, ['product_returned', 'customer_return'], true));
                        $trackingNumber = collect([
                            $order->shipment?->article_number,
                            $order->shipment?->tracking_number,
                            $order->tracking_number,
                        ])->first(fn ($number) => filled(trim((string) $number)));
                    @endphp
                    <div class="card my-order-card rounded-4 overflow-hidden {{ $hasReturn ? 'my-order-card-returned' : '' }}">
                        <div class="card-header my-order-card-header p-3 p-md-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <span class="badge my-order-number fw-bold rounded-pill px-3 py-2 me-1 mb-1"><i class="fa-solid fa-receipt me-1" aria-hidden="true"></i>Order #{{ $order->order_number }}</span>
                                <span class="text-muted small d-block d-md-inline-block mt-1 mt-md-0"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>{{ $order->created_at->format('M d, Y') }}</span>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-warning text-dark',
                                        'confirmed' => 'bg-info text-dark',
                                        'processing' => 'bg-primary text-white',
                                        'packed' => 'bg-transparent border text-secondary',
                                        'shipped' => 'bg-info text-dark',
                                        'delivered' => 'bg-success text-white',
                                        'cancelled' => 'bg-danger text-white',
                                    ];
                                    $statusIcons = [
                                        'pending' => 'fa-clock',
                                        'confirmed' => 'fa-circle-check',
                                        'processing' => 'fa-gear',
                                        'packed' => 'fa-box',
                                        'shipped' => 'fa-truck-fast',
                                        'delivered' => 'fa-box-open',
                                        'cancelled' => 'fa-ban',
                                    ];
                                @endphp
                                <span class="badge my-order-status {{ $statusColors[$order->order_status] ?? 'bg-dark' }} px-3 py-2 rounded-pill font-bold">
                                    <i class="fa-solid {{ $statusIcons[$order->order_status] ?? 'fa-circle-info' }} me-1" aria-hidden="true"></i> {{ strtoupper($order->order_status) }}
                                </span>
                                <span class="badge bg-white border text-secondary px-3 py-2 rounded-pill small">
                                    {{ strtoupper($order->payment_method) }} ({{ strtoupper($order->payment_status) }})
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-3 p-md-4">
                            <div class="row g-3 align-items-stretch">
                                <div class="col-md-8">
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($order->items as $item)
                                            <div class="my-order-item d-flex align-items-center gap-3">
                                                @if($item->product && $item->product->primary_image_url)
                                                    <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product_name }}" class="rounded-3 border my-order-product-image" style="width: 54px; height: 54px; object-fit: cover;" role="button" tabindex="0" data-order-image="{{ $item->product->primary_image_url }}" data-order-image-alt="{{ $item->product_name }}" aria-label="View image of {{ $item->product_name }}">
                                                @else
                                                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center border" style="width: 54px; height: 54px;">
                                                        <i class="fa-solid fa-shirt text-muted"></i>
                                                    </div>
                                                @endif
                                                <div class="flex-grow-1" style="min-width: 0;">
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

                                <div class="col-md-4">
                                    <div class="my-order-summary text-md-end">
                                        <div class="text-muted small mb-1">Order Total</div>
                                        <div class="fs-4 fw-bold text-dark mb-3">₹{{ number_format($order->grand_total, 2) }}</div>
                                        @include('partials.district_offer_summary')
                                        @if(filled($trackingNumber))
                                            <div class="my-order-tracking d-flex align-items-center gap-3 border rounded-3 shadow-sm py-2 px-3 mb-3 text-start" role="status">
                                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; background: rgba(212, 175, 55, .12);">
                                                    <i class="fa-solid fa-barcode text-gold" aria-hidden="true"></i>
                                                </span>
                                                <span class="d-block" style="min-width: 0;">
                                                    <span class="small fw-semibold d-block text-muted text-uppercase" style="letter-spacing: .04em;">Tracking ID</span>
                                                    <span class="font-monospace fw-semibold text-dark text-break">{{ trim((string) $trackingNumber) }}</span>
                                                </span>
                                            </div>
                                        @endif
                                        <a href="{{ route('order.tracking', ['order_number' => $order->order_number, 'phone' => $order->customer_phone]) }}" class="btn btn-qw-gold rounded-pill btn-sm px-4 py-2 fw-bold w-100">
                                            <i class="fa-solid fa-location-dot me-1"></i> Track Your Order
                                        </a>
                                    </div>
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
                        <p class="text-muted small">You haven't placed any orders with this account yet.</p>
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

@endsection
