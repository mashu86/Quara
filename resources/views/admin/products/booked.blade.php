@extends('layouts.admin')

@section('title', 'Booked Products - ' . $siteName)

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-user-lock text-warning me-2"></i>Booked Products</h4>
        <p class="text-muted small mb-0">View current product bookings and release them when needed.</p>
    </div>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 align-self-start">
        <i class="fa-solid fa-shirt me-1"></i> Product Master
    </a>
</div>

<form action="{{ route('admin.products.booked') }}" method="GET" class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
        <label for="booked-product-search" class="form-label small fw-semibold">Search product or booked by</label>
        <div class="d-flex flex-wrap gap-2">
            <input id="booked-product-search" type="search" name="search" value="{{ $search }}" class="form-control flex-grow-1 w-auto" placeholder="Product name or booking name / phone">
            <button class="btn btn-dark px-3" type="submit">Search</button>
            @if($search !== '')
                <a href="{{ route('admin.products.booked') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold fs-6">Current Bookings</h5>
        <span class="badge bg-dark rounded-pill">{{ $products->total() }} products</span>
    </div>
    <form id="bulk-unbook-form" action="{{ route('admin.products.booked.bulk-unbook') }}" method="POST">
        @csrf
        <input type="hidden" name="search" value="{{ $search }}">
        <input type="hidden" name="page" value="{{ $products->currentPage() }}">
        <div class="px-3 py-2 border-top border-bottom bg-light d-flex align-items-center gap-2 flex-wrap">
            <label class="small fw-semibold mb-0 d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input m-0" id="select-all-booked"> Select all on this page</label>
            <span class="small text-muted" id="selected-booked-count">0 selected</span>
            <button type="submit" class="btn btn-sm btn-success rounded-pill ms-auto" id="bulk-unbook-button" disabled>
                <i class="fa-solid fa-lock-open me-1"></i> Unbook selected
            </button>
        </div>
    </form>
    <div class="table-responsive booked-products-scroll">
        <table class="table table-hover align-middle mb-0 booked-products-table">
            <thead class="table-light small text-nowrap">
                <tr>
                    <th class="ps-3 booked-sticky-image">Select / Image</th>
                    <th>Product</th>
                    <th>Booked By</th>
                    <th>Size / Stock</th>
                    <th>Price</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td class="ps-3 booked-sticky-image">
                            <div class="d-flex align-items-center gap-2">
                                <input form="bulk-unbook-form" type="checkbox" class="form-check-input booked-product-checkbox m-0" name="product_ids[]" value="{{ $product->id }}" aria-label="Select {{ $product->name }}">
                                @php
                                    $preview = [
                                        'image' => $product->primary_image_url,
                                        'name' => $product->name,
                                        'price' => '₹' . number_format($product->final_price, 0),
                                        'sizes' => $product->sizes->map->only(['size', 'stock', 'chest', 'waist', 'length'])->values(),
                                    ];
                                @endphp
                                <button type="button" class="booked-product-preview position-relative rounded border-0 p-0 flex-shrink-0 overflow-hidden"
                                    data-preview="{{ json_encode($preview) }}" data-details-id="booked-product-details-{{ $product->id }}"
                                    aria-label="Preview {{ $product->name }}" title="View product image and details">
                                    <img src="{{ $product->primary_image_url }}" alt="" loading="lazy" width="44" height="54" class="d-block" style="object-fit: cover;">
                                    <span class="position-absolute top-50 start-50 translate-middle rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 26px; height: 26px; background: rgba(0,0,0,.65); font-size: .7rem;">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </span>
                                </button>
                                <template id="booked-product-details-{{ $product->id }}">
                                    <h6 class="fw-bold mb-1">{{ $product->name }}</h6>
                                    <p class="small text-muted mb-2">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                                    <p class="fw-bold mb-2">₹{{ number_format($product->final_price, 2) }}</p>
                                    <p class="small mb-3"><strong>Booked By:</strong> {{ $product->booked_by }}</p>
                                    @if($product->description)
                                        <p class="small" style="white-space: pre-line;">{{ strip_tags($product->description) }}</p>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-sm small mb-0">
                                            <thead><tr><th>Size</th><th>Stock</th><th>Chest</th><th>Waist</th><th>Length</th></tr></thead>
                                            <tbody>
                                                @forelse($product->sizes as $size)
                                                    <tr><td>{{ $size->size }}</td><td>{{ $size->stock }}</td><td>{{ $size->chest ?: '—' }}</td><td>{{ $size->waist ?: '—' }}</td><td>{{ $size->length ?: '—' }}</td></tr>
                                                @empty
                                                    <tr><td colspan="5" class="text-muted">No sizes recorded</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </template>
                            </div>
                        </td>
                        <td style="min-width: 190px;">
                            <a href="{{ route('admin.products.edit', $product) }}" class="fw-semibold text-dark text-decoration-none">{{ $product->name }}</a>
                            <div class="small text-muted">{{ $product->category?->name ?? 'Uncategorized' }}</div>
                        </td>
                        <td style="min-width: 140px;">
                            <span class="fw-semibold">{{ $product->booked_by }}</span>
                            <div class="small text-warning-emphasis"><i class="fa-solid fa-lock me-1"></i>Booked</div>
                        </td>
                        <td style="min-width: 120px;">
                            @forelse($product->sizes as $size)
                                <span class="badge bg-light text-dark border mb-1">{{ $size->size }}: {{ $size->stock }}</span>
                            @empty
                                <span class="small text-muted">No sizes recorded</span>
                            @endforelse
                        </td>
                        <td class="text-nowrap fw-semibold">₹{{ number_format($product->final_price, 2) }}</td>
                        <td class="text-end pe-3">
                            <form action="{{ route('admin.products.toggle-out-of-stock', $product) }}" method="POST" onsubmit="return confirm('Unbook this product? Its booking will be cleared and normal stock availability will apply.');">
                                @csrf
                                <input type="hidden" name="is_out_of_stock" value="0">
                                <input type="hidden" name="booked_by" value="">
                                <button type="submit" class="btn btn-sm btn-outline-success text-nowrap rounded-pill px-3">
                                    <i class="fa-solid fa-lock-open me-1"></i> Unbook
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            {{ $search !== '' ? 'No bookings match your search.' : 'No products are currently booked.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white pt-3">{{ $products->links() }}</div>
    @endif
</div>
<style>
    .booked-products-scroll { overflow-x: auto; }
    .booked-products-table { min-width: 850px; }
    .booked-sticky-image { position: sticky; left: 0; z-index: 2; min-width: 108px; width: 108px; background: #fff; box-shadow: 2px 0 4px rgba(0,0,0,.08); }
    thead .booked-sticky-image { z-index: 3; background: #f8f9fa; }
    @media (max-width: 767.98px) {
        .booked-products-table { min-width: 780px; }
        .booked-sticky-image { min-width: 108px; width: 108px; }
    }
</style>
@include('admin.products.partials.preview-modal', ['showDetails' => true])
@endsection

@section('scripts')
<script src="{{ asset('js/admin-product-preview.js') }}"></script>
<script>
document.addEventListener('click', function(event) {
    const button = event.target.closest('.booked-product-preview');
    if (!button) return;

    const product = JSON.parse(button.dataset.preview);
    const details = document.getElementById(button.dataset.detailsId);
    document.getElementById('productPreviewDetails').replaceChildren(details.content.cloneNode(true));
    window.openProductPreview(product.image, product.name, product.price, product.sizes);
});

const bookedCheckboxes = Array.from(document.querySelectorAll('.booked-product-checkbox'));
const selectAllBooked = document.getElementById('select-all-booked');
const bulkUnbookButton = document.getElementById('bulk-unbook-button');
const selectedBookedCount = document.getElementById('selected-booked-count');
function syncBookedSelection() {
    const selected = bookedCheckboxes.filter(checkbox => checkbox.checked).length;
    bulkUnbookButton.disabled = selected === 0;
    selectedBookedCount.textContent = `${selected} selected`;
    selectAllBooked.checked = bookedCheckboxes.length > 0 && selected === bookedCheckboxes.length;
    selectAllBooked.indeterminate = selected > 0 && selected < bookedCheckboxes.length;
}
selectAllBooked.addEventListener('change', function() {
    bookedCheckboxes.forEach(checkbox => checkbox.checked = selectAllBooked.checked);
    syncBookedSelection();
});
bookedCheckboxes.forEach(checkbox => checkbox.addEventListener('change', syncBookedSelection));
document.getElementById('bulk-unbook-form').addEventListener('submit', function(event) {
    if (!bookedCheckboxes.some(checkbox => checkbox.checked)) {
        event.preventDefault();
        return;
    }
    if (!confirm(`Unbook ${bookedCheckboxes.filter(checkbox => checkbox.checked).length} selected product(s)?`)) event.preventDefault();
});
</script>
@endsection
