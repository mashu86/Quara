@extends('layouts.admin')

@section('title', 'Record Manual Sale - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Record New Offline / Manual Sale</h3>
        <p class="text-muted small mb-0">Record a walk-in, phone, or direct customer purchase.</p>
    </div>
    <a href="{{ route('admin.manual-sales.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-3">&larr; Back to Manual Sales</a>
</div>

<form action="{{ route('admin.manual-sales.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <!-- Left Side: Product Selection & Pricing -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-shirt text-warning me-2"></i> Select Product & Size</h5>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="productSelect" class="form-select rounded-3" required onchange="updateSizes()">
                            <option value="">-- Choose Product --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" 
                                        data-price="{{ $prod->final_price }}"
                                        data-sizes='@json($prod->sizes)'>
                                    {{ $prod->name }} (Price: ₹{{ number_format($prod->final_price, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Size <span class="text-danger">*</span></label>
                            <select name="product_size_id" id="sizeSelect" class="form-select rounded-3" required onchange="updateStockNotice()">
                                <option value="">-- Select Product First --</option>
                            </select>
                            <div class="form-text small fw-bold text-success" id="stockNotice"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Quantity (pcs) <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="qtyInput" class="form-control rounded-3" value="1" min="1" required oninput="calcTotals()">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Unit Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="unit_price" id="priceInput" class="form-control rounded-3" placeholder="0.00" required oninput="calcTotals()">
                            <div class="form-text small">Default is current selling price, but you can override for custom discounts.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Delivery Charge (₹)</label>
                            <input type="number" step="0.01" name="delivery_charge" id="shippingInput" class="form-control rounded-3" value="0.00" min="0" oninput="calcTotals()">
                            <div class="form-text small">Leave as 0.00 for counter sales or free delivery.</div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark fs-6">Grand Total Amount:</span>
                        <span class="fs-3 fw-bold text-warning" id="grandTotalDisplay">₹0.00</span>
                    </div>
                </div>
            </div>

            <!-- Payment & Notes -->
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-credit-card text-warning me-2"></i> Payment Details</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="cash">Cash Payment</option>
                                <option value="upi">UPI (GPay/PhonePe/Paytm)</option>
                                <option value="bank_transfer">Bank Transfer / Card</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select rounded-3" required>
                                <option value="paid">Paid (Fully Received)</option>
                                <option value="pending">Pending (Pay Later)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Notes / Special Instructions</label>
                            <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="e.g. Walk-in customer discount / Counter sale receipt #42"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Customer Details & Submit -->
        <div class="col-lg-5">
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-user text-warning me-2"></i> Customer Details</h5>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Customer Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control rounded-3" placeholder="e.g. Anjali Nair" value="{{ old('customer_name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mobile Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="customer_phone" class="form-control rounded-3" placeholder="e.g. 9876543210" value="{{ old('customer_phone') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address (Optional)</label>
                        <input type="email" name="customer_email" class="form-control rounded-3" placeholder="customer@gmail.com" value="{{ old('customer_email') }}">
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-2">Delivery Address (Optional for shipped orders)</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <input type="text" name="house_building" class="form-control rounded-3 mb-2" placeholder="House / Building Name">
                        </div>
                        <div class="col-6">
                            <input type="text" name="street" class="form-control rounded-3 mb-2" placeholder="Street / Area">
                        </div>
                        <div class="col-6">
                            <input type="text" name="city" class="form-control rounded-3 mb-2" placeholder="City / Town" value="Naduvil">
                        </div>
                        <div class="col-6">
                            <input type="text" name="district" class="form-control rounded-3 mb-2" placeholder="District" value="Kannur">
                        </div>
                        <div class="col-6">
                            <input type="text" name="pin_code" class="form-control rounded-3 mb-2" placeholder="PIN Code" value="670582">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-3 mt-4 shadow-sm">RECORD MANUAL SALE</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    function updateSizes() {
        const select = document.getElementById('productSelect');
        const selectedOption = select.options[select.selectedIndex];
        const sizeSelect = document.getElementById('sizeSelect');
        const priceInput = document.getElementById('priceInput');

        sizeSelect.innerHTML = '<option value="">-- Choose Size --</option>';
        document.getElementById('stockNotice').innerText = '';

        if (!selectedOption.value) {
            priceInput.value = '';
            calcTotals();
            return;
        }

        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        priceInput.value = price.toFixed(2);

        const sizes = JSON.parse(selectedOption.getAttribute('data-sizes') || '[]');
        sizes.forEach(sz => {
            const opt = document.createElement('option');
            opt.value = sz.id;
            opt.setAttribute('data-stock', sz.stock);
            opt.innerText = `Size: ${sz.size} (Stock: ${sz.stock} pcs)`;
            if (sz.stock <= 0) {
                opt.disabled = true;
                opt.innerText += ' - OUT OF STOCK';
            }
            sizeSelect.appendChild(opt);
        });

        calcTotals();
    }

    function updateStockNotice() {
        const sizeSelect = document.getElementById('sizeSelect');
        const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
        const notice = document.getElementById('stockNotice');

        if (selectedOption && selectedOption.value) {
            const stock = selectedOption.getAttribute('data-stock');
            notice.innerText = `Available Stock: ${stock} pcs`;
        } else {
            notice.innerText = '';
        }
    }

    function calcTotals() {
        const price = parseFloat(document.getElementById('priceInput').value) || 0;
        const qty = parseInt(document.getElementById('qtyInput').value) || 1;
        const shipping = parseFloat(document.getElementById('shippingInput').value) || 0;

        const grandTotal = (price * qty) + shipping;
        document.getElementById('grandTotalDisplay').innerText = '₹' + grandTotal.toFixed(2);
    }
</script>
@endsection
