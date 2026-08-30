@extends('layouts.admin')

@section('title', 'Add Income - ' . $siteName . ' Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Add New Income</h3>
        <p class="text-muted small mb-0">Record wholesale sales or additional income for financial tracking.</p>
    </div>
    <a href="{{ route('admin.incomes.index') }}" class="btn btn-outline-dark rounded-pill px-3 py-1.5 fw-bold">
        &larr; Back to Incomes
    </a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('admin.incomes.store') }}" method="POST" onsubmit="return handleAdminFormSubmit(this);">
            @csrf

            <div class="row g-3 mb-3">
                <!-- Income Name -->
                <div class="col-md-8">
                    <label for="income_name" class="form-label fw-bold">Income Name <span class="text-danger">*</span></label>
                    <input type="text" name="income_name" id="income_name" class="form-control rounded-3 @error('income_name') is-invalid @enderror" value="{{ old('income_name') }}" placeholder="e.g. Wholesale Customer Order, Bulk Fabric Sale, Scrap Income" required>
                    @error('income_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Transaction Date -->
                <div class="col-md-4">
                    <label for="income_date" class="form-label fw-bold">Transaction Date <span class="text-danger">*</span></label>
                    <input type="date" name="income_date" id="income_date" class="form-control rounded-3 @error('income_date') is-invalid @enderror" value="{{ old('income_date', date('Y-m-d')) }}" required>
                    @error('income_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <!-- Income Type -->
                <div class="col-md-4">
                    <label for="type" class="form-label fw-bold">Income Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select rounded-3 @error('type') is-invalid @enderror" required onchange="toggleWholesaleFields();">
                        <option value="wholesale_selling" {{ old('type', 'wholesale_selling') === 'wholesale_selling' ? 'selected' : '' }}>Wholesale Selling</option>
                        <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>Other Income</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Income Price / Unit Price -->
                <div class="col-md-4">
                    <label for="income_price" class="form-label fw-bold">Income Price / Unit Rate (&#8377;) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">&#8377;</span>
                        <input type="number" step="0.01" name="income_price" id="income_price" class="form-control border-start-0 @error('income_price') is-invalid @enderror" value="{{ old('income_price') }}" placeholder="0.00" required oninput="calculateTotalIncome();">
                    </div>
                    @error('income_price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Total Selling Pieces (Wholesale) -->
                <div class="col-md-4" id="pieces_wrapper">
                    <label for="selling_pieces" class="form-label fw-bold">Total Selling Piece <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="selling_pieces" id="selling_pieces" class="form-control border-end-0 @error('selling_pieces') is-invalid @enderror" value="{{ old('selling_pieces', 1) }}" min="1" placeholder="1" oninput="calculateTotalIncome();">
                        <span class="input-group-text bg-light border-start-0">pcs</span>
                    </div>
                    @error('selling_pieces')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-4">
                <!-- Total Income Amount -->
                <div class="col-md-6">
                    <label for="total_income_amount" class="form-label fw-bold text-success">Total Income Amount (&#8377;) <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-success-subtle text-success border-end-0 fw-bold">&#8377;</span>
                        <input type="number" step="0.01" name="total_income_amount" id="total_income_amount" class="form-control border-start-0 fw-bold text-success @error('total_income_amount') is-invalid @enderror" value="{{ old('total_income_amount') }}" placeholder="0.00" required>
                    </div>
                    <small class="text-muted">Automatically calculated based on price & pieces, but can be manually edited if required.</small>
                    @error('total_income_amount')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="col-md-6">
                    <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select form-select-lg rounded-3 @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active (Included in Profit & Loss)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive (Excluded from Profit & Loss)</option>
                    </select>
                    <small class="text-muted">Active incomes directly add to business revenue in the Profit & Loss statement.</small>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Notes / Remarks -->
            <div class="mb-4">
                <label for="notes" class="form-label fw-bold">Notes / Customer Information</label>
                <textarea name="notes" id="notes" class="form-control rounded-3 @error('notes') is-invalid @enderror" rows="3" placeholder="Enter customer name, enquiry details, payment reference or notes (optional)...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                <a href="{{ route('admin.incomes.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold text-white">
                    <i class="fa-solid fa-check me-1"></i> Save Income Record
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleWholesaleFields() {
    const type = document.getElementById('type').value;
    const piecesWrapper = document.getElementById('pieces_wrapper');
    const sellingPieces = document.getElementById('selling_pieces');

    if (type === 'wholesale_selling') {
        piecesWrapper.style.display = 'block';
        if (!sellingPieces.value || parseInt(sellingPieces.value) < 1) {
            sellingPieces.value = 1;
        }
    } else {
        piecesWrapper.style.display = 'none';
        sellingPieces.value = 1;
    }
    calculateTotalIncome();
}

function calculateTotalIncome() {
    const price = parseFloat(document.getElementById('income_price').value) || 0;
    const type = document.getElementById('type').value;
    const pieces = type === 'wholesale_selling' ? (parseInt(document.getElementById('selling_pieces').value) || 1) : 1;
    
    const total = price * pieces;
    document.getElementById('total_income_amount').value = total.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    toggleWholesaleFields();
});
</script>
@endsection
