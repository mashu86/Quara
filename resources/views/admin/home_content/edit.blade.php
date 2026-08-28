@extends('layouts.admin')

@section('title', 'Edit Home Content - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .back-hc-btn {
            padding: 0.25rem 0.55rem !important;
            font-size: 0.82rem !important;
            border-radius: 8px !important;
        }
        .hc-header-title {
            font-size: 1.15rem !important;
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
        .toolbox-card .btn-sm {
            font-size: 0.72rem !important;
            padding: 0.2rem 0.45rem !important;
        }
        .submit-btn {
            padding: 0.55rem 1rem !important;
            font-size: 0.82rem !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-start align-items-md-center gap-2 mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-0 hc-header-title">Edit Home Content</h3>
        <span class="text-muted small text-truncate d-block me-2" style="max-width: 240px;">{{ $homeContent->title }}</span>
    </div>
    <a href="{{ route('admin.home-content.index') }}" class="btn btn-outline-dark rounded-3 px-2.5 px-md-3 py-1 py-md-1.5 back-hc-btn shadow-sm text-nowrap" title="Back to Home Content Master">
        &larr;<span class="d-none d-md-inline"> Back</span>
    </a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-3.5 p-md-5">
        <form action="{{ route('admin.home-content.update', $homeContent->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3 g-md-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Block Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control rounded-3" value="{{ old('title', $homeContent->title) }}" required>
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold">Image Position <span class="text-danger">*</span></label>
                    <select name="image_position" class="form-select rounded-3" required>
                        <option value="top" {{ old('image_position', $homeContent->image_position) === 'top' ? 'selected' : '' }}>Top (Above HTML)</option>
                        <option value="middle" {{ old('image_position', $homeContent->image_position) === 'middle' ? 'selected' : '' }}>Middle</option>
                        <option value="bottom" {{ old('image_position', $homeContent->image_position) === 'bottom' ? 'selected' : '' }}>Bottom (Below HTML)</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="active" {{ old('status', $homeContent->status) === 'active' ? 'selected' : '' }}>Active (Visible)</option>
                        <option value="inactive" {{ old('status', $homeContent->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Banner / Featured Image</label>
                    @if($homeContent->image_url)
                        <div class="mb-2">
                            <img src="{{ $homeContent->image_url }}" alt="Current Banner" class="rounded-3 border" style="height: 90px; object-fit: cover;">
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control rounded-3" accept="image/*">
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0">HTML Main Content <span class="text-danger">*</span></label>
                        <span class="badge bg-dark extra-small"><i class="fa-solid fa-code me-1"></i> HTML Editor Toolbox</span>
                    </div>

                    <!-- Toolbox Controls -->
                    <div class="card bg-light border-0 rounded-3 mb-2 shadow-sm toolbox-card">
                        <div class="card-body p-2 d-flex flex-wrap align-items-center gap-1">
                            <span class="extra-small fw-bold text-muted me-1"><i class="fa-solid fa-toolbox text-warning me-1"></i> Toolbox:</span>
                            <button type="button" class="btn btn-sm btn-white border shadow-sm fw-bold" onclick="insertHTML('<strong>', '</strong>')" title="Bold"><b>B</b></button>
                            <button type="button" class="btn btn-sm btn-white border shadow-sm" onclick="insertHTML('<em>', '</em>')" title="Italic"><i>I</i></button>
                            <button type="button" class="btn btn-sm btn-white border shadow-sm fw-bold" onclick="insertHTML('<h2 class=\'font-serif fw-bold mt-3 mb-2\'>', '</h2>')" title="Heading 2">H2</button>
                            <button type="button" class="btn btn-sm btn-white border shadow-sm" onclick="insertHTML('<p class=\'lead text-muted mb-3\'>', '</p>')" title="Paragraph"><i class="fa-solid fa-paragraph me-1"></i> Text</button>
                            <button type="button" class="btn btn-sm btn-white border shadow-sm text-gold fw-bold" onclick="insertHTML('<span class=\'text-gold fw-bold\'>', '</span>')" title="Gold Highlight">Gold</button>
                            <button type="button" class="btn btn-sm btn-white border shadow-sm text-dark fw-bold" onclick="insertHTML('<a href=\'/shop\' class=\'btn btn-dark rounded-pill px-4 py-2 mt-2\'>', '</a>')" title="Shop Button"><i class="fa-solid fa-bag-shopping me-1"></i> Button</button>
                            <button type="button" class="btn btn-sm btn-white border shadow-sm text-success fw-bold" onclick="insertHTML('<a href=\'https://wa.me/919544832975\' target=\'_blank\' class=\'btn btn-success rounded-pill px-4 py-2 mt-2\'><i class=\'fa-brands fa-whatsapp me-2\'></i>', '</a>')" title="WhatsApp Link"><i class="fa-brands fa-whatsapp me-1"></i> WA</button>
                            <button type="button" class="btn btn-sm btn-white border shadow-sm" onclick="insertHTML('<span class=\'badge bg-gold rounded-pill px-3 py-2\'>', '</span>')" title="Badge"><i class="fa-solid fa-tag me-1"></i> Badge</button>
                            <button type="button" class="btn btn-sm btn-white border shadow-sm" onclick="insertHTML('<div class=\'p-4 bg-light rounded-4 shadow-sm border text-center my-3\'>\n  ', '\n</div>')" title="Card Container"><i class="fa-solid fa-square-poll-vertical me-1"></i> Card</button>
                        </div>
                    </div>

                    <textarea name="content_html" id="htmlContentArea" class="form-control rounded-3 font-monospace" rows="8" required>{{ old('content_html', $homeContent->content_html) }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Custom CSS Styles</label>
                    <textarea name="custom_css" class="form-control rounded-3 font-monospace" rows="4">{{ old('custom_css', $homeContent->custom_css) }}</textarea>
                </div>

                <div class="col-12 mt-3 mt-md-4">
                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-2.5 py-md-3 shadow-sm text-dark submit-btn" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> UPDATE HOME CONTENT
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function insertHTML(startTag, endTag) {
    const textarea = document.getElementById('htmlContentArea');
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    const replacement = startTag + (selectedText || 'Your Content Here') + endTag;

    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
    textarea.focus();
    textarea.selectionStart = start + startTag.length;
    textarea.selectionEnd = start + startTag.length + (selectedText || 'Your Content Here').length;
}
</script>
@endsection
