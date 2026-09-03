@extends('layouts.admin')

@section('title', 'Edit Product - ' . $siteName . ' Admin')

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
    .size-stock-header-title {
        font-size: 0.8rem !important;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 flex-wrap gap-2">
    <h3 class="fw-bold mb-0 admin-prod-page-title text-truncate" style="max-width: 65%;">Edit Product: {{ $product->name }}</h3>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-dark rounded-pill btn-sm admin-back-btn text-nowrap">
        <i class="fa-solid fa-arrow-left me-1"></i><span class="d-none d-sm-inline">Back to </span>Products
    </a>
</div>

<form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" onsubmit="handleAdminFormSubmit(this)">
    @csrf
    @method('PUT')

    <div class="row g-3 g-md-4">
        <!-- Main Form -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-3 p-md-4 admin-prod-card-body">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Basic Details</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $product->name) }}" required>
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
                                        @php
                                            $selectedCategoryIds = old('category_ids', $product->categories->pluck('id')->toArray());
                                            if (empty($selectedCategoryIds) && $product->category_id) {
                                                $selectedCategoryIds = [$product->category_id];
                                            }
                                        @endphp
                                        @foreach($categories as $cat)
                                            <label class="category-item d-flex align-items-center gap-2 py-2 px-2 rounded cursor-pointer user-select-none" style="cursor: pointer; transition: background 0.15s ease-in-out;" onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'">
                                                <input class="form-check-input category-checkbox m-0 flex-shrink-0" type="checkbox" name="category_ids[]" value="{{ $cat->id }}" id="cat_cb_{{ $cat->id }}" onchange="updateCategorySelectionDisplay()" {{ in_array($cat->id, (array)$selectedCategoryIds) ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer;">
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

                        <div class="col-md-6" id="basePriceCol">
                            <label class="form-label fw-bold">Base Price (₹) <span class="text-danger">*</span></label>
                            <input type="text" inputmode="decimal" name="price" id="priceInput" class="form-control rounded-3" value="{{ old('price', $product->price) }}" required oninput="calcDiscount()" autocomplete="off">
                        </div>

                        <div class="col-md-6" id="discountTypeCol">
                            <label class="form-label fw-bold">Discount Type</label>
                            <select name="discount_type" id="discountTypeSelect" class="form-select rounded-3" onchange="calcDiscount()">
                                <option value="none" {{ old('discount_type', $product->discount_type) == 'none' ? 'selected' : '' }}>No Discount</option>
                                <option value="percentage" {{ old('discount_type', $product->discount_type) == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('discount_type', $product->discount_type) == 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                            </select>
                        </div>

                        <div class="col-md-4 d-none" id="discountValueContainer">
                            <label class="form-label fw-bold">Discount Value</label>
                            <input type="text" inputmode="decimal" name="discount_value" id="discountValueInput" class="form-control rounded-3" value="{{ old('discount_value', $product->discount_value) }}" oninput="calcDiscount()" autocomplete="off">
                        </div>

                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">Current Selling Price:</span>
                                <span class="fs-4 fw-bold text-warning" id="finalPriceDisplay">₹{{ number_format($product->final_price, 2) }}</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Product Description</label>
                            <textarea name="description" class="form-control rounded-3" rows="4">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Size-wise Stock Adjustment -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-3 p-sm-4">
                    <div class="border-bottom pb-2 mb-3">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <h5 class="fw-bold mb-0 size-stock-header-title"><i class="fa-solid fa-boxes-stacked text-warning me-2"></i> Size & Stock Management</h5>
                            <button type="button" class="btn btn-warning btn-sm fw-bold rounded-3 w-100 w-sm-auto shadow-sm" data-bs-toggle="modal" data-bs-target="#addStockBatchModal" style="font-size: 0.8rem;">
                                <i class="fa-solid fa-calendar-plus me-1"></i> + Add New Stock (Date-Wise)
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase d-block mb-2">
                            Existing Sizes & Measurements (Inches)
                        </label>
                        @foreach($product->sizes as $pSize)
                            <div class="p-3 border rounded-3 bg-light mb-2">
                                <div class="row g-2 align-items-center">
                                    <div class="col-6 col-md-3">
                                        <label class="form-label small fw-bold mb-1">Size Label *</label>
                                        <input type="text"
                                               name="existing_sizes[{{ $pSize->id }}]"
                                               class="form-control form-control-sm rounded-3"
                                               value="{{ old('existing_sizes.' . $pSize->id, $pSize->size) }}"
                                               placeholder="e.g. L / XL"
                                               maxlength="50"
                                               required>
                                        @error('existing_sizes.' . $pSize->id)
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label small fw-bold mb-1">Stock (pcs) *</label>
                                        <input type="text" inputmode="numeric" pattern="[0-9]*" name="existing_stocks[{{ $pSize->id }}]" class="form-control form-control-sm rounded-3" value="{{ old('existing_stocks.' . $pSize->id, $pSize->stock) }}" required>
                                        @error('existing_stocks.' . $pSize->id)
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <label class="form-label small text-muted mb-1">Chest (inch)</label>
                                        <input type="text" name="existing_chests[{{ $pSize->id }}]" class="form-control form-control-sm rounded-3" value="{{ old('existing_chests.' . $pSize->id, $pSize->chest) }}" placeholder="e.g. 40&quot;">
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <label class="form-label small text-muted mb-1">Waist (inch)</label>
                                        <input type="text" name="existing_waists[{{ $pSize->id }}]" class="form-control form-control-sm rounded-3" value="{{ old('existing_waists.' . $pSize->id, $pSize->waist) }}" placeholder="e.g. 34&quot;">
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <label class="form-label small text-muted mb-1">Length (inch)</label>
                                        <input type="text" name="existing_lengths[{{ $pSize->id }}]" class="form-control form-control-sm rounded-3" value="{{ old('existing_lengths.' . $pSize->id, $pSize->length) }}" placeholder="e.g. 42&quot;">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Stock Adjustment Reason (Logged to Audit Trail)</label>
                        <input type="text" name="stock_adjustment_reason" class="form-control rounded-3" placeholder="e.g. Received new inventory batch / Stock audit fix">
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Add Additional Sizes</h6>
                        <button type="button" class="btn btn-outline-dark btn-sm rounded-pill" onclick="addNewSizeRow()"><i class="fa-solid fa-plus me-1"></i> Add Size</button>
                    </div>

                    <div id="newSizesContainer"></div>
                </div>
            </div>

            <!-- Stock Movement Audit Log -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-warning"></i> Stock Movement Audit History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 250px;">
                        <table class="table align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Size</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>Before &rarr; After</th>
                                    <th>Reason / Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->stockMovements->take(15) as $move)
                                    <tr>
                                        <td>{{ $move->created_at->format('M d, Y H:i') }}</td>
                                        <td><span class="badge bg-dark">{{ $move->productSize->size ?? 'N/A' }}</span></td>
                                        <td><span class="badge bg-{{ $move->type === 'addition' || $move->type === 'restoration' ? 'success' : 'danger' }}">{{ ucfirst($move->type) }}</span></td>
                                        <td class="fw-bold">{{ $move->quantity }}</td>
                                        <td>{{ $move->stock_before }} &rarr; {{ $move->stock_after }}</td>
                                        <td>{{ $move->reason }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">No stock movement logs recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Status, Delivery Settings & Save -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Status & Delivery Setup</h5>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Product Visibility</label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active (Visible)</option>
                            <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive (Hidden)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Out of Stock (Manual Override)</label>
                        <div class="form-check form-switch p-2 bg-light rounded-3 border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" name="is_out_of_stock" id="isOutOfStockEditSwitch" value="1" {{ old('is_out_of_stock', $product->is_out_of_stock) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-dark small" for="isOutOfStockEditSwitch">
                                Enable "Out of Stock" Override
                            </label>
                        </div>
                        <div class="form-text small">When checked, the product will be treated as 0 stock and unpurchasable by customers while preserving existing inventory records.</div>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-3 fw-bold w-100 py-2.5 shadow-sm border-0 mb-4" style="font-size: 0.85rem; background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> UPDATE PRODUCT DETAILS
                    </button>
                </div>
            </div>
</form>

            <!-- Product Gallery Management -->
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <div>
                            <h5 class="fw-bold mb-0">Product Gallery</h5>
                            <span class="text-muted small" style="font-size: 0.73rem;">Only 1 image can be Primary. Click "Make Primary" to switch.</span>
                        </div>
                        <span class="badge bg-light text-dark border fw-bold" style="font-size: 0.72rem;">{{ $product->images->count() }} {{ Str::plural('Image', $product->images->count()) }}</span>
                    </div>

                    @if($product->images->isEmpty())
                        <div class="alert alert-warning py-2 px-3 small text-center mb-3">No gallery images uploaded yet.</div>
                    @else
                        <div class="row g-2 mb-3">
                            @foreach($product->images as $img)
                                <div class="col-6">
                                    <div class="border rounded-3 overflow-hidden p-1.5 text-center position-relative {{ $img->is_primary ? 'bg-warning bg-opacity-10 border-warning shadow-sm' : 'bg-light' }}">
                                        <img src="{{ $img->image_url }}" class="img-fluid rounded mb-2" style="height: 115px; object-fit: cover; width: 100%;">

                                        <div class="d-flex justify-content-between align-items-center px-1">
                                            @if($img->is_primary || $product->images->count() === 1)
                                                <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.68rem;" title="Main primary image">
                                                    <i class="fa-solid fa-star me-1"></i> PRIMARY
                                                </span>
                                            @else
                                                <form action="{{ route('admin.product-images.set-primary', $img->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-warning btn-sm py-0 px-2 fw-bold text-dark" style="font-size: 0.65rem;" title="Make this the primary display image">
                                                        <i class="fa-regular fa-star me-1"></i> Make Primary
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.product-images.destroy', $img->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Delete this image permanently?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger btn-sm p-0 ms-1" title="Delete Image">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Upload New Images -->
                    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" value="{{ $product->name }}">
                        <input type="hidden" name="category_id" value="{{ $product->category_id }}">
                        <input type="hidden" name="price" value="{{ $product->price }}">
                        <input type="hidden" name="discount_type" value="{{ $product->discount_type }}">
                        <input type="hidden" name="discount_value" value="{{ $product->discount_value }}">
                        <input type="hidden" name="status" value="{{ $product->status }}">
                        <input type="hidden" name="delivery_charge_type" value="{{ $product->delivery_charge_type }}">

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold mb-0">Upload Additional Gallery Images</label>
                                <button type="button" class="btn btn-outline-warning btn-sm rounded-pill font-bold" onclick="addEditImageSlot()">
                                    <i class="fa-solid fa-plus me-1"></i> + Add Image Slot
                                </button>
                            </div>
                            <div class="form-text small mb-2">Add as many gallery photos as you like.</div>
                            
                            <!-- Dynamic Image Slots Container -->
                            <div id="editImageSlotsContainer" class="d-flex flex-column gap-2 mb-3">
                                <div class="edit-image-slot-card p-2 bg-light border rounded-3 d-flex align-items-center justify-content-between gap-2">
                                    <div class="flex-grow-1">
                                        <input type="file" name="new_images[]" class="form-control form-control-sm rounded-3" accept="image/*" onchange="previewEditSlotImage(this)">
                                    </div>
                                    <div class="slot-preview-box d-none" style="width: 50px; height: 50px;">
                                        <img src="" class="img-fluid rounded border slot-preview-img" style="width: 50px; height: 50px; object-fit: cover;">
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" onclick="removeEditSlot(this)" title="Remove Slot">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark btn-sm rounded-pill w-100">+ Upload Selected Images</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Date-Wise Add Stock Batch Modal -->
    <div class="modal fade" id="addStockBatchModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <form action="{{ route('admin.products.add-stock-batch', $product->id) }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom py-3">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-boxes-stacked text-warning me-2"></i> Add Stock Batch (Date-Wise)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Stock Arrival Date</label>
                            <input type="date" name="stock_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Select Product Size</label>
                            <select name="product_size_id" class="form-select rounded-3" required>
                                @foreach($product->sizes as $sz)
                                    <option value="{{ $sz->id }}">Size: {{ $sz->size }} (Current: {{ $sz->stock }} pcs)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Quantity to Add (pcs)</label>
                            <input type="text" inputmode="numeric" pattern="[0-9]*" name="quantity_to_add" class="form-control rounded-3" value="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Batch Reference / Reason Note</label>
                            <input type="text" name="reason_note" class="form-control rounded-3" placeholder="e.g. Received shipment batch #104 from supplier" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning rounded-pill fw-bold px-4">+ ADD STOCK</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function calcDiscount() {
        const price = parseFloat(document.getElementById('priceInput').value) || 0;
        const typeSelect = document.getElementById('discountTypeSelect');
        const type = typeSelect ? typeSelect.value : 'none';
        const valInput = document.getElementById('discountValueInput');
        const valContainer = document.getElementById('discountValueContainer');
        const priceCol = document.getElementById('basePriceCol');
        const typeCol = document.getElementById('discountTypeCol');

        let val = parseFloat(valInput ? valInput.value : 0) || 0;

        if (type === 'none') {
            if (valContainer) valContainer.classList.add('d-none');
            if (priceCol) priceCol.className = 'col-md-6';
            if (typeCol) typeCol.className = 'col-md-6';
            val = 0;
        } else {
            if (valContainer) valContainer.classList.remove('d-none');
            if (priceCol) priceCol.className = 'col-md-4';
            if (typeCol) typeCol.className = 'col-md-4';
        }

        let finalPrice = price;
        if (type === 'fixed') {
            finalPrice = Math.max(0, price - val);
        } else if (type === 'percentage') {
            finalPrice = Math.max(0, price - (price * (val / 100)));
        }

        const display = document.getElementById('finalPriceDisplay');
        if (display) display.innerText = '₹' + finalPrice.toFixed(2);
    }

    function addNewSizeRow() {
        const container = document.getElementById('newSizesContainer');
        const div = document.createElement('div');
        div.className = 'p-3 border rounded-3 bg-white mb-2 shadow-sm position-relative';
        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-warning text-dark fw-bold small">New Size Row</span>
                <button type="button" class="btn btn-sm btn-outline-danger border-0 py-0 px-2 fw-bold" onclick="this.closest('.p-3').remove()">
                    <i class="fa-solid fa-xmark me-1"></i> Remove
                </button>
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold mb-1">Size Label *</label>
                    <input type="text" name="new_sizes[]" class="form-control form-control-sm rounded-3" placeholder="e.g. XL" required>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold mb-1">Stock (pcs) *</label>
                    <input type="text" inputmode="numeric" pattern="[0-9]*" name="new_stocks[]" class="form-control form-control-sm rounded-3" value="5" required>
                </div>
                <div class="col-4 col-md-2">
                    <label class="form-label small text-muted mb-1">Chest (inch)</label>
                    <input type="text" name="new_chests[]" class="form-control form-control-sm rounded-3" placeholder="e.g. 40&quot;">
                </div>
                <div class="col-4 col-md-2">
                    <label class="form-label small text-muted mb-1">Waist (inch)</label>
                    <input type="text" name="new_waists[]" class="form-control form-control-sm rounded-3" placeholder="e.g. 36&quot;">
                </div>
                <div class="col-4 col-md-2">
                    <label class="form-label small text-muted mb-1">Length (inch)</label>
                    <input type="text" name="new_lengths[]" class="form-control form-control-sm rounded-3" placeholder="e.g. 42&quot;">
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    // Edit Product Dynamic Image Slot Management
    function addEditImageSlot() {
        const container = document.getElementById('editImageSlotsContainer');
        const div = document.createElement('div');
        div.className = 'edit-image-slot-card p-2 bg-light border rounded-3 d-flex align-items-center justify-content-between gap-2';
        div.innerHTML = `
            <div class="flex-grow-1">
                <input type="file" name="new_images[]" class="form-control form-control-sm rounded-3" accept="image/*" onchange="previewEditSlotImage(this)">
            </div>
            <div class="slot-preview-box d-none" style="width: 50px; height: 50px;">
                <img src="" class="img-fluid rounded border slot-preview-img" style="width: 50px; height: 50px; object-fit: cover;">
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" onclick="removeEditSlot(this)" title="Remove Slot">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;
        container.appendChild(div);
    }

    function previewEditSlotImage(input) {
        const slotCard = input.closest('.edit-image-slot-card');
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

    function removeEditSlot(btn) {
        const container = document.getElementById('editImageSlotsContainer');
        const slots = container.querySelectorAll('.edit-image-slot-card');
        if (slots.length > 1) {
            btn.closest('.edit-image-slot-card').remove();
        } else {
            const slotCard = btn.closest('.edit-image-slot-card');
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
                const item = cb.closest('.category-item');
                return item ? item.innerText.trim() : '';
            }).filter(Boolean);

            if (checkboxes.length === 1) {
                btnText.innerText = labels[0] || '1 Selected';
            } else if (labels.length > 0) {
                btnText.innerText = `${checkboxes.length} Selected (${labels.slice(0, 2).join(', ')}${labels.length > 2 ? '...' : ''})`;
            } else {
                btnText.innerText = `${checkboxes.length} Selected`;
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
        calcDiscount();
    });
</script>
@endsection
