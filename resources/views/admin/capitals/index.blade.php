@extends('layouts.admin')

@section('title', 'Capital Investment Management - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .page-header-title { font-size: 1.15rem !important; }
        .page-header-subtitle { font-size: 0.72rem !important; }
        .add-btn { font-size: 0.8rem !important; padding: 0.4rem 0.8rem !important; }
        .stat-card-title { font-size: 0.75rem !important; }
        .stat-card-val { font-size: 1.2rem !important; }
        .filter-card-body { padding: 0.85rem !important; }
        .filter-card-body .form-label { font-size: 0.76rem !important; }
        .filter-card-body .form-control { font-size: 0.78rem !important; padding: 0.4rem 0.65rem !important; }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 page-header-title">Capital Investment Management</h3>
        <p class="text-muted small mb-0 page-header-subtitle">Record and manage capital investments date-wise to track total equity and cash position.</p>
    </div>
    <div>
        <button type="button" class="btn btn-warning rounded-pill px-3 py-2 fw-bold shadow-sm add-btn" data-bs-toggle="modal" data-bs-target="#addCapitalModal" style="background-color: var(--qw-gold); border-color: var(--qw-gold); color: #000;">
            <i class="fa-solid fa-plus me-1"></i> Add Capital Investment
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-4">
        <div class="card border-0 rounded-4 shadow-sm p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="fa-solid fa-building-columns fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block stat-card-title">Total Capital Investment</span>
                    <h3 class="fw-bold text-dark mb-0 stat-card-val">₹{{ number_format($totalCapital, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4">
        <div class="card border-0 rounded-4 shadow-sm p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="fa-solid fa-receipt fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block stat-card-title">Total Investment Entries</span>
                    <h3 class="fw-bold text-dark mb-0 stat-card-val">{{ $capitalCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4">
        <div class="card border-0 rounded-4 shadow-sm p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="fa-solid fa-filter fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block stat-card-title">Filtered Total</span>
                    <h3 class="fw-bold text-primary mb-0 stat-card-val">₹{{ number_format($filteredCapitalTotal, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-3 filter-card-body">
        <form action="{{ route('admin.capitals.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold mb-1">Search Capital / Note</label>
                <input type="text" name="search" class="form-control rounded-pill px-3" placeholder="Search by name or note..." value="{{ request('search') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold mb-1">Start Date</label>
                <input type="date" name="start_date" class="form-control rounded-pill px-3" value="{{ request('start_date') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold mb-1">End Date</label>
                <input type="date" name="end_date" class="form-control rounded-pill px-3" value="{{ request('end_date') }}">
            </div>
            <div class="col-12 col-md-2 mt-2 mt-md-0">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-dark rounded-pill w-100 fw-bold">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'start_date', 'end_date']))
                        <a href="{{ route('admin.capitals.index') }}" class="btn btn-outline-secondary rounded-pill" title="Reset Filters">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data Table Card -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-coins text-warning me-2"></i> Capital Investment Records
        </h5>
        <span class="badge bg-dark rounded-pill px-3 py-2">{{ $capitals->total() }} Records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Capital Name / Investor</th>
                        <th>Amount</th>
                        <th>Notes</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($capitals as $capital)
                        <tr>
                            <td class="ps-3 fw-semibold text-dark">
                                <i class="fa-regular fa-calendar me-1.5 text-muted"></i>
                                {{ \Carbon\Carbon::parse($capital->capital_date)->format('M d, Y') }}
                            </td>
                            <td>
                                <span class="fw-bold text-dark">{{ $capital->name }}</span>
                            </td>
                            <td class="fw-bold text-success fs-6">
                                ₹{{ number_format($capital->amount, 2) }}
                            </td>
                            <td class="small text-muted" style="max-width: 250px;">
                                {{ $capital->notes ?: '-' }}
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1" data-bs-toggle="modal" data-bs-target="#editCapitalModal_{{ $capital->id }}">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </button>
                                <form action="{{ route('admin.capitals.destroy', $capital->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this capital investment record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                        <i class="fa-solid fa-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Capital Modal -->
                        <div class="modal fade" id="editCapitalModal_{{ $capital->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                                        <h5 class="modal-title font-serif fw-bold">
                                            <i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Capital Investment
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('admin.capitals.update', $capital->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body p-4 text-start">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-dark">Capital Name / Investor <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $capital->name) }}" required placeholder="e.g. Initial Partner Capital / Self Investment">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-dark">Investment Date <span class="text-danger">*</span></label>
                                                <input type="date" name="capital_date" class="form-control rounded-3" value="{{ old('capital_date', \Carbon\Carbon::parse($capital->capital_date)->format('Y-m-d')) }}" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-dark">Investment Amount (₹) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light fw-bold">₹</span>
                                                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount', $capital->amount) }}" required placeholder="0.00">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-dark">Notes / Details (Optional)</label>
                                                <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="Additional details regarding this capital deposit...">{{ old('notes', $capital->notes) }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                                                <i class="fa-solid fa-check me-1"></i> Update Capital
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-building-columns fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No capital investment records found. Click <strong>"Add Capital Investment"</strong> to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($capitals->hasPages())
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $capitals->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add Capital Modal -->
<div class="modal fade" id="addCapitalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold">
                    <i class="fa-solid fa-plus-circle text-warning me-2"></i> Add Capital Investment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.capitals.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 text-start">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Capital Name / Investor <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" value="{{ old('name') }}" required placeholder="e.g. Initial Owner Capital / Business Fund">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Investment Date <span class="text-danger">*</span></label>
                        <input type="date" name="capital_date" class="form-control rounded-3" value="{{ old('capital_date', \Illuminate\Support\Carbon::now('Asia/Kolkata')->toDateString()) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Investment Amount (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">₹</span>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required placeholder="0.00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Notes / Details (Optional)</label>
                        <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="Additional notes about bank transaction or source of capital...">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-plus me-1"></i> Save Capital Investment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
