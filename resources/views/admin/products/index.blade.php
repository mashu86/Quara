@extends('layouts.admin')

@section('title', 'Product Master - ' . $siteName . ' Admin')

@section('content')
@php
    $activeFilterCount = (request()->filled('search') ? 1 : 0)
        + (request()->filled('category_id') ? 1 : 0)
        + (request()->filled('status') ? 1 : 0)
        + (request()->filled('stock_status') ? 1 : 0)
        + (request()->filled('sort') && request()->sort !== 'newest' ? 1 : 0);
@endphp

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 gap-2">
    <div>
        <h4 class="fw-bold mb-0" style="font-size: 0.95rem;">Product Master</h4>
        <p class="text-muted small mb-0 d-none d-sm-block">Manage product inventory, pricing, discounts and variants</p>
    </div>
    <div class="d-flex align-items-center gap-1.5 gap-sm-2">
        <a href="{{ route('admin.products.create') }}" class="btn btn-warning rounded-3 fw-bold btn-sm px-2.5 px-sm-3 py-1 text-nowrap" style="font-size: 0.78rem; background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Add New Product">
            <i class="fa-solid fa-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline"> Add Product</span>
        </a>

        <!-- Mobile Filter Icon Button (d-lg-none) -->
        <button type="button" class="btn btn-dark rounded-3 btn-sm px-2.5 py-1 position-relative d-lg-none" style="font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#productFilterModal" title="Filter Products">
            <i class="fa-solid fa-sliders text-warning"></i>
            @if($activeFilterCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size: 0.62rem;">{{ $activeFilterCount }}</span>
            @endif
        </button>

        @if($activeFilterCount > 0)
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-3 btn-sm px-2 py-1 d-lg-none" style="font-size: 0.78rem;" title="Clear Filters">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </div>
</div>

<!-- Desktop Search & Filters (d-none d-lg-block) -->
<div class="card border-0 rounded-4 shadow-sm mb-4 d-none d-lg-block">
    <div class="card-body">
        <form action="{{ route('admin.products.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control rounded-3" placeholder="Search product name..." value="{{ request()->search }}">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select rounded-3">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request()->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select rounded-3">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request()->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request()->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="stock_status" class="form-select rounded-3">
                    <option value="">All Stock</option>
                    <option value="in_stock" {{ request()->stock_status === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="out_of_stock" {{ request()->stock_status === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort" class="form-select rounded-3" onchange="this.form.submit()">
                    <option value="newest" {{ request()->sort === 'newest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request()->sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="price_low" {{ request()->sort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_high" {{ request()->sort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Product Mobile Filter Modal (d-lg-none) -->
<div class="modal fade d-lg-none" id="productFilterModal" tabindex="-1" aria-labelledby="productFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold" id="productFilterModalLabel">
                    <i class="fa-solid fa-sliders text-warning me-2"></i> Filter Products
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.products.index') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Search Name / Keyword</label>
                        <input type="text" name="search" class="form-control rounded-3" placeholder="Search product name..." value="{{ request()->search }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Category</label>
                        <select name="category_id" class="form-select rounded-3">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request()->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request()->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request()->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Stock Availability</label>
                        <select name="stock_status" class="form-select rounded-3">
                            <option value="">All Stock</option>
                            <option value="in_stock" {{ request()->stock_status === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                            <option value="out_of_stock" {{ request()->stock_status === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Sort By</label>
                        <select name="sort" class="form-select rounded-3">
                            <option value="newest" {{ request()->sort === 'newest' ? 'selected' : '' }}>Newest</option>
                            <option value="oldest" {{ request()->sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                            <option value="price_low" {{ request()->sort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request()->sort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Reset</a>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-check me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Original Price</th>
                        <th>Discount</th>
                        <th>Selling Price</th>
                        <th>Size-wise Stock</th>
                        <th>Out of Stock</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php $totalStock = $product->sizes->sum('stock'); @endphp
                        <tr>
                            <td>
                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="rounded-3 border" style="width: 55px; height: 70px; object-fit: cover;">
                            </td>
                            <td>
                                <h6 class="fw-bold text-dark mb-0">{{ $product->name }}</h6>
                                <span class="text-muted small">Slug: {{ $product->slug }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1" style="max-width: 160px;">
                                    @php
                                        $cats = $product->categories->isNotEmpty() ? $product->categories : collect([$product->category])->filter();
                                    @endphp
                                    @foreach($cats as $cat)
                                        <span class="badge bg-light text-dark border">{{ $cat->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>₹{{ number_format($product->price, 2) }}</td>
                            <td>
                                @if($product->discount_type === 'fixed')
                                    <span class="badge bg-danger">₹{{ number_format($product->discount_value, 2) }} OFF</span>
                                @elseif($product->discount_type === 'percentage')
                                    <span class="badge bg-danger">{{ (int)$product->discount_value }}% OFF</span>
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
                            </td>
                            <td class="fw-bold text-gold fs-6">₹{{ number_format($product->final_price, 2) }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1" style="max-width: 180px;">
                                    @foreach($product->sizes as $pSize)
                                        <span class="badge {{ $pSize->stock > 0 ? 'bg-dark' : 'bg-danger' }}" title="Size {{ $pSize->size }}">
                                            {{ $pSize->size }}: {{ $pSize->stock }}
                                        </span>
                                    @endforeach
                                </div>
                                <div class="small fw-bold mt-1 {{ $totalStock > 0 ? 'text-success' : 'text-danger' }}">
                                    Total: {{ $totalStock }} pcs
                                </div>
                            </td>
                            <td>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input out-of-stock-toggle" type="checkbox" role="switch"
                                           id="outOfStockToggle_{{ $product->id }}"
                                           data-product-id="{{ $product->id }}"
                                           data-url="{{ route('admin.products.toggle-out-of-stock', $product->id) }}"
                                           {{ $product->is_out_of_stock ? 'checked' : '' }}
                                           style="cursor: pointer; width: 2.3em; height: 1.2em;">
                                    <label class="form-check-label small fw-bold ms-1 {{ $product->is_out_of_stock ? 'text-danger' : 'text-success' }}"
                                           id="outOfStockLabel_{{ $product->id }}"
                                           for="outOfStockToggle_{{ $product->id }}" style="cursor: pointer; font-size: 0.78rem;">
                                        {{ $product->is_out_of_stock ? 'Out of Stock' : 'Normal Stock' }}
                                    </label>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $product->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Edit Product">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Delete this product permanently?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Delete Product">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No products found matching filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3">
        {{ $products->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.out-of-stock-toggle').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
        const productId = this.getAttribute('data-product-id');
        const url = this.getAttribute('data-url');
        const isChecked = this.checked;
        const label = document.getElementById('outOfStockLabel_' + productId);

        toggle.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_out_of_stock: isChecked })
        })
        .then(response => response.json())
        .then(data => {
            toggle.disabled = false;
            if (data.success) {
                if (data.is_out_of_stock) {
                    label.textContent = 'Out of Stock';
                    label.className = 'form-check-label small fw-bold ms-1 text-danger';
                } else {
                    label.textContent = 'Normal Stock';
                    label.className = 'form-check-label small fw-bold ms-1 text-success';
                }
            } else {
                toggle.checked = !isChecked;
                alert(data.message || 'Error updating stock status.');
            }
        })
        .catch(err => {
            toggle.disabled = false;
            toggle.checked = !isChecked;
            alert('Failed to connect to server. Please try again.');
        });
    });
});
</script>
@endsection
