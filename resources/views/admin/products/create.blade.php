@extends('layouts.admin')

@section('title', 'Add Product - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Create New Product</h3>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-3">&larr; Back to Products</a>
</div>

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" onsubmit="handleAdminFormSubmit(this)">
    @csrf
    <div class="row g-4">
        <!-- Main Fields -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Basic Details</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Floral Chiffon Korean Top" value="{{ old('name') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select rounded-3" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Base Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" id="priceInput" class="form-control rounded-3" placeholder="999.00" value="{{ old('price') }}" required oninput="calcDiscount()">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Discount Type</label>
                            <select name="discount_type" id="discountTypeSelect" class="form-select rounded-3" onchange="calcDiscount()">
                                <option value="none" {{ old('discount_type') == 'none' ? 'selected' : '' }}>No Discount</option>
                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Discount Value</label>
                            <input type="number" step="0.01" name="discount_value" id="discountValueInput" class="form-control rounded-3" placeholder="0.00" value="{{ old('discount_value', 0) }}" oninput="calcDiscount()">
                        </div>

                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">Estimated Final Selling Price:</span>
                                <span class="fs-4 fw-bold text-warning" id="finalPriceDisplay">₹0.00</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Product Description</label>
                            <textarea name="description" class="form-control rounded-3" rows="4" placeholder="Enter detailed fabric, styling and care instructions...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Size-wise Stock Section -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-boxes-stacked text-warning me-2"></i> Size & Stock Management</h5>
                        <button type="button" class="btn btn-outline-dark btn-sm rounded-pill" onclick="addSizeRow()"><i class="fa-solid fa-plus me-1"></i> Add Size Row</button>
                    </div>

                    <div id="sizeRowsContainer">
                        <div class="row g-2 mb-2 align-items-center size-row">
                            <div class="col-5">
                                <input type="text" name="sizes[]" class="form-control rounded-3" placeholder="Size (e.g. S, M, L, XL)" value="Free Size" required>
                            </div>
                            <div class="col-5">
                                <input type="number" name="stocks[]" class="form-control rounded-3" placeholder="Initial Stock Qty" value="10" min="0" required>
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 rounded-3" onclick="removeSizeRow(this)"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Images & Publish -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Status & Delivery Setup</h5>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="active">Active (Visible on website)</option>
                            <option value="inactive">Inactive (Hidden)</option>
                        </select>
                    </div>

                    <hr>

                    <h5 class="fw-bold mb-3 border-bottom pb-2">Product Images</h5>
                    
                    <!-- Main Cover Image Selection -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Main Image (Cover Photo) <span class="text-danger">*</span></label>
                        <input type="file" name="main_image" id="mainImageInput" class="form-control rounded-3" accept="image/*" required onchange="previewMainImage(this)">
                        <div class="form-text small mb-2">This image appears as the main product cover.</div>
                        
                        <!-- Main Image Live Preview Container -->
                        <div id="mainImagePreviewContainer" class="d-none mt-2 position-relative">
                            <div class="border rounded-3 p-2 bg-light text-center position-relative" style="max-width: 200px;">
                                <img id="mainImagePreview" src="" class="img-fluid rounded" style="max-height: 160px; object-fit: cover;">
                                <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-1 shadow" onclick="removeMainImage()" title="Remove Main Image">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sub Images Dynamic Multi-Slot Selection -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-bold mb-0">Sub Images / Additional Gallery</label>
                            <button type="button" class="btn btn-outline-warning btn-sm rounded-pill font-bold" onclick="addSubImageSlot()">
                                <i class="fa-solid fa-plus me-1"></i> Add 
                        </div>
                        <div class="form-text small mb-3">Add as many sub-images as needed. Each image can be previewed or removed individually.</div>
                        
                        <!-- Sub Image Slots Container -->
                        <div id="subImageSlotsContainer" class="d-flex flex-column gap-2">
                            <div class="sub-image-slot-card p-2 bg-light border rounded-3 d-flex align-items-center justify-content-between gap-2">
                                <div class="flex-grow-1">
                                    <input type="file" name="sub_images[]" class="form-control form-control-sm rounded-3" accept="image/*" onchange="previewSlotImage(this)">
                                </div>
                                <div class="slot-preview-box d-none" style="width: 50px; height: 50px;">
                                    <img src="" class="img-fluid rounded border slot-preview-img" style="width: 50px; height: 50px; object-fit: cover;">
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" onclick="removeSubSlot(this)" title="Remove Slot">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-3 shadow-sm mt-3">SAVE & PUBLISH PRODUCT</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    function calcDiscount() {
        const price = parseFloat(document.getElementById('priceInput').value) || 0;
        const type = document.getElementById('discountTypeSelect').value;
        const val = parseFloat(document.getElementById('discountValueInput').value) || 0;

        let finalPrice = price;
        if (type === 'fixed') {
            finalPrice = Math.max(0, price - val);
        } else if (type === 'percentage') {
            finalPrice = Math.max(0, price - (price * (val / 100)));
        }

        document.getElementById('finalPriceDisplay').innerText = '₹' + finalPrice.toFixed(2);
    }

    function addSizeRow() {
        const container = document.getElementById('sizeRowsContainer');
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 align-items-center size-row';
        div.innerHTML = `
            <div class="col-5">
                <input type="text" name="sizes[]" class="form-control rounded-3" placeholder="Size (e.g. S, M, L)" required>
            </div>
            <div class="col-5">
                <input type="number" name="stocks[]" class="form-control rounded-3" placeholder="Stock Qty" value="5" min="0" required>
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-outline-danger btn-sm w-100 rounded-3" onclick="removeSizeRow(this)"><i class="fa-solid fa-xmark"></i></button>
            </div>
        `;
        container.appendChild(div);
    }

    function removeSizeRow(btn) {
        const rows = document.querySelectorAll('.size-row');
        if (rows.length > 1) {
            btn.closest('.size-row').remove();
        } else {
            alert('At least one size row is required.');
        }
    }

    // Main Cover Image Live Preview & Removal
    function previewMainImage(input) {
        const container = document.getElementById('mainImagePreviewContainer');
        const img = document.getElementById('mainImagePreview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                container.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            container.classList.add('d-none');
        }
    }

    function removeMainImage() {
        const input = document.getElementById('mainImageInput');
        const container = document.getElementById('mainImagePreviewContainer');
        const img = document.getElementById('mainImagePreview');

        input.value = '';
        img.src = '';
        container.classList.add('d-none');
    }

    // Dynamic Sub-Image Slot Management
    function addSubImageSlot() {
        const container = document.getElementById('subImageSlotsContainer');
        const div = document.createElement('div');
        div.className = 'sub-image-slot-card p-2 bg-light border rounded-3 d-flex align-items-center justify-content-between gap-2';
        div.innerHTML = `
            <div class="flex-grow-1">
                <input type="file" name="sub_images[]" class="form-control form-control-sm rounded-3" accept="image/*" onchange="previewSlotImage(this)">
            </div>
            <div class="slot-preview-box d-none" style="width: 50px; height: 50px;">
                <img src="" class="img-fluid rounded border slot-preview-img" style="width: 50px; height: 50px; object-fit: cover;">
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" onclick="removeSubSlot(this)" title="Remove Slot">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;
        container.appendChild(div);
    }

    function previewSlotImage(input) {
        const slotCard = input.closest('.sub-image-slot-card');
        const previewBox = slotCard.querySelector('.slot-preview-box');
        const previewImg = slotCard.querySelector('.slot-preview-img');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewBox.classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            previewBox.classList.add('d-none');
            previewImg.src = '';
        }
    }

    function removeSubSlot(btn) {
        const container = document.getElementById('subImageSlotsContainer');
        const slots = container.querySelectorAll('.sub-image-slot-card');
        if (slots.length > 1) {
            btn.closest('.sub-image-slot-card').remove();
        } else {
            // Reset input in first slot
            const slotCard = btn.closest('.sub-image-slot-card');
            const input = slotCard.querySelector('input[type="file"]');
            const previewBox = slotCard.querySelector('.slot-preview-box');
            const previewImg = slotCard.querySelector('.slot-preview-img');
            input.value = '';
            previewImg.src = '';
            previewBox.classList.add('d-none');
        }
    }
</script>
@endsection
