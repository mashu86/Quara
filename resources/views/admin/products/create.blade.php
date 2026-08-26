@extends('layouts.admin')

@section('title', 'Add Product - QUARA WALDROP Admin')

@section('content')
<style>
@media (max-width: 576px) {
    .admin-prod-page-title {
        font-size: 1.05rem !important;
    }
    .admin-back-btn {
        font-size: 0.72rem !important;
        padding: 0.25rem 0.65rem !important;
    }
    .admin-prod-card-body {
        padding: 0.85rem !important;
    }
    .admin-prod-card-body h5 {
        font-size: 0.92rem !important;
        margin-bottom: 0.5rem !important;
    }
    .admin-prod-card-body .form-label {
        font-size: 0.78rem !important;
        margin-bottom: 0.2rem !important;
    }
    .admin-prod-card-body .form-control, 
    .admin-prod-card-body .form-select {
        font-size: 0.82rem !important;
        padding: 0.35rem 0.6rem !important;
    }
    .admin-prod-card-body .form-text {
        font-size: 0.72rem !important;
    }
    .btn-submit-product {
        font-size: 0.82rem !important;
        padding: 0.6rem 1rem !important;
    }
    .size-row .form-control {
        font-size: 0.8rem !important;
        padding: 0.3rem 0.5rem !important;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 flex-wrap gap-2">
    <h3 class="fw-bold mb-0 admin-prod-page-title">Create New Product</h3>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-dark rounded-pill btn-sm admin-back-btn text-nowrap">
        <i class="fa-solid fa-arrow-left me-1"></i><span class="d-none d-sm-inline">Back to </span>Products
    </a>
</div>

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" onsubmit="handleAdminFormSubmit(this)">
    @csrf
    <div class="row g-3 g-md-4">
        <!-- Main Fields -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-3 p-md-4 admin-prod-card-body">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Basic Details</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Floral Chiffon Korean Top" value="{{ old('name') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Categories (Select Multiple) <span class="text-danger">*</span></label>
                            <div class="dropdown custom-category-dropdown position-relative">
                                <button class="btn btn-outline-secondary form-select text-start rounded-3 d-flex justify-content-between align-items-center bg-white shadow-none" type="button" id="categoryDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <span id="categoryDropdownBtnText" class="text-truncate me-2 text-muted">Select Categories</span>
                                </button>
                                <div class="dropdown-menu p-3 shadow-lg border-0 rounded-3 w-100 mt-1" aria-labelledby="categoryDropdownBtn" style="min-width: 280px; max-width: 100%;">
                                    <div class="mb-2">
                                        <input type="text" class="form-control form-control-sm rounded-3" id="categorySearchInput" placeholder="🔍 Search category..." onkeyup="filterCategories(this)" onclick="event.stopPropagation()">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-primary small fw-bold" onclick="selectAllCategories()">Select All</button>
                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-muted small" onclick="clearCategorySelection()">Clear All</button>
                                    </div>
                                    <div id="categoryListContainer" style="max-height: 230px; overflow-y: auto;">
                                        @php $oldCategoryIds = old('category_ids', old('category_id') ? [old('category_id')] : []); @endphp
                                        @foreach($categories as $cat)
                                            <label class="category-item d-flex align-items-center gap-2 py-2 px-2 rounded cursor-pointer user-select-none" style="cursor: pointer; transition: background 0.15s ease-in-out;" onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'">
                                                <input class="form-check-input category-checkbox m-0 flex-shrink-0" type="checkbox" name="category_ids[]" value="{{ $cat->id }}" id="cat_cb_{{ $cat->id }}" onchange="updateCategorySelectionDisplay()" {{ in_array($cat->id, (array)$oldCategoryIds) ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer;">
                                                <span class="text-dark small fw-medium flex-grow-1">
                                                    {{ $cat->name }}
                                                </span>
                                            </label>
                                        @endforeach
                                        <div id="noCategoriesFound" class="text-muted small text-center py-2 d-none">No categories found</div>
                                    </div>
                                </div>
                            </div>
                            @error('category_ids')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
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
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-3 p-md-4 admin-prod-card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-boxes-stacked text-warning me-2"></i> Size & Stock Management</h5>
                        <button type="button" class="btn btn-outline-dark btn-sm  py-1 px-2.5" style="font-size: 0.78rem;" onclick="addSizeRow()"><i class="fa-solid fa-plus me-1"></i></button>
                    </div>

                    <div id="sizeRowsContainer">
                        <div class="row g-2 mb-2 align-items-center size-row">
                            <div class="col-5">
                                <input type="text" name="sizes[]" class="form-control rounded-3" placeholder="Size (e.g. S, M, L, XL)" value="Free Size" required>
                            </div>
                            <div class="col-5">
                                <input type="number" name="stocks[]" class="form-control rounded-3" placeholder="Initial Stock Qty" value="1" min="0" required>
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
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-3 p-md-4 admin-prod-card-body">
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
                            <button type="button" class="btn btn-outline-warning btn-sm py-1 px-2.5" style="font-size: 0.78rem;" onclick="addSubImageSlot()">
                                <i class="fa-solid fa-plus"></i>
                            </button>
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

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-2.5 py-md-3 btn-submit-product shadow-sm mt-3">SAVE & PUBLISH PRODUCT</button>
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
                <input type="number" name="stocks[]" class="form-control rounded-3" placeholder="Stock Qty" value="1" min="0" required>
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

    // Category Multi-Select Dropdown Functions
    function updateCategorySelectionDisplay() {
        const checkboxes = document.querySelectorAll('.category-checkbox:checked');
        const btnText = document.getElementById('categoryDropdownBtnText');
        if (!btnText) return;

        if (checkboxes.length === 0) {
            btnText.innerText = 'Select Categories';
            btnText.classList.add('text-muted');
            btnText.classList.remove('text-dark', 'fw-bold');
        } else {
            const labels = Array.from(checkboxes).map(cb => {
                const label = document.querySelector(`label[for="${cb.id}"]`);
                return label ? label.innerText.trim() : '';
            }).filter(Boolean);

            if (checkboxes.length === 1) {
                btnText.innerText = labels[0];
            } else {
                btnText.innerText = `${checkboxes.length} Selected (${labels.slice(0, 2).join(', ')}${labels.length > 2 ? '...' : ''})`;
            }
            btnText.classList.remove('text-muted');
            btnText.classList.add('text-dark', 'fw-bold');
        }
    }

    function filterCategories(input) {
        const term = input.value.toLowerCase().trim();
        const items = document.querySelectorAll('.category-item');
        let hasMatch = false;

        items.forEach(item => {
            const text = item.innerText.toLowerCase();
            if (text.includes(term)) {
                item.classList.remove('d-none');
                hasMatch = true;
            } else {
                item.classList.add('d-none');
            }
        });

        const noResult = document.getElementById('noCategoriesFound');
        if (noResult) {
            if (hasMatch) {
                noResult.classList.add('d-none');
            } else {
                noResult.classList.remove('d-none');
            }
        }
    }

    function selectAllCategories() {
        const visibleCheckboxes = document.querySelectorAll('.category-item:not(.d-none) .category-checkbox');
        visibleCheckboxes.forEach(cb => cb.checked = true);
        updateCategorySelectionDisplay();
    }

    function clearCategorySelection() {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        checkboxes.forEach(cb => cb.checked = false);
        updateCategorySelectionDisplay();
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateCategorySelectionDisplay();
    });
</script>
@endsection
