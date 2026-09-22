@extends('layouts.app')

@section('title', 'Ladies Size Guide - ' . $siteName)
@section('meta_description', 'Ladieswear size guide for Korean tops, crop tops, normal tops, ladies shirts and overcoats. Find your Indian size using body measurements in inches.')

@section('content')
<div class="container py-3 py-md-5">
    <!-- Page Header & Download Action -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-3 mb-md-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Ladies Size Guide</h1>
            <p class="text-muted small mb-0">Measurement guide for Korean crop tops, tops, ladies shirts, and overcoats.</p>
        </div>
        <div class="dropdown align-self-stretch align-self-sm-auto">
            <button class="btn btn-dark rounded-pill px-4 py-2 w-100 dropdown-toggle font-semibold shadow-sm" type="button" id="sizeGuideDownload" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.82rem;">
                <i class="fa-solid fa-download me-2 text-warning"></i>Download Chart
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                <li>
                    <button class="dropdown-item py-2 small fw-semibold" type="button" onclick="exportSizeGuide('pdf')">
                        <i class="fa-solid fa-file-pdf text-danger me-2"></i>Download PDF
                    </button>
                </li>
                <li>
                    <button class="dropdown-item py-2 small fw-semibold" type="button" onclick="exportSizeGuide('image')">
                        <i class="fa-solid fa-file-image text-success me-2"></i>Download PNG Image
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden border">
        @include('partials.ladies_size_guide', ['guideId' => 'sizeGuideCard'])
    </div>
</div>

@include('partials.ladies_size_guide_export')
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function exportSizeGuide(type) {
    const element = document.getElementById('sizeGuideExport');
    const button = document.getElementById('sizeGuideDownload');
    const original = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Preparing...';
    const finish = () => { button.disabled = false; button.innerHTML = original; };
    if (type === 'image') {
        html2canvas(element, { scale: 2, useCORS: true, backgroundColor: '#ffffff' }).then(canvas => {
            const link = document.createElement('a'); link.download = '{{ Str::slug($siteName) }}-ladies-size-guide.png'; link.href = canvas.toDataURL('image/png'); link.click(); finish();
        }).catch(() => { alert('Could not create the image. Please try again.'); finish(); });
        return;
    }
    html2pdf().set({ margin: 0.25, filename: '{{ Str::slug($siteName) }}-ladies-size-guide.pdf', image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' }, jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' } }).from(element).save().then(finish).catch(() => { alert('Could not create the PDF. Please try again.'); finish(); });
}
</script>
@endsection
