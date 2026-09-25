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
                        <option value="{{ $cat->id }}" {{ request()->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <select name="stock_status" class="form-select rounded-3">
                    <option value="">All Products</option>
                    <option value="in_stock" {{ request()->stock_status === 'in_stock' ? 'selected' : '' }}>Available (In Stock)</option>
                    <option value="reserved" {{ request()->stock_status === 'reserved' ? 'selected' : '' }}>🔒 Booked Products</option>
                    <option value="out_of_stock" {{ request()->stock_status === 'out_of_stock' ? 'selected' : '' }}>0 Stock Available</option>
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
                                <option value="{{ $cat->id }}" {{ request()->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
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
                            <option value="out_of_stock" {{ request()->stock_status === 'out_of_stock' ? 'selected' : '' }}>0 Stock Available</option>
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
    <div class="card-body py-2.5 px-3 d-flex align-items-center justify-content-between gap-2.5">
        <div class="d-flex align-items-center gap-2" style="max-width: 100%;">
            <span class="badge bg-dark text-white p-2 rounded-circle flex-shrink-0"><i class="fa-brands fa-instagram text-warning fs-6"></i></span>
            <div class="min-w-0">
                <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 0.84rem;">Instagram Image Measurement Label Settings</h6>
            </div>
        </div>
        <button type="button" class="btn btn-dark rounded-pill btn-sm px-3 py-1.5 fw-bold text-nowrap shadow-sm d-flex align-items-center gap-1.5" data-bs-toggle="offcanvas" data-bs-target="#instaSettingsDrawer" style="font-size: 0.78rem;" title="Configure Instagram Image Measurement Label Settings">
            <i class="fa-solid fa-gear text-warning"></i> <span>Settings</span>
        </button>
    </div>
</div>

<!-- Products Table Container -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden" id="products-table-card" style="max-width: 100%;">
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
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-desktop-tbody">
                    @forelse($products as $product)
                        @include('admin.products.partials.desktop_rows', ['products' => collect([$product])])
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No products found matching filters.</td>
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
                <p class="small text-muted mb-3">Marking <strong id="bookedModalProductName" class="text-dark">Product</strong> as Booked. Enter customer details below for quick tracking.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Booked By <span class="text-danger">*</span></label>
                    <input type="text" id="modalBookedByInput" class="form-control rounded-3" placeholder="e.g. Anjali" required>
                </div>
            </div>
            <div class="modal-footer bg-light rounded-bottom-4 border-0 px-3 py-2.5">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal" onclick="cancelBookedModal()">Cancel</button>
                <button type="button" class="btn btn-warning btn-sm rounded-pill fw-bold px-4" id="saveBookedModalBtn" onclick="submitBookedModal()" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                    <i class="fa-solid fa-check me-1"></i> Save & Mark Booked
                </button>
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
    let currentBookedToggle = null;
    let bookedModalInstance = null;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const productsTbody = document.getElementById('products-desktop-tbody');

    if (productsTbody) {
        productsTbody.addEventListener('change', function(event) {
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
                toggle.checked = data.status === 'active';
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

        if (desktopContainer) {
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

            if (data.desktop_html) {
                currentPage++;
                hasMorePages = data.has_more;

                const desktopTbody = document.getElementById('products-desktop-tbody');
                if (desktopTbody) {
                    desktopTbody.insertAdjacentHTML('beforeend', data.desktop_html);
                }

                initOutOfStockToggles();

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

    function initOutOfStockToggles() {
        document.querySelectorAll('.out-of-stock-toggle').forEach(function(toggle) {
            if (toggle.dataset.bound === 'true') return;
            toggle.dataset.bound = 'true';

            toggle.addEventListener('change', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name') || 'Product';
                const currentBookedBy = this.getAttribute('data-booked-by') || '';
                const url = this.getAttribute('data-url');
                const isChecked = this.checked;

                if (isChecked) {
                    // Revert checked state until user submits modal
                    this.checked = false;
                    currentBookedToggle = this;

                    const productNameElem = document.getElementById('bookedModalProductName');
                    const bookedInputElem = document.getElementById('modalBookedByInput');
                    if (productNameElem) productNameElem.textContent = productName;
                    if (bookedInputElem) bookedInputElem.value = currentBookedBy;

                    const modalElem = document.getElementById('toggleBookedModal');
                    if (modalElem) {
                        bookedModalInstance = new bootstrap.Modal(modalElem);
                        bookedModalInstance.show();
                        setTimeout(() => { if (bookedInputElem) bookedInputElem.focus(); }, 400);
                    }
                } else {
                    // Un-booking product directly via toggle OFF
                    toggle.disabled = true;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ is_out_of_stock: false, booked_by: '' })
                    })
                    .then(res => res.json())
                    .then(data => {
                        toggle.disabled = false;
                        if (data.success) {
                            toggle.checked = false;
                            toggle.setAttribute('data-booked-by', '');
                            const label = document.getElementById('outOfStockLabel_' + productId);
                            const totalStock = parseInt(toggle.getAttribute('data-total-stock') || '0', 10);
                            if (totalStock <= 0) {
                                toggle.disabled = true;
                                toggle.style.cursor = 'not-allowed';
                            } else {
                                toggle.disabled = false;
                                toggle.style.cursor = 'pointer';
                            }
                            if (label) {
                                if (totalStock <= 0) {
                                    label.textContent = 'Sold Out';
                                    label.className = 'form-check-label small fw-bold ms-1 text-danger';
                                    label.style.cursor = 'not-allowed';
                                } else {
                                    label.textContent = 'Available';
                                    label.className = 'form-check-label small fw-bold ms-1 text-success';
                                    label.style.cursor = 'pointer';
                                }
                            }
                            const displayDiv = document.getElementById('bookedByDisplay_' + productId);
                            if (displayDiv) displayDiv.classList.add('d-none');
                        } else {
                            toggle.checked = true;
                            alert(data.message || 'Error updating stock status.');
                        }
                    })
                    .catch(err => {
                        toggle.disabled = false;
                        toggle.checked = true;
                        alert('Failed to connect to server.');
                    });
                }
            });
        });
    }

    window.submitBookedModal = function() {
        if (!currentBookedToggle) return;
        const toggle = currentBookedToggle;
        const productId = toggle.getAttribute('data-product-id');
        const url = toggle.getAttribute('data-url');
        const bookedByInput = document.getElementById('modalBookedByInput');
        const bookedByVal = bookedByInput ? bookedByInput.value.trim() : '';

        if (!bookedByVal) {
            alert('Booked By details are mandatory when marking a product as Booked!');
            if (bookedByInput) bookedByInput.focus();
            return;
        }

        toggle.disabled = true;
        const saveBtn = document.getElementById('saveBookedModalBtn');
        if (saveBtn) saveBtn.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_out_of_stock: true, booked_by: bookedByVal })
        })
        .then(res => res.json())
        .then(data => {
            toggle.disabled = false;
            if (saveBtn) saveBtn.disabled = false;

            if (data.success) {
                toggle.checked = true;
                toggle.setAttribute('data-booked-by', data.booked_by || '');

                const label = document.getElementById('outOfStockLabel_' + productId);
                if (label) {
                    label.textContent = '🔒 Booked';
                    label.className = 'form-check-label small fw-bold ms-1 text-danger';
                }

                const displayDiv = document.getElementById('bookedByDisplay_' + productId);
                const textSpan = document.getElementById('bookedByText_' + productId);
                if (displayDiv && textSpan) {
                    if (data.booked_by) {
                        textSpan.textContent = data.booked_by;
                        displayDiv.classList.remove('d-none');
                    } else {
                        displayDiv.classList.add('d-none');
                    }
                }

                if (bookedModalInstance) bookedModalInstance.hide();
                currentBookedToggle = null;
            } else {
                alert(data.message || 'Error updating booked status.');
            }
        })
        .catch(err => {
            toggle.disabled = false;
            if (saveBtn) saveBtn.disabled = false;
            alert('Failed to connect to server.');
        });
    };

    window.cancelBookedModal = function() {
        if (currentBookedToggle) {
            currentBookedToggle.checked = false;
            currentBookedToggle = null;
        }
    };

    initOutOfStockToggles();
});
</script>
@endsection
