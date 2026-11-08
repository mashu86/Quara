@if(!empty($noteCategory?->notes))
    <div class="alert alert-success d-flex align-items-start gap-2 rounded-4 border-0 shadow-sm mb-3 py-2 px-3" role="note">
        <i class="fa-solid fa-circle-info mt-1"></i>
        <div class="small fw-semibold" style="white-space: pre-line;">{{ $noteCategory->notes }}</div>
    </div>
@endif
