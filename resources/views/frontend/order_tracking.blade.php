@extends('layouts.app')

@section('title', 'Track Order - ' . $siteName)
@section('meta_robots', 'noindex, nofollow')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <h2 class="font-serif fw-bold display-6 mb-2"><i class="fa-solid fa-truck-fast text-gold me-2"></i> TRACK YOUR ORDER</h2>
                <p class="text-muted">Enter your Order Number (e.g. QW-20260820-XXXXX) and mobile number below.</p>
            </div>

            @if(!$order)
                <!-- Order lookup is only needed when this page is opened without a valid My Orders link. -->
                <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
                    <form action="{{ route('order.tracking') }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold">Order Number</label>
                                <input type="text" name="order_number" class="form-control rounded-3" placeholder="QW-20260820-XXXXX" value="{{ request()->order_number }}" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold">Mobile Phone Number</label>
                                <input type="tel" name="phone" class="form-control rounded-3" placeholder="10-digit mobile" value="{{ request()->phone }}" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-qw-gold w-100 rounded-3">VIEW ORDER</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

            @if(request()->filled('order_number') && !$order)
                <div class="alert alert-danger text-center rounded-3 shadow-sm py-4">
                    <i class="fa-solid fa-circle-xmark fs-2 mb-2"></i>
                    <h5>No Order Found</h5>
                    <p class="mb-0 small">Please check your Order Number and Mobile Number and try again.</p>
                </div>
            @endif

            @if($order)
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center border-bottom pb-3 mb-4">
                        <div>
                            <span class="text-muted small text-uppercase font-bold">Order Details</span>
                            <h4 class="font-serif fw-bold text-gold mb-0">{{ $order->order_number }}</h4>
                        </div>
                        <div class="mt-2 mt-sm-0 text-sm-end">
                            @if($order->order_status === 'cancelled')
                                <span class="badge bg-danger fs-6 px-3 py-2 text-uppercase">Status: CANCELLED</span>
                            @elseif(filled($trackingNumber))
                                <span class="badge bg-success fs-6 px-3 py-2 text-uppercase">
                                    <i class="fa-solid fa-truck-fast me-1"></i> Product Shipped
                                </span>
                            @elseif($order->order_status === 'packed')
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2 text-uppercase">
                                    <i class="fa-solid fa-box-open me-1"></i> Packed · Ready to Ship
                                </span>
                            @elseif(!$order->is_dispatched_to_courier)
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2 text-uppercase">
                                    <i class="fa-solid fa-box-open me-1"></i> Packing Process
                                </span>
                            @else
                                <span class="badge bg-success fs-6 px-3 py-2 text-uppercase">
                                    <i class="fa-solid fa-truck-fast me-1"></i> Handed Over to Courier Partner
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($order->order_status !== 'cancelled')
                        <div class="p-3 p-md-4 rounded-4 border mb-4">
                            @if(filled($trackingNumber))
                                <div class="alert alert-success mb-3" role="status">
                                    <i class="fa-solid fa-circle-check me-2" aria-hidden="true"></i>Your product has been shipped. Check your order status through India Post.
                                </div>
                                <label for="india-post-consignment" class="form-label small fw-bold">India Post Tracking Number</label>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md">
                                        <div class="input-group">
                                            <input id="india-post-consignment" type="text" class="form-control font-monospace" value="{{ trim((string) $trackingNumber) }}" readonly>
                                            <button id="copy-india-post-consignment" type="button" class="btn btn-outline-dark" aria-label="Copy tracking number" title="Copy tracking number">
                                                <i class="fa-regular fa-copy" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-auto">
                                        <a href="https://www.indiapost.gov.in/" target="_blank" rel="noopener noreferrer" class="btn btn-qw-gold rounded-pill px-4 w-100">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-2" aria-hidden="true"></i> Check Your Product Tracking
                                            <span class="visually-hidden">(opens in a new tab)</span>
                                        </a>
                                    </div>
                                </div>
                                <p id="india-post-copy-status" class="small text-muted mt-2 mb-0" role="status" aria-live="polite">India Post opens in a new tab. Paste the copied number there to view tracking updates.</p>
                            @else
                                @if($trackingDetailsAvailable)
                                    <div class="alert alert-warning mb-0" role="status">
                                        <div class="fw-bold"><i class="fa-solid fa-boxes-packing me-2" aria-hidden="true"></i>Your product is being prepared for shipping.</div>
                                        <p class="small mt-1 mb-3">The tracking number will be available soon and will appear here.</p>
                                        <a href="https://www.indiapost.gov.in/" target="_blank" rel="noopener noreferrer" class="btn btn-qw-gold rounded-pill px-4">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-2" aria-hidden="true"></i> Check Your Product Tracking
                                            <span class="visually-hidden">(opens in a new tab)</span>
                                        </a>
                                    </div>
                                @else
                                    <div class="alert alert-danger mb-0" role="status">
                                        <div class="fw-bold"><i class="fa-solid fa-circle-exclamation me-2" aria-hidden="true"></i>Tracking number is not available for orders placed on or before October 17, 2026.</div>
                                        <p class="small mb-0 mt-1">Please contact us if you need help with this order.</p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif

                    <div class="row g-4 mt-4">
                        <div class="col-sm-6">
                            <div class="bg-light p-3 rounded-3">
                                <h6 class="font-serif fw-bold mb-2">Delivery Address</h6>
                                <p class="mb-1 fw-bold small">{{ $order->customer_name }}</p>
                                <p class="mb-0 text-muted small">
                                    {{ $order->house_building }}, {{ $order->street }}, {{ $order->area }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pin_code }}
                                </p>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="bg-light p-3 rounded-3">
                                <h6 class="font-serif fw-bold mb-2">Payment Summary</h6>
                                <p class="mb-1 small">Method: <strong class="text-uppercase">{{ $order->payment_method }}</strong></p>
                                <p class="mb-1 small">Status: <strong class="text-uppercase text-success">{{ $order->payment_status }}</strong></p>
                                <p class="mb-0 small">Grand Total: <strong class="text-gold fs-6">₹{{ number_format($order->grand_total, 2) }}</strong></p>
                            @include('partials.district_offer_summary')
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('copy-india-post-consignment')?.addEventListener('click', async function () {
    const input = document.getElementById('india-post-consignment');
    const status = document.getElementById('india-post-copy-status');
    try {
        if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
        await navigator.clipboard.writeText(input.value);
        status.textContent = 'Tracking number copied. Open India Post in the new tab and paste it to check your product tracking.';
    } catch (error) {
        input.focus();
        input.select();
        input.setSelectionRange(0, input.value.length);
        status.textContent = 'Select Copy from your device menu, then paste this number on the India Post website.';
    }
});
</script>
@endsection
