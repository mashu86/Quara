@extends('layouts.admin')

@section('title', 'Edit Category - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 gap-2">
    <h4 class="fw-bold mb-0 text-truncate" style="font-size: 0.95rem; max-width: 65%;">Edit Category: {{ $category->name }}</h4>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-2.5 px-sm-3 py-1 text-nowrap" style="font-size: 0.78rem;">
        <i class="fa-solid fa-arrow-left me-0 me-sm-1"></i><span class="d-none d-sm-inline"> Back to Categories</span>
    </a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-3 p-md-5">
        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" onsubmit="handleAdminFormSubmit(this)">
            @csrf
            @method('PUT')

            <div class="row g-3 g-md-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $category->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Text Color (Hex) <span class="text-danger">*</span></label>
                    <input type="color" name="text_color" class="form-control form-control-color w-100 rounded-3" value="{{ old('text_color', $category->text_color) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Background Image</label>
                    <div id="backgroundImagePreview"
                         class="mb-2 rounded-3 border"
                         data-original-image="{{ $category->background_image ? $category->background_image_url : '' }}"
                         style="width: 90px; height: 60px; background-color: #000; background-image: {{ $category->background_image ? 'url(\'' . $category->background_image_url . '\')' : 'none' }}; background-size: cover; background-position: center;"
                         title="Background image preview"></div>
                    <input id="backgroundImageInput" type="file" name="background_image" class="form-control rounded-3" accept="image/*">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="active" {{ old('status', $category->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $category->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-12 mt-3 mt-md-4">
                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 w-sm-auto px-4 px-sm-5 py-2.5 py-sm-2 shadow-sm" style="font-size: 0.82rem; background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> UPDATE CATEGORY
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('backgroundImageInput')?.addEventListener('change', function () {
        const preview = document.getElementById('backgroundImagePreview');
        const selectedFile = this.files?.[0];

        if (!preview) {
            return;
        }

        if (preview.dataset.previewUrl) {
            URL.revokeObjectURL(preview.dataset.previewUrl);
            delete preview.dataset.previewUrl;
        }

        if (selectedFile) {
            const previewUrl = URL.createObjectURL(selectedFile);
            preview.dataset.previewUrl = previewUrl;
            preview.style.backgroundImage = `url("${previewUrl}")`;
            return;
        }

        const originalImage = preview.dataset.originalImage;
        preview.style.backgroundImage = originalImage ? `url("${originalImage}")` : 'none';
    });
</script>
@endsection
