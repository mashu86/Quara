@extends('layouts.admin')

@section('title', 'Add Home Content - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Create Home Content Block</h3>
    <a href="{{ route('admin.home-content.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-3">&larr; Back</a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('admin.home-content.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Block Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Festival Special Offer Banner" value="{{ old('title') }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Image Position <span class="text-danger">*</span></label>
                    <select name="image_position" class="form-select rounded-3" required>
                        <option value="top">Top (Above HTML)</option>
                        <option value="middle">Middle</option>
                        <option value="bottom">Bottom (Below HTML)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="active">Active (Visible)</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Banner / Featured Image (Saved as Binary BLOB)</label>
                    <input type="file" name="image" class="form-control rounded-3" accept="image/*">
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0">HTML Main Content <span class="text-danger">*</span></label>
                        <span class="badge bg-dark"><i class="fa-solid fa-code me-1"></i> HTML Editor Toolbox</span>
                    </div>

                    <!-- Toolbox Controls -->
                    <div class="card bg-light border-0 rounded-3 mb-2 shadow-sm">
                        <div class="card-body p-2 d-flex flex-wrap align-items-center gap-1">
                            <span class="small fw-bold text-muted me-2"><i class="fa-solid fa-toolbox text-warning me-1"></i> Toolbox:</span>
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

                    <textarea name="content_html" id="htmlContentArea" class="form-control rounded-3 font-monospace" rows="8" placeholder="Enter HTML content, banners, headings, calls-to-action..." required>{{ old('content_html') }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Custom CSS Styles (Optional)</label>
                    <textarea name="custom_css" class="form-control rounded-3 font-monospace" rows="4" placeholder="e.g. .home-banner { background: gold; color: black; }">{{ old('custom_css') }}</textarea>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-warning rounded-pill fw-bold px-5 py-2">SAVE HOME CONTENT</button>
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
