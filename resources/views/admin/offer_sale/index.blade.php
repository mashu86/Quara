@extends('layouts.admin')

@section('title', 'Offer Sale Manager - ' . $siteName . ' Admin')

@section('content')
@php
    $selectedProductCategoryIds = collect(request('product_category_ids', []))
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->unique()
        ->values()
        ->all();
@endphp
<style>
    :root {
        --qw-admin-card-bg: #ffffff;
        --qw-admin-border-color: #e2e8f0;
        --qw-admin-primary: #0f172a;
        --qw-gold-accent: #d4af37;
        --qw-gold-hover: #b89628;
    }

    .offer-sale-container {
        font-family: inherit;
    }

    /* Active Offer Controller Styling */
    .active-offer-card {
        background: #ffffff;
        border: 1px solid var(--qw-admin-border-color);
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
    }

    .offer-option-card {
        cursor: pointer;
        transition: all 0.15s ease;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #ffffff;
        padding: 6px 10px;
        position: relative;
    }
    .offer-option-card:hover {
        border-color: var(--qw-gold-accent);
        background: #fffdf5;
    }
    .offer-option-card.active-combo {
        border-color: #d4af37 !important;
        background: #fffdf5 !important;
    }
    .offer-option-card.active-discount {
        border-color: #ef4444 !important;
        background: #fef2f2 !important;
    }

    /* Tab Custom Styling */
    .offer-nav-tab {
        font-weight: 700;
        border-radius: 50px !important;
        padding: 6px 16px !important;
        font-size: 0.78rem;
        color: #475569;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0 !important;
        transition: all 0.2s ease;
    }
    .offer-nav-tab:hover {
        color: #0f172a;
        background-color: #e2e8f0;
    }
    .offer-nav-tab.active {
        background-color: #0f172a !important;
        color: #ffffff !important;
        border-color: #0f172a !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15) !important;
    }

    /* Product Grid & List Container */
    .product-list-container {
        min-height: 400px;
        max-height: 68vh;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-y;
        border: 2px dashed #cbd5e1;
        border-radius: 10px;
        background-color: #f8fafc;
        padding: 8px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .product-list-container.drag-over {
        border-color: var(--qw-gold-accent);
        background-color: #fffdf5;
    }

    /* Product Cards */
    .bulk-product-card {
        cursor: grab;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        user-select: none;
        touch-action: pan-y;
        border-radius: 8px !important;
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02) !important;
        padding: 6px 8px !important;
    }
    .bulk-product-card:hover {
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04) !important;
        border-color: var(--qw-gold-accent) !important;
    }
    .bulk-product-card:active {
        cursor: grabbing;
    }
    .bulk-product-card.sortable-ghost {
        opacity: 0.4;
        background-color: #f1f5f9;
        border: 2px dashed var(--qw-gold-accent) !important;
    }
    .bulk-product-card.sortable-chosen {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    }

    .bulk-card-title {
        font-size: 0.76rem;
        font-weight: 600;
        line-height: 1.25;
        margin-bottom: 2px;
        color: #0f172a;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
    }

    /* Circular Action Button (+ and x) */
    .bulk-action-btn-circle {
        width: 28px !important;
        height: 28px !important;
        border-radius: 50% !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        transition: transform 0.15s ease, background-color 0.15s ease;
    }
    .bulk-action-btn-circle:hover {
        transform: scale(1.08);
    }

    /* Ultra-Compact Mobile Responsive Styling (< 768px) */
    @media (max-width: 767.98px) {
        .offer-sale-container {
            padding: 0 !important;
        }
        .offer-sale-header {
            gap: 4px !important;
            margin-bottom: 8px !important;
        }
        .offer-sale-header h4 {
            font-size: 0.90rem !important;
        }
        .offer-sale-header p {
            font-size: 0.65rem !important;
        }
        .bulk-manager-header-btns {
            width: 100%;
            display: flex;
            flex-direction: row !important;
            gap: 6px !important;
        }
        .bulk-manager-header-btns .btn {
            flex: 1 1 50%;
            justify-content: center;
            font-size: 0.65rem !important;
            padding: 4px 8px !important;
            white-space: nowrap;
            border-radius: 30px !important;
        }

        /* Active Offer Card Mobile Sizing */
        .active-offer-card {
            border-radius: 8px !important;
            margin-bottom: 8px !important;
        }
        .active-offer-card .card-header {
            padding: 6px 8px !important;
        }
        .active-offer-card .card-header h6 {
            font-size: 0.72rem !important;
        }
        .active-offer-card .card-header .badge {
            font-size: 0.55rem !important;
            padding: 1px 5px !important;
        }
        .active-offer-card .card-body {
            padding: 6px 8px !important;
        }
        .offer-option-card {
            padding: 5px 7px !important;
            border-radius: 6px !important;
            border: 1px solid #e2e8f0 !important;
        }
        .offer-option-card .fw-bold {
            font-size: 0.70rem !important;
        }
        .offer-option-card .text-muted {
            font-size: 0.60rem !important;
        }
        .offer-option-card .badge {
            font-size: 0.52rem !important;
            padding: 1px 4px !important;
        }
        .form-check-input {
            width: 13px !important;
            height: 13px !important;
        }

        /* Category Selection & Search Tabs */
        .offer-nav-tab {
            flex: 1 1 45%;
            text-align: center;
            padding: 3px 6px !important;
            font-size: 0.65rem !important;
            border-radius: 20px !important;
        }
        .form-select-sm, .form-control {
            font-size: 0.65rem !important;
            height: 28px !important;
            padding: 2px 6px !important;
        }
        .form-label {
            font-size: 0.60rem !important;
            margin-bottom: 2px !important;
        }

        /* Available & Assigned Product Containers */
        .product-list-container {
            min-height: 200px !important;
            max-height: 48vh !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            touch-action: pan-y !important;
            padding: 5px !important;
            gap: 5px !important;
            border-radius: 8px !important;
        }
        .bulk-product-card {
            padding: 5px 6px !important;
            border-radius: 6px !important;
            touch-action: pan-y !important;
        }
        .bulk-card-img {
            width: 32px !important;
            height: 32px !important;
            border-radius: 4px !important;
        }
        .bulk-card-title {
            font-size: 0.68rem !important;
            line-height: 1.2 !important;
            margin-bottom: 1px !important;
            -webkit-line-clamp: 1 !important;
        }
        .bulk-product-card .fw-bold.text-dark {
            font-size: 0.68rem !important;
        }
        .bulk-product-card .badge {
            font-size: 0.52rem !important;
            padding: 1px 4px !important;
        }
        .bulk-action-btn-circle {
            width: 22px !important;
            height: 22px !important;
            font-size: 0.55rem !important;
        }

        /* Card Headers */
        .card-header h6 {
            font-size: 0.70rem !important;
        }
        .card-header .badge {
            font-size: 0.55rem !important;
            padding: 1px 4px !important;
        }
    }
</style>

<div class="offer-sale-container">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-2 mb-md-3 gap-2 offer-sale-header">
        <div>
            <h4 class="fw-bold mb-0.5 fs-5 fs-md-4 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-tags text-warning"></i> Offer Sale Manager
            </h4>
            <p class="text-muted small mb-0">Manage live active offer sales, combo packages, product discounts & product assignments</p>
        </div>
        <div class="bulk-manager-header-btns d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-outline-danger rounded-pill btn-sm fw-bold px-3 py-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#confirmRemoveAllOffersModal">
                <i class="fa-solid fa-trash-can me-1"></i> Clear Offers
            </button>
            <a href="{{ route('admin.display-order.index', ['combo_category_id' => $selectedCategoryId]) }}" class="btn btn-dark rounded-pill btn-sm fw-bold px-3 py-1 shadow-sm">
                <i class="fa-solid fa-arrow-down-short-wide me-1 text-warning"></i> Reorder Display
            </a>
        </div>
    </div>

    <!-- System Active Offer Controller Card -->
    <div class="card active-offer-card mb-2.5 mb-md-4 overflow-hidden">
        <div class="card-header bg-light border-bottom py-2 py-md-3 px-2.5 px-md-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                <div>
                    <h6 class="fw-bold mb-0 text-dark d-flex flex-wrap align-items-center gap-1.5" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-tower-broadcast text-danger"></i> System Active Store Offer
                        @if($activeOfferCategory)
                            <span class="badge bg-success-subtle text-success border border-success px-2 py-0.5 rounded-pill fw-bold" style="font-size: 0.65rem;">
                                <i class="fa-solid fa-bolt me-1"></i> Live: {{ $activeOfferCategory->name }}
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-0.5 rounded-pill fw-bold" style="font-size: 0.65rem;">
                                <i class="fa-solid fa-circle-xmark me-1"></i> No Active Offer
                            </span>
                        @endif
                    </h6>
                    <p class="text-muted small mb-0 mt-1 d-none d-md-block" style="font-size: 0.75rem;">
                        Selecting an offer category activates it immediately for all website visitors and ranks it #1 in navigation menus.
                    </p>
                </div>
                <div class="text-start text-md-end d-none d-md-block">
                    <span class="badge bg-dark text-white rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.70rem;">
                        {{ $offerCategories->count() }} Total Categories ({{ $comboCategories->count() }} Combo | {{ $discountCategories->count() }} Discount)
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body p-2 p-md-3.5">
            <form action="{{ route('admin.offer-sale.activate') }}" method="POST" id="activateOfferForm">
                @csrf
                
                <!-- Simple Standard Inline Radio Buttons (None vs Offer Store) -->
                <div class="d-flex align-items-center gap-3 mb-2 pb-2 border-bottom">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input mt-0" type="radio" name="offer_category_id" id="activeOfferNone" value="0" {{ !$activeOfferCategory ? 'checked' : '' }} onchange="submitActiveOffer(this)">
                        <label class="form-check-label fw-bold text-dark small" for="activeOfferNone" style="font-size: 0.72rem; cursor: pointer;">
                            None
                        </label>
                    </div>

                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input mt-0" type="radio" name="offer_mode_toggle" id="activeOfferModeEnabled" value="1" {{ $activeOfferCategory ? 'checked' : '' }} onchange="toggleOfferStoreMode(true)">
                        <label class="form-check-label fw-bold text-dark small" for="activeOfferModeEnabled" style="font-size: 0.72rem; cursor: pointer;">
                            Offer Store
                        </label>
                    </div>
                </div>

                <!-- Offer Category Cards Grid -->
                <div id="offerCategoryCardsContainer" style="{{ !$activeOfferCategory ? 'display: none;' : '' }}">
                    @if($comboCategories->count() > 0)
                        <div class="mb-2">
                            <div class="small fw-bold text-uppercase text-dark tracking-wider mb-1 d-flex align-items-center gap-1" style="font-size: 0.68rem;">
                                <i class="fa-solid fa-crown text-warning" style="font-size: 0.65rem;"></i> <span>Combo Package Categories ({{ $comboCategories->count() }})</span>
                            </div>
                            <div class="row g-1.5">
                                @foreach($comboCategories as $cCat)
                                    @php $isActive = $activeOfferCategory && $activeOfferCategory->id == $cCat->id; @endphp
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="offer-option-card h-100 {{ $isActive ? 'active-combo' : '' }}">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-1.5 overflow-hidden me-1">
                                                    <input type="radio" name="offer_category_id" value="{{ $cCat->id }}" {{ $isActive ? 'checked' : '' }} onchange="submitActiveOffer(this)" class="form-check-input mt-0 flex-shrink-0">
                                                    <div class="text-truncate">
                                                        <div class="fw-bold text-dark text-truncate d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                                            <i class="fa-solid fa-crown text-warning" style="font-size: 0.65rem;"></i> {{ $cCat->name }}
                                                        </div>
                                                        <div class="text-muted extra-small mt-0.5" style="font-size: 0.60rem;">
                                                            Min: {{ $cCat->min_count }} Pcs • ₹{{ number_format($cCat->combo_price, 0) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($isActive)
                                                    <span class="badge bg-warning text-dark rounded-pill fw-bold flex-shrink-0 px-1.5 py-0.5" style="font-size: 0.55rem;">
                                                        <i class="fa-solid fa-bolt me-0.5"></i> LIVE
                                                    </span>
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($discountCategories->count() > 0)
                        <div>
                            <div class="small fw-bold text-uppercase text-dark tracking-wider mb-1 d-flex align-items-center gap-1" style="font-size: 0.68rem;">
                                <i class="fa-solid fa-tags text-danger" style="font-size: 0.65rem;"></i> <span>Product Discount Categories ({{ $discountCategories->count() }})</span>
                            </div>
                            <div class="row g-1.5">
                                @foreach($discountCategories as $dCat)
                                    @php $isActive = $activeOfferCategory && $activeOfferCategory->id == $dCat->id; @endphp
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="offer-option-card h-100 {{ $isActive ? 'active-discount' : '' }}">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-1.5 overflow-hidden me-1">
                                                    <input type="radio" name="offer_category_id" value="{{ $dCat->id }}" {{ $isActive ? 'checked' : '' }} onchange="submitActiveOffer(this)" class="form-check-input mt-0 flex-shrink-0">
                                                    <div class="text-truncate">
                                                        <div class="fw-bold text-dark text-truncate d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                                            <i class="fa-solid fa-tag text-danger" style="font-size: 0.65rem;"></i> {{ $dCat->name }}
                                                        </div>
                                                        <div class="text-muted extra-small mt-0.5" style="font-size: 0.60rem;">
                                                            Discount: {{ $dCat->discount_type === 'percentage' ? $dCat->discount_value . '% OFF' : '₹' . number_format($dCat->discount_value, 0) . ' OFF' }}
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($isActive)
                                                    <span class="badge bg-danger text-white rounded-pill fw-bold flex-shrink-0 px-1.5 py-0.5" style="font-size: 0.55rem;">
                                                        <i class="fa-solid fa-bolt me-0.5"></i> LIVE
                                                    </span>
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Product Assignment & Category Switcher Wrapper Section -->
    <div id="productAssignmentSection" style="{{ !$activeOfferCategory ? 'display: none;' : '' }}">
        <div class="card border-0 rounded-4 shadow-sm mb-3">
            <div class="card-body p-2 p-md-3.5">
                <!-- Tabs: Combo Offers vs Product Discount Offers -->
                <ul class="nav nav-pills gap-2 mb-2 border-bottom pb-2" id="offerTypeTabs" role="tablist">
                    <li class="nav-item flex-grow-1 flex-sm-grow-0" role="presentation">
                        <button class="nav-link offer-nav-tab w-100 {{ ($selectedCategory && $selectedCategory->offer_type === 'combo') || (!$selectedCategory && $comboCategories->isNotEmpty()) ? 'active' : '' }}" 
                                id="combo-tab" data-bs-toggle="pill" data-bs-target="#combo-tab-pane" type="button" role="tab">
                            <i class="fa-solid fa-crown me-1.5 text-warning"></i> Combo Offers ({{ $comboCategories->count() }})
                        </button>
                    </li>
                    <li class="nav-item flex-grow-1 flex-sm-grow-0" role="presentation">
                        <button class="nav-link offer-nav-tab w-100 {{ $selectedCategory && $selectedCategory->offer_type === 'discount' ? 'active' : '' }}" 
                                id="discount-tab" data-bs-toggle="pill" data-bs-target="#discount-tab-pane" type="button" role="tab">
                            <i class="fa-solid fa-tags me-1.5 text-danger"></i> Product Discount Offers ({{ $discountCategories->count() }})
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="offerTypeTabsContent">
                    <!-- Combo Offers Tab Pane -->
                    <div class="tab-pane fade {{ ($selectedCategory && $selectedCategory->offer_type === 'combo') || (!$selectedCategory && $comboCategories->isNotEmpty()) ? 'show active' : '' }}" id="combo-tab-pane" role="tabpanel">
                        <form action="{{ route('admin.offer-sale.index') }}" method="GET" id="comboCategorySelectForm" class="row g-2 align-items-center">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Select Combo Category</label>
                                <select name="offer_category_id" class="form-select form-select-sm rounded-3 fw-bold border-secondary-subtle" onchange="document.getElementById('comboCategorySelectForm').submit();">
                                    @forelse($comboCategories as $cCat)
                                        <option value="{{ $cCat->id }}" {{ $selectedCategoryId == $cCat->id ? 'selected' : '' }}>
                                            {{ $cCat->name }} (Min: {{ $cCat->min_count }} Pcs | ₹{{ number_format($cCat->combo_price, 2) }}) {{ $cCat->is_active_offer ? '(LIVE ACTIVE)' : '' }}
                                        </option>
                                    @empty
                                        <option value="">No Combo Offer Categories Found</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Search / apply filters</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control rounded-start-3" placeholder="Search product by title..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-dark rounded-end-3 px-3" title="Apply search and filters">
                                        <i class="fa-solid fa-magnifying-glass text-warning"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3" data-category-filter>
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Filter available by category</label>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm w-100 text-start rounded-3 dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-category-filter-label>All product categories</button>
                                    <div class="dropdown-menu w-100 p-2 shadow" style="max-height: 245px; overflow-y: auto;">
                                        <label class="dropdown-item-text form-check border-bottom pb-2 mb-1 px-1">
                                            <input class="form-check-input me-1" type="checkbox" data-category-filter-all {{ empty($selectedProductCategoryIds) ? 'checked' : '' }}>
                                            <span class="fw-bold">All product categories</span>
                                        </label>
                                        @foreach($productFilterCategories as $productCategory)
                                            <label class="dropdown-item form-check px-1 py-1 mb-0">
                                                <input class="form-check-input me-1" type="checkbox" name="product_category_ids[]" value="{{ $productCategory->id }}" data-category-filter-option {{ in_array($productCategory->id, $selectedProductCategoryIds, true) ? 'checked' : '' }}>
                                                <span>{{ $productCategory->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Available price range (₹)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="min_price" min="0" step="0.01" class="form-control" placeholder="Min" value="{{ request('min_price') }}" aria-label="Minimum price">
                                    <input type="number" name="max_price" min="0" step="0.01" class="form-control" placeholder="Max" value="{{ request('max_price') }}" aria-label="Maximum price">
                                    @if(!empty($selectedProductCategoryIds) || request()->filled('min_price') || request()->filled('max_price') || request()->filled('search') || request('booked_filter', 'without') !== 'without')
                                        <a href="{{ route('admin.offer-sale.index', ['offer_category_id' => $selectedCategoryId]) }}" class="btn btn-outline-secondary" title="Clear product filters"><i class="fa-solid fa-rotate-left"></i></a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Booked products</label>
                                <select name="booked_filter" class="form-select form-select-sm rounded-3">
                                    <option value="without" {{ request('booked_filter', 'without') === 'without' ? 'selected' : '' }}>Without booked (default)</option>
                                    <option value="include" {{ request('booked_filter') === 'include' ? 'selected' : '' }}>Include all booked</option>
                                    <option value="only" {{ request('booked_filter') === 'only' ? 'selected' : '' }}>Only booked products</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-dark btn-sm rounded-pill px-4 fw-bold">
                                    <i class="fa-solid fa-filter text-warning me-2"></i>Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Product Discount Offers Tab Pane -->
                    <div class="tab-pane fade {{ $selectedCategory && $selectedCategory->offer_type === 'discount' ? 'show active' : '' }}" id="discount-tab-pane" role="tabpanel">
                        <form action="{{ route('admin.offer-sale.index') }}" method="GET" id="discountCategorySelectForm" class="row g-2 align-items-center">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Select Discount Category</label>
                                <select name="offer_category_id" class="form-select form-select-sm rounded-3 fw-bold border-secondary-subtle" onchange="document.getElementById('discountCategorySelectForm').submit();">
                                    @forelse($discountCategories as $dCat)
                                        <option value="{{ $dCat->id }}" {{ $selectedCategoryId == $dCat->id ? 'selected' : '' }}>
                                            {{ $dCat->name }} (Discount: {{ $dCat->discount_type === 'percentage' ? $dCat->discount_value . '%' : '₹' . number_format($dCat->discount_value, 2) }}) {{ $dCat->is_active_offer ? '(LIVE ACTIVE)' : '' }}
                                        </option>
                                    @empty
                                        <option value="">No Product Discount Categories Found</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Search / apply filters</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control rounded-start-3" placeholder="Search product by title..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-dark rounded-end-3 px-3" title="Apply search and filters">
                                        <i class="fa-solid fa-magnifying-glass text-warning"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3" data-category-filter>
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Filter available by category</label>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm w-100 text-start rounded-3 dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-category-filter-label>All product categories</button>
                                    <div class="dropdown-menu w-100 p-2 shadow" style="max-height: 245px; overflow-y: auto;">
                                        <label class="dropdown-item-text form-check border-bottom pb-2 mb-1 px-1">
                                            <input class="form-check-input me-1" type="checkbox" data-category-filter-all {{ empty($selectedProductCategoryIds) ? 'checked' : '' }}>
                                            <span class="fw-bold">All product categories</span>
                                        </label>
                                        @foreach($productFilterCategories as $productCategory)
                                            <label class="dropdown-item form-check px-1 py-1 mb-0">
                                                <input class="form-check-input me-1" type="checkbox" name="product_category_ids[]" value="{{ $productCategory->id }}" data-category-filter-option {{ in_array($productCategory->id, $selectedProductCategoryIds, true) ? 'checked' : '' }}>
                                                <span>{{ $productCategory->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Available price range (₹)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="min_price" min="0" step="0.01" class="form-control" placeholder="Min" value="{{ request('min_price') }}" aria-label="Minimum price">
                                    <input type="number" name="max_price" min="0" step="0.01" class="form-control" placeholder="Max" value="{{ request('max_price') }}" aria-label="Maximum price">
                                    @if(!empty($selectedProductCategoryIds) || request()->filled('min_price') || request()->filled('max_price') || request()->filled('search') || request('booked_filter', 'without') !== 'without')
                                        <a href="{{ route('admin.offer-sale.index', ['offer_category_id' => $selectedCategoryId]) }}" class="btn btn-outline-secondary" title="Clear product filters"><i class="fa-solid fa-rotate-left"></i></a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Booked products</label>
                                <select name="booked_filter" class="form-select form-select-sm rounded-3">
                                    <option value="without" {{ request('booked_filter', 'without') === 'without' ? 'selected' : '' }}>Without booked (default)</option>
                                    <option value="include" {{ request('booked_filter') === 'include' ? 'selected' : '' }}>Include all booked</option>
                                    <option value="only" {{ request('booked_filter') === 'only' ? 'selected' : '' }}>Only booked products</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-dark btn-sm rounded-pill px-4 fw-bold">
                                    <i class="fa-solid fa-filter text-warning me-2"></i>Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if(!$selectedCategory)
            <div class="card border-0 rounded-4 shadow-sm py-5 text-center text-muted">
                <div class="card-body">
                    <i class="fa-solid fa-tags fs-1 text-warning mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-dark">No Offer Category Selected</h5>
                    <p class="small text-muted mb-3">Please select or create an Offer Category first in Category Master.</p>
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-dark rounded-pill px-4 btn-sm fw-bold">Add New Offer Category</a>
                </div>
            </div>
        @else

        <!-- 2-Column Desktop Grid / 1-Column Mobile Stack Product Assignment -->
        <div class="row g-3">

            <!-- Left Column: Available Products -->
            <div class="col-12 col-md-6">
                <div class="card border-0 rounded-4 shadow-sm h-100">
                    <div class="card-header bg-dark text-white py-2 px-3 rounded-top-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-truncate" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-boxes-stacked text-warning me-1.5"></i> Available Products
                        </h6>
                        <span class="badge bg-warning text-dark rounded-pill fw-bold" id="availableCountBadge">
                            {{ $availableProducts->count() }}
                        </span>
                    </div>
                    <div class="card-body p-2 p-md-3">
                        <p class="small text-muted mb-2 d-none d-md-block" style="font-size: 0.74rem;">
                            Drag cards to right column or click <strong class="text-dark">"+"</strong> to assign.
                        </p>

                        <div class="product-list-container" id="availableProductsList" data-column="available">
                            @forelse($availableProducts as $prod)
                                @php
                                    $totalStock = $prod->sizes->sum('stock');
                                @endphp
                                <div class="card bulk-product-card" 
                                     data-product-id="{{ $prod->id }}" 
                                     id="product_card_{{ $prod->id }}">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-2 overflow-hidden flex-grow-1" style="min-width: 0;">
                                            <!-- Thumbnail Image with Eye Overlay -->
                                            <div class="position-relative flex-shrink-0">
                                                <img src="{{ $prod->primary_image_url }}" alt="{{ $prod->name }}" 
                                                     class="rounded-3 object-fit-cover bulk-card-img" style="width: 42px; height: 42px; cursor: pointer;"
                                                     onclick="openProductDetailModal(this.nextElementSibling)">
                                                <button type="button" class="btn p-0 position-absolute top-50 start-50 translate-middle border-0 rounded-circle" 
                                                        style="width: 16px; height: 16px; display: inline-flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.6); color: #ffffff;"
                                                        data-product-name="{{ $prod->name }}"
                                                        data-product-image="{{ $prod->primary_image_url }}"
                                                        data-product-price="₹{{ number_format($prod->final_price, 2) }}"
                                                        data-product-orig-price="{{ $prod->original_price > $prod->final_price ? '₹'.number_format($prod->original_price, 2) : '' }}"
                                                        data-product-category="{{ $prod->category->name ?? 'N/A' }}"
                                                        data-product-combo="{{ $prod->comboCategory->name ?? '' }}"
                                                        data-product-sizes='@json($prod->sizes)'
                                                        onclick="openProductDetailModal(this)"
                                                        title="View Details">
                                                    <i class="fa-solid fa-eye" style="font-size: 0.45rem; color: #ffffff;"></i>
                                                </button>
                                            </div>
                                            <div class="overflow-hidden flex-grow-1" style="min-width: 0;">
                                                <h6 class="bulk-card-title" title="{{ $prod->name }}">{{ $prod->name }}</h6>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <span class="fw-bold text-dark" style="font-size: 0.74rem;">₹{{ number_format($prod->final_price, 0) }}</span>
                                                    <span class="badge bg-secondary-subtle text-dark border rounded-pill" style="font-size: 0.60rem;">Stk: {{ $totalStock }}</span>
                                                    @if(filled($prod->booked_by))
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.60rem;" title="This product is already booked"><i class="fa-solid fa-bookmark me-1"></i>Booked</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-warning rounded-circle shadow-xs flex-shrink-0 add-prod-btn bulk-action-btn-circle" 
                                                onclick="moveProduct('{{ $prod->id }}', 'add')"
                                                style="background-color: var(--qw-gold-accent); border-color: var(--qw-gold-accent); color: #000;"
                                                title="Add to offer">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted small" id="noAvailableNotice">
                                    <i class="fa-solid fa-box-open fs-3 text-secondary mb-2 d-block opacity-50"></i>
                                    No available products found.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Assigned Products -->
            <div class="col-12 col-md-6">
                <div class="card border-0 rounded-4 shadow-sm h-100 border-start border-3 border-warning">
                    <div class="card-header bg-dark text-white py-2 px-3 rounded-top-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-truncate" style="font-size: 0.85rem;" title="{{ $selectedCategory->name }}">
                            <i class="fa-solid {{ $selectedCategory->offer_type === 'combo' ? 'fa-crown' : 'fa-tags' }} text-warning me-1.5"></i> {{ Str::limit($selectedCategory->name, 22) }}
                        </h6>
                        <span class="badge bg-warning text-dark rounded-pill fw-bold" id="assignedCountBadge">
                            {{ $assignedProducts->count() }} Included
                        </span>
                    </div>
                    <div class="card-body p-2 p-md-3">
                        <p class="small text-muted mb-2 d-none d-md-block" style="font-size: 0.74rem;">
                            Products assigned to <strong class="text-dark">{{ $selectedCategory->name }}</strong>.
                        </p>

                        <div class="product-list-container" id="assignedProductsList" data-column="assigned">
                            @forelse($assignedProducts as $prod)
                                @php
                                    $totalStock = $prod->sizes->sum('stock');
                                @endphp
                                <div class="card bulk-product-card border-warning" 
                                     data-product-id="{{ $prod->id }}" 
                                     id="product_card_{{ $prod->id }}">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-2 overflow-hidden flex-grow-1" style="min-width: 0;">
                                            <span class="text-muted small cursor-grab d-none d-sm-inline" title="Drag to reorder/remove"><i class="fa-solid fa-grip-vertical"></i></span>
                                            <!-- Thumbnail Image with Eye Overlay -->
                                            <div class="position-relative flex-shrink-0">
                                                <img src="{{ $prod->primary_image_url }}" alt="{{ $prod->name }}" 
                                                     class="rounded-3 object-fit-cover bulk-card-img" style="width: 42px; height: 42px; cursor: pointer;"
                                                     onclick="openProductDetailModal(this.nextElementSibling)">
                                                <button type="button" class="btn p-0 position-absolute top-50 start-50 translate-middle border-0 rounded-circle" 
                                                        style="width: 16px; height: 16px; display: inline-flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.6); color: #ffffff;"
                                                        data-product-name="{{ $prod->name }}"
                                                        data-product-image="{{ $prod->primary_image_url }}"
                                                        data-product-price="₹{{ number_format($prod->final_price, 2) }}"
                                                        data-product-orig-price="{{ $prod->original_price > $prod->final_price ? '₹'.number_format($prod->original_price, 2) : '' }}"
                                                        data-product-category="{{ $prod->category->name ?? 'N/A' }}"
                                                        data-product-combo="{{ $prod->comboCategory->name ?? '' }}"
                                                        data-product-sizes='@json($prod->sizes)'
                                                        onclick="openProductDetailModal(this)"
                                                        title="View Details">
                                                    <i class="fa-solid fa-eye" style="font-size: 0.45rem; color: #ffffff;"></i>
                                                </button>
                                            </div>
                                            <div class="overflow-hidden flex-grow-1" style="min-width: 0;">
                                                <h6 class="bulk-card-title" title="{{ $prod->name }}">{{ $prod->name }}</h6>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <span class="fw-bold text-dark" style="font-size: 0.74rem;">₹{{ number_format($prod->final_price, 0) }}</span>
                                                    <span class="badge bg-secondary-subtle text-dark border rounded-pill" style="font-size: 0.60rem;">Stk: {{ $totalStock }}</span>
                                                    @if(filled($prod->booked_by))
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.60rem;" title="This product is already booked"><i class="fa-solid fa-bookmark me-1"></i>Booked</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger rounded-circle shadow-xs flex-shrink-0 remove-prod-btn bulk-action-btn-circle" 
                                                onclick="moveProduct('{{ $prod->id }}', 'remove')"
                                                title="Remove from offer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted small" id="noAssignedNotice">
                                    <i class="fa-solid fa-tags fs-3 text-warning mb-2 d-block opacity-50"></i>
                                    No products assigned yet. Drag products from left column or click "+".
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @endif
    </div>

    <!-- Confirmation Modal: Remove Offer From All Available Products -->
    <div class="modal fade" id="confirmRemoveAllOffersModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-danger text-white py-3 px-4 rounded-top-4">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-warning fs-5"></i> Confirm Bulk Offer Reset
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="fa-solid fa-box-archive fs-1 text-danger mb-3 opacity-75"></i>
                    <h6 class="fw-bold text-dark mb-2">Clear offer assignment from Available & Booked products?</h6>
                    <p class="text-muted small mb-0">
                        This will unassign offers from available and booked products. 
                        <strong class="text-success d-block mt-1">✓ Sold Out products will NEVER be affected and will keep their offer price intact.</strong>
                    </p>
                </div>
                <div class="modal-footer bg-light border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('admin.offer-sale.remove-offers') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger rounded-pill px-4 btn-sm fw-bold">
                            <i class="fa-solid fa-trash-can me-1.5"></i> Yes, Remove Offers Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Modal -->
    <div class="modal fade" id="bulkProductDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-dark text-white py-2.5 px-3 rounded-top-4">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                        <i class="fa-solid fa-eye text-warning"></i> <span id="modalProdTitle">Product Details</span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="row g-3">
                        <div class="col-sm-5 text-center">
                            <img id="modalProdImg" src="" alt="Product Image" class="img-fluid rounded-3 shadow-sm border" style="max-height: 210px; width: 100%; object-fit: cover;">
                        </div>
                        <div class="col-sm-7">
                            <h6 class="fw-bold text-dark mb-1" id="modalProdNameHeading">--</h6>
                            <div class="mb-2 d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-dark border me-1" id="modalProdCat">Category</span>
                                <span class="badge bg-warning text-dark d-none" id="modalProdComboTag">Combo</span>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 mb-2.5">
                                <span class="fs-5 fw-bold text-dark" id="modalProdPrice">₹0</span>
                                <span class="small text-muted text-decoration-line-through" id="modalProdOrigPrice"></span>
                            </div>
                            <h6 class="fw-bold text-dark small mb-1 border-top pt-2" style="font-size: 0.78rem;">Sizes & Stock Breakdown:</h6>
                            <div id="modalProdSizesContainer" class="d-flex flex-column gap-1.5" style="max-height: 140px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-2 text-end">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const SELECTED_CATEGORY_ID = {{ $selectedCategoryId ?? 'null' }};
    const ASSIGN_URL = "{{ route('admin.offer-sale.assign') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";

    function submitActiveOffer(el) {
        document.getElementById('activateOfferForm').submit();
    }

    function toggleOfferStoreMode(show) {
        const cardsContainer = document.getElementById('offerCategoryCardsContainer');
        const assignmentSection = document.getElementById('productAssignmentSection');
        if (cardsContainer) cardsContainer.style.display = show ? 'block' : 'none';
        if (assignmentSection) assignmentSection.style.display = show ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-category-filter]').forEach(function(filter) {
            const allCheckbox = filter.querySelector('[data-category-filter-all]');
            const options = Array.from(filter.querySelectorAll('[data-category-filter-option]'));
            const label = filter.querySelector('[data-category-filter-label]');
            const updateLabel = function() {
                const selectedCount = options.filter(option => option.checked).length;
                allCheckbox.checked = selectedCount === 0;
                label.textContent = selectedCount === 0
                    ? 'All product categories'
                    : `${selectedCount} categor${selectedCount === 1 ? 'y' : 'ies'} selected`;
            };

            allCheckbox.addEventListener('change', function() {
                if (allCheckbox.checked) {
                    options.forEach(option => { option.checked = false; });
                }
                updateLabel();
            });
            options.forEach(option => option.addEventListener('change', updateLabel));
            updateLabel();
        });

        const availableEl = document.getElementById('availableProductsList');
        const assignedEl = document.getElementById('assignedProductsList');

        if (availableEl && assignedEl) {
            const sortableOptions = {
                group: 'offerGroup',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                delay: 150,
                delayOnTouchOnly: true,
                touchStartThreshold: 5,
                direction: 'vertical'
            };

            new Sortable(availableEl, Object.assign({}, sortableOptions, {
                onAdd: function(evt) {
                    const prodId = evt.item.getAttribute('data-product-id');
                    updateProductServer(prodId, 'remove');
                }
            }));

            new Sortable(assignedEl, Object.assign({}, sortableOptions, {
                onAdd: function(evt) {
                    const prodId = evt.item.getAttribute('data-product-id');
                    updateProductServer(prodId, 'add');
                }
            }));
        }
    });

    function openProductDetailModal(btn) {
        const name = btn.getAttribute('data-product-name');
        const image = btn.getAttribute('data-product-image');
        const price = btn.getAttribute('data-product-price');
        const origPrice = btn.getAttribute('data-product-orig-price');
        const category = btn.getAttribute('data-product-category');
        const combo = btn.getAttribute('data-product-combo');
        const sizes = JSON.parse(btn.getAttribute('data-product-sizes') || '[]');

        document.getElementById('modalProdTitle').textContent = name;
        document.getElementById('modalProdNameHeading').textContent = name;
        document.getElementById('modalProdImg').src = image;
        document.getElementById('modalProdPrice').textContent = price;
        document.getElementById('modalProdOrigPrice').textContent = origPrice;
        document.getElementById('modalProdCat').textContent = category;

        const comboTag = document.getElementById('modalProdComboTag');
        if (combo) {
            comboTag.textContent = 'Offer: ' + combo;
            comboTag.classList.remove('d-none');
        } else {
            comboTag.classList.add('d-none');
        }

        let sizesHtml = '';
        if (sizes.length === 0) {
            sizesHtml = '<span class="text-muted small">No size details found.</span>';
        } else {
            sizes.forEach(sz => {
                let specs = [];
                if (sz.chest) specs.push(`Chest: ${sz.chest}"`);
                if (sz.waist) specs.push(`Waist: ${sz.waist}"`);
                if (sz.length) specs.push(`Length: ${sz.length}"`);
                
                const specsStr = specs.length > 0 ? `<div class="text-muted extra-small opacity-75">${specs.join(' • ')}</div>` : '';
                const stockBadge = sz.stock > 0 
                    ? `<span class="badge bg-success" style="font-size:0.68rem;">Stock: ${sz.stock}</span>`
                    : `<span class="badge bg-danger" style="font-size:0.68rem;">Out of Stock</span>`;

                sizesHtml += `
                    <div class="d-flex align-items-center justify-content-between bg-light p-1.5 rounded border small">
                        <div>
                            <span class="fw-bold text-dark" style="font-size:0.75rem;">Size: ${sz.size}</span>
                            ${specsStr}
                        </div>
                        ${stockBadge}
                    </div>
                `;
            });
        }

        document.getElementById('modalProdSizesContainer').innerHTML = sizesHtml;

        const modal = new bootstrap.Modal(document.getElementById('bulkProductDetailModal'));
        modal.show();
    }

    function moveProduct(productId, action) {
        const card = document.getElementById(`product_card_${productId}`);
        if (!card) return;

        const availableContainer = document.getElementById('availableProductsList');
        const assignedContainer = document.getElementById('assignedProductsList');

        if (action === 'add' && assignedContainer) {
            assignedContainer.appendChild(card);
            updateProductServer(productId, 'add');
        } else if (action === 'remove' && availableContainer) {
            availableContainer.appendChild(card);
            updateProductServer(productId, 'remove');
        }
    }

    function updateProductServer(productId, action) {
        if (!SELECTED_CATEGORY_ID) return;

        const card = document.getElementById(`product_card_${productId}`);

        fetch(ASSIGN_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                offer_category_id: SELECTED_CATEGORY_ID,
                product_ids: [productId],
                action: action
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (action === 'add' && card) {
                    card.classList.add('border-warning');
                    let btn = card.querySelector('.add-prod-btn, .remove-prod-btn');
                    if (btn) {
                        btn.className = 'btn btn-outline-danger rounded-circle shadow-xs flex-shrink-0 remove-prod-btn bulk-action-btn-circle';
                        btn.removeAttribute('style');
                        btn.setAttribute('onclick', `moveProduct('${productId}', 'remove')`);
                        btn.setAttribute('title', 'Remove from offer');
                        btn.innerHTML = `<i class="fa-solid fa-xmark"></i>`;
                    }
                } else if (action === 'remove' && card) {
                    card.classList.remove('border-warning');
                    let btn = card.querySelector('.add-prod-btn, .remove-prod-btn');
                    if (btn) {
                        btn.className = 'btn btn-warning rounded-circle shadow-xs flex-shrink-0 add-prod-btn bulk-action-btn-circle';
                        btn.setAttribute('style', 'background-color: var(--qw-gold-accent); border-color: var(--qw-gold-accent); color: #000;');
                        btn.setAttribute('onclick', `moveProduct('${productId}', 'add')`);
                        btn.setAttribute('title', 'Add to offer');
                        btn.innerHTML = `<i class="fa-solid fa-plus"></i>`;
                    }
                }
                updateCounters();
            } else {
                alert(data.message || 'Error updating product category assignment.');
                location.reload();
            }
        })
        .catch(err => {
            console.error('AJAX Error:', err);
            alert('Failed to update product category.');
        });
    }

    function updateCounters() {
        const availableEl = document.getElementById('availableProductsList');
        const assignedEl = document.getElementById('assignedProductsList');
        const availableBadge = document.getElementById('availableCountBadge');
        const assignedBadge = document.getElementById('assignedCountBadge');

        const availCount = availableEl ? availableEl.querySelectorAll('.bulk-product-card').length : 0;
        const assignCount = assignedEl ? assignedEl.querySelectorAll('.bulk-product-card').length : 0;

        if (availableBadge) availableBadge.textContent = `${availCount}`;
        if (assignedBadge) assignedBadge.textContent = `${assignCount} Included`;
    }
</script>
@endsection
