@extends('layouts.admin')

@section('title', 'Edit Order Adjustment #' . $operation->id . ' - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .op-form-title { font-size: 1.15rem !important; }
        .op-form-subtitle { font-size: 0.72rem !important; }
        .op-back-btn { font-size: 0.76rem !important; padding: 0.35rem 0.6rem !important; border-radius: 8px !important; }
        .card-body.p-4 { padding: 1rem 0.85rem !important; }
        .card-body h5 { font-size: 0.92rem !important; margin-bottom: 0.75rem !important; }
        .card-body h6 { font-size: 0.84rem !important; }
        .form-label { font-size: 0.76rem !important; margin-bottom: 0.25rem !important; }
        .form-control, .form-select { font-size: 0.78rem !important; padding: 0.4rem 0.65rem !important; }
        .op-save-btn { padding: 0.75rem 1rem !important; font-size: 0.85rem !important; }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 op-form-title">Edit Order Adjustment #{{ $operation->id }}</h3>
        <p class="text-muted small mb-0 op-form-subtitle">Update inventory condition or refund amount for Order #{{ $operation->order->order_number }}</p>
    </div>
    <a href="{{ route('admin.order-operations.create', $operation->order_id) }}" class="btn btn-outline-dark rounded-3 px-3 py-1.5 fw-bold shadow-sm op-back-btn">
        &larr; Back<span class="d-none d-md-inline"> to Order</span>
    </a>
</div>

<form action="{{ route('admin.order-operations.update', $operation->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row g-3 g-md-4">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            
            <!-- 1. Fixed Product & Action Context -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4 bg-light border-start border-4 border-warning">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-dark">
                        <i class="fa-solid fa-box text-warning me-2"></i> Adjustment Context
                    </h5>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label text-muted small mb-0">Action Recorded:</label>
                            <div class="fw-bold text-dark fs-6">{{ $operation->operation_type_label }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-muted small mb-0">Product & Size:</label>
                            <div class="fw-bold text-dark fs-6">
                                {{ $operation->orderItem ? $operation->orderItem->product_name : ($operation->product ? $operation->product->name : 'N/A') }}
                                @if($operation->orderItem)
                                    <span class="badge bg-dark ms-1">{{ $operation->orderItem->size }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-muted small mb-0">Quantity Affected:</label>
                            <div class="fw-bold text-dark fs-6">{{ $operation->quantity }} pcs</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-muted small mb-0">Recorded Date:</label>
                            <div class="fw-bold text-dark fs-6">{{ $operation->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Inventory Condition -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-2 border-bottom pb-2 text-dark">
                        <i class="fa-solid fa-warehouse text-warning me-2"></i> Inventory Condition
                    </h5>
                    <p class="text-muted small mb-3">Choose whether the product should be added back into live website stock or kept frozen.</p>

                    @php
                        $currentCondition = old('inventory_condition', $operation->inventory_condition ?? ($operation->is_product_restored ? 'return_to_stock' : 'do_not_restock'));
                    @endphp

                    <div class="d-flex flex-column gap-2.5">
                        <div class="form-check p-3 border rounded-3 bg-light cursor-pointer">
                            <input class="form-check-input ms-0 me-2" type="radio" name="inventory_condition" id="editInvReturn" value="return_to_stock" {{ $currentCondition === 'return_to_stock' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark small cursor-pointer" for="editInvReturn">
                                <i class="fa-solid fa-arrow-rotate-left text-success me-1"></i> Return to Stock & Website
                                <span class="d-block text-muted fw-normal" style="font-size: 0.76rem;">Restores product stock to inventory so it becomes purchasable again on the website.</span>
                            </label>
                        </div>
                        <div class="form-check p-3 border rounded-3 bg-light cursor-pointer">
                            <input class="form-check-input ms-0 me-2" type="radio" name="inventory_condition" id="editInvDoNotRestock" value="do_not_restock" {{ $currentCondition === 'do_not_restock' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark small cursor-pointer" for="editInvDoNotRestock">
                                <i class="fa-solid fa-snowflake text-danger me-1"></i> Do Not Restock / Freeze Item
                                <span class="d-block text-muted fw-normal" style="font-size: 0.76rem;">Product is damaged, defective, or kept by customer. Stock is NOT restored to inventory.</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Refund Details -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-2 border-bottom pb-2 text-dark">
                        <i class="fa-solid fa-hand-holding-dollar text-warning me-2"></i> Refund Details
                    </h5>

                    @php
                        $isRefunded = old('refund_option', $operation->is_money_refunded ? 'refund' : 'no_refund');
                        $refundAmount = old('refund_amount', number_format($operation->total_refund_amount, 2, '.', ''));
                    @endphp

                    <div class="d-flex flex-column flex-sm-row gap-3 mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="refund_option" id="editRefundNo" value="no_refund" {{ $isRefunded === 'no_refund' ? 'checked' : '' }} onchange="toggleEditRefundBox()">
                            <label class="form-check-label fw-bold text-dark small cursor-pointer" for="editRefundNo">
                                No Refund
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="refund_option" id="editRefundYes" value="refund" {{ $isRefunded === 'refund' ? 'checked' : '' }} onchange="toggleEditRefundBox()">
                            <label class="form-check-label fw-bold text-dark small cursor-pointer" for="editRefundYes">
                                Refund Issued to Customer
                            </label>
                        </div>
                    </div>

                    <div id="editRefundAmountBox" class="p-3 bg-light rounded-3 border {{ $isRefunded === 'refund' ? '' : 'd-none' }}">
                        <label class="form-label fw-bold small">Refund Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="refund_amount" id="editRefundAmountInput" class="form-control rounded-3" value="{{ $refundAmount }}" placeholder="Enter refund amount...">
                        <span class="text-muted d-block mt-1" style="font-size: 0.75rem;">This amount will deduct from sales revenue in Profit & Loss calculations.</span>
                    </div>
                </div>
            </div>

            <!-- 4. Notes & Save Button -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Adjustment Notes</label>
                        <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="Enter reason or additional details regarding this adjustment...">{{ old('notes', $operation->notes) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning rounded-pill fw-bold flex-grow-1 py-3 shadow-sm text-dark op-save-btn" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                            <i class="fa-solid fa-floppy-disk me-1"></i> UPDATE ADJUSTMENT RECORD
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Side: Order Summary -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-receipt text-warning me-2"></i> Order Details</h6>
                </div>
                <div class="card-body p-3 style-small" style="font-size: 0.8rem;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Order Number:</span>
                        <span class="fw-bold text-warning">{{ $operation->order->order_number }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Customer Name:</span>
                        <span class="fw-bold text-dark">{{ $operation->order->customer_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Phone Number:</span>
                        <span class="fw-bold text-dark">{{ $operation->order->customer_phone }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Net Grand Total:</span>
                        <span class="fw-bold text-dark">₹{{ number_format($operation->order->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    function toggleEditRefundBox() {
        const isRefund = document.getElementById('editRefundYes').checked;
        const box = document.getElementById('editRefundAmountBox');
        if (isRefund) {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleEditRefundBox();
    });
</script>
@endsection
