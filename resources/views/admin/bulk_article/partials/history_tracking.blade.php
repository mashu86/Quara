@if($trackingNo)
    <div class="d-flex align-items-center gap-1 flex-wrap">
        <span class="th-track">{{ $trackingNo }}</span>
        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Copy tracking number" aria-label="Copy tracking number" onclick="copyTrackingNumber(this, @js($trackingNo))"><i class="fa-regular fa-copy"></i></button>
        <a href="{{ $indiaPostUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger py-0 px-1" title="Copy and open India Post tracking" aria-label="Open India Post tracking" onclick="copyTrackingNumber(null, @js($trackingNo))"><i class="fa-solid fa-arrow-up-right-from-square"></i> <span class="small">India Post</span></a>
    </div>
@else
    <span class="text-muted">—</span>
@endif
