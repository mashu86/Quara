@extends('layouts.admin')

@section('title', 'Edit Salary Entry - ' . $siteName)

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Salary Entry</h4>
            <p class="text-muted small mb-0">Modify salary record details for {{ $salary->employee->name ?? 'Employee' }}.</p>
        </div>
        <a href="{{ route('admin.salary-master.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Salary Master
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 max-w-xl mx-auto">
        <div class="card-body p-4">
            <form action="{{ route('admin.salary-master.update', $salary->id) }}" method="POST" onsubmit="return handleAdminFormSubmit(this);">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Employee</label>
                    <input type="text" class="form-control bg-light" value="{{ $salary->employee->name ?? 'Unknown' }}" readonly disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Work / Salary Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $salary->date->format('Y-m-d')) }}" required>
                    @error('date') <div class="invalid-feedback">{{ $message }}</div> @errorEnd
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Salary Amount (₹) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">₹</span>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $salary->amount) }}" required>
                    </div>
                    @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @errorEnd
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Payment Status <span class="text-danger">*</span></label>
                    <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                        <option value="paid" {{ old('payment_status', $salary->payment_status) === 'paid' ? 'selected' : '' }}>Paid (Cash expense will be created/updated)</option>
                        <option value="unpaid" {{ old('payment_status', $salary->payment_status) === 'unpaid' ? 'selected' : '' }}>Unpaid (No cash expense)</option>
                    </select>
                    @error('payment_status') <div class="invalid-feedback">{{ $message }}</div> @errorEnd
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Notes / Remarks</label>
                    <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror" value="{{ old('notes', $salary->notes) }}">
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @errorEnd
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('admin.salary-master.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-dark rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-check me-1 text-warning"></i> Update Salary Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
