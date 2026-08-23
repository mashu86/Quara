@extends('layouts.app')

@section('title', 'Checkout - QUARA WALDROP')

@section('content')
<div class="container py-4">
    <h2 class="font-serif fw-bold display-6 mb-4"><i class="fa-solid fa-lock text-gold me-2"></i> SECURE CHECKOUT</h2>

    <form action="{{ route('checkout.process') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- Customer Shipping Address -->
            <div class="col-lg-7">
                <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
                    <h5 class="font-serif fw-bold mb-3 pb-2 border-bottom"><i class="fa-solid fa-truck me-2 text-gold"></i> Shipping Address</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control rounded-3" value="{{ old('customer_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="email" name="customer_email" class="form-control rounded-3" placeholder="name@example.com" value="{{ old('customer_email', session('customer_email')) }}" required>
                                @if(!session('customer_email'))
                                    <button type="button" class="btn btn-outline-warning btn-sm fw-bold px-3" onclick="showOtpModal()"><i class="fa-solid fa-envelope me-1"></i> Verify OTP</button>
                                @else
                                    <span class="input-group-text bg-success text-white small fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Verified</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Mobile Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="customer_phone" class="form-control rounded-3" placeholder="10-digit mobile number" value="{{ old('customer_phone') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">House / Flat / Building No. <span class="text-danger">*</span></label>
                            <input type="text" name="house_building" class="form-control rounded-3" value="{{ old('house_building') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Street / Road Name <span class="text-danger">*</span></label>
                            <input type="text" name="street" class="form-control rounded-3" value="{{ old('street') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Area / Landmark <span class="text-danger">*</span></label>
                            <input type="text" name="area" class="form-control rounded-3" value="{{ old('area') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">City / Town <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control rounded-3" value="{{ old('city') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">District <span class="text-danger">*</span></label>
                            <input type="text" name="district" class="form-control rounded-3" value="{{ old('district') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">State <span class="text-danger">*</span></label>
                            <input type="text" name="state" class="form-control rounded-3" value="{{ old('state') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">PIN Code <span class="text-danger">*</span></label>
                            <input type="text" name="pin_code" class="form-control rounded-3" value="{{ old('pin_code') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Special Delivery Notes (Optional)</label>
                            <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="e.g. Leave with security, call before delivery">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Option Selector -->
                <div class="bg-white p-4 rounded-4 shadow-sm border">
                    <h5 class="font-serif fw-bold mb-3 pb-2 border-bottom"><i class="fa-solid fa-wallet me-2 text-gold"></i> Payment Method</h5>

                    <input type="hidden" name="payment_method" value="online">
                    <div class="p-3 rounded-3 border border-warning bg-light d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-bold fs-6 text-dark"><i class="fa-solid fa-qrcode text-warning me-2"></i> Online Payment (UPI / GPay / PhonePe / QR)</div>
                            <div class="text-muted small">Instant & 100% secure payment via Razorpay gateway</div>
                        </div>
                        <span class="badge bg-success rounded-pill px-3 py-2"><i class="fa-solid fa-shield-halved me-1"></i> Razorpay UPI</span>
                    </div>
                </div>
            </div>

            <!-- Order Review Sidebar -->
            <div class="col-lg-5">
                <div class="bg-white p-4 rounded-4 shadow-sm border sticky-top" style="top: 90px;">
                    <h5 class="font-serif fw-bold mb-3 pb-2 border-bottom">ITEMS IN ORDER</h5>

                    <div class="mb-4" style="max-height: 280px; overflow-y: auto;">
                        @foreach($cart as $item)
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="rounded-3 border" style="width: 50px; height: 65px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h6 class="font-serif fw-bold mb-0 text-truncate" style="max-width: 180px;">{{ $item['name'] }}</h6>
                                    <div class="text-muted small">Size: <span class="fw-bold text-dark">{{ $item['size'] }}</span> | Qty: {{ $item['quantity'] }}</div>
                                </div>
                                <div class="fw-bold text-gold">₹{{ number_format($item['subtotal'], 2) }}</div>
                            </div>
                        @endforeach
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold">₹{{ number_format($summary['subtotal'], 2) }}</span>
                    </div>

                    @if($summary['discount'] > 0)
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount</span>
                            <span>-₹{{ number_format($summary['discount'], 2) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Shipping Charge</span>
                        @if($summary['shipping'] > 0)
                            <span class="fw-bold text-dark">₹{{ number_format($summary['shipping'], 2) }}</span>
                        @else
                            <span class="text-success fw-semibold">FREE</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between mb-4 fs-4 fw-bold">
                        <span>Total Payable</span>
                        <span class="text-gold">₹{{ number_format($summary['grand_total'], 2) }}</span>
                    </div>

                    <button type="submit" class="btn btn-qw-gold btn-lg w-100 rounded-pill shadow-sm">
                        PLACE ORDER NOW <i class="fa-solid fa-circle-check ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@include('frontend.partials.email_otp_modal')

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(!session('customer_email'))
            showOtpModal();
        @endif
    });
</script>
@endsection
@endsection
