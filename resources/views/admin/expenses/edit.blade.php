@extends('layouts.admin')

@section('title', 'Edit Expense - QUARA WALDROP Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .back-expense-btn {
            padding: 0.25rem 0.55rem !important;
            font-size: 0.82rem !important;
            border-radius: 8px !important;
        }
        .page-header-title {
            font-size: 1.15rem !important;
        }
        .page-header-subtitle {
            font-size: 0.72rem !important;
        }
        .card-body.p-4, .card-body.p-5 {
            padding: 1rem 0.85rem !important;
        }
        .form-label {
            font-size: 0.78rem !important;
            margin-bottom: 0.25rem !important;
        }
        .form-control, .form-select {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.65rem !important;
        }
        .submit-btn {
            padding: 0.65rem 1rem !important;
            font-size: 0.82rem !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 page-header-title">Edit Expense Record</h3>
        <p class="text-muted small mb-0 page-header-subtitle">Update expense details, amount, category, or add/remove bill receipt images.</p>
    </div>
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-dark rounded-3 px-2.5 px-md-3 py-1.5 py-md-2 fw-bold shadow-sm back-expense-btn" title="Back to Expenses">
        &larr;<span class="d-none d-md-inline"> Back to Expenses</span>
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('admin.expenses.update', $expense->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="remove_receipt_image" id="removeReceiptImageInput" value="0">
                    <div id="removedImagesInputsContainer"></div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Expense Name / Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Cardboard Courier Packing Boxes Batch #4" value="{{ old('title', $expense->title) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expense Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control rounded-3" placeholder="1500.00" value="{{ old('amount', $expense->amount) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control rounded-3" value="{{ old('expense_date', \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Category</label>
                        <select name="category" class="form-select rounded-3">
                            @php
                                $categories = [
                                    'Packaging & Materials',
                                    'Shipping & Courier Charges',
                                    'Inventory / Fabric Purchase',
                                    'Marketing & Advertisements',
                                    'Rent & Utilities',
                                    'Salaries / Workmanship',
                                    'Miscellaneous'
                                ];
                            @endphp
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category', $expense->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold d-block mb-1">
                            <i class="fa-solid fa-file-image text-warning me-1"></i> Receipt / Bill Images <span class="text-muted font-normal">(Optional - Select single or multiple)</span>
                        </label>
                        
                        <div class="p-3 bg-light rounded-3 border">
                            <div id="receiptImagesGrid" class="d-flex flex-wrap gap-3 align-items-center">
                                
                                <!-- Existing Uploaded Images Gallery -->
                                @foreach($expense->receipt_images as $idx => $imgPath)
                                    <div class="position-relative border rounded-3 p-1 bg-white shadow-sm" id="existingCard_{{ $idx }}" style="width: 120px; height: 120px;">
                                        <a href="{{ asset($imgPath) }}" target="_blank" title="View full image">
                                            <img src="{{ asset($imgPath) }}" class="w-100 h-100 rounded-2" style="object-fit: cover;">
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 translate-middle-y me-n1 mt-n1 shadow" 
                                                style="width: 24px; height: 24px; padding: 0; line-height: 24px; font-size: 11px;" 
                                                title="Remove this existing image" onclick="removeExistingImage('{{ $imgPath }}', {{ $idx }})">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                @endforeach

                                <!-- Hidden file inputs will be dynamically appended here -->
                                <div id="fileInputsContainer">
                                    <input type="file" name="receipt_images[]" id="receiptInput_0" class="d-none" accept="image/*" onchange="handleFileSelected(this, 0)">
                                </div>

                                <!-- Add New Image Card Button -->
                                <label for="receiptInput_0" id="addReceiptImageCard" class="btn btn-outline-warning rounded-3 d-flex flex-column align-items-center justify-content-center border-2 shadow-sm" style="width: 120px; height: 120px; border-style: dashed !important; background-color: #fff8e6;">
                                    <i class="fa-solid fa-cloud-arrow-up fs-3 text-warning mb-1"></i>
                                    <span class="fw-bold text-dark" style="font-size: 0.76rem;">+ Add Image</span>
                                </label>
                            </div>
                            <div class="form-text extra-small text-muted mt-2">
                                <i class="fa-solid fa-info-circle me-1"></i> Click <strong>+ Add Image</strong> to attach more photos or bills. Click the red <strong>X</strong> on any card to delete that specific image.
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Notes / Description</label>
                        <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="Additional details or invoice reference number...">{{ old('notes', $expense->notes) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-2.5 py-md-3 shadow-sm text-dark submit-btn" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> UPDATE EXPENSE RECORD
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let fileIndexCounter = 0;

function handleFileSelected(input, index) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            // Create Thumbnail Card
            const card = document.createElement('div');
            card.className = 'position-relative border rounded-3 p-1 bg-white shadow-sm';
            card.id = `receiptCard_${index}`;
            card.style.width = '120px';
            card.style.height = '120px';

            card.innerHTML = `
                <img src="${e.target.result}" class="w-100 h-100 rounded-2" style="object-fit: cover;">
                <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 translate-middle-y me-n1 mt-n1 shadow" 
                        style="width: 24px; height: 24px; padding: 0; line-height: 24px; font-size: 11px;" 
                        title="Remove this image" onclick="removeReceiptCard(${index})">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            const addCard = document.getElementById('addReceiptImageCard');
            document.getElementById('receiptImagesGrid').insertBefore(card, addCard);

            // Prepare next file input
            fileIndexCounter++;
            const newInput = document.createElement('input');
            newInput.type = 'file';
            newInput.name = 'receipt_images[]';
            newInput.id = `receiptInput_${fileIndexCounter}`;
            newInput.className = 'd-none';
            newInput.accept = 'image/*';
            newInput.onchange = function() { handleFileSelected(this, fileIndexCounter); };
            
            document.getElementById('fileInputsContainer').appendChild(newInput);
            addCard.setAttribute('for', `receiptInput_${fileIndexCounter}`);
        }
        reader.readAsDataURL(file);
    }
}

function removeReceiptCard(index) {
    const card = document.getElementById(`receiptCard_${index}`);
    const input = document.getElementById(`receiptInput_${index}`);
    if (card) card.remove();
    if (input) input.remove();
}

function removeExistingImage(imgPath, idx) {
    if (confirm('Are you sure you want to remove this receipt image?')) {
        const card = document.getElementById(`existingCard_${idx}`);
        if (card) card.remove();

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'removed_receipt_images[]';
        input.value = imgPath;
        document.getElementById('removedImagesInputsContainer').appendChild(input);
    }
}
</script>
@endsection
