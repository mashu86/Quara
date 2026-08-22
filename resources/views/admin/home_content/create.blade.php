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
                    <label class="form-label fw-bold">HTML Main Content <span class="text-danger">*</span></label>
                    <textarea name="content_html" class="form-control rounded-3" rows="8" placeholder="Enter HTML content, banners, headings, calls-to-action..." required>{{ old('content_html') }}</textarea>
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
