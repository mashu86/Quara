@extends('layouts.admin')

@section('title', 'Duplicate Paid Products - ' . $siteName . ' Admin')

@section('content')
<style>
    .duplicate-report-heading { font-size: 1.65rem !important; line-height: 1.2; letter-spacing: -.02em; }
    .duplicate-report-subtitle { max-width: 760px; font-size: .94rem !important; line-height: 1.5; }
    .duplicate-report-count { white-space: nowrap; font-size: .9rem; font-weight: 700; }
    .duplicate-filter-card { border: 1px solid #edf0f2 !important; }
    .duplicate-filter-card .form-label { margin-bottom: .45rem; color: #253044; font-size: .84rem; }
    .duplicate-filter-card .form-control { min-height: 48px; border-color: #dce1e7; border-radius: 12px !important; }
    .duplicate-filter-card .form-control:focus { border-color: #f2b900; box-shadow: 0 0 0 .2rem rgba(255, 193, 7, .16); }
    .duplicate-filter-actions { display: flex; gap: .55rem; }
    .duplicate-filter-actions .btn { min-height: 48px; border-radius: 12px !important; }
    .duplicate-filter-submit { flex: 1 1 auto; white-space: nowrap; }
    .duplicate-filter-reset { width: 48px; flex: 0 0 48px; }
    .duplicate-product-thumb { width: 68px; height: 82px; object-fit: cover; cursor: zoom-in; }
    .duplicate-order-row { border-top: 1px solid #edf0f2; }
    .duplicate-order-row:first-child { border-top: 0; }
    @media (max-width: 767.98px) {
        .duplicate-report-heading { font-size: 1.35rem !important; }
        .duplicate-report-subtitle { font-size: .82rem !important; }
        .duplicate-filter-card .card-body { padding: 1rem !important; }
        .duplicate-filter-actions { grid-column: 1 / -1; }
    }
</style>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1 duplicate-report-heading"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Duplicate Paid Products</h4>
        <p class="text-muted mb-0 duplicate-report-subtitle">Products found in multiple paid orders. Returned and cancelled product lines are excluded.</p>
    </div>
    <span class="badge bg-warning-subtle text-dark border border-warning rounded-pill px-3 py-2 duplicate-report-count">{{ $groups->total() }} products flagged</span>
</div>

<div class="card border-0 rounded-4 shadow-sm mb-4 duplicate-filter-card">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.reports.duplicate-paid-products') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Search Product</label>
                <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Product name">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold">From date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold">To date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-2 duplicate-filter-actions">
                <button class="btn btn-warning fw-bold duplicate-filter-submit" type="submit"><i class="fa-solid fa-magnifying-glass me-1"></i>Search</button>
                <a class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center duplicate-filter-reset" href="{{ route('admin.reports.duplicate-paid-products') }}" title="Reset filters" aria-label="Reset filters"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

@forelse($groups as $group)
    @php
        $matchingItems = $orderItemsByProduct->get($group->product_id, collect());
        $product = $productsById->get($group->product_id);
        $imageUrl = $product ? $product->primary_image_url : \App\Models\Setting::logoUrl();
    @endphp
    <div class="card border-0 rounded-4 shadow-sm mb-3 overflow-hidden">
        <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="border-0 bg-light rounded-3 p-1 shadow-sm flex-shrink-0" onclick="openDuplicateProductImage(@js($imageUrl), @js($group->product_name))" title="View product image">
                    <img src="{{ $imageUrl }}" alt="{{ $group->product_name }}" class="duplicate-product-thumb rounded-2 d-block">
                </button>
                <div class="min-w-0 flex-grow-1">
                    <h6 class="fw-bold text-dark mb-1 text-break"><i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>{{ $group->product_name }}</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-warning text-dark">{{ $group->buyer_count }} different buyers</span>
                        <span class="badge bg-light text-dark border">{{ $group->paid_order_count }} paid orders</span>
                        <span class="badge bg-light text-dark border">{{ $group->total_quantity }} units altogether</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @foreach($matchingItems as $item)
                <div class="duplicate-order-row p-3 px-md-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-secondary-subtle text-dark"><i class="fa-regular fa-calendar me-1"></i>{{ \Illuminate\Support\Carbon::parse($item->sale_date ?? $item->order_created_at)->format('d M Y, h:i A') }}</span>
                            <a href="{{ route('admin.orders.show', $item->order_id) }}" class="badge bg-dark text-decoration-none"><i class="fa-solid fa-receipt me-1"></i>#{{ $item->order_number }}</a>
                            <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Paid</span>
                            <span class="badge bg-info-subtle text-dark border"><i class="fa-solid fa-truck-fast me-1"></i>{{ ucfirst(str_replace('_', ' ', $item->order_status)) }}</span>
                        </div>
                        <span class="fw-bold text-dark">₹{{ number_format((float) $item->subtotal, 2) }}</span>
                    </div>
                    <div class="row g-2 small">
                        <div class="col-12 col-md-5"><span class="text-muted">Customer:</span> <strong>{{ $item->customer_name }}</strong> <span class="text-muted">{{ $item->customer_phone }}</span></div>
                        <div class="col-6 col-md-2"><span class="text-muted">Size:</span> <strong>{{ $item->size ?: '—' }}</strong></div>
                        <div class="col-6 col-md-2"><span class="text-muted">Qty:</span> <strong>{{ $item->quantity }}</strong></div>
                        <div class="col-12 col-md-3"><span class="text-muted">Payment:</span> <strong>{{ ucfirst($item->payment_method) }}</strong></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="card border-0 rounded-4 shadow-sm"><div class="card-body text-center py-5 text-muted">
        <i class="fa-solid fa-circle-check fa-2x text-success mb-2"></i>
        <div class="fw-semibold">No duplicate paid products found</div>
        <small>Returned and cancelled product lines are not counted.</small>
    </div></div>
@endforelse
@if($groups->hasPages())<div class="mt-3">{{ $groups->links() }}</div>@endif

<div class="modal fade" id="duplicateProductImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content border-0 shadow rounded-4 overflow-hidden">
        <div class="modal-header bg-dark text-white py-2"><h6 class="modal-title fw-bold" id="duplicateProductImageTitle">Product image</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body p-0 text-center bg-light"><img id="duplicateProductImage" src="" alt="Product image" class="img-fluid w-100" style="max-height:75vh;object-fit:contain"></div>
    </div></div>
</div>
<script>
function openDuplicateProductImage(url, name) {
    document.getElementById('duplicateProductImage').src = url;
    document.getElementById('duplicateProductImageTitle').textContent = name;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('duplicateProductImageModal')).show();
}
</script>
@endsection
