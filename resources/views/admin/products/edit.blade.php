@extends('layouts.admin')

@section('title', 'Edit Product - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Edit Product: {{ $product->name }}</h3>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-3">&larr; Back to Products</a>
</div>

<form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Main Form -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Basic Details</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $product->name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select rounded-3" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Base Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" id="priceInput" class="form-control rounded-3" value="{{ old('price', $product->price) }}" required oninput="calcDiscount()">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Discount Type</label>
                            <select name="discount_type" id="discountTypeSelect" class="form-select rounded-3" onchange="calcDiscount()">
                                <option value="none" {{ old('discount_type', $product->discount_type) == 'none' ? 'selected' : '' }}>No Discount</option>
                                <option value="percentage" {{ old('discount_type', $product->discount_type) == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('discount_type', $product->discount_type) == 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Discount Value</label>
                            <input type="number" step="0.01" name="discount_value" id="discountValueInput" class="form-control rounded-3" value="{{ old('discount_value', $product->discount_value) }}" oninput="calcDiscount()">
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
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-boxes-stacked text-warning me-2"></i> Size & Stock Management</h5>
                        <button type="button" class="btn btn-warning btn-sm rounded-pill font-bold" data-bs-toggle="modal" data-bs-target="#addStockBatchModal">
                            <i class="fa-solid fa-calendar-plus me-1"></i> + Add New Stock (Date-Wise)
                        </button>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase">Existing Size Stocks</label>
                        @foreach($product->sizes as $pSize)
                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-4">
                                    <input type="text" class="form-control bg-light rounded-3" value="Size: {{ $pSize->size }}" readonly>
                                </div>
                                <div class="col-5">
                                    <input type="number" name="existing_stocks[{{ $pSize->id }}]" class="form-control rounded-3" value="{{ $pSize->stock }}" min="0" required>
                                </div>
                                <div class="col-3">
                                    <span class="badge bg-dark rounded-pill px-3">Current: {{ $pSize->stock }} pcs</span>
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

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-3 shadow-sm mb-4">UPDATE PRODUCT</button>
                </div>
            </div>
</form>

            <!-- Product Gallery Management -->
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Product Gallery</h5>

                    <div class="row g-2 mb-3">
                        @foreach($product->images as $img)
                            <div class="col-6 position-relative">
                                <div class="border rounded-3 overflow-hidden p-1 text-center bg-light">
                                    <img src="{{ filter_var($img->image_path, FILTER_VALIDATE_URL) ? $img->image_path : asset($img->image_path) }}" class="img-fluid rounded mb-2" style="height: 120px; object-fit: cover; width: 100%;">

                                    <div class="d-flex justify-content-between align-items-center">
                                        @if($img->is_primary)
                                            <span class="badge bg-warning text-dark small">Primary</span>
                                        @else
                                            <form action="{{ route('admin.product-images.set-primary', $img->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none small">Make Primary</button>
                                            </form>
                                        @endif

                                        <form action="{{ route('admin.product-images.destroy', $img->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this image?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger btn-sm p-0"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

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
                            <input type="number" name="quantity_to_add" class="form-control rounded-3" value="10" min="1" required>
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

    function addNewSizeRow() {
        const container = document.getElementById('newSizesContainer');
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 align-items-center';
        div.innerHTML = `
            <div class="col-5">
                <input type="text" name="new_sizes[]" class="form-control rounded-3" placeholder="New Size (e.g. XL)" required>
            </div>
            <div class="col-5">
                <input type="number" name="new_stocks[]" class="form-control rounded-3" placeholder="Stock Qty" value="5" min="0" required>
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-outline-danger btn-sm w-100 rounded-3" onclick="this.closest('.row').remove()"><i class="fa-solid fa-xmark"></i></button>
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
</script>
@endsection
