@extends('layouts.admin')

@section('title', 'Display & Sorting Master - ' . $siteName)

@section('styles')
<style>
    .drag-handle {
        cursor: grab;
        padding: 8px 12px;
        color: #888888;
        transition: color 0.2s ease;
    }
    .drag-handle:hover, .sortable-ghost .drag-handle {
        color: var(--qw-gold-dark) !important;
    }
    .sortable-ghost {
        opacity: 0.4;
        background-color: #F8F9FA !important;
        border: 2px dashed var(--qw-gold) !important;
    }
    .sortable-chosen {
        background-color: #FFFDF0 !important;
    }
    .sortable-drag {
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
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
        width: 28px;
        height: 28px;
        background-color: #0D0D0E;
        color: #D4AF37;
        font-weight: 700;
        font-size: 0.8rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .category-order-item, .product-order-item {
        background: #FFFFFF;
        border: 1px solid #EAEAEA;
        border-radius: 14px;
        transition: all 0.2s ease;
    }
    .category-order-item:hover, .product-order-item:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        border-color: #DDD;
    }
    .thumb-img {
        width: 54px;
        height: 54px;
        object-fit: cover;
        border-radius: 10px;
        background-color: #F0F0F0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Page Title Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="font-serif fw-bold text-dark mb-1">
                <i class="fa-solid fa-arrows-up-down-left-right text-warning me-2"></i> Client Display & Sorting Control
            </h3>
            <p class="text-muted small mb-0">Control which order Categories & Products appear on the customer site and set default view mode.</p>
        </div>
    </div>

    <!-- Alert / Toast Container -->
    <div id="ajaxAlertContainer"></div>

    <!-- SECTION 1: DEFAULT ORDER PREFERENCE -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h5 class="font-serif fw-bold mb-0 text-dark">
                <i class="fa-solid fa-sliders text-gold me-2"></i> 1. Default Client Display Preference
            </h5>
            <small class="text-muted">Choose what customer sees first on the home page as default.</small>
        </div>
        <div class="card-body p-4">
            <form id="preferenceForm" action="{{ route('admin.display-order.update-preference') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="w-100 h-100">
                            <input type="radio" name="default_display_order_by" value="category" class="d-none preference-radio" {{ $defaultOrderBy === 'category' ? 'checked' : '' }}>
                            <div class="preference-card p-4 h-100 {{ $defaultOrderBy === 'category' ? 'active' : '' }}" id="prefCardCategory">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-warning text-dark rounded-circle p-3 fs-4" style="background-color: var(--qw-gold) !important;">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Order Based On: Category</h6>
                                        <p class="small text-muted mb-0">Client website displays Categories first on home page, followed by Trending Products.</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="col-md-6">
                        <label class="w-100 h-100">
                            <input type="radio" name="default_display_order_by" value="product" class="d-none preference-radio" {{ $defaultOrderBy === 'product' ? 'checked' : '' }}>
                            <div class="preference-card p-4 h-100 {{ $defaultOrderBy === 'product' ? 'active' : '' }}" id="prefCardProduct">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-dark text-white rounded-circle p-3 fs-4">
                                        <i class="fa-solid fa-shirt text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Order Based On: Product</h6>
                                        <p class="small text-muted mb-0">Client website displays Trending Products first on home page, followed by Shop Categories.</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" id="savePrefBtn">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Display Preference
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SECTION 2: DRAG AND DROP POSITION MANAGER -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <ul class="nav nav-pills card-header-pills fw-semibold" id="sortingTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 py-2" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories-content" type="button" role="tab">
                        <i class="fa-solid fa-layer-group me-2"></i> Category Drag & Drop Order ({{ $categories->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 py-2" id="products-tab" data-bs-toggle="tab" data-bs-target="#products-content" type="button" role="tab">
                        <i class="fa-solid fa-shirt me-2"></i> Product Drag & Drop Order ({{ $products->count() }})
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="sortingTabContent">

                <!-- CATEGORIES DRAG & DROP TAB -->
                <div class="tab-pane fade show active" id="categories-content" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="small text-muted">
                            <i class="fa-solid fa-hand-pointer text-warning me-1"></i> Drag items up/down using handle <i class="fa-solid fa-grip-vertical"></i> to re-arrange position.
                        </div>
                        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3" id="saveCatOrderBtn">
                            <i class="fa-solid fa-check me-1"></i> Save Category Order
                        </button>
                    </div>

                    <div id="sortableCategories" class="d-flex flex-column gap-2">
                        @forelse($categories as $index => $cat)
                            <div class="category-order-item p-3 d-flex align-items-center justify-content-between" data-id="{{ $cat->id }}">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="drag-handle fs-5" title="Drag to reorder">
                                        <i class="fa-solid fa-grip-vertical"></i>
                                    </div>
                                    <span class="order-badge cat-order-badge">#{{ $index + 1 }}</span>
                                    <img src="{{ $cat->background_image_url }}" alt="{{ $cat->name }}" class="thumb-img">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">{{ $cat->name }}</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-dark border small">{{ $cat->products_count }} Products</span>
                                            @if($cat->status === 'active')
                                                <span class="badge bg-success small">Active</span>
                                            @else
                                                <span class="badge bg-secondary small">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="text-muted small d-none d-md-block">
                                    Slug: <code>{{ $cat->slug }}</code>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">No categories found.</div>
                        @endforelse
                    </div>
                </div>

                <!-- PRODUCTS DRAG & DROP TAB -->
                <div class="tab-pane fade" id="products-content" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="small text-muted">
                            <i class="fa-solid fa-hand-pointer text-warning me-1"></i> Drag products up/down using handle <i class="fa-solid fa-grip-vertical"></i> to re-arrange position.
                        </div>
                        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3" id="saveProdOrderBtn">
                            <i class="fa-solid fa-check me-1"></i> Save Product Order
                        </button>
                    </div>

                    <div id="sortableProducts" class="d-flex flex-column gap-2">
                        @forelse($products as $index => $prod)
                            <div class="product-order-item p-3 d-flex align-items-center justify-content-between" data-id="{{ $prod->id }}">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="drag-handle fs-5" title="Drag to reorder">
                                        <i class="fa-solid fa-grip-vertical"></i>
                                    </div>
                                    <span class="order-badge prod-order-badge">#{{ $index + 1 }}</span>
                                    <img src="{{ $prod->primary_image_url }}" alt="{{ $prod->name }}" class="thumb-img">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1 text-truncate" style="max-width: 320px;">{{ $prod->name }}</h6>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge bg-light text-dark border small">{{ $prod->category->name ?? 'Uncategorized' }}</span>
                                            <span class="fw-bold text-gold small">₹{{ number_format($prod->final_price, 2) }}</span>
                                            @if($prod->status === 'active')
                                                <span class="badge bg-success small">Active</span>
                                            @else
                                                <span class="badge bg-secondary small">Inactive</span>
                                            @endif
                                            @if($prod->is_out_of_stock)
                                                <span class="badge bg-danger small">Out of Stock</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="text-muted small d-none d-md-block">
                                    ID: <code>#{{ $prod->id }}</code>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">No products found.</div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<!-- SortableJS CDN for touch and drag-and-drop support -->
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

    // Preference Radio Styling & AJAX Save
    const radios = document.querySelectorAll('.preference-radio');
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.preference-card').forEach(card => card.classList.remove('active'));
            if (this.value === 'category') {
                document.getElementById('prefCardCategory').classList.add('active');
            } else {
                document.getElementById('prefCardProduct').classList.add('active');
            }
        });
    });

    document.getElementById('preferenceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const selectedPref = document.querySelector('input[name="default_display_order_by"]:checked').value;
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
            body: JSON.stringify({ default_display_order_by: selectedPref })
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Display Preference';
            if (data.success) {
                showAlert('success', data.message);
            } else {
                showAlert('danger', 'Failed to update preference.');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Display Preference';
            showAlert('danger', 'Server error while saving preference.');
        });
    });

    // Function to update position badge index numbers
    function updateBadges(containerSelector, badgeSelector) {
        const items = document.querySelectorAll(`${containerSelector} .${badgeSelector}`);
        items.forEach((badge, idx) => {
            badge.textContent = `#${idx + 1}`;
        });
    }

    // 1. Initialize Sortable for Categories
    const elCategories = document.getElementById('sortableCategories');
    if (elCategories) {
        new Sortable(elCategories, {
            handle: '.drag-handle',
            animation: 150,
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

    // 2. Initialize Sortable for Products
    const elProducts = document.getElementById('sortableProducts');
    if (elProducts) {
        new Sortable(elProducts, {
            handle: '.drag-handle',
            animation: 150,
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
