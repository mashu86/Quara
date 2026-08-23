@extends('layouts.admin')

@section('title', 'Add Category - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">New</h3>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-3">&larr; Back to Categories</a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" onsubmit="handleAdminFormSubmit(this)">
            @csrf

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Korean Tops, Western Dresses" value="{{ old('name') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Text Color (Hex) <span class="text-danger">*</span></label>
                    <input type="color" name="text_color" class="form-control form-control-color w-100 rounded-3" value="{{ old('text_color', '#FFFFFF') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Background Image (Optional)</label>
                    <input type="file" name="background_image" class="form-control rounded-3" accept="image/*">
                    <div class="form-text small">Uploaded image will serve as category card background.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-warning rounded-pill fw-bold px-5 py-2">SAVE CATEGORY</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
