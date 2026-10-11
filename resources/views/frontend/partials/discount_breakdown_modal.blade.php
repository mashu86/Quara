<div class="modal fade" id="discountBreakdownModal" data-bs-backdrop="false" tabindex="-1" aria-labelledby="discountBreakdownTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="discountBreakdownTitle">Discount details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @forelse($discountBreakdown as $entry)
                    <div class="d-flex justify-content-between align-items-start gap-3 py-2 border-bottom">
                        <div>
                            <div class="fw-semibold">{{ $entry['title'] }}</div>
                            <div class="small text-muted">
                                {{ $entry['detail'] }}
                                @if($entry['detail'] === 'Combo Offer')
                                    · {{ $entry['quantity'] }} eligible item(s)
                                @else
                                    · {{ $entry['quantity'] }} item(s)
                                @endif
                            </div>
                        </div>
                        <strong class="text-success text-nowrap">−₹{{ number_format($entry['amount'], 2) }}</strong>
                    </div>
                @empty
                    <p class="text-muted mb-0">No product discounts apply to these items.</p>
                @endforelse
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <strong>Total Discount</strong>
                <strong class="text-success">−₹{{ number_format($summary['discount'], 2) }}</strong>
            </div>
        </div>
    </div>
</div>
