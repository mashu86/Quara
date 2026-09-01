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
    <div class="d-flex align-items-center">
        <a href="{{ route('admin.products.create') }}" class="btn btn-warning rounded-3 fw-bold btn-sm px-2.5 px-sm-3 py-1.5 text-nowrap shadow-sm me-2 me-sm-3" style="font-size: 0.78rem; background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Add New Product">
            <i class="fa-solid fa-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline"> Add Product</span>
        </a>

        <!-- Mobile Filter Icon Button (d-lg-none) -->
        <button type="button" class="btn btn-dark rounded-3 btn-sm px-2.5 py-1.5 position-relative d-lg-none shadow-sm" style="font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#productFilterModal" title="Filter Products">
            <i class="fa-solid fa-sliders text-warning"></i>
            @if($activeFilterCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size: 0.62rem;">{{ $activeFilterCount }}</span>
            @endif
        </button>

        @if($activeFilterCount > 0)
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-3 btn-sm px-2 py-1.5 d-lg-none ms-2" style="font-size: 0.78rem;" title="Clear Filters">
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
        <div class="table-responsive" id="products-table-scroll-container" style="max-height: 75vh; overflow-y: auto;">
            <table class="table align-middle mb-0">
                <thead class="table-light sticky-top shadow-sm" style="z-index: 5;">
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
                <tbody id="products-desktop-tbody">
                    @forelse($products as $product)
                        @include('admin.products.partials.desktop_rows', ['products' => collect([$product])])
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No products found matching filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-2 text-center border-top">
        <div id="infinite-scroll-loading" class="d-none text-muted small py-1">
            <div class="spinner-border spinner-border-sm text-warning me-1" role="status"></div>
            Loading more products...
        </div>
        <div id="infinite-scroll-end" class="{{ $products->hasMorePages() ? 'd-none' : '' }} text-muted small py-1">
            <i class="fa-solid fa-circle-check text-success me-1"></i> All {{ $products->total() }} products loaded
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let nextPageUrl = @json($products->nextPageUrl());
    let hasMore = @json($products->hasMorePages());
    let isLoading = false;

    const desktopContainer = document.getElementById('products-table-scroll-container');
    const desktopTbody = document.getElementById('products-desktop-tbody');
    const loadingSpinner = document.getElementById('infinite-scroll-loading');
    const endNotice = document.getElementById('infinite-scroll-end');

    function checkAndLoadMore() {
        if (isLoading || !hasMore || !nextPageUrl) return;

        let shouldLoad = false;

        if (desktopContainer && desktopContainer.offsetParent !== null) {
            const scrollBottom = desktopContainer.scrollHeight - desktopContainer.scrollTop - desktopContainer.clientHeight;
            if (scrollBottom < 150) {
                shouldLoad = true;
            }
        }

        const windowScrollBottom = document.documentElement.scrollHeight - window.innerHeight - window.scrollY;
        if (windowScrollBottom < 300) {
            shouldLoad = true;
        }

        if (shouldLoad) {
            fetchNextPage();
        }
    }

    function fetchNextPage() {
        isLoading = true;
        if (loadingSpinner) loadingSpinner.classList.remove('d-none');
        if (endNotice) endNotice.classList.add('d-none');

        fetch(nextPageUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.desktop_html && desktopTbody) {
                desktopTbody.insertAdjacentHTML('beforeend', data.desktop_html);
            }

            nextPageUrl = data.next_page_url;
            hasMore = data.has_more;
            isLoading = false;
            if (loadingSpinner) loadingSpinner.classList.add('d-none');

            if (!hasMore && endNotice) {
                endNotice.classList.remove('d-none');
            }

            initOutOfStockToggles();
        })
        .catch(err => {
            console.error('Error fetching more products:', err);
            isLoading = false;
            if (loadingSpinner) loadingSpinner.classList.add('d-none');
        });
    }

    if (desktopContainer) {
        desktopContainer.addEventListener('scroll', checkAndLoadMore);
    }
    window.addEventListener('scroll', checkAndLoadMore);

    function initOutOfStockToggles() {
        document.querySelectorAll('.out-of-stock-toggle').forEach(function(toggle) {
            if (toggle.dataset.bound === 'true') return;
            toggle.dataset.bound = 'true';

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
    }

    initOutOfStockToggles();
});
</script>
@endsection
