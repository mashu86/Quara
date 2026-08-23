@extends('layouts.admin')

@section('title', 'Expenses Management - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Business Expense Management</h3>
        <p class="text-muted small mb-0">Record all business expenditures, operating costs & material expenses.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.profit-loss') }}" class="btn btn-outline-dark rounded-pill font-bold px-3">
            <i class="fa-solid fa-chart-line text-warning me-1"></i> Profit & Loss Report
        </a>
        <a href="{{ route('admin.expenses.create') }}" class="btn btn-warning rounded-pill fw-bold px-4">
            <i class="fa-solid fa-plus me-1"></i> + Record Expense
        </a>
    </div>
</div>

@php
    $activeExpenseFilterCount = (request()->filled('search') ? 1 : 0)
        + (request()->filled('start_date') ? 1 : 0)
        + (request()->filled('end_date') ? 1 : 0);
@endphp

<!-- Mobile / Tablet Filter Button Bar (d-lg-none) -->
<div class="d-lg-none mb-3">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-dark rounded-pill px-3 py-2 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#expenseFilterModal">
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
</div>

<!-- Desktop Filters (d-none d-lg-block) -->
<div class="card border-0 rounded-4 shadow-sm mb-4 d-none d-lg-block">
    <div class="card-body p-3">
        <form action="{{ route('admin.expenses.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control rounded-pill px-3" placeholder="Search expense name or category..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control rounded-pill px-3" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control rounded-pill px-3" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-dark rounded-pill w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
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
<div class="card border-0 rounded-4 shadow-sm">
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
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td class="fw-bold text-dark">{{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}</td>
                            <td class="fw-semibold">{{ $expense->title ?? $expense->expense_name }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $expense->category ?? 'General' }}</span></td>
                            <td class="fw-bold text-danger">₹{{ number_format($expense->amount, 2) }}</td>
                            <td class="small text-muted">{{ $expense->notes ?? '-' }}</td>
                            <td class="text-end">
                                <form action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this expense record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
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
@endsection
