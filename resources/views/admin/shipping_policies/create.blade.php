@extends('layouts.admin')

@section('title', 'Create Shipping Policy - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 gap-2">
    <div>
        <h4 class="fw-bold mb-0" style="font-size: 0.95rem;">Create Delivery Shipping Policy</h4>
        <p class="text-muted small mb-0 d-none d-sm-block">Define custom delivery charge rules based on cart item quantity or subtotal amount.</p>
    </div>
    <a href="{{ route('admin.shipping-policies.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-2.5 px-sm-3 py-1 text-nowrap" style="font-size: 0.78rem;">
        <i class="fa-solid fa-arrow-left me-0 me-sm-1"></i><span class="d-none d-sm-inline"> Back to </span>Policies
    </a>
</div>

<form action="{{ route('admin.shipping-policies.store') }}" method="POST">
    @csrf
    <div class="card border-0 rounded-4 shadow-sm max-w-800 mx-auto overflow-hidden">
        <div class="card-header bg-dark text-white p-3 p-sm-4">
            <h5 class="fw-bold mb-0 text-warning" style="font-size: 0.88rem;"><i class="fa-solid fa-sliders me-2"></i> Policy Criteria & Pricing Configuration</h5>
        </div>
        <div class="card-body p-3 p-sm-5">

            @if($errors->any())
                <div class="alert alert-danger rounded-3 mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Policy Name -->
            <div class="mb-4">
                <label class="form-label fw-bold" style="font-size: 0.82rem;">Policy Name / Title <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control rounded-3 py-2" style="font-size: 0.85rem;" placeholder="e.g. Free Shipping on Orders ₹999 & Above" value="{{ old('name') }}" required>
                <div class="form-text small">Give a descriptive name to identify this policy rule easily.</div>
            </div>

            <!-- Policy Criteria -->
            <div class="mb-4">
                <label class="form-label fw-bold" style="font-size: 0.82rem;">Select Policy Criteria <span class="text-danger">*</span></label>
                <select name="criteria_type" id="criteriaTypeSelect" class="form-select rounded-3 py-2 fw-bold" style="font-size: 0.85rem;" required onchange="updateCriteriaLabels()">
                    <option value="cart_price" {{ old('criteria_type') === 'cart_price' ? 'selected' : '' }}>💰 Cart Price Subtotal (₹)</option>
                    <option value="cart_count" {{ old('criteria_type') === 'cart_count' ? 'selected' : '' }}>📦 Cart Item Count (Total Quantity)</option>
                </select>
                <div class="form-text small">Choose whether this rule checks total order amount or item quantity.</div>
            </div>

            <!-- Conditions Grid -->
            <div class="row g-4 mb-4">
                <!-- From Condition -->
                <div class="col-md-6">
                    <div class="p-3 bg-light border rounded-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-primary rounded-pill">Step 1</span>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Starting Bound (From)</h6>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Operator</label>
                            <select name="from_operator" class="form-select form-select-sm rounded-3" required>
                                <option value=">=" selected>&ge; Greater than or Equal to</option>
                                <option value=">">&gt; Greater than</option>
                                <option value="<=">&le; Less than or Equal to</option>
                                <option value="<">&lt; Less than</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small fw-bold" id="fromValueLabel">From Value</label>
                            <input type="number" step="0.01" name="from_value" class="form-control form-control-sm rounded-3" value="{{ old('from_value', 0) }}" min="0" required>
                        </div>
                    </div>
                </div>

                <!-- To Condition (Optional) -->
                <div class="col-md-6">
                    <div class="p-3 bg-light border rounded-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-secondary rounded-pill">Step 2 (Optional)</span>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Ending Bound (To)</h6>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Operator</label>
                            <select name="to_operator" class="form-select form-select-sm rounded-3">
                                <option value="">No Upper Bound (Unlimited)</option>
                                <option value="<=" selected>&le; Less than or Equal to</option>
                                <option value="<">&lt; Less than</option>
                                <option value=">=">&ge; Greater than or Equal to</option>
                                <option value=">">&gt; Greater than</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small fw-bold" id="toValueLabel">To Value</label>
                            <input type="number" step="0.01" name="to_value" class="form-control form-control-sm rounded-3" value="{{ old('to_value') }}" min="0" placeholder="Unlimited if left empty">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Type Custom Card Selector -->
            <div class="mb-4">
                <label class="form-label fw-bold d-block mb-3" style="font-size: 0.82rem;">Delivery Rate Selection <span class="text-danger">*</span></label>
                
                <div class="row g-3">
                    <div class="col-sm-6">
                        <input type="radio" class="btn-check" name="delivery_type" id="deliveryFree" value="free" {{ old('delivery_type', 'free') === 'free' ? 'checked' : '' }} onchange="toggleCustomChargeBox()" autocomplete="off">
                        <label class="btn btn-outline-success w-100 p-3 rounded-4 text-start border-2 h-100" for="deliveryFree">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold fs-6"><i class="fa-solid fa-truck-fast me-2"></i> Free Delivery</span>
                                <span class="badge bg-success text-white">₹0.00</span>
                            </div>
                            <small class="text-muted d-block">Applies ₹0 shipping fee when condition matches.</small>
                        </label>
                    </div>

                    <div class="col-sm-6">
                        <input type="radio" class="btn-check" name="delivery_type" id="deliveryCustom" value="custom" {{ old('delivery_type') === 'custom' ? 'checked' : '' }} onchange="toggleCustomChargeBox()" autocomplete="off">
                        <label class="btn btn-outline-warning w-100 p-3 rounded-4 text-start border-2 text-dark h-100" for="deliveryCustom">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold fs-6"><i class="fa-solid fa-coins me-2 text-warning"></i> Custom Charge</span>
                                <span class="badge bg-warning text-dark">Fixed Fee</span>
                            </div>
                            <small class="text-muted d-block">Specify a custom delivery charge amount.</small>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Custom Charge Input Field -->
            <div id="customChargeBox" class="mb-4 d-none p-4 bg-warning-subtle border border-warning rounded-4">
                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.82rem;">Custom Delivery Charge Amount (₹) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text fw-bold bg-warning border-warning">₹</span>
                    <input type="number" step="0.01" name="charge_amount" id="chargeAmountInput" class="form-control form-control-lg rounded-end-3 fw-bold" placeholder="50.00" value="{{ old('charge_amount', '50.00') }}" min="0">
                </div>
                <div class="form-text small mt-2">This charge amount will be added to the customer order total.</div>
            </div>

            <!-- Priority & Status -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="font-size: 0.82rem;">Evaluation Priority</label>
                    <input type="number" name="priority" class="form-control rounded-3" value="{{ old('priority', 0) }}" min="0">
                    <div class="form-text small">Lower numbers (e.g. 0) are evaluated first.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="font-size: 0.82rem;">Policy Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="active" selected>🟢 Active (Enable Rule)</option>
                        <option value="inactive">🔴 Inactive (Disable Rule)</option>
                    </select>
                </div>
            </div>

            <div class="col-12 mt-3 mt-md-4 text-center text-sm-start">
                <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 w-sm-auto px-4 px-sm-5 py-2.5 py-sm-2 shadow-sm text-dark" style="font-size: 0.82rem; background-color: var(--qw-gold); border-color: var(--qw-gold);">
                    <i class="fa-solid fa-floppy-disk me-1"></i> SAVE POLICY
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    function updateCriteriaLabels() {
        const type = document.getElementById('criteriaTypeSelect').value;
        const fromLabel = document.getElementById('fromValueLabel');
        const toLabel = document.getElementById('toValueLabel');

        if (type === 'cart_count') {
            fromLabel.innerText = 'From Item Count (Qty)';
            toLabel.innerText = 'To Item Count (Qty)';
        } else {
            fromLabel.innerText = 'From Cart Subtotal (₹)';
            toLabel.innerText = 'To Cart Subtotal (₹)';
        }
    }

    function toggleCustomChargeBox() {
        const isCustom = document.getElementById('deliveryCustom').checked;
        const box = document.getElementById('customChargeBox');
        if (isCustom) {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateCriteriaLabels();
        toggleCustomChargeBox();
    });
</script>
@endsection
