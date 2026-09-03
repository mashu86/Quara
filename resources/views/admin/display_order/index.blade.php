@extends('layouts.admin')

@section('title', 'Display & Sorting Master - ' . $siteName)

@section('styles')
<style>
    .drag-handle {
        cursor: grab;
        color: #666;
        transition: color 0.2s ease;
    }
    .drag-handle:active {
        cursor: grabbing;
    }
    .sortable-ghost {
        opacity: 0.3;
        transform: scale(0.95);
    }
    .sortable-chosen {
        border: 2px dashed var(--qw-gold) !important;
        background-color: #FFFDF0 !important;
    }
    .sortable-drag {
        box-shadow: 0 12px 30px rgba(0,0,0,0.2) !important;
        transform: scale(1.03) !important;
    }
    .grid-thumb-img {
        height: 125px;
        width: 100%;
        object-fit: cover;
        border-radius: 8px;
    }
    @media (max-width: 576px) {
        .grid-thumb-img {
            height: 95px;
        }
        .order-card-title {
            font-size: 0.78rem !important;
        }
        .pref-title {
            font-size: 0.68rem !important;
            line-height: 1.15 !important;
        }
        .pref-desc {
            font-size: 0.60rem !important;
            line-height: 1.1 !important;
        }
        .preference-card {
            padding: 8px 6px !important;
            border-radius: 10px !important;
        }
    }
    .category-order-item, .product-order-item {
        cursor: grab;
        user-select: none;
    }
    .category-order-item:active, .product-order-item:active {
        cursor: grabbing;
    }
    .category-grid-card, .product-grid-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .category-grid-card:hover, .product-grid-card:hover {
        border-color: var(--qw-gold) !important;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08) !important;
    }
    .preference-card {
        cursor: pointer;
        border: 2px solid #E9ECEF;
        border-radius: 16px;
        transition: all 0.25s ease;
    }
    .preference-card:hover {
        border-color: var(--qw-gold);
        transform: translateY(-2px);
    }
    .preference-card.active {
        border-color: var(--qw-gold);
        background-color: #FFFDF5;
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.15);
    }
    .order-badge {
        width: 26px;
        height: 26px;
        background-color: #111111;
        color: #D4AF37;
        font-weight: 700;
        font-size: 0.75rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Page Title Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="font-serif fw-bold text-dark mb-1 fs-4 fs-md-3">
                <i class="fa-solid fa-arrows-up-down-left-right text-warning me-2"></i> Client Display & Sorting Control
            </h3>
            <p class="text-muted small mb-0">Control which order Categories & Products appear on the customer site and set default view mode.</p>
        </div>
    </div>

    <!-- Alert / Toast Container -->
    <div id="ajaxAlertContainer"></div>

    <!-- SECTION 1: DEFAULT ORDER & LAYOUT PREFERENCE -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h5 class="font-serif fw-bold mb-0 text-dark fs-6 fs-md-5">
                <i class="fa-solid fa-sliders text-gold me-2"></i> 1. Default Client Display & Layout Preferences
            </h5>
            <small class="text-muted">Choose section ordering and category presentation mode on the customer home page.</small>
        </div>
        <div class="card-body p-3 p-md-4">
            <form id="preferenceForm" action="{{ route('admin.display-order.update-preference') }}" method="POST">
                @csrf
                <!-- Sub-Section A: Section Order -->
                <h6 class="font-serif fw-bold text-dark mb-2">A. Main Page Section Order</h6>
                <p class="small text-muted mb-3" style="font-size: 0.76rem;">Select which component appears at the top of the main home page.</p>
                <div class="row g-2 g-md-3 mb-4">
                    <div class="col-6 col-md-6">
                        <label class="w-100 h-100 mb-0">
                            <input type="radio" name="default_display_order_by" value="category" class="d-none preference-radio-order" {{ ($defaultOrderBy ?? 'category') === 'category' ? 'checked' : '' }}>
                            <div class="preference-card p-2 p-md-4 h-100 {{ ($defaultOrderBy ?? 'category') === 'category' ? 'active' : '' }}" id="prefCardCategory">
                                <div class="d-flex align-items-center gap-2 gap-md-3">
                                    <div class="bg-warning text-dark rounded-circle p-2 p-md-3 fs-5 fs-md-4 flex-shrink-0 d-none d-md-flex align-items-center justify-content-center" style="background-color: var(--qw-gold) !important; width: 40px; height: 40px;">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1 pref-title"><i class="fa-solid fa-circle-check text-success me-1 d-none active-check"></i> 1st Category, 2nd Products</h6>
                                        <p class="small text-muted mb-0 pref-desc">Categories first, then Trending Products.</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="col-6 col-md-6">
                        <label class="w-100 h-100 mb-0">
                            <input type="radio" name="default_display_order_by" value="product" class="d-none preference-radio-order" {{ ($defaultOrderBy ?? 'category') === 'product' ? 'checked' : '' }}>
                            <div class="preference-card p-2 p-md-4 h-100 {{ ($defaultOrderBy ?? 'category') === 'product' ? 'active' : '' }}" id="prefCardProduct">
                                <div class="d-flex align-items-center gap-2 gap-md-3">
                                    <div class="bg-dark text-white rounded-circle p-2 p-md-3 fs-5 fs-md-4 flex-shrink-0 d-none d-md-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fa-solid fa-shirt text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1 pref-title"><i class="fa-solid fa-circle-check text-success me-1 d-none active-check"></i> 1st Products, 2nd Category</h6>
                                        <p class="small text-muted mb-0 pref-desc">Products first, then Shop Categories.</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Sub-Section B: Category Layout Style -->
                <h6 class="font-serif fw-bold text-dark mb-2">B. Category Display Layout Style</h6>
                <p class="small text-muted mb-3" style="font-size: 0.76rem;">Choose how categories are formatted and presented to customers on the home page.</p>
                <div class="row g-1 g-md-3 mb-4">
                    <div class="col-4 col-md-4">
                        <label class="w-100 h-100 mb-0">
                            <input type="radio" name="category_display_style" value="grid" class="d-none preference-radio-style" {{ ($categoryDisplayStyle ?? 'grid') === 'grid' ? 'checked' : '' }}>
                            <div class="preference-card p-2 p-md-3 h-100 {{ ($categoryDisplayStyle ?? 'grid') === 'grid' ? 'active' : '' }}" id="styleCardGrid">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="bg-secondary text-white rounded-circle p-2 fs-6 flex-shrink-0 d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fa-solid fa-table-cells-large"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-0 pref-title">Grid View</h6>
                                </div>
                                <p class="small text-muted mb-0 pref-desc">Normal responsive grid cards.</p>
                            </div>
                        </label>
                    </div>

                    <div class="col-4 col-md-4">
                        <label class="w-100 h-100 mb-0">
                            <input type="radio" name="category_display_style" value="drawer" class="d-none preference-radio-style" {{ ($categoryDisplayStyle ?? 'grid') === 'drawer' ? 'checked' : '' }}>
                            <div class="preference-card p-2 p-md-3 h-100 {{ ($categoryDisplayStyle ?? 'grid') === 'drawer' ? 'active' : '' }}" id="styleCardDrawer">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="bg-dark text-warning rounded-circle p-2 fs-6 flex-shrink-0 d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fa-solid fa-sidebar"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-0 pref-title">Right Drawer</h6>
                                </div>
                                <p class="small text-muted mb-0 pref-desc">Right-side drawer panel.</p>
                            </div>
                        </label>
                    </div>

                    <div class="col-4 col-md-4">
                        <label class="w-100 h-100 mb-0">
                            <input type="radio" name="category_display_style" value="horizontal_scroll" class="d-none preference-radio-style" {{ ($categoryDisplayStyle ?? 'grid') === 'horizontal_scroll' ? 'checked' : '' }}>
                            <div class="preference-card p-2 p-md-3 h-100 {{ ($categoryDisplayStyle ?? 'grid') === 'horizontal_scroll' ? 'active' : '' }}" id="styleCardScroll">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="bg-primary text-white rounded-circle p-2 fs-6 flex-shrink-0 d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fa-solid fa-arrows-left-right"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-0 pref-title">Horizontal Scroll</h6>
                                </div>
                                <p class="small text-muted mb-0 pref-desc">Linear single-row slider.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark btn-sm py-2 shadow-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" id="savePrefBtn">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Display Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SECTION 2: GRID DRAG AND DROP POSITION MANAGER -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <ul class="nav nav-pills card-header-pills fw-semibold gap-2" id="sortingTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-3 px-md-4 py-2 small" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories-content" type="button" role="tab">
                        <i class="fa-solid fa-layer-group me-1.5"></i> Category Order ({{ $categories->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-3 px-md-4 py-2 small" id="products-tab" data-bs-toggle="tab" data-bs-target="#products-content" type="button" role="tab">
                        <i class="fa-solid fa-shirt me-1.5"></i> Product Order ({{ $products->count() }})
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="tab-content" id="sortingTabContent">

                <!-- CATEGORIES GRID DRAG & DROP TAB -->
                <div class="tab-pane fade show active" id="categories-content" role="tabpanel">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                        <div class="small text-muted" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-hand-pointer text-warning me-1"></i> Drag cards <i class="fa-solid fa-up-down-left-right"></i> to reorder. (Mobile: 2 per row | Laptop: 4 per row)
                        </div>
                        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold" id="saveCatOrderBtn" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-check me-1"></i> Save Category Order
                        </button>
                    </div>

                    <!-- Categories 2-col (mobile) & 4-col (desktop) Grid -->
                    <div class="row g-2 g-md-3" id="sortableCategories">
                        @forelse($categories as $index => $cat)
                            <div class="col-6 col-md-4 col-lg-3 category-order-item" data-id="{{ $cat->id }}">
                                <div class="card h-100 border rounded-4 shadow-sm overflow-hidden category-grid-card position-relative">
                                    <!-- Top Drag Bar & Badge -->
                                    <div class="d-flex justify-content-between align-items-center p-2 bg-light border-bottom">
                                        <span class="order-badge cat-order-badge">#{{ $index + 1 }}</span>
                                        <div class="drag-handle text-secondary p-1" title="Drag to reorder">
                                            <i class="fa-solid fa-up-down-left-right"></i>
                                        </div>
                                    </div>

                                    <!-- Category Image -->
                                    <div class="position-relative bg-light text-center p-1.5">
                                        <img src="{{ $cat->background_image_url }}" alt="{{ $cat->name }}" class="img-fluid rounded-3 grid-thumb-img">
                                    </div>

                                    <!-- Category Details -->
                                    <div class="p-2 text-center d-flex flex-column flex-grow-1">
                                        <h6 class="fw-bold text-dark mb-1 text-truncate order-card-title" title="{{ $cat->name }}">{{ $cat->name }}</h6>
                                        <div class="d-flex justify-content-center align-items-center gap-1 mt-auto">
                                            <span class="badge bg-light text-dark border" style="font-size: 0.62rem;">{{ $cat->products_count }} Prods</span>
                                            @if($cat->status === 'active')
                                                <span class="badge bg-success" style="font-size: 0.62rem;">Active</span>
                                            @else
                                                <span class="badge bg-secondary" style="font-size: 0.62rem;">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 p-4 text-center text-muted">No categories found.</div>
                        @endforelse
                    </div>
                </div>

                <!-- PRODUCTS GRID DRAG & DROP TAB -->
                <div class="tab-pane fade" id="products-content" role="tabpanel">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                        <div class="small text-muted" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-hand-pointer text-warning me-1"></i> Drag cards <i class="fa-solid fa-up-down-left-right"></i> to reorder. (Mobile: 2 per row | Laptop: 4 per row)
                        </div>
                        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold" id="saveProdOrderBtn" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-check me-1"></i> Save Product Order
                        </button>
                    </div>

                    <!-- Products 2-col (mobile) & 4-col (desktop) Grid -->
                    <div class="row g-2 g-md-3" id="sortableProducts">
                        @forelse($products as $index => $prod)
                            <div class="col-6 col-md-4 col-lg-3 product-order-item" data-id="{{ $prod->id }}">
                                <div class="card h-100 border rounded-4 shadow-sm overflow-hidden product-grid-card position-relative">
                                    <!-- Top Drag Bar & Badge -->
                                    <div class="d-flex justify-content-between align-items-center p-2 bg-light border-bottom">
                                        <span class="order-badge prod-order-badge">#{{ $index + 1 }}</span>
                                        <div class="drag-handle text-secondary p-1" title="Drag to reorder">
                                            <i class="fa-solid fa-up-down-left-right"></i>
                                        </div>
                                    </div>

                                    <!-- Product Image -->
                                    <div class="position-relative text-center p-1.5 bg-light">
                                        <img src="{{ $prod->primary_image_url }}" alt="{{ $prod->name }}" class="img-fluid rounded-3 grid-thumb-img">
                                        @if($prod->is_out_of_stock)
                                            <span class="badge bg-danger position-absolute top-0 start-0 m-2" style="font-size: 0.58rem;">OUT OF STOCK</span>
                                        @endif
                                    </div>

                                    <!-- Product Details -->
                                    <div class="p-2 text-center d-flex flex-column flex-grow-1">
                                        <h6 class="fw-bold text-dark mb-1 text-truncate order-card-title" title="{{ $prod->name }}">{{ $prod->name }}</h6>
                                        <div class="small fw-bold text-warning mb-1" style="font-size: 0.78rem;">₹{{ number_format($prod->final_price, 2) }}</div>
                                        <div class="d-flex justify-content-center align-items-center gap-1 mt-auto">
                                            <span class="badge bg-light text-dark border text-truncate" style="font-size: 0.6rem; max-width: 80px;">{{ $prod->category->name ?? 'Uncategorized' }}</span>
                                            @if($prod->status === 'active')
                                                <span class="badge bg-success" style="font-size: 0.6rem;">Active</span>
                                            @else
                                                <span class="badge bg-secondary" style="font-size: 0.6rem;">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 p-4 text-center text-muted">No products found.</div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<!-- SortableJS CDN for touch and drag-and-drop grid support -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Utility: Show Alert Message
    function showAlert(type, message) {
        const container = document.getElementById('ajaxAlertContainer');
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-${type === 'success' ? 'circle-check' : 'triangle-exclamation'} me-2"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        container.innerHTML = alertHtml;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Section Order Radios Styling
    const orderRadios = document.querySelectorAll('.preference-radio-order');
    orderRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.preference-radio-order').forEach(r => {
                const card = r.closest('label').querySelector('.preference-card');
                if (card) card.classList.remove('active');
            });
            const activeCard = this.closest('label').querySelector('.preference-card');
            if (activeCard) activeCard.classList.add('active');
        });
    });

    // Category Style Radios Styling
    const styleRadios = document.querySelectorAll('.preference-radio-style');
    styleRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.preference-radio-style').forEach(r => {
                const card = r.closest('label').querySelector('.preference-card');
                if (card) card.classList.remove('active');
            });
            const activeCard = this.closest('label').querySelector('.preference-card');
            if (activeCard) activeCard.classList.add('active');
        });
    });

    document.getElementById('preferenceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const selectedPref = document.querySelector('input[name="default_display_order_by"]:checked')?.value || 'category';
        const selectedStyle = document.querySelector('input[name="category_display_style"]:checked')?.value || 'grid';
        const submitBtn = document.getElementById('savePrefBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';

        fetch('{{ route("admin.display-order.update-preference") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                default_display_order_by: selectedPref,
                category_display_style: selectedStyle
            })
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Display Preferences';
            if (data.success) {
                showAlert('success', data.message);
            } else {
                showAlert('danger', 'Failed to update preferences.');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Display Preferences';
            showAlert('danger', 'Server error while saving preferences.');
        });
    });

    // Function to update position badge index numbers
    function updateBadges(containerSelector, badgeSelector) {
        const items = document.querySelectorAll(`${containerSelector} .${badgeSelector}`);
        items.forEach((badge, idx) => {
            badge.textContent = `#${idx + 1}`;
        });
    }

    // 1. Initialize Sortable for Categories Grid (Whole Card Draggable)
    const elCategories = document.getElementById('sortableCategories');
    if (elCategories) {
        new Sortable(elCategories, {
            animation: 150,
            touchStartThreshold: 3,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function() {
                updateBadges('#sortableCategories', 'cat-order-badge');
                saveCategoryOrder(false);
            }
        });
    }

    function saveCategoryOrder(showAlertMsg = true) {
        const order = Array.from(document.querySelectorAll('#sortableCategories .category-order-item'))
            .map(item => item.getAttribute('data-id'));

        fetch('{{ route("admin.display-order.update-category-order") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order: order })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && showAlertMsg) {
                showAlert('success', data.message);
            }
        })
        .catch(err => {
            showAlert('danger', 'Failed to save category order.');
        });
    }

    document.getElementById('saveCatOrderBtn')?.addEventListener('click', function() {
        saveCategoryOrder(true);
    });

    // 2. Initialize Sortable for Products Grid (Whole Card Draggable)
    const elProducts = document.getElementById('sortableProducts');
    if (elProducts) {
        new Sortable(elProducts, {
            animation: 150,
            touchStartThreshold: 3,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function() {
                updateBadges('#sortableProducts', 'prod-order-badge');
                saveProductOrder(false);
            }
        });
    }

    function saveProductOrder(showAlertMsg = true) {
        const order = Array.from(document.querySelectorAll('#sortableProducts .product-order-item'))
            .map(item => item.getAttribute('data-id'));

        fetch('{{ route("admin.display-order.update-product-order") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order: order })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && showAlertMsg) {
                showAlert('success', data.message);
            }
        })
        .catch(err => {
            showAlert('danger', 'Failed to save product order.');
        });
    }

    document.getElementById('saveProdOrderBtn')?.addEventListener('click', function() {
        saveProductOrder(true);
    });
});
</script>
@endsection
