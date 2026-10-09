<div class="cart-summary-card bg-white p-3 p-md-4 rounded-4 shadow-sm border sticky-top" style="top: 90px;">
    <h6 class="font-serif fw-bold mb-3 pb-2 border-bottom">ORDER SUMMARY</h6>

    <div class="d-flex justify-content-between mb-2 small">
        <span class="text-muted">Bag Subtotal</span>
        <span class="fw-semibold">&#8377;{{ number_format($summary['subtotal'], 2) }}</span>
    </div>

    <div class="d-flex justify-content-between mb-2 small text-success {{ $summary['discount'] > 0 ? '' : 'd-none' }}">
        <span>Total Discount</span>
        <span>-&#8377;{{ number_format($summary['discount'], 2) }}</span>
    </div>

    <div class="d-flex justify-content-between mb-2 small">
        <span class="text-muted">Shipping Charge</span>
        @if($summary['shipping'] > 0)
            <span class="fw-bold text-dark">&#8377;{{ number_format($summary['shipping'], 2) }}</span>
        @else
            <span class="text-success fw-semibold">FREE</span>
        @endif
    </div>

    <div class="d-flex justify-content-between mb-2 small text-muted {{ !empty($summary['rounding_adjustment']) && $summary['rounding_adjustment'] > 0 ? '' : 'd-none' }}">
        <span>Rounded Paisa (Round Off)</span>
        <span class="fw-semibold text-primary">+&#8377;{{ number_format($summary['rounding_adjustment'] ?? 0, 2) }}</span>
    </div>

    <hr class="my-2">
    <div class="d-flex justify-content-between mb-3 fs-6 fw-bold">
        <span>Grand Total</span>
        <span class="text-gold">&#8377;{{ number_format($summary['grand_total'], 2) }}</span>
    </div>

    @if(!$stockValidation['valid'])
        <div class="alert alert-warning small py-2 mb-2">
            @foreach($stockValidation['errors'] as $validationError)
                <div>{{ $validationError }}</div>
            @endforeach
        </div>
    @endif
    <a href="{{ $stockValidation['valid'] ? route('checkout.index') : '#' }}" class="btn btn-qw-gold btn-sm w-100 rounded-pill shadow-sm py-1-5 fw-bold {{ !$stockValidation['valid'] ? 'disabled' : '' }}" style="font-size: 0.78rem; padding-top: 6px; padding-bottom: 6px;" aria-disabled="{{ $stockValidation['valid'] ? 'false' : 'true' }}" tabindex="{{ $stockValidation['valid'] ? '0' : '-1' }}">
        PROCEED TO CHECKOUT <i class="fa-solid fa-arrow-right ms-1"></i>
    </a>
</div>
