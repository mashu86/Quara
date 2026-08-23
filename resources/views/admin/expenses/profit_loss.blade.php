@extends('layouts.admin')

@section('title', 'Profit & Loss Report - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Profit & Loss Statement</h3>
        <p class="text-muted small mb-0">Financial summary comparing revenue against expenses for the selected period.</p>
    </div>
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-3">&larr; Back to Expenses</a>
</div>

<!-- Date Filter Card -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.reports.profit-loss') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">Start Date</label>
                <input type="date" name="start_date" class="form-control rounded-pill px-3" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">End Date</label>
                <input type="date" name="end_date" class="form-control rounded-pill px-3" value="{{ $endDate }}">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-warning rounded-pill w-100 fw-bold mt-4"><i class="fa-solid fa-chart-pie me-1"></i> Generate Report</button>
            </div>
        </form>
    </div>
</div>

<!-- NET PROFIT / LOSS BANNER -->
<div class="card border-0 rounded-4 shadow-sm mb-4 bg-{{ $isProfit ? 'success' : 'danger' }} text-white">
    <div class="card-body p-4 p-md-5 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <span class="text-uppercase tracking-wider fw-bold small opacity-75">Financial Result ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} &ndash; {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }})</span>
            <h1 class="display-4 fw-bold mb-0">
                {{ $isProfit ? 'PROFIT: ₹' : 'LOSS: -₹' }}{{ number_format(abs($netProfitLoss), 2) }}
            </h1>
            <p class="mb-0 mt-1 opacity-90 fs-6">
                @if($isProfit)
                    <i class="fa-solid fa-circle-check me-1"></i> Excellent! Your business is operating with a positive net margin of ₹{{ number_format($netProfitLoss, 2) }}.
                @else
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Expenses exceeded total revenue by ₹{{ number_format(abs($netProfitLoss), 2) }} for this period.
                @endif
            </p>
        </div>
        <div>
            <span class="badge bg-white text-{{ $isProfit ? 'success' : 'danger' }} fs-5 px-4 py-3 rounded-pill fw-bold shadow-sm">
                {{ $isProfit ? 'NET PROFIT' : 'NET LOSS' }}
            </span>
        </div>
    </div>
</div>

<!-- Financial Summary Grid -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Total Sales Revenue</span>
                    <span class="badge bg-light text-success"><i class="fa-solid fa-arrow-trend-up"></i></span>
                </div>
                <h3 class="fw-bold text-dark mb-1">₹{{ number_format($totalSalesRevenue, 2) }}</h3>
                <div class="small text-muted">
                    Collected Shipping: ₹{{ number_format($totalShippingRevenue, 2) }}
                </div>
                <hr>
                <div class="d-flex justify-content-between small text-dark fw-bold">
                    <span>Total Gross Revenue:</span>
                    <span>₹{{ number_format($totalGrossRevenue, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Total Business Expenses</span>
                    <span class="badge bg-light text-danger"><i class="fa-solid fa-arrow-trend-down"></i></span>
                </div>
                <h3 class="fw-bold text-danger mb-1">₹{{ number_format($totalExpenses, 2) }}</h3>
                <div class="small text-muted">Total Recorded Expense Receipts: {{ $expensesList->count() }}</div>
                <hr>
                <div class="d-flex justify-content-between small text-muted">
                    <span>Expense Ratio:</span>
                    <span class="fw-bold text-dark">{{ $totalGrossRevenue > 0 ? round(($totalExpenses / $totalGrossRevenue) * 100, 1) : 0 }}% of revenue</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Orders Breakdown</span>
                    <span class="badge bg-light text-dark"><i class="fa-solid fa-bag-shopping"></i></span>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $totalOrdersCount }} Orders</h3>
                <div class="small text-muted">Total Orders Paid & Delivered</div>
                <hr>
                <div class="d-flex justify-content-between small text-dark mb-1">
                    <span>Online Website Orders:</span>
                    <span class="fw-bold">{{ $onlineOrdersCount }}</span>
                </div>
                <div class="d-flex justify-content-between small text-dark">
                    <span>Manual Offline Sales:</span>
                    <span class="fw-bold text-warning">{{ $manualOrdersCount }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Itemized Expenses Breakdown Table -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-receipt text-warning me-2"></i> Expenses Included in this Period</h5>
        <span class="badge bg-dark rounded-pill px-3">{{ $expensesList->count() }} Items</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Expense Name</th>
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
                            <td colspan="5" class="text-center py-4 text-muted">No expenses recorded for this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
