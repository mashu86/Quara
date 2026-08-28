@extends('layouts.admin')

@section('title', 'Expenses Management - ' . $siteName . ' Admin')

@section('content')
<style>
    .expense-summary-card {
        height: 100%;
        overflow: hidden;
        position: relative;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .expense-summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08) !important;
    }
    .expense-summary-card .min-w-0 {
        min-width: 0;
    }
    .expense-summary-icon {
        align-items: center;
        border-radius: 14px;
        display: flex;
        flex: 0 0 46px;
        font-size: 1.15rem;
        height: 46px;
        justify-content: center;
        width: 46px;
    }
    .expense-summary-label {
        color: #6c757d;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .expense-summary-value {
        color: #151515;
        font-size: clamp(1.15rem, 2vw, 1.55rem);
        line-height: 1.15;
    }
    .filtered-expense-card {
        background: linear-gradient(135deg, #171719 0%, #2c2c30 100%);
        color: #fff;
    }
    .expense-action-cell {
        white-space: nowrap;
    }
    .expense-action-group {
        align-items: center;
        display: inline-flex;
        flex-wrap: nowrap;
        gap: 0.3rem;
    }
    .expense-action-group form {
        display: inline-flex !important;
        flex: 0 0 auto;
        margin: 0 !important;
    }
    @media (max-width: 576px) {
        .expense-title {
            font-size: 1.15rem !important;
        }
        .expense-subtitle {
            font-size: 0.72rem !important;
        }
        .pnl-btn, .add-expense-btn {
            border-radius: 8px !important;
            font-size: 0.78rem !important;
            padding: 0.4rem 0.65rem !important;
        }
        .search-trigger-btn {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.75rem !important;
        }
        #expenseFilterModal .modal-title {
            font-size: 0.9rem !important;
        }
        #expenseFilterModal .form-label {
            font-size: 0.76rem !important;
        }
        #expenseFilterModal .form-control {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.65rem !important;
        }
        #expenseFilterModal .btn {
            font-size: 0.78rem !important;
            padding: 0.35rem 0.8rem !important;
        }
        #expenseFilterModal .modal-body {
            padding: 1rem !important;
        }
        #expenseFilterModal .modal-footer {
            padding: 0.65rem 1rem !important;
        }
        .expense-summary-card .card-body {
            align-items: center !important;
            column-gap: 0.5rem !important;
            display: grid !important;
            grid-template-columns: 32px minmax(0, 1fr);
            grid-template-rows: auto auto;
            padding: 0.75rem !important;
            row-gap: 0.4rem !important;
        }
        .expense-summary-icon {
            border-radius: 9px;
            flex-basis: 32px;
            font-size: 0.78rem;
            grid-column: 1;
            grid-row: 1;
            height: 32px;
            width: 32px;
        }
        .expense-summary-card .min-w-0 {
            display: contents;
        }
        .expense-summary-label {
            font-size: 0.58rem;
            grid-column: 2;
            grid-row: 1;
            letter-spacing: 0.02em;
            margin-bottom: 0 !important;
            white-space: nowrap;
        }
        .expense-summary-value {
            font-size: 0.92rem;
            grid-column: 1 / -1;
            grid-row: 2;
            margin-top: 0.05rem;
        }
    }
    @media (max-width: 767.98px) {
        .expense-action-group .btn {
            align-items: center;
            border-radius: 50% !important;
            display: inline-flex;
            height: 30px;
            justify-content: center;
            margin: 0 !important;
            padding: 0 !important;
            width: 30px;
        }
        .expense-action-group .btn i {
            margin: 0 !important;
        }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 expense-title">General Expenses</h3>
        <p class="text-muted small mb-0 expense-subtitle">All expenses shown here are general business expenses.</p>
    </div>
    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
        <a href="{{ route('admin.reports.profit-loss') }}" class="btn btn-outline-dark rounded-pill px-3 py-1.5 pnl-btn shadow-sm w-100 w-sm-auto text-center fw-bold" title="Profit & Loss Report">
            <i class="fa-solid fa-chart-line text-warning me-1"></i> Profit & Loss Report
        </a>
        <a href="{{ route('admin.expenses.create') }}" class="btn btn-warning rounded-pill px-3 py-1.5 add-expense-btn shadow-sm text-dark w-100 w-sm-auto text-center fw-bold" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Record Expense">
            <i class="fa-solid fa-plus me-1"></i> Record Expense
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
    $activeExpenseFilterCount = (request()->filled('search') ? 1 : 0)
        + (request()->filled('start_date') ? 1 : 0)
        + (request()->filled('end_date') ? 1 : 0);
@endphp

<!-- General Expense Summary -->
<div class="row g-2 g-md-3 mb-3 mb-md-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm expense-summary-card">
            <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                <div class="expense-summary-icon bg-dark text-warning"><i class="fa-solid fa-wallet"></i></div>
                <div class="min-w-0">
                    <div class="expense-summary-label mb-1">Total Expenses</div>
                    <div class="expense-summary-value fw-bold text-truncate">&#8377;{{ number_format($totalExpenses, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm expense-summary-card">
            <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                <div class="expense-summary-icon bg-warning-subtle text-warning-emphasis"><i class="fa-solid fa-calendar-days"></i></div>
                <div class="min-w-0">
                    <div class="expense-summary-label mb-1">This Month</div>
                    <div class="expense-summary-value fw-bold text-truncate">&#8377;{{ number_format($thisMonthExpenses, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm expense-summary-card">
            <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                <div class="expense-summary-icon bg-primary-subtle text-primary"><i class="fa-solid fa-calendar-week"></i></div>
                <div class="min-w-0">
                    <div class="expense-summary-label mb-1">This Week</div>
                    <div class="expense-summary-value fw-bold text-truncate">&#8377;{{ number_format($thisWeekExpenses, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm expense-summary-card">
            <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                <div class="expense-summary-icon bg-success-subtle text-success"><i class="fa-solid fa-sun"></i></div>
                <div class="min-w-0">
                    <div class="expense-summary-label mb-1">Today</div>
                    <div class="expense-summary-value fw-bold text-truncate">&#8377;{{ number_format($todayExpenses, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile / Tablet Filter Button Bar (d-lg-none) -->
<div class="d-lg-none mb-3">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-dark rounded-pill px-3 py-2 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2 search-trigger-btn" data-bs-toggle="modal" data-bs-target="#expenseFilterModal">
            <i class="fa-solid fa-sliders text-warning"></i>
            <span class="fw-semibold">Filter Expenses</span>
            @if($activeExpenseFilterCount > 0)
                <span class="badge bg-warning text-dark rounded-pill">{{ $activeExpenseFilterCount }}</span>
            @endif
        </button>
        @if($activeExpenseFilterCount > 0)
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear Filters">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </div>
    @if($activeExpenseFilterCount > 0)
        <div class="card border-0 shadow-sm filtered-expense-card rounded-4 mt-2">
            <div class="card-body p-3 d-flex justify-content-between align-items-center gap-3">
                <div>
                    <div class="small text-white-50">Filtered Expenses</div>
                    <div class="small text-white-50">{{ $expenses->total() }} matching record(s)</div>
                </div>
                <div class="fw-bold fs-5 text-warning text-nowrap">&#8377;{{ number_format($filteredExpenseTotal, 2) }}</div>
            </div>
        </div>
    @endif
</div>

<!-- Desktop Filters (d-none d-lg-block) -->
<div class="row g-3 mb-4 d-none d-lg-flex align-items-stretch">
    <div class="{{ $activeExpenseFilterCount > 0 ? 'col-lg-9' : 'col-12' }}">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <form action="{{ route('admin.expenses.index') }}" method="GET" class="row g-2 align-items-center w-100 m-0">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control rounded-pill px-3" placeholder="Search expense name or category..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="start_date" class="form-control rounded-pill px-3" value="{{ request('start_date') }}" aria-label="Expense start date">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="end_date" class="form-control rounded-pill px-3" value="{{ request('end_date') }}" aria-label="Expense end date">
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-dark rounded-pill w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                        <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary rounded-pill" title="Clear filters"><i class="fa-solid fa-rotate-left"></i></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if($activeExpenseFilterCount > 0)
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm filtered-expense-card rounded-4 h-100">
                <div class="card-body p-3 d-flex flex-column justify-content-center">
                    <div class="small text-white-50 mb-1">Filtered Expenses</div>
                    <div class="fw-bold fs-4 text-warning text-truncate">&#8377;{{ number_format($filteredExpenseTotal, 2) }}</div>
                    <div class="small text-white-50">{{ $expenses->total() }} matching record(s)</div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Expense Mobile Filter Modal (d-lg-none) -->
<div class="modal fade d-lg-none" id="expenseFilterModal" tabindex="-1" aria-labelledby="expenseFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold" id="expenseFilterModalLabel">
                    <i class="fa-solid fa-sliders text-warning me-2"></i> Filter Expenses
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.expenses.index') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Search Keyword</label>
                        <input type="text" name="search" class="form-control rounded-3" placeholder="Search expense name or category..." value="{{ request('search') }}">
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
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Reset</a>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-check me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Expenses Table -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Expense Title</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Receipt Bill</th>
                        <th>Notes</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td class="fw-bold text-dark">{{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}</td>
                            <td class="fw-semibold">
                                {{ $expense->title ?? $expense->expense_name }}
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $expense->category ?? 'General' }}</span></td>
                            <td class="fw-bold text-danger">₹{{ number_format($expense->amount, 2) }}</td>
                            <td>
                                @if($expense->receipt_image)
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-primary fw-semibold" onclick="showExpenseDetail({{ $expense->id }})">
                                        <i class="fa-solid fa-file-invoice text-success me-1"></i> View Bill
                                    </button>
                                @else
                                    <span class="text-muted extra-small">No Receipt</span>
                                @endif
                            </td>
                            <td class="small text-muted text-truncate" style="max-width: 180px;">{{ $expense->notes ?? '-' }}</td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" onclick="showExpenseDetail({{ $expense->id }})" title="View Expense">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn btn-sm btn-outline-warning text-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Edit Expense">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Are you sure you want to delete this expense record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Delete Expense">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-wallet fs-2 mb-2 d-block text-warning"></i>
                                No expenses recorded yet. Click "+ Record Expense" to add your first expense.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($expenses->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $expenses->links() }}
        </div>
    @endif
</div>

<!-- EXPENSE DETAILS MODAL -->
<div class="modal fade" id="expenseDetailModal" tabindex="-1" aria-labelledby="expenseDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold d-flex align-items-center gap-2" id="expenseDetailModalLabel">
                    <i class="fa-solid fa-receipt text-warning"></i>
                    <span>Expense Details</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="expenseDetailModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3 justify-content-between">
                <a id="modalEditExpenseBtn" href="#" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                    <i class="fa-solid fa-pen me-1"></i> Edit Expense
                </a>
                <button type="button" class="btn btn-dark rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showExpenseDetail(expenseId) {
    const modal = new bootstrap.Modal(document.getElementById('expenseDetailModal'));
    const modalBody = document.getElementById('expenseDetailModalBody');
    
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-warning" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    modal.show();

    fetch(`{{ url('/admin/expenses') }}/${expenseId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const exp = data.expense;
            const editBtn = document.getElementById('modalEditExpenseBtn');
            if (editBtn) {
                editBtn.href = `{{ url('/admin/expenses') }}/${exp.id}/edit`;
            }
            let receiptHtml = '';
            const images = exp.receipt_image_urls || (exp.receipt_image_url ? [exp.receipt_image_url] : []);

            if (images.length > 0) {
                let imgCards = images.map((url, i) => `
                    <div class="col-6 col-md-4 text-center">
                        <div class="border rounded-3 p-2 bg-light shadow-sm position-relative">
                            <a href="${url}" target="_blank" title="Click to view full image">
                                <img src="${url}" class="img-fluid rounded-2 mb-2" style="height: 140px; width: 100%; object-fit: cover;">
                            </a>
                            <a href="${url}" target="_blank" class="btn btn-xs btn-outline-dark rounded-pill px-2 py-0.5 extra-small">
                                <i class="fa-solid fa-up-right-from-square me-1"></i> Full Image ${images.length > 1 ? '#' + (i + 1) : ''}
                            </a>
                        </div>
                    </div>
                `).join('');

                receiptHtml = `
                    <div class="mt-4 border-top pt-3">
                        <span class="fw-bold text-dark d-block mb-3"><i class="fa-solid fa-images text-warning me-1"></i> Uploaded Receipt Documents (${images.length}):</span>
                        <div class="row g-3">
                            ${imgCards}
                        </div>
                    </div>
                `;
            } else {
                receiptHtml = `
                    <div class="mt-3 border-top pt-3">
                        <span class="text-muted extra-small"><i class="fa-solid fa-info-circle me-1"></i> No receipt or bill image uploaded for this expense.</span>
                    </div>
                `;
            }

            modalBody.innerHTML = `
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <span class="text-muted small text-uppercase fw-bold d-block">Expense Title</span>
                        <h4 class="fw-bold text-dark mb-0">${exp.title}</h4>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="text-muted small text-uppercase fw-bold d-block">Expense Amount</span>
                        <h3 class="fw-bold text-danger mb-0">₹${exp.amount}</h3>
                    </div>
                </div>

                <div class="row g-3 p-3 bg-light rounded-3 border mb-3">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Category</span>
                        <span class="badge bg-dark text-white px-3 py-1.5 rounded-pill">${exp.category || 'General'}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Expense Date</span>
                        <span class="fw-bold text-dark">${exp.expense_date}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <span class="fw-bold text-dark d-block mb-1">Notes / Description:</span>
                    <p class="text-secondary bg-white p-3 border rounded-3 mb-0">${exp.notes ? exp.notes : '<em>No notes specified.</em>'}</p>
                </div>

                ${receiptHtml}
            `;
        } else {
            modalBody.innerHTML = `<div class="alert alert-danger mb-0">Failed to load expense details.</div>`;
        }
    })
    .catch(err => {
        modalBody.innerHTML = `<div class="alert alert-danger mb-0">An error occurred while loading details.</div>`;
    });
}
</script>
@endsection
