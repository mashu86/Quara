@extends('layouts.admin')

@section('title', 'Profit & Loss Report - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .pnl-header-title {
            font-size: 1.15rem !important;
        }
        .pnl-header-subtitle {
            font-size: 0.72rem !important;
        }
        .pnl-top-btn {
            font-size: 0.78rem !important;
            padding: 0.35rem 0.6rem !important;
            border-radius: 8px !important;
        }
        .pnl-banner-card {
            padding: 1.25rem 1rem !important;
        }
        .pnl-banner-period {
            font-size: 0.68rem !important;
        }
        .pnl-banner-amount {
            font-size: 1.45rem !important;
            line-height: 1.2 !important;
            margin-top: 0.25rem !important;
            margin-bottom: 0.25rem !important;
        }
        .pnl-banner-text {
            font-size: 0.76rem !important;
        }
        .pnl-banner-badge {
            font-size: 0.78rem !important;
            padding: 0.35rem 0.85rem !important;
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
            margin-top: 0.5rem !important;
        }
        .breakdown-card-header {
            padding: 0.75rem 0.85rem !important;
        }
        .breakdown-card-header h5 {
            font-size: 0.88rem !important;
        }
        .breakdown-card-header .badge {
            font-size: 0.68rem !important;
            padding: 0.25rem 0.55rem !important;
        }
        .breakdown-card-body {
            padding: 0.85rem !important;
        }
        .breakdown-main-title {
            font-size: 0.76rem !important;
        }
        .breakdown-main-amount {
            font-size: 1.25rem !important;
        }
        .breakdown-line-item {
            font-size: 0.74rem !important;
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
        <h3 class="fw-bold mb-1 pnl-header-title">Profit & Loss Statement</h3>
        <p class="text-muted small mb-0 pnl-header-subtitle">Complete financial summary comparing total sales revenue against itemized costs and expenses.</p>
    </div>
    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
        <a href="{{ route('admin.reports.razorpay-charges') }}" class="btn btn-outline-success rounded-pill px-3 py-1.5 pnl-top-btn shadow-sm w-100 w-sm-auto text-center" title="Razorpay Charges Report">
            <i class="fa-brands fa-credit-card me-1"></i> Razorpay Charges Report
        </a>
        <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-dark rounded-pill px-3 py-1.5 pnl-top-btn shadow-sm w-100 w-sm-auto text-center" title="Back to Expenses">
            &larr; Back to Expenses
        </a>
    </div>
</div>

<!-- Date Filter Card -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-3 filter-card-body">
        <form action="{{ route('admin.reports.profit-loss') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-4">
                <label class="form-label small fw-bold mb-1">Start Date</label>
                <input type="date" name="start_date" class="form-control rounded-pill px-3" value="{{ $startDate }}">
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label small fw-bold mb-1">End Date</label>
                <input type="date" name="end_date" class="form-control rounded-pill px-3" value="{{ $endDate }}">
            </div>
            <div class="col-12 col-md-4 mt-2 mt-md-0">
                <button type="submit" class="btn btn-warning rounded-pill w-100 fw-bold shadow-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                    <i class="fa-solid fa-chart-pie me-1"></i> Generate Financial Statement
                </button>
            </div>
        </form>
    </div>
</div>

<!-- NET PROFIT / LOSS BANNER -->
<div class="card border-0 rounded-4 shadow-sm mb-4 bg-{{ $isProfit ? 'success' : 'danger' }} text-white">
    <div class="card-body p-4 p-md-5 pnl-banner-card d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <span class="text-uppercase tracking-wider fw-bold small opacity-75 d-block pnl-banner-period">
                Financial Period ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} &ndash; {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }})
            </span>
            <h1 class="display-5 fw-bold mb-0 pnl-banner-amount">
                {{ $isProfit ? 'NET PROFIT: ₹' : 'NET LOSS: -₹' }}{{ number_format(abs($netProfitLoss), 2) }}
            </h1>
            <p class="mb-0 mt-1 opacity-90 fs-6 pnl-banner-text">
                @if($isProfit)
                    <i class="fa-solid fa-circle-check me-1"></i> Operating with net profit of ₹{{ number_format($netProfitLoss, 2) }}.
                @else
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Total costs & expenses exceeded sales revenue by ₹{{ number_format(abs($netProfitLoss), 2) }}.
                @endif
            </p>
        </div>
        <div>
            <span class="badge bg-white text-{{ $isProfit ? 'success' : 'danger' }} fs-6 fs-md-5 px-3 px-md-4 py-2 py-md-3 rounded-pill fw-bold shadow-sm pnl-banner-badge">
                {{ $isProfit ? 'NET PROFIT' : 'NET LOSS' }}
            </span>
        </div>
    </div>
</div>

<!-- Financial Breakdown Grid -->
<div class="row g-3 g-md-4 mb-4">
    <!-- Revenue Section -->
    <div class="col-md-6">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center breakdown-card-header">
                <h5 class="fw-bold mb-0 text-success"><i class="fa-solid fa-arrow-trend-up me-1.5"></i> Sales Revenue</h5>
                <span class="badge bg-success rounded-pill px-2.5 px-md-3">{{ $totalOrdersCount }} Orders</span>
            </div>
            <div class="card-body p-3.5 p-md-4 breakdown-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small fw-bold breakdown-main-title">Total Sales (Gross Revenue)</span>
                    <h3 class="fw-bold text-success mb-0 breakdown-main-amount">₹{{ number_format($totalGrossRevenue, 2) }}</h3>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between breakdown-line-item mb-2">
                    <span class="text-dark"><i class="fa-solid fa-money-bill-wave text-success me-1"></i> COD Sales:</span>
                    <span class="fw-bold text-dark">₹{{ number_format($codSalesRevenue, 2) }} <small class="text-muted">({{ $codOrdersCount }})</small></span>
                </div>
                <div class="d-flex justify-content-between breakdown-line-item mb-2">
                    <span class="text-dark"><i class="fa-brands fa-credit-card text-primary me-1"></i> Online Sales:</span>
                    <span class="fw-bold text-dark">₹{{ number_format($onlineSalesRevenue, 2) }} <small class="text-muted">({{ $onlineOrdersCount }})</small></span>
                </div>
                <div class="d-flex justify-content-between breakdown-line-item mb-0 pt-2 border-top">
                    <span class="text-muted">Collected Shipping:</span>
                    <span class="fw-bold text-secondary">₹{{ number_format($totalShippingRevenue, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Deductions & Expenses Section -->
    <div class="col-md-6">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center breakdown-card-header">
                <h5 class="fw-bold mb-0 text-danger"><i class="fa-solid fa-arrow-trend-down me-1.5"></i> Costs & Deductions</h5>
                <span class="badge bg-danger rounded-pill px-2.5 px-md-3">Total Deductions</span>
            </div>
            <div class="card-body p-3.5 p-md-4 breakdown-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small fw-bold breakdown-main-title">Total Deductions</span>
                    <h3 class="fw-bold text-danger mb-0 breakdown-main-amount">₹{{ number_format($totalExpenses, 2) }}</h3>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between breakdown-line-item mb-2">
                    <span class="text-dark"><i class="fa-solid fa-box-archive text-warning me-1"></i> Product Cost:</span>
                    <span class="fw-bold text-dark">₹{{ number_format($totalProductCost, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between breakdown-line-item mb-2">
                    <span class="text-dark"><i class="fa-solid fa-truck text-info me-1"></i> Shipping Cost:</span>
                    <span class="fw-bold text-dark">₹{{ number_format($totalShippingRevenue, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between breakdown-line-item mb-2">
                    <span class="text-dark"><i class="fa-brands fa-credit-card text-danger me-1"></i> Razorpay Charges:</span>
                    <span class="fw-bold text-danger">₹{{ number_format($totalRazorpayCharges, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between breakdown-line-item mb-0 pt-2 border-top">
                    <span class="text-muted"><i class="fa-solid fa-receipt me-1"></i> General Expenses:</span>
                    <span class="fw-bold text-danger">₹{{ number_format($otherExpenses, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Itemized Recorded Expenses Table -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center table-card-header">
        <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-receipt text-warning me-1.5"></i> Expense Receipts in Period</h5>
        <span class="badge bg-dark rounded-pill px-2.5 px-md-3">{{ $expensesList->count() }} Receipts</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Expense Title</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expensesList as $exp)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($exp->expense_date)->format('M d, Y') }}</td>
                            <td class="fw-semibold">{{ $exp->title ?? $exp->expense_name }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $exp->category ?? 'General' }}</span></td>
                            <td class="fw-bold text-danger">₹{{ number_format($exp->amount, 2) }}</td>
                            <td class="small text-muted">{{ $exp->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No general expenses recorded for this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
