@extends('layouts.admin')

@section('title', 'Bulk Offer Category Manager - ' . $siteName . ' Admin')

@section('content')
<style>
    .bulk-product-card {
        cursor: grab;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        user-select: none;
        touch-action: none;
        margin-bottom: 8px !important;
        border-radius: 12px !important;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04) !important;
    }
    .bulk-product-card:last-child {
        margin-bottom: 0 !important;
    }
    .bulk-product-card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08) !important;
    }
    .bulk-product-card:active {
        cursor: grabbing;
    }
    .bulk-product-card.sortable-ghost {
        opacity: 0.4;
        background-color: #f8f9fa;
        border: 2px dashed #ffc107 !important;
    }
    .bulk-product-card.sortable-chosen {
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
    .product-list-container {
        min-height: 460px;
        max-height: 68vh;
        overflow-y: auto;
        border: 2px dashed #dee2e6;
        border-radius: 0.85rem;
        background-color: #fcfcfc;
        padding: 0.75rem;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .product-list-container.drag-over {
        border-color: #ffc107;
        background-color: #fffdf5;
    }

    /* Mobile 2-Column Responsive Styles: Compact Image on Top, Details Below */
    @media (max-width: 767.98px) {
        .bulk-manager-page {
            padding: 0 !important;
        }
        .product-list-container {
            min-height: 380px;
            max-height: 60vh;
            padding: 0.35rem;
            gap: 6px;
        }
        .bulk-product-card {
            padding: 0.35rem !important;
            margin-bottom: 6px !important;
            border-radius: 8px !important;
        }
        .bulk-card-inner {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 4px !important;
        }
        .bulk-card-info-wrap {
            flex-direction: column !important;
            align-items: center !important;
            text-align: center;
            width: 100% !important;
            margin-right: 0 !important;
            gap: 3px !important;
        }
        .bulk-card-img-container {
            width: 100% !important;
            display: flex;
            justify-content: center;
            margin-bottom: 2px;
        }
        .bulk-card-img {
            width: 52px !important;
            height: 52px !important;
            object-fit: cover !important;
            border-radius: 6px !important;
        }
        .bulk-card-details {
            width: 100% !important;
            text-align: center;
        }
        .bulk-card-title {
            font-size: 0.68rem !important;
            line-height: 1.18;
            margin-bottom: 2px !important;
            white-space: normal !important;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }
        .bulk-card-price-stock {
            justify-content: center !important;
            gap: 3px !important;
        }
        .bulk-card-price {
            font-size: 0.65rem !important;
        }
        .bulk-card-stock {
            font-size: 0.58rem !important;
            padding: 1.5px 3.5px !important;
        }
        .bulk-action-btn {
            width: 100% !important;
            font-size: 0.62rem !important;
            padding: 3px 4px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin-top: 2px;
        }
        .bulk-action-btn span.d-none.d-sm-inline {
            display: inline !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 gap-2">
    <div>
        <h4 class="fw-bold mb-0 fs-6 fs-md-4">
            <i class="fa-solid fa-crown text-warning me-1.5"></i> Bulk Offer Category Manager
        </h4>
        <p class="text-muted small mb-0 d-none d-sm-block">Drag and drop or 1-click add/remove products to build active Offer Combo Categories</p>
    </div>
    <div>
        <a href="{{ route('admin.display-order.index', ['combo_category_id' => $selectedCategoryId]) }}" class="btn btn-outline-dark rounded-3 btn-sm fw-bold px-2.5 py-1 shadow-sm" style="font-size: 0.78rem;">
            <i class="fa-solid fa-arrow-down-short-wide me-1 text-warning"></i> <span class="d-none d-sm-inline">Reorder Combo Display</span><span class="d-inline d-sm-none">Reorder</span>
        </a>
    </div>
</div>

<!-- Category Selector & Search Header -->
<div class="card border-0 rounded-4 shadow-sm mb-3">
    <div class="card-body p-2.5 p-md-3.5">
        <form action="{{ route('admin.bulk-combo-offer.index') }}" method="GET" id="comboCategoryFilterForm" class="row g-2 align-items-center">
            <div class="col-12 col-md-5 col-lg-4">
                <label class="form-label fw-bold small text-muted text-uppercase mb-1" style="font-size: 0.72rem;">Select Offer Combo Category</label>
                <select name="combo_category_id" class="form-select form-select-sm rounded-3 fw-bold border-warning" onchange="document.getElementById('comboCategoryFilterForm').submit();">
                    @forelse($comboCategories as $cCat)
                        <option value="{{ $cCat->id }}" {{ $selectedCategoryId == $cCat->id ? 'selected' : '' }}>
                            👑 {{ $cCat->name }} (Min: {{ $cCat->min_count }} Pcs | ₹{{ number_format($cCat->combo_price, 2) }})
                        </option>
                    @empty
                        <option value="">No Active Offer Categories Found</option>
                    @endforelse
                </select>
            </div>
            <div class="col-8 col-md-5 col-lg-5">
                <label class="form-label fw-bold small text-muted text-uppercase mb-1" style="font-size: 0.72rem;">Search Products</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control rounded-start-3" placeholder="Product name..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-dark rounded-end-3 px-2.5">
                        <i class="fa-solid fa-magnifying-glass text-warning"></i>
                    </button>
                </div>
            </div>
            <div class="col-4 col-md-2 col-lg-3 text-end pt-md-4">
                @if(request('search'))
                    <a href="{{ route('admin.bulk-combo-offer.index', ['combo_category_id' => $selectedCategoryId]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                        <i class="fa-solid fa-rotate-left"></i> Clear
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if(!$selectedCategory)
    <div class="card border-0 rounded-4 shadow-sm py-5 text-center text-muted">
        <div class="card-body">
            <i class="fa-solid fa-crown fs-1 text-warning mb-3"></i>
            <h5>No Offer Category Selected</h5>
            <p class="small">Please select or create an active Offer Combo Category first in Category Master.</p>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-dark rounded-pill px-4 btn-sm">Manage Categories</a>
        </div>
    </div>
@else

<!-- Side-by-Side 2-Column Mobile & Desktop Drag-and-Drop -->
<div class="row g-2 g-md-4 bulk-manager-page">

    <!-- Left Column: Available Products (col-6 for side-by-side on mobile) -->
    <div class="col-6 col-lg-6">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-header bg-dark text-white py-2.5 px-2 px-md-3 rounded-top-4 d-flex justify-content-between align-items-center">
                <h6 class="font-serif fw-bold mb-0 text-truncate" style="font-size: 0.82rem;">
                    <i class="fa-solid fa-boxes-stacked text-warning me-1"></i> Available
                </h6>
                <span class="badge bg-warning text-dark rounded-pill fw-bold" id="availableCountBadge" style="font-size: 0.68rem;">
                    {{ $availableProducts->count() }}
                </span>
            </div>
            <div class="card-body p-2 p-md-3">
                <p class="small text-muted mb-2 d-none d-md-block" style="font-size: 0.76rem;">
                    Drag cards to the right column or click <strong>"+ Add"</strong>.
                </p>

                <div class="product-list-container d-flex flex-column gap-2" id="availableProductsList" data-column="available">
                    @forelse($availableProducts as $prod)
                        @php
                            $totalStock = $prod->sizes->sum('stock');
                        @endphp
                        <div class="card border rounded-3 p-1.5 bulk-product-card bg-white shadow-xs position-relative" 
                             data-product-id="{{ $prod->id }}" 
                             id="product_card_{{ $prod->id }}">
                            <div class="d-flex align-items-center justify-content-between gap-1 bulk-card-inner">
                                <div class="d-flex align-items-center gap-1.5 overflow-hidden me-1 bulk-card-info-wrap">
                                    <!-- Image with Eye overlay button -->
                                    <div class="position-relative flex-shrink-0 bulk-card-img-container">
                                        <img src="{{ $prod->primary_image_url }}" alt="{{ $prod->name }}" 
                                             class="rounded-2 object-fit-cover bulk-card-img" style="width: 44px; height: 44px; cursor: pointer;"
                                             onclick="openProductDetailModal(this.nextElementSibling)">
                                        <button type="button" class="btn p-0 position-absolute top-50 start-50 translate-middle border-0 rounded-circle" 
                                                style="width: 17px; height: 17px; display: inline-flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.45); color: #ffffff; box-shadow: 0 1px 4px rgba(0,0,0,0.3);"
                                                data-product-name="{{ $prod->name }}"
                                                data-product-image="{{ $prod->primary_image_url }}"
                                                data-product-price="₹{{ number_format($prod->final_price, 2) }}"
                                                data-product-orig-price="{{ $prod->original_price > $prod->final_price ? '₹'.number_format($prod->original_price, 2) : '' }}"
                                                data-product-category="{{ $prod->category->name ?? 'N/A' }}"
                                                data-product-combo="{{ $prod->comboCategory->name ?? '' }}"
                                                data-product-sizes='@json($prod->sizes)'
                                                onclick="openProductDetailModal(this)"
                                                title="View Details">
                                            <i class="fa-solid fa-eye" style="font-size: 0.5rem; color: #ffffff; opacity: 0.95;"></i>
                                        </button>
                                    </div>
                                    <div class="overflow-hidden bulk-card-details">
                                        <h6 class="fw-bold text-dark mb-0 text-truncate bulk-card-title" title="{{ $prod->name }}">{{ $prod->name }}</h6>
                                        <div class="d-flex flex-wrap align-items-center gap-1 bulk-card-price-stock">
                                            <span class="fw-bold text-gold bulk-card-price" style="font-size: 0.72rem;">₹{{ number_format($prod->final_price, 0) }}</span>
                                            <span class="badge bg-dark bulk-card-stock">Stk: {{ $totalStock }}</span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-warning rounded-pill fw-bold text-dark p-1 px-2 text-nowrap flex-shrink-0 add-prod-btn bulk-action-btn" 
                                        onclick="moveProduct('{{ $prod->id }}', 'add')"
                                        style="font-size: 0.7rem; background-color: var(--qw-gold); border-color: var(--qw-gold);"
                                        title="Add to combo">
                                    <i class="fa-solid fa-plus"></i> <span class="d-none d-sm-inline">Add</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small" id="noAvailableNotice">
                            <i class="fa-solid fa-box-open fs-4 text-secondary mb-2 d-block opacity-50"></i>
                            No available products found.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Assigned Category Products (col-6 for side-by-side on mobile) -->
    <div class="col-6 col-lg-6">
        <div class="card border-0 rounded-4 shadow-sm h-100 border-start border-3 border-warning">
            <div class="card-header bg-dark text-white py-2.5 px-2 px-md-3 rounded-top-4 d-flex justify-content-between align-items-center">
                <h6 class="font-serif fw-bold mb-0 text-truncate" style="font-size: 0.82rem;" title="{{ $selectedCategory->name }}">
                    <i class="fa-solid fa-crown text-warning me-1"></i> {{ Str::limit($selectedCategory->name, 14) }}
                </h6>
                <span class="badge bg-gold text-dark rounded-pill fw-bold" id="assignedCountBadge" style="font-size: 0.68rem;">
                    {{ $assignedProducts->count() }} Included
                </span>
            </div>
            <div class="card-body p-2 p-md-3">
                <p class="small text-muted mb-2 d-none d-md-block" style="font-size: 0.76rem;">
                    Products currently in <strong>{{ $selectedCategory->name }}</strong>.
                </p>

                <div class="product-list-container d-flex flex-column gap-2" id="assignedProductsList" data-column="assigned">
                    @forelse($assignedProducts as $prod)
                        @php
                            $totalStock = $prod->sizes->sum('stock');
                        @endphp
                        <div class="card border border-warning rounded-3 p-1.5 bulk-product-card bg-white shadow-xs position-relative" 
                             data-product-id="{{ $prod->id }}" 
                             id="product_card_{{ $prod->id }}">
                            <div class="d-flex align-items-center justify-content-between gap-1 bulk-card-inner">
                                <div class="d-flex align-items-center gap-1.5 overflow-hidden me-1 bulk-card-info-wrap">
                                    <span class="text-muted small me-0.5 cursor-grab d-none d-sm-inline" title="Drag to reorder/remove"><i class="fa-solid fa-grip-vertical"></i></span>
                                    <!-- Image with Eye overlay button -->
                                    <div class="position-relative flex-shrink-0 bulk-card-img-container">
                                        <img src="{{ $prod->primary_image_url }}" alt="{{ $prod->name }}" 
                                             class="rounded-2 object-fit-cover bulk-card-img" style="width: 44px; height: 44px; cursor: pointer;"
                                             onclick="openProductDetailModal(this.nextElementSibling)">
                                        <button type="button" class="btn p-0 position-absolute top-50 start-50 translate-middle border-0 rounded-circle" 
                                                style="width: 17px; height: 17px; display: inline-flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.45); color: #ffffff; box-shadow: 0 1px 4px rgba(0,0,0,0.3);"
                                                data-product-name="{{ $prod->name }}"
                                                data-product-image="{{ $prod->primary_image_url }}"
                                                data-product-price="₹{{ number_format($prod->final_price, 2) }}"
                                                data-product-orig-price="{{ $prod->original_price > $prod->final_price ? '₹'.number_format($prod->original_price, 2) : '' }}"
                                                data-product-category="{{ $prod->category->name ?? 'N/A' }}"
                                                data-product-combo="{{ $prod->comboCategory->name ?? '' }}"
                                                data-product-sizes='@json($prod->sizes)'
                                                onclick="openProductDetailModal(this)"
                                                title="View Details">
                                            <i class="fa-solid fa-eye" style="font-size: 0.5rem; color: #ffffff; opacity: 0.95;"></i>
                                        </button>
                                    </div>
                                    <div class="overflow-hidden bulk-card-details">
                                        <h6 class="fw-bold text-dark mb-0 text-truncate bulk-card-title" title="{{ $prod->name }}">{{ $prod->name }}</h6>
                                        <div class="d-flex flex-wrap align-items-center gap-1 bulk-card-price-stock">
                                            <span class="fw-bold text-gold bulk-card-price" style="font-size: 0.72rem;">₹{{ number_format($prod->final_price, 0) }}</span>
                                            <span class="badge bg-dark bulk-card-stock">Stk: {{ $totalStock }}</span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-bold p-1 px-2 text-nowrap flex-shrink-0 remove-prod-btn bulk-action-btn" 
                                        onclick="moveProduct('{{ $prod->id }}', 'remove')"
                                        style="font-size: 0.7rem;"
                                        title="Remove from combo">
                                    <i class="fa-solid fa-xmark"></i> <span class="d-none d-sm-inline">Remove</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small" id="noAssignedNotice">
                            <i class="fa-solid fa-crown fs-4 text-warning mb-2 d-block opacity-50"></i>
                            No products assigned yet. Drag products from left column or click "+ Add".
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Product Details Modal -->
<div class="modal fade" id="bulkProductDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-dark text-white py-2.5 px-3 rounded-top-4">
                <h6 class="modal-title font-serif fw-bold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
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
                            <span class="fs-5 fw-bold text-gold font-serif" id="modalProdPrice">₹0</span>
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

@endif

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const SELECTED_CATEGORY_ID = {{ $selectedCategoryId ?? 'null' }};
    const ASSIGN_URL = "{{ route('admin.bulk-combo-offer.assign') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";

    document.addEventListener('DOMContentLoaded', function() {
        const availableEl = document.getElementById('availableProductsList');
        const assignedEl = document.getElementById('assignedProductsList');

        if (availableEl && assignedEl) {
            // Left Column Sortable with instant touch drag
            new Sortable(availableEl, {
                group: 'bulkComboGroup',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                delay: 0,
                delayOnTouchOnly: false,
                touchStartThreshold: 3,
                onAdd: function(evt) {
                    const prodId = evt.item.getAttribute('data-product-id');
                    updateProductServer(prodId, 'remove');
                }
            });

            // Right Column Sortable with instant touch drag
            new Sortable(assignedEl, {
                group: 'bulkComboGroup',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                delay: 0,
                delayOnTouchOnly: false,
                touchStartThreshold: 3,
                onAdd: function(evt) {
                    const prodId = evt.item.getAttribute('data-product-id');
                    updateProductServer(prodId, 'add');
                }
            });
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
            comboTag.textContent = '👑 Combo: ' + combo;
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

        const modalElem = document.getElementById('bulkProductDetailModal');
        if (modalElem) {
            modalElem.scrollTop = 0;
            let modalInstance = bootstrap.Modal.getInstance(modalElem);
            if (!modalInstance) {
                modalInstance = new bootstrap.Modal(modalElem);
            }
            modalInstance.show();
        }
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
                combo_category_id: SELECTED_CATEGORY_ID,
                product_ids: [productId],
                action: action
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Transform Card UI dynamically based on action
                if (action === 'add' && card) {
                    card.classList.add('border-warning');
                    let btn = card.querySelector('.add-prod-btn, .remove-prod-btn');
                    if (btn) {
                        btn.className = 'btn btn-sm btn-outline-danger rounded-pill fw-bold p-1 px-2 text-nowrap flex-shrink-0 remove-prod-btn bulk-action-btn';
                        btn.setAttribute('onclick', `moveProduct('${productId}', 'remove')`);
                        btn.innerHTML = `<i class="fa-solid fa-xmark"></i> <span class="d-none d-sm-inline">Remove</span>`;
                    }
                } else if (action === 'remove' && card) {
                    card.classList.remove('border-warning');
                    let btn = card.querySelector('.add-prod-btn, .remove-prod-btn');
                    if (btn) {
                        btn.className = 'btn btn-sm btn-warning rounded-pill fw-bold text-dark p-1 px-2 text-nowrap flex-shrink-0 add-prod-btn bulk-action-btn';
                        btn.setAttribute('style', 'font-size: 0.7rem; background-color: var(--qw-gold); border-color: var(--qw-gold);');
                        btn.setAttribute('onclick', `moveProduct('${productId}', 'add')`);
                        btn.innerHTML = `<i class="fa-solid fa-plus"></i> <span class="d-none d-sm-inline">Add</span>`;
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
