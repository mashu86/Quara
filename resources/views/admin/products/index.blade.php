@extends('layouts.admin')

@section('title', 'Product Master - ' . $siteName . ' Admin')

@section('content')
<style>
    /* Prevent page-level horizontal overflow */
    #products-table-card {
        max-width: 100% !important;
        overflow: hidden !important;
    }
    #products-table-scroll-container {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: auto !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    .products-table {
        min-width: 780px;
        margin-bottom: 0;
    }
    .prod-sticky-col {
        position: sticky;
        left: 0;
        z-index: 5;
        background-color: #ffffff !important;
        box-shadow: 2px 0 6px rgba(0, 0, 0, 0.08);
    }
    thead .prod-sticky-col {
        z-index: 6;
        background-color: #f8f9fa !important;
    }
    .prod-img-wrapper {
        position: relative;
        width: 44px;
        height: 56px;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
    }
    .prod-img-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.85;
        transition: opacity 0.2s ease;
    }
    .prod-img-wrapper:hover .prod-img-overlay {
        opacity: 1;
        background: rgba(0, 0, 0, 0.55);
    }
    .prod-action-btn {
        width: 30px;
        height: 30px;
        font-size: 0.78rem;
    }

    .mobile-product-card {
        border: 1px solid rgba(0, 0, 0, 0.07);
        border-radius: 1rem;
        box-shadow: 0 4px 14px rgba(28, 25, 20, 0.08);
    }
    .mobile-product-image-wrap {
        position: relative;
        aspect-ratio: 4 / 4.8;
        overflow: hidden;
        background: #f5f3ef;
    }
    .mobile-product-image {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .mobile-product-ribbon,
    .mobile-product-offer {
        position: absolute;
        top: 0.5rem;
        z-index: 1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
    }
    .mobile-product-ribbon { left: 0.5rem; }
    .mobile-product-offer { right: 0.5rem; }
    .mobile-product-name {
        min-height: 2.4em;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .mobile-product-category {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .mobile-product-action {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 0.65rem;
    }

    @media (max-width: 767.98px) {
        #products-mobile-grid { --bs-gutter-x: 0.45rem; --bs-gutter-y: 0.55rem; }
        #products-mobile-grid > .col { display: flex; }
        .mobile-product-card { width: 100%; border-radius: 0.7rem; box-shadow: 0 2px 8px rgba(28, 25, 20, 0.07); }
        .mobile-product-image-wrap { height: clamp(92px, 29vw, 132px); aspect-ratio: auto; }
        .mobile-product-image { object-fit: contain; padding: 0.2rem; }
        .mobile-product-card .card-body { padding: 0.42rem !important; }
        .mobile-product-name { min-height: 2.2em; font-size: 0.72rem; line-height: 1.1; margin-bottom: 0.25rem !important; }
        .mobile-product-card .text-gold { font-size: 0.75rem; }
        .mobile-product-card .small,
        .mobile-product-card .badge { font-size: 0.56rem; }
        .mobile-product-card .card-body > .d-flex.mb-2 { margin-bottom: 0.28rem !important; }
        .mobile-product-card .card-body > .d-flex.flex-wrap { gap: 0.2rem !important; margin-bottom: 0.28rem !important; }
        .mobile-product-category { max-width: 100%; padding: 0.2em 0.4em; }
        .mobile-product-stock { margin-bottom: 0.28rem !important; }
        .mobile-product-stock .mb-1 { margin-bottom: 0.15rem !important; }
        .mobile-product-stock .gap-1 { gap: 0.18rem !important; }
        .mobile-product-card .mobile-product-ribbon,
        .mobile-product-card .mobile-product-offer { top: 0.3rem; font-size: 0.54rem; }
        .mobile-product-ribbon { left: 0.3rem; }
        .mobile-product-offer { right: 0.3rem; }
        .mobile-product-action { width: 24px; height: 24px; font-size: 0.62rem; border-radius: 0.45rem; }
        .mobile-product-card .border-top { padding-top: 0.28rem !important; }
        .mobile-product-card .pt-2 { padding-top: 0.28rem !important; }
        .mobile-product-card .gap-2 { gap: 0.3rem !important; }
        .mobile-product-card .mb-2 { margin-bottom: 0.28rem !important; }
        .mobile-product-categories { max-height: 1.1rem; overflow: hidden; }
        .mobile-booking-block .book-product-btn { font-size: 0.56rem !important; padding: 0.1rem 0.45rem !important; }
        .mobile-booking-block .view-bookings-btn { font-size: 0.56rem !important; }
        .mobile-booking-block .mt-1 { margin-top: 0.15rem !important; }
        .mobile-product-card .form-switch .form-check-input { width: 1.9em !important; height: 1em !important; }
    }

    /* Phones: keep two cards per row but make each card shorter so more rows fit on screen. */
    @media (max-width: 575.98px) {
        #products-mobile-grid { --bs-gutter-y: 0.4rem; }
        .mobile-product-image-wrap { height: clamp(70px, 22vw, 92px); }
        .mobile-product-card .card-body { padding: 0.32rem !important; }
        .mobile-product-name { min-height: 0; -webkit-line-clamp: 1; line-clamp: 1; }
        .mobile-booking-block { display: flex; align-items: center; justify-content: space-between; gap: 0.25rem; }
        .mobile-booking-block .mt-1 { margin-top: 0 !important; }
        .mobile-product-action { width: 22px; height: 22px; }
    }

    @media (min-width: 992px) {
        #products-mobile-grid { display: none !important; }
    }

    @media (max-width: 767.98px) {
        .products-table {
            min-width: 680px !important;
        }
        .products-table th, .products-table td {
            font-size: 0.72rem !important;
            padding: 0.4rem 0.35rem !important;
            white-space: nowrap;
        }
        .products-table .prod-sticky-col {
            min-width: 95px !important;
            max-width: 105px !important;
            padding-left: 0.2rem !important;
            padding-right: 0.2rem !important;
            white-space: normal !important;
        }
        .products-table .prod-img-wrapper {
            width: 36px !important;
            height: 48px !important;
        }
        .products-table .prod-name-text {
            font-size: 0.68rem !important;
            max-width: 95px !important;
            line-height: 1.15 !important;
            word-break: break-word !important;
        }
        .products-table .badge {
            font-size: 0.60rem !important;
            padding: 0.2em 0.4em !important;
        }
        .products-table .prod-action-btn {
            width: 26px !important;
            height: 26px !important;
            font-size: 0.65rem !important;
        }
    }

    @media (max-width: 576px) {
        #productPreviewModal .modal-dialog {
            margin: 0.5rem auto !important;
            max-width: 94vw !important;
            min-height: calc(100vh - 1rem) !important;
            min-height: calc(100dvh - 1rem) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        #productPreviewModal .modal-content {
            max-height: 85vh !important;
            max-height: 85dvh !important;
            width: 100% !important;
            border-radius: 1rem !important;
        }
        #productPreviewModal .modal-body {
            min-height: 180px !important;
            max-height: 60vh !important;
            max-height: 60dvh !important;
        }
        #productPreviewModal .modal-body img {
            max-height: 55vh !important;
            max-height: 55dvh !important;
        }
    }
</style>

@php
    $activeFilterCount = (request()->filled('search') ? 1 : 0)
        + (request()->filled('category_id') ? 1 : 0)
        + (request()->filled('status') ? 1 : 0)
        + (request()->filled('stock_status') ? 1 : 0)
        + (request()->filled('sort') && request()->sort !== 'newest' ? 1 : 0);
@endphp

<!-- Header Row -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4" style="max-width: 100%;">
    <div>
        <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.1rem;">Product Master</h4>
        <p class="text-muted small mb-0 d-none d-sm-block">Manage product inventory, pricing, discounts and variants</p>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2 w-100 w-sm-auto justify-content-start justify-content-sm-end">
        <a href="{{ route('admin.products.booked-conflicts') }}" class="btn btn-outline-dark rounded-3 fw-bold btn-sm px-2.5 px-sm-3 py-1.5 text-nowrap shadow-sm me-1 me-sm-0" style="font-size: 0.78rem;" title="Audit Booked Products Conflict">
            <i class="fa-solid fa-wrench me-1 text-warning"></i><span>Conflicts</span>
        </a>
        <a href="{{ route('admin.products.create') }}" class="btn btn-warning rounded-3 fw-bold btn-sm px-2.5 px-sm-3 py-1.5 text-nowrap shadow-sm me-1 me-sm-0" style="font-size: 0.78rem; background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Add New Product">
            <i class="fa-solid fa-plus me-1"></i><span>Add Product</span>
        </a>

        <!-- Mobile Filter Icon Button (d-lg-none) -->
        <button type="button" class="btn btn-dark rounded-3 btn-sm px-2.5 py-1.5 position-relative d-lg-none shadow-sm" style="font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#productFilterModal" title="Filter Products">
            <i class="fa-solid fa-sliders text-warning me-1"></i>Filter
            @if($activeFilterCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size: 0.62rem;">{{ $activeFilterCount }}</span>
            @endif
        </button>

        @if($activeFilterCount > 0)
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-3 btn-sm px-2 py-1.5 d-lg-none" style="font-size: 0.78rem;" title="Clear Filters">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </div>
</div>

<!-- Desktop Search & Filters (d-none d-lg-block) -->
<div class="card border-0 rounded-4 shadow-sm mb-4 d-none d-lg-block">
    <div class="card-body py-3">
        <form action="{{ route('admin.products.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-lg-3">
                <input type="text" name="search" class="form-control rounded-3" placeholder="Search product name..." value="{{ request()->search }}">
            </div>
            <div class="col-lg-2">
                <select name="category_id" class="form-select rounded-3">
                    <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request()->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ ($cat->is_offer_category || $cat->is_combo_offer) ? '👑 ' . $cat->name . ' (Offer Category)' : $cat->name }}
                                </option>
                            @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <select name="stock_status" class="form-select rounded-3">
                    <option value="">All Products</option>
                    <option value="in_stock" {{ request()->stock_status === 'in_stock' ? 'selected' : '' }}>Available (In Stock)</option>
                    <option value="reserved" {{ request()->stock_status === 'reserved' ? 'selected' : '' }}>🔒 Booked Products</option>
                    <option value="out_of_stock" {{ request()->stock_status === 'out_of_stock' ? 'selected' : '' }}>Sold Out</option>
                    <option value="return_to_stock" {{ request()->stock_status === 'return_to_stock' ? 'selected' : '' }}>Returned to Stock</option>
                    <option value="do_not_restock" {{ request()->stock_status === 'do_not_restock' ? 'selected' : '' }}>Frozen Stock</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select name="sort" class="form-select rounded-3">
                    <option value="newest" {{ request()->sort === 'newest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request()->sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="price_low" {{ request()->sort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_high" {{ request()->sort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                </select>
            </div>
            <div class="col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark rounded-3 flex-grow-1 fw-bold text-nowrap d-flex align-items-center justify-content-center gap-1">
                    <i class="fa-solid fa-sliders text-warning"></i> Apply Filter
                </button>
                @if(count(request()->all()) > 0)
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-3 text-nowrap d-flex align-items-center justify-content-center px-3" title="Clear Filters">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Product Mobile Filter Modal (d-lg-none) -->
<div class="modal fade d-lg-none" id="productFilterModal" tabindex="-1" aria-labelledby="productFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold fs-6" id="productFilterModalLabel">
                    <i class="fa-solid fa-sliders text-warning me-2"></i> Filter Products
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.products.index') }}" method="GET">
                <div class="modal-body p-3.5">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Search Name / Keyword</label>
                        <input type="text" name="search" class="form-control rounded-3" placeholder="Search product name..." value="{{ request()->search }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Category</label>
                        <select name="category_id" class="form-select rounded-3">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request()->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ ($cat->is_offer_category || $cat->is_combo_offer) ? '👑 ' . $cat->name . ' (Offer Category)' : $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request()->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request()->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Stock Availability</label>
                        <select name="stock_status" class="form-select rounded-3">
                            <option value="">All Products</option>
                            <option value="in_stock" {{ request()->stock_status === 'in_stock' ? 'selected' : '' }}>Available (In Stock)</option>
                            <option value="reserved" {{ request()->stock_status === 'reserved' ? 'selected' : '' }}>🔒 Booked Products</option>
                            <option value="out_of_stock" {{ request()->stock_status === 'out_of_stock' ? 'selected' : '' }}>Sold Out</option>
                            <option value="return_to_stock" {{ request()->stock_status === 'return_to_stock' ? 'selected' : '' }}>Returned to Stock</option>
                            <option value="do_not_restock" {{ request()->stock_status === 'do_not_restock' ? 'selected' : '' }}>Frozen Stock</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Sort By</label>
                        <select name="sort" class="form-select rounded-3">
                            <option value="newest" {{ request()->sort === 'newest' ? 'selected' : '' }}>Newest</option>
                            <option value="oldest" {{ request()->sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                            <option value="price_low" {{ request()->sort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request()->sort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">Reset</a>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark btn-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-check me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Instagram Overlay Measurement Format Header & Settings Button -->
<div class="card border-0 rounded-4 shadow-sm mb-3 overflow-hidden" style="max-width: 100%;">
    <div class="card-body py-3 px-3 d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 0;">
            <span class="badge bg-dark text-white p-2 rounded-circle flex-shrink-0"><i class="fa-brands fa-instagram text-warning fs-6"></i></span>
            <div style="min-width: 0;">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.84rem; line-height: 1.4; overflow-wrap: anywhere;">Instagram Image Measurement Label Settings</h6>
            </div>
        </div>
        <button type="button" class="btn btn-dark rounded-pill btn-sm px-3 py-2 fw-bold text-nowrap shadow-sm d-flex align-items-center justify-content-center gap-2 flex-shrink-0" data-bs-toggle="offcanvas" data-bs-target="#instaSettingsDrawer" aria-controls="instaSettingsDrawer" style="font-size: 0.78rem; min-height: 44px;" title="Configure Instagram Image Measurement Label Settings">
            <i class="fa-solid fa-gear text-warning"></i> <span>Settings</span>
        </button>
    </div>
</div>

<!-- Mobile Product Cards -->
<div class="row row-cols-2 g-2 d-lg-none mb-3" id="products-mobile-grid">
    @include('admin.products.partials.mobile_cards', ['products' => $products])
</div>

<!-- Desktop Products Table -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden d-none d-lg-block" id="products-table-card" style="max-width: 100%;">
    <div class="card-body p-0" style="max-width: 100%; overflow: hidden;">
        <div id="products-table-scroll-container" style="display: block; width: 100%; max-width: 100%; max-height: 75vh; overflow-x: auto; overflow-y: auto; -webkit-overflow-scrolling: touch;">
            <table class="table products-table align-middle mb-0">
                <thead class="table-light sticky-top shadow-sm" style="z-index: 5;">
                    <tr>
                        <th class="prod-sticky-col text-center" style="min-width: 110px;">Product</th>
                        <th>Category</th>
                        <th>Original Price</th>
                        <th>Discount</th>
                        <th>Selling Price</th>
                        <th>Size-wise Stock</th>
                        <th>Booked Stock</th>
                        <th>Active</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-desktop-tbody">
                    @forelse($products as $product)
                        @include('admin.products.partials.desktop_rows', ['products' => collect([$product])])
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">No products found matching filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.products.bookings.index') }}" class="btn btn-outline-dark btn-sm rounded-pill"><i class="fa-solid fa-bookmark me-1"></i> Size Bookings</a>
</div>
<div class="text-center py-2">
    <div id="infinite-scroll-loading" class="d-none text-muted small py-1">
        <div class="spinner-border spinner-border-sm text-warning me-1" role="status"></div>
        Loading more products...
    </div>
    <div id="infinite-scroll-end" class="{{ $products->hasMorePages() ? 'd-none' : '' }} text-muted small py-1">
        <i class="fa-solid fa-circle-check text-success me-1"></i> All {{ $products->total() }} products loaded
    </div>
</div>

<!-- Toggle Booked Details Modal -->
<div class="modal fade" id="toggleBookedModal" tabindex="-1" aria-labelledby="toggleBookedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold fs-6" id="toggleBookedModalLabel">
                    <i class="fa-solid fa-user-tag text-warning me-2"></i> Mark Product as Booked
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="cancelBookedModal()"></button>
            </div>
            <div class="modal-body p-3.5">
                <p class="small text-muted mb-3">Marking <strong id="bookedModalProductName" class="text-dark">Product</strong> as Booked. Add the booking details below.</p>
                <div class="mb-3">
                    <label for="modalBookingSize" class="form-label fw-bold small">Size <span class="text-danger">*</span></label>
                    <select id="modalBookingSize" class="form-select rounded-3" required></select>
                </div>
                <div class="mb-3">
                    <label for="modalBookingQuantity" class="form-label fw-bold small">Quantity <span class="text-danger">*</span></label>
                    <input type="number" id="modalBookingQuantity" class="form-control rounded-3" min="1" value="1" required>
                    <div id="modalBookingStock" class="form-text">Available stock</div>
                </div>
                <div class="mb-3">
                    <label for="modalBookingType" class="form-label fw-bold small">Booked type <span class="text-danger">*</span></label>
                    <select id="modalBookingType" class="form-select rounded-3" required>
                        <option value="business_whatsapp">Booked for business WhatsApp</option>
                        <option value="whatsapp_customer">Booked by WhatsApp customer</option>
                        <option value="instagram_customer">Booked by Instagram customer</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="modalBookedByInput" class="form-label fw-bold small" id="modalBookedByLabel">Customer name or phone <span class="text-danger">*</span></label>
                    <input type="text" id="modalBookedByInput" class="form-control rounded-3" placeholder="Name or phone number">
                </div>
                <div class="mb-1">
                    <label for="modalBookingDate" class="form-label fw-bold small">Booked date <span class="text-danger">*</span></label>
                    <input type="date" id="modalBookingDate" class="form-control rounded-3" required>
                </div>
            </div>
            <div class="modal-footer bg-light rounded-bottom-4 border-0 px-3 py-2.5">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal" onclick="cancelBookedModal()">Cancel</button>
                <button type="button" class="btn btn-warning btn-sm rounded-pill fw-bold px-4" id="saveBookedModalBtn" onclick="submitBookedModal()" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                    <i class="fa-solid fa-check me-1"></i> Save Booking
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Booked Customers List Modal -->
<div class="modal fade" id="bookingListModal" tabindex="-1" aria-labelledby="bookingListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white border-0 py-2.5">
                <h5 class="modal-title font-serif fw-bold fs-6" id="bookingListModalLabel">
                    <i class="fa-solid fa-users text-warning me-2"></i> Booked Customers
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <p class="small text-muted mb-3">Bookings for <strong id="bookingListProductName" class="text-dark">Product</strong>. Turn a switch off to remove that booking.</p>
                <div id="bookingListBody"></div>
            </div>
        </div>
    </div>
</div>

@include('admin.products.partials.preview-modal')

<!-- Instagram Overlay Settings Offcanvas Drawer -->
<div class="offcanvas offcanvas-end rounded-start-4 border-0 shadow-lg" tabindex="-1" id="instaSettingsDrawer" aria-labelledby="instaSettingsDrawerLabel" style="width: 390px; max-width: 92vw;">
    <div class="offcanvas-header bg-dark text-white p-3">
        <h5 class="offcanvas-title font-serif fw-bold fs-6 d-flex align-items-center gap-2 mb-0" id="instaSettingsDrawerLabel">
            <i class="fa-brands fa-instagram text-warning fs-5"></i> Instagram Label Settings
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3.5">
        <!-- 1. Display Format -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">
                <i class="fa-solid fa-list-check text-warning"></i> Display Format
            </label>
            <div class="d-flex flex-column gap-2">
                <div class="form-check custom-radio-card p-2.5 rounded-3 border bg-light cursor-pointer">
                    <input class="form-check-input mt-1 cursor-pointer" type="radio" name="insta_drawer_format" id="instaFormatPriceCWL" value="price_cwl" onchange="updateInstaSettingFromDrawer('format', 'price_cwl')">
                    <label class="form-check-label cursor-pointer w-100" for="instaFormatPriceCWL">
                        <strong class="d-block text-dark small">1) Price, C, W, L</strong>
                        <span class="text-muted extra-small d-block" style="font-size: 0.70rem;">Shows Price line followed by C, W, L lines.</span>
                    </label>
                </div>
                <div class="form-check custom-radio-card p-2.5 rounded-3 border bg-light cursor-pointer">
                    <input class="form-check-input mt-1 cursor-pointer" type="radio" name="insta_drawer_format" id="instaFormatPriceCWLS" value="price_cwl_size" onchange="updateInstaSettingFromDrawer('format', 'price_cwl_size')">
                    <label class="form-check-label cursor-pointer w-100" for="instaFormatPriceCWLS">
                        <strong class="d-block text-dark small">2) Price, C, W, L and Size</strong>
                        <span class="text-muted extra-small d-block" style="font-size: 0.70rem;">Shows Price, C, W, L and available Size list.</span>
                    </label>
                </div>
                <div class="form-check custom-radio-card p-2.5 rounded-3 border bg-light cursor-pointer">
                    <input class="form-check-input mt-1 cursor-pointer" type="radio" name="insta_drawer_format" id="instaFormatSizePriceOnly" value="size_price_only" onchange="updateInstaSettingFromDrawer('format', 'size_price_only')">
                    <label class="form-check-label cursor-pointer w-100" for="instaFormatSizePriceOnly">
                        <strong class="d-block text-dark small">3) Size and Price only (no C, W, L)</strong>
                        <span class="text-muted extra-small d-block" style="font-size: 0.70rem;">Shows only Price and Size line (hides C, W, L).</span>
                    </label>
                </div>
                <div class="form-check custom-radio-card p-2.5 rounded-3 border bg-light cursor-pointer">
                    <input class="form-check-input mt-1 cursor-pointer" type="radio" name="insta_drawer_format" id="instaFormatAutoFallback" value="auto_fallback" onchange="updateInstaSettingFromDrawer('format', 'auto_fallback')">
                    <label class="form-check-label cursor-pointer w-100" for="instaFormatAutoFallback">
                        <strong class="d-block text-dark small">4) Auto (Size & Price only if C, W, L blank)</strong>
                        <span class="text-muted extra-small d-block" style="font-size: 0.70rem;">Shows C, W, L if values exist, else automatically falls back to Size & Price only.</span>
                    </label>
                </div>
            </div>
        </div>

        <hr class="my-3 opacity-25">

        <!-- 2. Measurement Form (Short or Full) -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">
                <i class="fa-solid fa-font text-warning"></i> Label Style (Form)
            </label>
            <div class="row g-2">
                <div class="col-6">
                    <input type="radio" class="btn-check" name="insta_drawer_form" id="instaFormShort" value="short" autocomplete="off" onchange="updateInstaSettingFromDrawer('form', 'short')">
                    <label class="btn btn-outline-dark btn-sm w-100 rounded-3 py-2 fw-bold d-flex flex-column align-items-center" for="instaFormShort">
                        <span style="font-size: 0.80rem;">Short</span>
                        <span class="text-muted font-monospace" style="font-size: 0.68rem;">(C, W, L)</span>
                    </label>
                </div>
                <div class="col-6">
                    <input type="radio" class="btn-check" name="insta_drawer_form" id="instaFormFull" value="full" autocomplete="off" onchange="updateInstaSettingFromDrawer('form', 'full')">
                    <label class="btn btn-outline-dark btn-sm w-100 rounded-3 py-2 fw-bold d-flex flex-column align-items-center" for="instaFormFull">
                        <span style="font-size: 0.80rem;">Full</span>
                        <span class="text-muted" style="font-size: 0.68rem;">(Chest, Waist, Length)</span>
                    </label>
                </div>
            </div>
        </div>

        <hr class="my-3 opacity-25">

        <!-- 3. Background Color -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center justify-content-between">
                <span><i class="fa-solid fa-fill-drip text-warning me-1"></i> Background Color</span>
                <span class="text-muted font-monospace extra-small" id="instaBgHexVal">#000000</span>
            </label>
            <div class="d-flex align-items-center gap-2 mb-2">
                <input type="color" class="form-control form-control-color rounded-3 border cursor-pointer" id="instaBgColorPicker" value="#000000" style="width: 44px; height: 38px;" onchange="updateInstaSettingFromDrawer('bgColor', this.value)">
                <input type="text" class="form-control rounded-3 font-monospace small" id="instaBgColorText" value="#000000" placeholder="#000000" oninput="updateInstaSettingFromDrawer('bgColor', this.value)">
            </div>
            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                <button type="button" class="btn btn-sm border rounded-pill px-2.5 py-1 extra-small bg-black text-white" onclick="setPresetColor('bgColor', '#000000')">Black</button>
                <button type="button" class="btn btn-sm border rounded-pill px-2.5 py-1 extra-small text-white" style="background: #1f2937;" onclick="setPresetColor('bgColor', '#1f2937')">Dark Gray</button>
                <button type="button" class="btn btn-sm border rounded-pill px-2.5 py-1 extra-small text-white" style="background: #0f172a;" onclick="setPresetColor('bgColor', '#0f172a')">Navy</button>
                <button type="button" class="btn btn-sm border rounded-pill px-2.5 py-1 extra-small text-white" style="background: #3b0764;" onclick="setPresetColor('bgColor', '#3b0764')">Purple</button>
                <button type="button" class="btn btn-sm border rounded-pill px-2.5 py-1 extra-small bg-white text-dark" onclick="setPresetColor('bgColor', '#ffffff')">White</button>
            </div>
        </div>

        <hr class="my-3 opacity-25">

        <!-- 4. Text Color -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center justify-content-between">
                <span><i class="fa-solid fa-palette text-warning me-1"></i> Text Color</span>
                <span class="text-muted font-monospace extra-small" id="instaTextHexVal">#ffffff</span>
            </label>
            <div class="d-flex align-items-center gap-2 mb-2">
                <input type="color" class="form-control form-control-color rounded-3 border cursor-pointer" id="instaTextColorPicker" value="#ffffff" style="width: 44px; height: 38px;" onchange="updateInstaSettingFromDrawer('textColor', this.value)">
                <input type="text" class="form-control rounded-3 font-monospace small" id="instaTextColorText" value="#ffffff" placeholder="#ffffff" oninput="updateInstaSettingFromDrawer('textColor', this.value)">
            </div>
            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                <button type="button" class="btn btn-sm border rounded-pill px-2.5 py-1 extra-small bg-white text-dark" onclick="setPresetColor('textColor', '#ffffff')">White</button>
                <button type="button" class="btn btn-sm border rounded-pill px-2.5 py-1 extra-small text-dark" style="background: #f59e0b;" onclick="setPresetColor('textColor', '#f59e0b')">Gold</button>
                <button type="button" class="btn btn-sm border rounded-pill px-2.5 py-1 extra-small text-dark" style="background: #facc15;" onclick="setPresetColor('textColor', '#facc15')">Yellow</button>
                <button type="button" class="btn btn-sm border rounded-pill px-2.5 py-1 extra-small text-dark" style="background: #06b6d4;" onclick="setPresetColor('textColor', '#06b6d4')">Cyan</button>
                <button type="button" class="btn btn-sm border rounded-pill px-2.5 py-1 extra-small bg-black text-white" onclick="setPresetColor('textColor', '#000000')">Black</button>
            </div>
        </div>

        <hr class="my-3 opacity-25">

        <!-- 5. Position -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">
                <i class="fa-solid fa-up-down-left-right text-warning"></i> Box Position
            </label>
            <div class="row g-2">
                <div class="col-6">
                    <input type="radio" class="btn-check" name="insta_drawer_position" id="instaPosTopLeft" value="top-left" autocomplete="off" onchange="updateInstaSettingFromDrawer('position', 'top-left')">
                    <label class="btn btn-outline-dark btn-sm w-100 rounded-3 py-2 fw-semibold extra-small text-start" for="instaPosTopLeft">
                        ↖ Top-Left
                    </label>
                </div>
                <div class="col-6">
                    <input type="radio" class="btn-check" name="insta_drawer_position" id="instaPosTopRight" value="top-right" autocomplete="off" onchange="updateInstaSettingFromDrawer('position', 'top-right')">
                    <label class="btn btn-outline-dark btn-sm w-100 rounded-3 py-2 fw-semibold extra-small text-start" for="instaPosTopRight">
                        ↗ Top-Right
                    </label>
                </div>
                <div class="col-6">
                    <input type="radio" class="btn-check" name="insta_drawer_position" id="instaPosBottomLeft" value="bottom-left" autocomplete="off" onchange="updateInstaSettingFromDrawer('position', 'bottom-left')">
                    <label class="btn btn-outline-dark btn-sm w-100 rounded-3 py-2 fw-semibold extra-small text-start" for="instaPosBottomLeft">
                        ↙ Bottom-Left
                    </label>
                </div>
                <div class="col-6">
                    <input type="radio" class="btn-check" name="insta_drawer_position" id="instaPosBottomRight" value="bottom-right" autocomplete="off" onchange="updateInstaSettingFromDrawer('position', 'bottom-right')">
                    <label class="btn btn-outline-dark btn-sm w-100 rounded-3 py-2 fw-semibold extra-small text-start" for="instaPosBottomRight">
                        ↘ Bottom-Right
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas-footer p-3 bg-light border-top d-flex align-items-center justify-content-between">
        <span class="text-success extra-small fw-bold d-none" id="instaSavedNotice"><i class="fa-solid fa-circle-check me-1"></i> Saved!</span>
        <button type="button" class="btn btn-warning rounded-pill px-4 btn-sm fw-bold ms-auto" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" data-bs-dismiss="offcanvas">
            <i class="fa-solid fa-check me-1"></i> Done & Apply
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/admin-product-preview.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.syncInstaSettingsUI();

    const desktopContainer = document.getElementById('products-table-scroll-container');
    const loadingSpinner = document.getElementById('infinite-scroll-loading');
    const noMoreNotice = document.getElementById('infinite-scroll-end');
    
    let currentPage = 1;
    let hasMorePages = {{ $products->hasMorePages() ? 'true' : 'false' }};
    let isLoading = false;
    let currentBookBtn = null;
    let bookedModalInstance = null;
    const bookingTypeInput = document.getElementById('modalBookingType');
    const bookingDateInput = document.getElementById('modalBookingDate');
    const bookedByInput = document.getElementById('modalBookedByInput');
    const bookedByField = bookedByInput?.closest('.mb-3');
    const bookedByLabel = document.getElementById('modalBookedByLabel');

    function localToday() {
        const today = new Date();
        return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    }

    function syncBookingTypeFields() {
        if (!bookingTypeInput || !bookedByInput) return;
        const type = bookingTypeInput.value;
        const businessBooking = type === 'business_whatsapp';
        bookedByField?.classList.toggle('d-none', businessBooking);
        bookedByInput.required = !businessBooking;
        if (businessBooking) bookedByInput.value = '';
        bookedByLabel.innerHTML = type === 'instagram_customer'
            ? 'Instagram name or customer name <span class="text-danger">*</span>'
            : 'Customer name or phone <span class="text-danger">*</span>';
        bookedByInput.placeholder = type === 'instagram_customer' ? 'Instagram name or customer name' : 'Name or phone number';
    }

    function openBookingModal(button) {
        currentBookBtn = button;
        bookingTypeInput.value = 'whatsapp_customer';
        bookedByInput.value = '';
        bookingDateInput.value = localToday();
        syncBookingTypeFields();
        document.getElementById('bookedModalProductName').textContent = button.dataset.productName || 'Product';
        const sizeSelect = document.getElementById('modalBookingSize');
        const sizeRows = JSON.parse(button.dataset.sizes || '[]');
        sizeSelect.innerHTML = '<option value="">Select size</option>' + sizeRows.map(row => `<option value="${row.id}" ${row.available < 1 ? 'disabled' : ''}>${row.size} (${row.available} available)</option>`).join('');
        const quantityInput = document.getElementById('modalBookingQuantity');
        quantityInput.value = '1';
        document.getElementById('modalBookingStock').textContent = 'Choose a size';
        sizeSelect.onchange = () => {
            const selected = sizeRows.find(row => String(row.id) === sizeSelect.value);
            quantityInput.max = selected?.available || 0;
            document.getElementById('modalBookingStock').textContent = selected ? `${selected.available} unbooked unit(s) available` : 'Choose a size';
        };
        bookedModalInstance = bootstrap.Modal.getOrCreateInstance(document.getElementById('toggleBookedModal'));
        bookedModalInstance.show();
        setTimeout(() => { if (bookedByInput.required) bookedByInput.focus(); }, 350);
    }

    bookingTypeInput?.addEventListener('change', syncBookingTypeFields);
    document.addEventListener('click', function(event) {
        const bookButton = event.target.closest('.book-product-btn');
        if (bookButton) { openBookingModal(bookButton); return; }
        const viewButton = event.target.closest('.view-bookings-btn');
        if (viewButton) openBookingListModal(viewButton);
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    // Status switches live in both the desktop table and the mobile cards.
    const statusToggleRoot = document;

    if (statusToggleRoot) {
        statusToggleRoot.addEventListener('change', function(event) {
            const toggle = event.target.closest('.product-status-toggle');
            if (!toggle) return;

            const previousState = !toggle.checked;
            toggle.disabled = true;
            fetch(toggle.dataset.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json().then(data => ({ response, data })))
            .then(({ response, data }) => {
                if (!response.ok || !data.success) throw new Error(data.message || 'Could not update product status.');
                document.querySelectorAll(`.product-status-toggle[data-product-id="${toggle.dataset.productId}"]`)
                    .forEach(t => { t.checked = data.status === 'active'; });
                if (toggle.closest('.form-check')) {
                    toggle.closest('.form-check').title = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                }
            })
            .catch(error => {
                toggle.checked = previousState;
                alert(error.message || 'Could not update product status.');
            })
            .finally(() => { toggle.disabled = false; });
        });
    }

    function checkAndLoadMore() {
        if (isLoading || !hasMorePages) return;

        let isNearBottom = false;

        if (window.matchMedia('(min-width: 992px)').matches && desktopContainer) {
            const scrollBottom = desktopContainer.scrollTop + desktopContainer.clientHeight;
            const scrollHeight = desktopContainer.scrollHeight;
            isNearBottom = (scrollHeight - scrollBottom) < 150;
        } else {
            const windowScrollBottom = window.innerHeight + window.scrollY;
            const docHeight = document.documentElement.scrollHeight;
            isNearBottom = (docHeight - windowScrollBottom) < 250;
        }

        if (isNearBottom) {
            loadNextProductsPage();
        }
    }

    function loadNextProductsPage() {
        isLoading = true;
        if (loadingSpinner) loadingSpinner.classList.remove('d-none');

        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('page', currentPage + 1);

        fetch(`${window.location.pathname}?${urlParams.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            isLoading = false;
            if (loadingSpinner) loadingSpinner.classList.add('d-none');

            if (data.desktop_html || data.mobile_html) {
                currentPage++;
                hasMorePages = data.has_more;

                const desktopTbody = document.getElementById('products-desktop-tbody');
                if (desktopTbody) {
                    desktopTbody.insertAdjacentHTML('beforeend', data.desktop_html);
                }

                const mobileGrid = document.getElementById('products-mobile-grid');
                if (mobileGrid && data.mobile_html) {
                    mobileGrid.insertAdjacentHTML('beforeend', data.mobile_html);
                }


                if (!hasMorePages && noMoreNotice) {
                    noMoreNotice.classList.remove('d-none');
                }
            }
        })
        .catch(err => {
            console.error('Error fetching more products:', err);
            isLoading = false;
            if (loadingSpinner) loadingSpinner.classList.add('d-none');
        });
    }

    if (desktopContainer) {
        desktopContainer.addEventListener('scroll', checkAndLoadMore, { passive: true });
    }
    window.addEventListener('scroll', checkAndLoadMore, { passive: true });
    window.addEventListener('resize', checkAndLoadMore);

    window.submitBookedModal = function() {
        if (!currentBookBtn) return;
        const productSizeId = document.getElementById('modalBookingSize').value;
        const quantity = parseInt(document.getElementById('modalBookingQuantity').value || '0', 10);
        const bookedByVal = bookedByInput ? bookedByInput.value.trim() : '';
        const bookingType = bookingTypeInput.value;
        const bookingDate = bookingDateInput.value;

        if (!productSizeId || quantity < 1) { alert('Select a size and valid quantity.'); return; }
        if (bookingType !== 'business_whatsapp' && !bookedByVal) {
            alert('Enter customer details for this booking type.');
            if (bookedByInput) bookedByInput.focus();
            return;
        }
        if (!bookingDate) {
            alert('Select the booked date.');
            bookingDateInput.focus();
            return;
        }

        const saveBtn = document.getElementById('saveBookedModalBtn');
        if (saveBtn) saveBtn.disabled = true;

        fetch(currentBookBtn.dataset.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ product_size_id: productSizeId, quantity, booked_by: bookedByVal, booking_type: bookingType, booking_date: bookingDate })
        })
        .then(res => res.json())
        .then(data => {
            if (saveBtn) saveBtn.disabled = false;
            if (data.success) {
                if (bookedModalInstance) bookedModalInstance.hide();
                window.location.reload();
            } else {
                alert(data.message || Object.values(data.errors || {}).flat()[0] || 'Error saving booking.');
            }
        })
        .catch(() => {
            if (saveBtn) saveBtn.disabled = false;
            alert('Failed to connect to server.');
        });
    };

    window.cancelBookedModal = function() {
        currentBookBtn = null;
    };

    // Booked customers list: each row has its own switch; turning it off removes that booking.
    let bookingListChanged = false;
    const bookingListModalElem = document.getElementById('bookingListModal');
    bookingListModalElem?.addEventListener('hidden.bs.modal', () => { if (bookingListChanged) window.location.reload(); });

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function openBookingListModal(button) {
        const bookings = JSON.parse(button.dataset.bookings || '[]');
        document.getElementById('bookingListProductName').textContent = button.dataset.productName || 'Product';
        document.getElementById('bookingListBody').innerHTML = bookings.map(booking => `
            <div class="d-flex align-items-center justify-content-between gap-2 border rounded-3 p-2 mb-2 booking-list-row">
                <div class="small">
                    <div class="fw-bold text-dark"><i class="fa-solid fa-user-tag text-warning me-1"></i>${escapeHtml(booking.name)}</div>
                    <div class="text-muted" style="font-size: 0.72rem;">
                        ${escapeHtml(booking.type)}
                        ${booking.size ? ` · Size <strong>${escapeHtml(booking.size)}</strong> × ${booking.quantity}` : ''}
                        ${booking.date ? ` · <i class="fa-regular fa-calendar"></i> ${escapeHtml(booking.date)}` : ''}
                    </div>
                </div>
                <div class="form-check form-switch mb-0 flex-shrink-0">
                    <input class="form-check-input booking-row-toggle" type="checkbox" role="switch" checked
                           data-url="${escapeHtml(booking.url)}" data-legacy="${booking.legacy ? 'true' : 'false'}"
                           style="width: 2.3em; height: 1.2em; cursor: pointer;" title="Turn off to remove this booking">
                    <label class="form-check-label small fw-bold text-danger ms-1" style="font-size: 0.75rem;">Booked</label>
                </div>
            </div>`).join('') || '<p class="small text-muted mb-0">No active bookings.</p>';
        bootstrap.Modal.getOrCreateInstance(bookingListModalElem).show();
    }

    bookingListModalElem?.addEventListener('change', function(event) {
        const toggle = event.target.closest('.booking-row-toggle');
        if (!toggle || toggle.checked) return;
        toggle.disabled = true;
        const body = toggle.dataset.legacy === 'true' ? { is_out_of_stock: false, booked_by: '' } : {};

        fetch(toggle.dataset.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Could not remove booking.');
            bookingListChanged = true;
            toggle.closest('.booking-list-row').remove();
            const listBody = document.getElementById('bookingListBody');
            if (!listBody.querySelector('.booking-list-row')) listBody.innerHTML = '<p class="small text-muted mb-0">No active bookings.</p>';
        })
        .catch(error => {
            toggle.disabled = false;
            toggle.checked = true;
            alert(error.message || 'Failed to connect to server.');
        });
    });

});
</script>
@endsection
