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

<!-- Filters -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
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
                            <td class="fw-semibold">{{ $expense->expense_name }}</td>
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
