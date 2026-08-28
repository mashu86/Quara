@extends('layouts.app')

@section('title', 'Checkout - ' . $siteName)
@section('meta_robots', 'noindex, nofollow')

@section('content')
<div class="container py-4">
    <h5 class="font-serif fw-bold fs-5 mb-3"><i class="fa-solid fa-lock text-gold me-2"></i> SECURE CHECKOUT</h5>

    <form action="{{ route('checkout.process') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- Customer Shipping Address -->
            <div class="col-lg-7">
                <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
                    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-1 mb-3 pb-2 border-bottom">
                        <h5 class="font-serif fw-bold mb-0"><i class="fa-solid fa-truck me-2 text-gold"></i> Shipping Address</h5>
                        <span class="badge bg-light text-muted border rounded-pill px-3 py-2 small fw-normal"><i class="fa-solid fa-user-check me-1 text-success"></i> Guest Checkout Enabled</span>
                    </div>

                    <div class="row g-3">
                        <div id="autofillBadgeContainer" class="col-12" style="display: {{ $lastOrder ? 'block' : 'none' }};">
                            <div class="alert alert-success border-0 rounded-3 small py-2 px-3 d-flex align-items-center justify-content-between mb-0">
                                <div>
                                    <i class="fa-solid fa-wand-magic-sparkles me-2 text-gold"></i>
                                    <strong>Saved address autofilled from previous order!</strong> You can edit any field below.
                                </div>
                                <span class="badge bg-white text-success border fw-normal">Editable</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control rounded-3" value="{{ old('customer_name', $lastOrder?->customer_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="customer_email" id="checkout_customer_email" class="form-control rounded-3" placeholder="name@example.com" value="{{ old('customer_email', session('customer_email')) }}" required>
                            <div class="form-text small text-muted">
                                <i class="fa-solid fa-sparkles text-gold me-1"></i> Typing your email automatically fetches saved delivery address from past orders.
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Mobile Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="customer_phone" class="form-control rounded-3" placeholder="10-digit mobile number" value="{{ old('customer_phone', $lastOrder?->customer_phone) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">House / Flat / Building No. <span class="text-danger">*</span></label>
                            <input type="text" name="house_building" class="form-control rounded-3" value="{{ old('house_building', $lastOrder?->house_building) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Street / Road Name <span class="text-danger">*</span></label>
                            <input type="text" name="street" class="form-control rounded-3" value="{{ old('street', $lastOrder?->street) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Area / Landmark <span class="text-danger">*</span></label>
                            <input type="text" name="area" class="form-control rounded-3" value="{{ old('area', $lastOrder?->area) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">City / Town <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control rounded-3" value="{{ old('city', $lastOrder?->city) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">District <span class="text-danger">*</span></label>
                            <input type="text" name="district" class="form-control rounded-3" value="{{ old('district', $lastOrder?->district) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">State <span class="text-danger">*</span></label>
                            <input type="text" name="state" class="form-control rounded-3" value="{{ old('state', $lastOrder?->state) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">PIN Code <span class="text-danger">*</span></label>
                            <input type="text" name="pin_code" class="form-control rounded-3" value="{{ old('pin_code', $lastOrder?->pin_code) }}" required>
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
                    <div class="p-3 rounded-3 border border-warning bg-light d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
                        <div>
                            <div class="fw-bold fs-6 text-dark"><i class="fa-solid fa-credit-card text-warning me-2"></i> Online Payment</div>
                            <div class="text-muted small">Pay securely using UPI, cards, netbanking or wallets via Razorpay</div>
                        </div>
                        <span class="badge bg-success rounded-pill px-3 py-2 mt-1 mt-sm-0"><i class="fa-solid fa-shield-halved me-1"></i> Razorpay Secure</span>
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

                    <button type="submit" class="btn btn-qw-gold btn-sm w-100 rounded-pill shadow-sm py-1-5 fw-bold" style="font-size: 0.82rem; padding-top: 7px; padding-bottom: 7px;">
                        PLACE ORDER NOW <i class="fa-solid fa-circle-check ms-1"></i>
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
        const emailInput = document.getElementById('checkout_customer_email');
        if (!emailInput) return;

        let debounceTimer;

        function autoFetchAddressByEmail() {
            const email = emailInput.value.trim();
            if (!email || !email.includes('@') || email.length < 5) return;

            fetch("{{ route('checkout.fetch_address') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ email: email })
            })
            .then(res => res.json())
            .then(data => {
                if (data.found && data.details) {
                    const d = data.details;
                    const nameInput = document.querySelector('input[name="customer_name"]');
                    const phoneInput = document.querySelector('input[name="customer_phone"]');
                    const houseInput = document.querySelector('input[name="house_building"]');
                    const streetInput = document.querySelector('input[name="street"]');
                    const areaInput = document.querySelector('input[name="area"]');
                    const cityInput = document.querySelector('input[name="city"]');
                    const districtInput = document.querySelector('input[name="district"]');
                    const stateInput = document.querySelector('input[name="state"]');
                    const pinInput = document.querySelector('input[name="pin_code"]');

                    if (nameInput) nameInput.value = d.customer_name || nameInput.value || '';
                    if (phoneInput) phoneInput.value = d.customer_phone || phoneInput.value || '';
                    if (houseInput) houseInput.value = d.house_building || houseInput.value || '';
                    if (streetInput) streetInput.value = d.street || streetInput.value || '';
                    if (areaInput) areaInput.value = d.area || areaInput.value || '';
                    if (cityInput) cityInput.value = d.city || cityInput.value || '';
                    if (districtInput) districtInput.value = d.district || districtInput.value || '';
                    if (stateInput) stateInput.value = d.state || stateInput.value || '';
                    if (pinInput) pinInput.value = d.pin_code || pinInput.value || '';

                    const autofillBadge = document.getElementById('autofillBadgeContainer');
                    if (autofillBadge) autofillBadge.style.display = 'block';
                }
            })
            .catch(err => console.log('Address fetch error:', err));
        }

        emailInput.addEventListener('blur', autoFetchAddressByEmail);
        emailInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(autoFetchAddressByEmail, 700);
        });

        // Trigger auto fetch on load if email is already present
        if (emailInput.value.trim().length > 5) {
            autoFetchAddressByEmail();
        }
    });
</script>
@endsection
@endsection
