@extends('layouts.admin')

@section('title', 'Income Management - ' . $siteName . ' Admin')

@section('content')
<style>
    .income-summary-card {
        height: 100%;
        overflow: hidden;
        position: relative;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .income-summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08) !important;
    }
    .income-summary-card .min-w-0 {
        min-width: 0;
    }
    .income-summary-icon {
        align-items: center;
        border-radius: 14px;
        display: flex;
        flex: 0 0 46px;
        font-size: 1.15rem;
        height: 46px;
        justify-content: center;
        width: 46px;
    }
    .income-summary-label {
        color: #6c757d;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .income-summary-value {
        color: #151515;
        font-size: clamp(1.15rem, 2vw, 1.55rem);
        line-height: 1.15;
    }
    .filtered-income-card {
        background: linear-gradient(135deg, #0d3b2b 0%, #155724 100%);
        color: #fff;
    }
    @media (max-width: 576px) {
        .income-title {
            font-size: 1.15rem !important;
        }
        .income-subtitle {
            font-size: 0.72rem !important;
        }
        .pnl-btn, .add-income-btn {
            border-radius: 8px !important;
            font-size: 0.78rem !important;
            padding: 0.4rem 0.65rem !important;
        }
        .search-trigger-btn {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.75rem !important;
        }
        .income-summary-card .card-body {
            align-items: center !important;
            column-gap: 0.5rem !important;
            display: grid !important;
            grid-template-columns: 32px minmax(0, 1fr);
            grid-template-rows: auto auto;
            padding: 0.75rem !important;
            row-gap: 0.4rem !important;
        }
        .income-summary-icon {
            border-radius: 9px;
            flex-basis: 32px;
            font-size: 0.78rem;
            grid-column: 1;
            grid-row: 1;
            height: 32px;
            width: 32px;
        }
        .income-summary-card .min-w-0 {
            display: contents;
        }
        .income-summary-label {
            font-size: 0.58rem;
            grid-column: 2;
            grid-row: 1;
            letter-spacing: 0.02em;
            margin-bottom: 0 !important;
            white-space: nowrap;
        }
        .income-summary-value {
            font-size: 0.92rem;
            grid-column: 1 / -1;
            grid-row: 2;
            margin-top: 0.05rem;
        }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 income-title">Additional Income Management</h3>
        <p class="text-muted small mb-0 income-subtitle">Record manual sales from wholesale enquiries and additional business income. Active incomes are included in Profit & Loss calculations.</p>
    </div>
    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
        <a href="{{ route('admin.reports.profit-loss') }}" class="btn btn-outline-dark rounded-pill px-3 py-1.5 pnl-btn shadow-sm w-100 w-sm-auto text-center fw-bold" title="Profit & Loss Report">
            <i class="fa-solid fa-chart-line text-warning me-1"></i> Profit & Loss Report
        </a>
        <a href="{{ route('admin.incomes.create') }}" class="btn btn-success rounded-pill px-3 py-1.5 add-income-btn shadow-sm text-white w-100 w-sm-auto text-center fw-bold" title="Add New Income">
            <i class="fa-solid fa-plus me-1"></i> Add Income
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@php
    $activeIncomeFilterCount = (request()->filled('search') ? 1 : 0)
        + (request()->filled('type') ? 1 : 0)
        + (request()->filled('status') ? 1 : 0)
        + (request()->filled('start_date') ? 1 : 0)
        + (request()->filled('end_date') ? 1 : 0);
@endphp

<!-- Summary Cards Grid -->
<div class="row g-2 g-md-3 mb-3 mb-md-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm income-summary-card">
            <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                <div class="income-summary-icon bg-success-subtle text-success"><i class="fa-solid fa-sun"></i></div>
                <div class="min-w-0">
                    <div class="income-summary-label mb-1">Today Income</div>
                    <div class="income-summary-value fw-bold text-success text-truncate">&#8377;{{ number_format($todayIncome, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm income-summary-card">
            <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                <div class="income-summary-icon bg-primary-subtle text-primary"><i class="fa-solid fa-calendar-days"></i></div>
                <div class="min-w-0">
                    <div class="income-summary-label mb-1">This Month</div>
                    <div class="income-summary-value fw-bold text-primary text-truncate">&#8377;{{ number_format($thisMonthIncome, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm income-summary-card">
            <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                <div class="income-summary-icon bg-warning-subtle text-warning-emphasis"><i class="fa-solid fa-calendar"></i></div>
                <div class="min-w-0">
                    <div class="income-summary-label mb-1">This Year</div>
                    <div class="income-summary-value fw-bold text-dark text-truncate">&#8377;{{ number_format($thisYearIncome, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm income-summary-card border-start border-4 border-success">
            <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                <div class="income-summary-icon bg-dark text-warning"><i class="fa-solid fa-filter"></i></div>
                <div class="min-w-0">
                    <div class="income-summary-label mb-1">Selected Period</div>
                    <div class="income-summary-value fw-bold text-success text-truncate">&#8377;{{ number_format($selectedPeriodIncome, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile / Tablet Filter Trigger Button (d-lg-none) -->
<div class="d-lg-none mb-3">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-dark rounded-pill px-3 py-2 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2 search-trigger-btn" data-bs-toggle="modal" data-bs-target="#incomeFilterModal">
            <i class="fa-solid fa-sliders text-warning"></i>
            <span class="fw-semibold">Filter Incomes</span>
            @if($activeIncomeFilterCount > 0)
                <span class="badge bg-warning text-dark rounded-pill">{{ $activeIncomeFilterCount }}</span>
            @endif
        </button>
        @if($activeIncomeFilterCount > 0)
            <a href="{{ route('admin.incomes.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear Filters">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </div>
</div>

<!-- Desktop Filter Bar (d-none d-lg-block) -->
<div class="card border-0 rounded-4 shadow-sm mb-4 d-none d-lg-block">
    <div class="card-body p-3">
        <form action="{{ route('admin.incomes.index') }}" method="GET" class="row g-2 align-items-center m-0">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control rounded-pill px-3" placeholder="Search income name or notes..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select rounded-pill px-3">
                    <option value="">-- All Types --</option>
                    <option value="wholesale_selling" {{ request('type') === 'wholesale_selling' ? 'selected' : '' }}>Wholesale Selling</option>
                    <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select rounded-pill px-3">
                    <option value="">-- All Statuses --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active (In P&L)</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive (Excluded)</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control rounded-pill px-3" value="{{ request('start_date') }}" aria-label="Start date">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control rounded-pill px-3" value="{{ request('end_date') }}" aria-label="End date">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-dark rounded-pill w-100" title="Apply Filter"><i class="fa-solid fa-filter"></i></button>
                @if($activeIncomeFilterCount > 0)
                    <a href="{{ route('admin.incomes.index') }}" class="btn btn-outline-secondary rounded-pill" title="Clear filters"><i class="fa-solid fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Mobile Filter Modal -->
<div class="modal fade d-lg-none" id="incomeFilterModal" tabindex="-1" aria-labelledby="incomeFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold" id="incomeFilterModalLabel">
                    <i class="fa-solid fa-sliders text-warning me-2"></i> Filter Income Records
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.incomes.index') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Search Keyword</label>
                        <input type="text" name="search" class="form-control rounded-3" placeholder="Search income name or notes..." value="{{ request('search') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Income Type</label>
                        <select name="type" class="form-select rounded-3">
                            <option value="">All Types</option>
                            <option value="wholesale_selling" {{ request('type') === 'wholesale_selling' ? 'selected' : '' }}>Wholesale Selling</option>
                            <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other Income</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active (Included in P&L)</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive (Excluded)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">From Date</label>
                        <input type="date" name="start_date" class="form-control rounded-3" value="{{ request('start_date') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">To Date</label>
                        <input type="date" name="end_date" class="form-control rounded-3" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <a href="{{ route('admin.incomes.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Reset</a>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold text-white">
                        <i class="fa-solid fa-check me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Incomes Table Card -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Income Name</th>
                        <th>Type</th>
                        <th>Price / Unit</th>
                        <th>Pieces</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incomes as $inc)
                        <tr>
                            <td class="fw-bold text-dark">{{ \Carbon\Carbon::parse($inc->income_date)->format('M d, Y') }}</td>
                            <td class="fw-semibold text-dark">
                                {{ $inc->income_name }}
                                @if($inc->notes)
                                    <div class="small text-muted text-truncate" style="max-width: 220px;" title="{{ $inc->notes }}">
                                        {{ $inc->notes }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $inc->type === 'wholesale_selling' ? 'primary' : 'info' }}-subtle text-{{ $inc->type === 'wholesale_selling' ? 'primary' : 'dark' }} border">
                                    {{ $inc->type_label }}
                                </span>
                            </td>
                            <td class="fw-semibold">₹{{ number_format($inc->income_price, 2) }}</td>
                            <td>
                                @if($inc->type === 'wholesale_selling')
                                    <span class="badge bg-dark text-white rounded-pill px-2.5">{{ $inc->selling_pieces }} pcs</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="fw-bold text-success fs-6">₹{{ number_format($inc->total_income_amount, 2) }}</td>
                            <td>
                                <form action="{{ route('admin.incomes.toggle-status', $inc->id) }}" method="POST" class="d-inline mb-0">
                                    @csrf
                                    <button type="submit" class="btn btn-xs border-0 p-0 shadow-none" title="Click to toggle status">
                                        <span class="badge bg-{{ $inc->status === 'active' ? 'success' : 'secondary' }} rounded-pill px-2.5 py-1">
                                            <i class="fa-solid fa-{{ $inc->status === 'active' ? 'check-circle' : 'minus-circle' }} me-1"></i>
                                            {{ ucfirst($inc->status) }}
                                        </span>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" onclick="showIncomeDetail({{ $inc->id }})" title="View Income Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.incomes.edit', $inc->id) }}" class="btn btn-sm btn-outline-warning text-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Edit Income">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.incomes.destroy', $inc->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Are you sure you want to delete this income record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Delete Income">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-hand-holding-dollar fs-2 mb-2 d-block text-success"></i>
                                No income records found. Click "+ Add Income" to record a wholesale sale or additional income.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($incomes->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $incomes->links() }}
        </div>
    @endif
</div>

<!-- INCOME DETAILS MODAL -->
<div class="modal fade" id="incomeDetailModal" tabindex="-1" aria-labelledby="incomeDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold d-flex align-items-center gap-2" id="incomeDetailModalLabel">
                    <i class="fa-solid fa-hand-holding-dollar text-success"></i>
                    <span>Income Details</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="incomeDetailModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3 justify-content-between">
                <a id="modalEditIncomeBtn" href="#" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                    <i class="fa-solid fa-pen me-1"></i> Edit Income
                </a>
                <button type="button" class="btn btn-dark rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showIncomeDetail(incomeId) {
    const modal = new bootstrap.Modal(document.getElementById('incomeDetailModal'));
    const modalBody = document.getElementById('incomeDetailModalBody');
    
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    modal.show();

    fetch(`{{ url('/admin/incomes') }}/${incomeId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const inc = data.income;
            const editBtn = document.getElementById('modalEditIncomeBtn');
            if (editBtn) {
                editBtn.href = `{{ url('/admin/incomes') }}/${inc.id}/edit`;
            }

            modalBody.innerHTML = `
                <div class="row g-3 mb-3">
                    <div class="col-8">
                        <span class="text-muted small text-uppercase fw-bold d-block">Income Name</span>
                        <h5 class="fw-bold text-dark mb-0">${inc.income_name}</h5>
                    </div>
                    <div class="col-4 text-end">
                        <span class="text-muted small text-uppercase fw-bold d-block">Total Amount</span>
                        <h4 class="fw-bold text-success mb-0">₹${inc.total_income_amount}</h4>
                    </div>
                </div>

                <div class="row g-3 p-3 bg-light rounded-3 border mb-3">
                    <div class="col-6">
                        <span class="text-muted small d-block">Income Type</span>
                        <span class="badge bg-dark text-white px-2.5 py-1 rounded-pill">${inc.type_label}</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Transaction Date</span>
                        <span class="fw-bold text-dark">${inc.income_date}</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Price / Piece</span>
                        <span class="fw-bold text-dark">₹${inc.income_price}</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Selling Pieces</span>
                        <span class="fw-bold text-dark">${inc.selling_pieces ? inc.selling_pieces + ' pcs' : 'N/A'}</span>
                    </div>
                    <div class="col-12 border-top pt-2">
                        <span class="text-muted small d-block">Status</span>
                        <span class="badge bg-${inc.status === 'active' ? 'success' : 'secondary'} rounded-pill px-3 py-1">
                            ${inc.status === 'active' ? 'Active (Included in Profit & Loss)' : 'Inactive (Excluded)'}
                        </span>
                    </div>
                </div>

                <div class="mb-0">
                    <span class="fw-bold text-dark d-block mb-1">Notes / Remarks:</span>
                    <p class="text-secondary bg-white p-3 border rounded-3 mb-0">${inc.notes ? inc.notes : '<em>No notes specified.</em>'}</p>
                </div>
            `;
        } else {
            modalBody.innerHTML = `<div class="alert alert-danger mb-0">Failed to load income details.</div>`;
        }
    })
    .catch(err => {
        modalBody.innerHTML = `<div class="alert alert-danger mb-0">An error occurred while loading details.</div>`;
    });
}
</script>
@endsection
