@extends('layouts.app')

@section('title', 'Checkout - ' . $siteName)
@section('meta_robots', 'noindex, nofollow')

@section('content')
<div class="container py-4">
    <h5 class="font-serif fw-bold fs-5 mb-3"><i class="fa-solid fa-lock text-gold me-2"></i> SECURE CHECKOUT</h5>

    <form action="{{ route('checkout.process') }}" method="POST" data-district-checkout="{{ route('checkout.district-offer') }}" data-base-total="{{ $summary['grand_total'] }}" data-base-rounding="{{ $summary['rounding_adjustment'] ?? 0 }}" data-base-product-total="{{ max(0, $summary['subtotal'] - $summary['discount']) }}" data-base-shipping="{{ $summary['shipping'] }}">
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
                            <label class="form-label small fw-bold">Email Address <span class="text-muted fw-normal">(Optional)</span></label>
                            <input type="email" name="customer_email" id="checkout_customer_email" class="form-control rounded-3" placeholder="name@example.com" value="{{ old('customer_email', session('customer_email')) }}">
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
                            <label class="form-label small fw-bold" for="checkoutPin">PIN Code *</label>
                            <input id="checkoutPin" type="text" name="pin_code" inputmode="numeric" pattern="[1-9][0-9]{5}" maxlength="6" class="form-control rounded-3" value="{{ old('pin_code', $lastOrder?->pin_code) }}" required>
                            <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" role="switch" id="autoFindLocation" data-auto-pincode><label class="form-check-label small" for="autoFindLocation">Auto-find district &amp; state</label></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold" for="checkoutDistrict">District *</label>
                            <input id="checkoutDistrict" type="text" name="district" class="form-control rounded-3" value="{{ old('district', $lastOrder?->district) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold" for="checkoutState">State *</label>
                            <input id="checkoutState" type="text" name="state" class="form-control rounded-3" value="{{ old('state', $lastOrder?->state) }}" required>
                        </div>
                        <div class="col-12 small"><span id="pinLookupMessage" role="status"></span></div>

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
                    <div class="checkout-payment-option p-3 rounded-3 border bg-light">
                        <div class="checkout-payment-heading d-flex align-items-center justify-content-between gap-3 mb-2">
                            <i class="fa-solid fa-circle-check text-success fs-5 flex-shrink-0"></i>
                            <span class="checkout-payment-badge badge bg-success rounded-pill px-3 py-2"><i class="fa-solid fa-shield-halved me-1"></i> Razorpay</span>
                        </div>
                        <div class="checkout-payment-details">
                            <div class="fw-bold fs-6 text-dark"><i class="fa-solid fa-credit-card text-warning me-2"></i> Online Payment</div>
                            <div class="text-muted small">Pay securely using UPI (Google Pay, PhonePe, Paytm), Cards, Netbanking or Wallets via Razorpay</div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Order Review Sidebar -->
            <div class="col-lg-5">
                <div class="checkout-order-card bg-white p-3 p-md-4 rounded-4 shadow-sm border sticky-top" style="top: 90px;">
                    <h5 class="font-serif fw-bold mb-3 pb-2 border-bottom">ITEMS IN ORDER</h5>

                    <div class="checkout-order-items mb-4">
                        @foreach($cart as $item)
                            <div class="checkout-order-item d-flex align-items-center gap-3 mb-3">
                                <div class="checkout-order-image-wrap position-relative flex-shrink-0">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="checkout-order-image rounded-3 border" style="width: 58px; height: 76px; object-fit: cover;">
                                    @if($product = $productsById->get($item['product_id']))
                                        <button type="button" class="checkout-image-details-btn" data-bs-toggle="modal" data-bs-target="#checkoutProductInfo{{ $loop->index }}" aria-label="View {{ $item['name'] }} details" title="View product details">
                                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                </div>
                                <div class="checkout-order-copy flex-grow-1">
                                    <h6 class="font-serif fw-bold mb-0">{{ $item['name'] }}</h6>
                                    <div class="text-muted small">@if(!empty($item['size']))Size: <span class="fw-bold text-dark">{{ $item['size'] }}</span> | @endif Qty: {{ $item['quantity'] }}</div>
                                </div>
                                @if(!empty($item['is_combo_offer']))
                                    <span class="badge bg-warning-subtle text-dark border border-warning" style="font-size: 0.68rem;"><i class="fa-solid fa-crown me-1"></i> Combo Item</span>
                                @else
                                    <div class="fw-bold text-gold">₹{{ number_format($item['subtotal'], 2) }}</div>
                                @endif
                            </div>
                            @if($product)
                                <div class="modal fade" id="checkoutProductInfo{{ $loop->index }}" tabindex="-1" aria-labelledby="checkoutProductInfoTitle{{ $loop->index }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                                        <div class="modal-content border-0 rounded-4">
                                            <div class="modal-header">
                                                <h2 class="modal-title h5 fw-bold" id="checkoutProductInfoTitle{{ $loop->index }}">{{ $product->name }}</h2>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-5"><img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="img-fluid rounded-3 w-100" style="max-height:420px;object-fit:cover"></div>
                                                    <div class="col-md-7">
                                                        <div class="h5 fw-bold text-warning mb-3">₹{{ number_format((float) ($item['final_price'] ?? $product->effective_final_price), 2) }}</div>
                                                        <div class="text-secondary mb-3">{!! $product->description !!}</div>
                                                        @if($product->sizes->isNotEmpty())
                                                            <h3 class="h6 fw-bold">Sizes and measurements</h3>
                                                            <div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0"><thead><tr><th>Size</th><th>Chest</th><th>Waist</th><th>Hip</th><th>Length</th></tr></thead><tbody>
                                                                @foreach($product->sizes as $size)
                                                                    <tr class="{{ $size->size === ($item['size'] ?? null) ? 'table-warning' : '' }}"><td>{{ $size->size ?: 'Standard' }}{{ $size->size === ($item['size'] ?? null) ? ' (Selected)' : '' }}</td><td>{{ $size->chest ? $size->chest.'″' : '—' }}</td><td>{{ $size->waist ? $size->waist.'″' : '—' }}</td><td>{{ $size->hip ? $size->hip.'″' : '—' }}</td><td>{{ $size->length ? $size->length.'″' : '—' }}</td></tr>
                                                                @endforeach
                                                            </tbody></table></div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
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

                    @if(!empty($summary['has_active_coupons']))
                    <div class="border rounded-3 p-3 mb-3" id="couponBox" data-applied="{{ !empty($summary['coupon_discount']) ? '1' : '0' }}">
                        <label class="form-label fw-semibold mb-2" for="couponCode">Coupon Code</label>
                        <div class="input-group"><input class="form-control text-uppercase" id="couponCode" placeholder="Enter coupon code" value="{{ session('master_coupon.code') }}" {{ !empty($summary['coupon_discount']) ? 'readonly' : '' }}><button class="btn btn-outline-dark" type="button" id="couponAction">{{ !empty($summary['coupon_discount']) ? 'Remove' : 'Apply' }}</button></div>
                        <div id="couponMessage" class="small mt-2">@if(!empty($summary['coupon_discount']))<span class="text-success">Coupon applied: {{ session('master_coupon.code') }}</span>@endif</div>
                    </div>
                    <div id="couponDiscountRow" class="d-flex justify-content-between mb-2 text-success" @if(empty($summary['coupon_discount'])) hidden @endif><span>Coupon Discount</span><strong id="couponDiscountAmount">-₹{{ number_format($summary['coupon_discount'] ?? 0, 2) }}</strong></div>

                    @endif
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping Charge</span>
                        @if($summary['shipping'] > 0)
                            <span class="fw-bold text-dark">₹{{ number_format($summary['shipping'], 2) }}</span>
                        @else
                            <span class="text-success fw-semibold">FREE</span>
                        @endif
                    </div>

                    <div id="districtOfferRow" hidden><div class="d-flex justify-content-between mb-2 text-success"><span>District Wise Special Offer</span><strong id="districtOfferAmount"></strong></div></div>
                    <div class="d-flex justify-content-between mb-3 text-muted small"><span>Rounded Paisa (Round Off)</span><span id="checkoutRounding">+₹{{ number_format($summary['rounding_adjustment'] ?? 0, 2) }}</span></div>

                    <div class="d-flex justify-content-between mb-4 fs-4 fw-bold">
                        <span>Total Payable</span>
                        <span class="text-gold" id="checkoutTotal">₹{{ number_format($summary['grand_total'], 2) }}</span>
                    </div>

                    <button type="submit" class="btn btn-qw-gold btn-sm w-100 rounded-pill shadow-sm py-1-5 fw-bold" style="font-size: 0.82rem; padding-top: 7px; padding-bottom: 7px;">
                        PLACE ORDER NOW <i class="fa-solid fa-circle-check ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .checkout-payment-heading { min-height: 2rem; }
    .checkout-payment-badge { white-space: nowrap; font-size: 9px; }
    .checkout-order-items { max-height: 280px; overflow-y: auto; }
    .checkout-order-copy { min-width: 0; }
    .checkout-order-copy h6 { overflow-wrap: anywhere; }
    .checkout-order-item > .fw-bold.text-gold { flex: 0 0 auto; white-space: nowrap; }
    .checkout-order-image-wrap { width: 58px; height: 76px; }
    .checkout-image-details-btn {
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        width: 100%; height: 100%; padding: 0; border: 0; border-radius: 0.5rem;
        color: #fff; background: rgba(0, 0, 0, 0.38); opacity: 0;
        transition: opacity 0.18s ease; cursor: pointer;
    }
    .checkout-image-details-btn i { font-size: 0.8rem; }
    .checkout-order-image-wrap:hover .checkout-image-details-btn,
    .checkout-image-details-btn:focus-visible { opacity: 1; }

    @media (max-width: 575.98px) {
        .checkout-order-card { position: static !important; }
        .checkout-order-items { max-height: none; overflow: visible; }
        .checkout-order-item { gap: 0.75rem !important; }
        .checkout-order-image { width: 56px !important; height: 72px !important; flex: 0 0 56px; }
        .checkout-order-image-wrap { width: 56px; height: 72px; }
        .checkout-image-details-btn { opacity: 1; background: rgba(0, 0, 0, 0.2); }
        .checkout-order-item > .fw-bold.text-gold { font-size: 0.95rem; }
    }
</style>

@include('frontend.partials.email_otp_modal')

@section('scripts')
<script src="{{ asset('js/checkout_district_offer.js') }}?v={{ filemtime(public_path('js/checkout_district_offer.js')) }}"></script>
<script>
document.addEventListener('DOMContentLoaded',()=>{const form=document.querySelector('[data-district-checkout]'),input=document.getElementById('couponCode'),button=document.getElementById('couponAction'),msg=document.getElementById('couponMessage'),row=document.getElementById('couponDiscountRow'),amount=document.getElementById('couponDiscountAmount'),total=document.getElementById('checkoutTotal'),rounding=document.getElementById('checkoutRounding'),box=document.getElementById('couponBox');let couponDiscount=Number(@json($summary['coupon_discount']??0));const recalc=()=>{const pin=document.querySelector('[name="pin_code"]')?.value.trim()||'';const data=window.checkoutDistrictOfferData||{};let raw=Number(form.dataset.baseProductTotal)+Number(form.dataset.baseShipping||0)-couponDiscount;if(data.pin===pin&&data.discount)raw-=Number(data.discount);raw=Math.max(0,raw);const rounded=Math.ceil(raw);total.textContent='₹'+rounded.toFixed(2);rounding.textContent='+₹'+(rounded-raw).toFixed(2);};button.addEventListener('click',async()=>{const remove=box.dataset.applied==='1';button.disabled=true;msg.textContent=remove?'Removing coupon…':'Checking coupon…';try{const response=await fetch(remove?@json(route('checkout.coupon.remove')):@json(route('checkout.coupon.apply')),{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'Accept':'application/json'},body:JSON.stringify(remove?{}:{code:input.value,pin_code:document.querySelector(`[name="pin_code"]`)?.value||""})});const data=await response.json();if(!response.ok)throw new Error(data.message||'Unable to apply coupon.');if(remove){couponDiscount=0;box.dataset.applied='0';input.readOnly=false;input.value='';button.textContent='Apply';row.hidden=true;msg.textContent=data.message;}else{couponDiscount=Number(data.discount);box.dataset.applied='1';input.value=data.code;input.readOnly=true;button.textContent='Remove';row.hidden=false;amount.textContent='-₹'+couponDiscount.toFixed(2);msg.textContent=data.message;msg.className='small mt-2 text-success';if(data.pin_code){window.checkoutDistrictOfferData={pin:data.pin_code,discount:Number(data.district_discount||0)};document.getElementById("districtOfferRow").hidden=!(Number(data.district_discount)>0);document.getElementById("districtOfferAmount").textContent="-\u20b9"+Number(data.district_discount||0).toFixed(2);}}recalc();}catch(e){msg.textContent=e.message;msg.className='small mt-2 text-danger';}finally{button.disabled=false;}});form.elements.pin_code?.addEventListener('input',recalc);});
</script>
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
                    if (pinInput) { pinInput.value = d.pin_code || pinInput.value || ''; pinInput.dispatchEvent(new Event('input', {bubbles: true})); }

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
