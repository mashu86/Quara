@extends('layouts.admin')

@section('title', 'Manual Sales - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .add-offline-btn {
            border-radius: 8px !important;
            font-size: 0.78rem !important;
            padding: 0.4rem 0.65rem !important;
        }
        .offline-sales-title {
            font-size: 1.15rem !important;
        }
        .offline-sales-subtitle {
            font-size: 0.72rem !important;
        }
        .search-trigger-btn {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.75rem !important;
        }
        #manualSalesFilterModal .modal-title {
            font-size: 0.9rem !important;
        }
        #manualSalesFilterModal .form-label {
            font-size: 0.76rem !important;
        }
        #manualSalesFilterModal .form-control {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.65rem !important;
        }
        #manualSalesFilterModal .btn {
            font-size: 0.78rem !important;
            padding: 0.35rem 0.8rem !important;
        }
        #manualSalesFilterModal .modal-body {
            padding: 1rem !important;
        }
        #manualSalesFilterModal .modal-footer {
            padding: 0.65rem 1rem !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 offline-sales-title">Offline Sales</h3>
        <p class="text-muted small mb-0 offline-sales-subtitle">Record offline store purchases, counter sales & direct customer orders.</p>
    </div>
    <a href="{{ route('admin.manual-sales.create') }}" class="btn btn-warning rounded-3 rounded-md-pill fw-bold px-2.5 px-md-4 py-1.5 py-md-2 add-offline-btn shadow-sm text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Record New Offline Sale">
        <i class="fa-solid fa-plus me-0 me-md-1"></i><span class="d-none d-md-inline"> Record New Offline Sale</span>
    </a>
</div>

@php
    $activeManualFilterCount = request()->filled('search') ? 1 : 0;
@endphp

<!-- Mobile / Tablet Filter Button Bar (d-lg-none) -->
<div class="d-lg-none mb-3">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-dark rounded-pill px-3 py-2 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2 search-trigger-btn" data-bs-toggle="modal" data-bs-target="#manualSalesFilterModal">
            <i class="fa-solid fa-sliders text-warning"></i>
            <span class="fw-semibold">Search Offline Sales</span>
            @if($activeManualFilterCount > 0)
                <span class="badge bg-warning text-dark rounded-pill">{{ $activeManualFilterCount }}</span>
            @endif
        </button>
        @if($activeManualFilterCount > 0)
            <a href="{{ route('admin.manual-sales.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear Filters">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </div>
</div>

<!-- Desktop Filter / Search (d-none d-lg-block) -->
<div class="card border-0 rounded-4 shadow-sm mb-4 d-none d-lg-block">
    <div class="card-body p-3">
        <form action="{{ route('admin.manual-sales.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-9">
                <input type="text" name="search" class="form-control rounded-pill px-3" placeholder="Search by Order #, Customer Name, Phone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark rounded-pill w-100"><i class="fa-solid fa-magnifying-glass me-1"></i> Search</button>
                <a href="{{ route('admin.manual-sales.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Manual Sales Mobile Filter Modal (d-lg-none) -->
<div class="modal fade d-lg-none" id="manualSalesFilterModal" tabindex="-1" aria-labelledby="manualSalesFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold" id="manualSalesFilterModalLabel">
                    <i class="fa-solid fa-sliders text-warning me-2"></i> Search Offline Sales
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.manual-sales.index') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Search Order # / Customer Name / Phone</label>
                        <input type="text" name="search" class="form-control rounded-3" placeholder="Search by Order #, Customer Name, Phone..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <a href="{{ route('admin.manual-sales.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Reset</a>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive" id="manual-sales-scroll-container" style="max-height: 75vh; overflow-y: auto;">
            <table class="table align-middle mb-0">
                <thead class="table-light sticky-top shadow-sm" style="z-index: 5;">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Purchased Item(s)</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="manual-sales-tbody">
                    @forelse($manualOrders as $order)
                        @include('admin.manual_sales.partials.desktop_rows', ['manualOrders' => collect([$order])])
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-receipt fs-2 mb-2 d-block text-warning"></i>
                                No manual offline sales recorded yet. Click "Record New Offline Sale" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-2 text-center border-top">
        <div id="infinite-scroll-loading" class="d-none text-muted small py-1">
            <div class="spinner-border spinner-border-sm text-warning me-1" role="status"></div>
            Loading more offline sales...
        </div>
        <div id="infinite-scroll-end" class="{{ $manualOrders->hasMorePages() ? 'd-none' : '' }} text-muted small py-1">
            <i class="fa-solid fa-circle-check text-success me-1"></i> All {{ $manualOrders->total() }} offline sales loaded
        </div>
    </div>
</div>

@section('scripts')
<script>
    function openImagePreviewModal(imageUrl, title) {
        let modalEl = document.getElementById('imagePreviewModal');
        if (!modalEl) {
            const modalHtml = `
                <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header bg-dark text-white py-2.5 px-3">
                                <h5 class="modal-title fs-6 fw-bold text-truncate" id="imagePreviewModalTitle">Product Image Preview</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-3 text-center bg-light">
                                <img id="imagePreviewModalImg" src="" alt="Product Large Image" class="img-fluid rounded-3 border shadow-sm" style="max-height: 80vh; object-fit: contain;">
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
        let nextPageUrl = @json($manualOrders->nextPageUrl());
        let hasMore = @json($manualOrders->hasMorePages());
        let isLoading = false;

        const scrollContainer = document.getElementById('manual-sales-scroll-container');
        const tbody = document.getElementById('manual-sales-tbody');
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

                nextPageUrl = data.next_page_url;
                hasMore = data.has_more;
                isLoading = false;
                if (loadingSpinner) loadingSpinner.classList.add('d-none');

                if (!hasMore && endNotice) {
                    endNotice.classList.remove('d-none');
                }
            })
            .catch(err => {
                console.error('Error fetching more offline sales:', err);
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
@endsection
