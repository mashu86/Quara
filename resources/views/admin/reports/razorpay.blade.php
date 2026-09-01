@extends('layouts.admin')

@section('title', 'Razorpay Charges Master Report - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .rzp-header-title {
            font-size: 1.15rem !important;
        }
        .rzp-header-subtitle {
            font-size: 0.72rem !important;
        }
        .rzp-top-btn {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.75rem !important;
        }
        .filter-card-body {
            padding: 0.85rem !important;
        }
        .filter-card-body .form-label {
            font-size: 0.76rem !important;
            margin-bottom: 0.2rem !important;
        }
        .filter-card-body .form-control {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.65rem !important;
        }
        .filter-card-body .btn {
            font-size: 0.8rem !important;
            padding: 0.45rem 0.8rem !important;
        }
        .stat-card-title {
            font-size: 0.66rem !important;
        }
        .stat-card-amount {
            font-size: 1.1rem !important;
            margin-top: 0.2rem !important;
            margin-bottom: 0.1rem !important;
        }
        .stat-card-sub {
            font-size: 0.64rem !important;
        }
        .table-card-header {
            padding: 0.75rem 0.85rem !important;
        }
        .table-card-header h5 {
            font-size: 0.88rem !important;
        }
        .table-card-header .badge {
            font-size: 0.68rem !important;
        }
        .table th, .table td {
            font-size: 0.76rem !important;
            padding: 0.5rem 0.65rem !important;
        }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 rzp-header-title"><i class="fa-brands fa-credit-card text-success me-2"></i> Razorpay Charges Report</h3>
        <p class="text-muted small mb-0 rzp-header-subtitle">Detailed breakdown of payment gateway processing fees and net received amounts per online order.</p>
    </div>
    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-dark rounded-pill px-3 py-1.5 rzp-top-btn shadow-sm w-100 w-sm-auto text-center fw-bold" title="Fee Settings">
            <i class="fa-solid fa-gear me-1"></i> Fee Settings
        </a>
        <a href="{{ route('admin.reports.profit-loss') }}" class="btn btn-warning rounded-pill px-3 py-1.5 rzp-top-btn shadow-sm text-dark w-100 w-sm-auto text-center fw-bold" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Profit & Loss Report">
            <i class="fa-solid fa-chart-pie me-1"></i> Profit & Loss Report
        </a>
    </div>
</div>

<!-- Date Filter Card -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-3 filter-card-body">
        <form action="{{ route('admin.reports.razorpay-charges') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-4">
                <label class="form-label small fw-bold mb-1">Start Date</label>
                <input type="date" name="start_date" class="form-control rounded-pill px-3" value="{{ $startDate }}">
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label small fw-bold mb-1">End Date</label>
                <input type="date" name="end_date" class="form-control rounded-pill px-3" value="{{ $endDate }}">
            </div>
            <div class="col-12 col-md-4 mt-2 mt-md-0">
                <button type="submit" class="btn btn-dark rounded-pill w-100 fw-bold shadow-sm">
                    <i class="fa-solid fa-filter me-1"></i> Filter Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Financial Summary Cards (2x2 Grid on Mobile) -->
<div class="row g-2 g-md-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-2.5 p-md-3 h-100">
            <span class="text-muted small fw-bold text-uppercase stat-card-title">Online Revenue</span>
            <h4 class="fw-bold text-dark mt-1 mb-0 stat-card-amount">₹{{ number_format($totalOnlineRevenue, 2) }}</h4>
            <span class="extra-small text-secondary mt-1 stat-card-sub">Total Online Payments</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-2.5 p-md-3 h-100">
            <span class="text-muted small fw-bold text-uppercase stat-card-title">Base Fee ({{ $feePct }}%)</span>
            <h4 class="fw-bold text-secondary mt-1 mb-0 stat-card-amount">₹{{ number_format($totalRazorpayBaseFee, 2) }}</h4>
            <span class="extra-small text-muted mt-1 stat-card-sub">Gateway Base Charge</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-2.5 p-md-3 h-100">
            <span class="text-muted small fw-bold text-uppercase stat-card-title">GST ({{ $gstPct }}%)</span>
            <h4 class="fw-bold text-danger mt-1 mb-0 stat-card-amount">₹{{ number_format($totalRazorpayGstFee, 2) }}</h4>
            <span class="extra-small text-muted mt-1 stat-card-sub">Tax on Gateway Fee</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm bg-danger text-white p-2.5 p-md-3 h-100">
            <span class="text-uppercase fw-bold small opacity-75 stat-card-title">Total Deducted</span>
            <h4 class="fw-bold mt-1 mb-0 stat-card-amount">₹{{ number_format($totalRazorpayCharges, 2) }}</h4>
            <span class="extra-small opacity-90 mt-1 stat-card-sub">Net: ₹{{ number_format($totalNetReceived, 2) }}</span>
        </div>
    </div>
</div>

<!-- Detailed Orders Table -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center table-card-header">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-list-check text-warning me-1.5"></i> Gateway Deductions
        </h5>
        <span class="badge bg-dark rounded-pill px-2.5 px-md-3">{{ $orders->total() }} Orders</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" id="razorpay-scroll-container" style="max-height: 75vh; overflow-y: auto;">
            <table class="table align-middle mb-0">
                <thead class="table-light sticky-top shadow-sm" style="z-index: 5;">
                    <tr>
                        <th>Date</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Order Amount</th>
                        <th>Base Fee ({{ $feePct }}%)</th>
                        <th>GST ({{ $gstPct }}%)</th>
                        <th>Total Charge</th>
                        <th>Net Received</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="razorpay-tbody">
                    @forelse($orders as $order)
                        @include('admin.reports.partials.razorpay_rows', ['orders' => collect([$order])])
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">No online paid orders found in this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-2 text-center border-top">
        <div id="infinite-scroll-loading" class="d-none text-muted small py-1">
            <div class="spinner-border spinner-border-sm text-warning me-1" role="status"></div>
            Loading more online orders...
        </div>
        <div id="infinite-scroll-end" class="{{ $orders->hasMorePages() ? 'd-none' : '' }} text-muted small py-1">
            <i class="fa-solid fa-circle-check text-success me-1"></i> All {{ $orders->total() }} records loaded
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let nextPageUrl = @json($orders->nextPageUrl());
        let hasMore = @json($orders->hasMorePages());
        let isLoading = false;

        const scrollContainer = document.getElementById('razorpay-scroll-container');
        const tbody = document.getElementById('razorpay-tbody');
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
                console.error('Error fetching more Razorpay records:', err);
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
