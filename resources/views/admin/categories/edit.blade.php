@extends('layouts.admin')

@section('title', 'Edit Category - QUARA WALDROP Admin')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
    <h3 class="fw-bold mb-0 fs-4 fs-sm-3">Edit Category: {{ $category->name }}</h3>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-3 py-1-5 mt-1 mt-sm-0" style="font-size: 0.78rem;">
        &larr; Back to Categories
    </a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" onsubmit="handleAdminFormSubmit(this)">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $category->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Text Color (Hex) <span class="text-danger">*</span></label>
                    <input type="color" name="text_color" class="form-control form-control-color w-100 rounded-3" value="{{ old('text_color', $category->text_color) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Background Image</label>
                    @if($category->background_image)
                        <div class="mb-2">
                            <img src="{{ $category->background_image_url }}" alt="Current Background" class="rounded-3 border" style="height: 60px; object-fit: cover;">
                        </div>
                    @endif
                    <input type="file" name="background_image" class="form-control rounded-3" accept="image/*">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="active" {{ old('status', $category->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $category->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-warning rounded-pill fw-bold px-4 px-sm-5 py-2" style="font-size: 0.82rem;">
                        <span class="d-inline d-sm-none">UPDATE</span>
                        <span class="d-none d-sm-inline">UPDATE CATEGORY</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
