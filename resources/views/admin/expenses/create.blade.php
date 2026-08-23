@extends('layouts.admin')

@section('title', 'Record Expense - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Record New Expense</h3>
        <p class="text-muted small mb-0">Add operational costs, packing materials, logistics, or inventory purchase costs.</p>
    </div>
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-3">&larr; Back to Expenses</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('admin.expenses.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-bold">Expense Name / Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Cardboard Courier Packing Boxes Batch #4" value="{{ old('title', old('expense_name')) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expense Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control rounded-3" placeholder="1500.00" value="{{ old('amount') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control rounded-3" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Category</label>
                        <select name="category" class="form-select rounded-3">
                            <option value="Packaging & Materials">Packaging & Materials</option>
                            <option value="Shipping & Courier Charges">Shipping & Courier Charges</option>
                            <option value="Inventory / Fabric Purchase">Inventory / Fabric Purchase</option>
                            <option value="Marketing & Advertisements">Marketing & Advertisements</option>
                            <option value="Rent & Utilities">Rent & Utilities</option>
                            <option value="Salaries / Workmanship">Salaries / Workmanship</option>
                            <option value="Miscellaneous">Miscellaneous</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Notes / Description</label>
                        <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="Additional details or invoice reference number...">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-3 shadow-sm">SAVE EXPENSE RECORD</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
