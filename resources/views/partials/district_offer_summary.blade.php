@if((float) $order->district_offer_discount > 0)
<div class="d-flex justify-content-between gap-2 text-success small mb-2">
    <span>District Wise Special Offer <span class="text-muted">({{ $order->district_offer_snapshot['district'] ?? $order->district }})</span></span>
    <strong>-₹{{ number_format($order->district_offer_discount, 2) }}</strong>
</div>
@endif
