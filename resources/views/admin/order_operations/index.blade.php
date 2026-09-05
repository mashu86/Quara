@extends('layouts.admin')

@section('title', 'Order Adjustments - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 768px) {
        .op-title { font-size: 1.15rem !important; }
        .op-subtitle { font-size: 0.72rem !important; }
        .op-table th, .op-table td { font-size: 0.73rem !important; padding: 0.5rem 0.35rem !important; }
        .op-table .btn { font-size: 0.68rem !important; padding: 0.25rem 0.45rem !important; }
        .op-table .badge { font-size: 0.63rem !important; padding: 0.2em 0.45em !important; }
        .op-prod-img { width: 32px !important; height: 40px !important; }
    }
</style>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 op-title">Order Adjustments</h3>
        <p class="text-muted small mb-0 op-subtitle">Record product returns, damaged items, customer refunds, and post-order financial adjustments.</p>
    </div>
</div>

@php
    $activeFilterCount = (request()->filled('search') ? 1 : 0)
        + (request()->filled('has_operation') ? 1 : 0)
        + (request()->filled('operation_status') ? 1 : 0)
        + (request()->filled('from_date') ? 1 : 0)
        + (request()->filled('to_date') ? 1 : 0);
@endphp

<!-- Mobile Filter Button Bar (d-lg-none) -->
<div class="d-lg-none mb-3">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-dark rounded-pill px-3 py-2 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#orderOpsFilterModal">
            <i class="fa-solid fa-sliders text-warning"></i>
            <span class="fw-semibold small">Filter Adjustments</span>
            @if($activeFilterCount > 0)
                <span class="badge bg-warning text-dark rounded-pill">{{ $activeFilterCount }}</span>
            @endif
        </button>
        @if($activeFilterCount > 0)
            <a href="{{ route('admin.order-operations.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear Filters">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </div>
</div>

<!-- Desktop Search & Filters (d-none d-lg-block) -->
<div class="card border-0 rounded-4 shadow-sm mb-4 d-none d-lg-block">
    <div class="card-body p-3 p-sm-4">
        <form action="{{ route('admin.order-operations.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-lg-3 col-md-4">
                <label class="form-label small fw-bold text-dark">Search Order / Customer / Product</label>
                <div class="input-group">
                    <span class="input-group-text bg-white rounded-start-3 text-muted border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control rounded-end-3 border-start-0" placeholder="Order #, Customer Name, Phone..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-6 col-lg-2 col-md-2">
                <label class="form-label small fw-bold text-dark"><i class="fa-regular fa-calendar me-1"></i> From Date</label>
                <input type="date" name="from_date" class="form-control rounded-3" value="{{ request('from_date') }}">
            </div>

            <div class="col-6 col-lg-2 col-md-2">
                <label class="form-label small fw-bold text-dark"><i class="fa-regular fa-calendar me-1"></i> To Date</label>
                <input type="date" name="to_date" class="form-control rounded-3" value="{{ request('to_date') }}">
            </div>

            <div class="col-6 col-lg-2 col-md-2">
                <label class="form-label small fw-bold text-dark">Adjustment Filter</label>
                <select name="has_operation" class="form-select rounded-3">
                    <option value="">All Orders</option>
                    <option value="with_ops" {{ $hasOperationFilter === 'with_ops' ? 'selected' : '' }}>With Adjustments</option>
                    <option value="without_ops" {{ $hasOperationFilter === 'without_ops' ? 'selected' : '' }}>Without Adjustments</option>
                </select>
            </div>

            <div class="col-6 col-lg-2 col-md-2">
                <label class="form-label small fw-bold text-dark">Status</label>
                <select name="operation_status" class="form-select rounded-3">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $operationStatusFilter === 'active' ? 'selected' : '' }}>ACTIVE Only</option>
                    <option value="inactive" {{ $operationStatusFilter === 'inactive' ? 'selected' : '' }}>INACTIVE Only</option>
                </select>
            </div>

            <div class="col-12 col-lg-1 col-md-12 d-flex gap-1">
                <button type="submit" class="btn btn-dark w-100 rounded-3 fw-semibold py-2 shadow-sm" title="Apply Filter">
                    <i class="fa-solid fa-filter"></i>
                </button>
                @if($activeFilterCount > 0)
                    <a href="{{ route('admin.order-operations.index') }}" class="btn btn-outline-secondary rounded-3" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Mobile Filter Modal (d-lg-none) -->
<div class="modal fade d-lg-none" id="orderOpsFilterModal" tabindex="-1" aria-labelledby="orderOpsFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold fs-6" id="orderOpsFilterModalLabel">
                    <i class="fa-solid fa-sliders text-warning me-2"></i> Filter Adjustments
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.order-operations.index') }}" method="GET">
                <div class="modal-body p-3.5">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Search Order # / Customer / Product</label>
                        <input type="text" name="search" class="form-control rounded-3" placeholder="Order #, Name, Phone..." value="{{ $search }}">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small"><i class="fa-regular fa-calendar me-1"></i> From Date</label>
                            <input type="date" name="from_date" class="form-control rounded-3" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small"><i class="fa-regular fa-calendar me-1"></i> To Date</label>
                            <input type="date" name="to_date" class="form-control rounded-3" value="{{ request('to_date') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Adjustment Filter</label>
                        <select name="has_operation" class="form-select rounded-3">
                            <option value="">All Orders</option>
                            <option value="with_ops" {{ $hasOperationFilter === 'with_ops' ? 'selected' : '' }}>With Adjustments</option>
                            <option value="without_ops" {{ $hasOperationFilter === 'without_ops' ? 'selected' : '' }}>Without Adjustments</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Adjustment Status</label>
                        <select name="operation_status" class="form-select rounded-3">
                            <option value="">All Statuses (Active & Inactive)</option>
                            <option value="active" {{ $operationStatusFilter === 'active' ? 'selected' : '' }}>ACTIVE Only</option>
                            <option value="inactive" {{ $operationStatusFilter === 'inactive' ? 'selected' : '' }}>INACTIVE Only</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-3 py-2.5">
                    <a href="{{ route('admin.order-operations.index') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">Reset</a>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark btn-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-filter me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Orders & Adjustments Section -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <!-- MOBILE VIEW: Compact Cards Layout (d-block d-md-none) -->
        <div class="d-block d-md-none p-2 bg-light" id="order-ops-mobile-container">
            @include('admin.order_operations.partials.mobile_cards')
        </div>

        <!-- DESKTOP VIEW: Table Layout (d-none d-md-block) -->
        <div class="d-none d-md-block">
            <div class="table-responsive" id="order-ops-scroll-container" style="max-height: 75vh; overflow-y: auto;">
                <table class="table op-table align-middle mb-0">
                    <thead class="table-dark sticky-top shadow-sm" style="z-index: 5;">
                        <tr>
                            <th class="ps-3">Order ID</th>
                            <th>Customer</th>
                            <th>Purchased Item(s)</th>
                            <th>Order Amount</th>
                            <th>Order Status</th>
                            <th>Adjustments Recorded</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="order-ops-tbody">
                        @forelse($orders as $order)
                            @include('admin.order_operations.partials.desktop_rows', ['orders' => collect([$order])])
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No orders or adjustments matching criteria found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white py-2 text-center border-top">
        <div id="infinite-scroll-loading" class="d-none text-muted small py-1">
            <div class="spinner-border spinner-border-sm text-warning me-1" role="status"></div>
            Loading more adjustments...
        </div>
        <div id="infinite-scroll-end" class="{{ $orders->hasMorePages() ? 'd-none' : '' }} text-muted small py-1">
            <i class="fa-solid fa-circle-check text-success me-1"></i> All {{ $orders->total() }} records loaded
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openImagePreviewModal(imageUrl, title) {
        let modalEl = document.getElementById('imagePreviewModal');
        if (!modalEl) {
            const modalHtml = `
                <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header bg-dark text-white py-2.5 px-3">
                                <h5 class="modal-title fs-6 fw-bold text-truncate" id="imagePreviewModalTitle">Product Image Preview</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-2 p-sm-3 text-center bg-dark d-flex align-items-center justify-content-center" style="min-height: 350px;">
                                <img id="imagePreviewModalImg" src="" alt="Product Large Image" class="img-fluid rounded-3 shadow" style="max-height: 80vh; max-width: 100%; object-fit: contain;">
                            </div>
                        </div>
                    </div>
                </div>`;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            modalEl = document.getElementById('imagePreviewModal');
        }
        document.getElementById('imagePreviewModalImg').src = imageUrl;
        document.getElementById('imagePreviewModalTitle').textContent = title || 'Product Image Preview';
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        let nextPageUrl = @json($orders->nextPageUrl());
        let hasMore = @json($orders->hasMorePages());
        let isLoading = false;

        const scrollContainer = document.getElementById('order-ops-scroll-container');
        const tbody = document.getElementById('order-ops-tbody');
        const mobileContainer = document.getElementById('order-ops-mobile-container');
        const loadingSpinner = document.getElementById('infinite-scroll-loading');
        const endNotice = document.getElementById('infinite-scroll-end');

        function checkAndLoadMore() {
            if (isLoading || !hasMore || !nextPageUrl) return;

            let shouldLoad = false;

            if (scrollContainer && scrollContainer.offsetParent !== null) {
                const scrollBottom = scrollContainer.scrollHeight - scrollContainer.scrollTop - scrollContainer.clientHeight;
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
                if (data.desktop_html && tbody) {
                    tbody.insertAdjacentHTML('beforeend', data.desktop_html);
                }
                if (data.mobile_html && mobileContainer) {
                    mobileContainer.insertAdjacentHTML('beforeend', data.mobile_html);
                }

                nextPageUrl = data.next_page_url;
                hasMore = data.has_more;
                isLoading = false;
                if (loadingSpinner) loadingSpinner.classList.add('d-none');

                if (!hasMore && endNotice) {
                    endNotice.classList.remove('d-none');
                }
            })
            .catch(err => {
                console.error('Error fetching more order adjustments:', err);
                isLoading = false;
                if (loadingSpinner) loadingSpinner.classList.add('d-none');
            });
        }

        if (scrollContainer) {
            scrollContainer.addEventListener('scroll', checkAndLoadMore);
        }
        window.addEventListener('scroll', checkAndLoadMore);
    });
</script>
@endsection
