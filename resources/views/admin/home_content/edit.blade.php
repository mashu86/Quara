@extends('layouts.admin')

@section('title', 'Edit Home Content - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Edit Home Content: {{ $homeContent->title }}</h3>
    <a href="{{ route('admin.home-content.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-3">&larr; Back</a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('admin.home-content.update', $homeContent->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Block Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control rounded-3" value="{{ old('title', $homeContent->title) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Image Position <span class="text-danger">*</span></label>
                    <select name="image_position" class="form-select rounded-3" required>
                        <option value="top" {{ old('image_position', $homeContent->image_position) === 'top' ? 'selected' : '' }}>Top (Above HTML)</option>
                        <option value="middle" {{ old('image_position', $homeContent->image_position) === 'middle' ? 'selected' : '' }}>Middle</option>
                        <option value="bottom" {{ old('image_position', $homeContent->image_position) === 'bottom' ? 'selected' : '' }}>Bottom (Below HTML)</option>
                    </select>
                </div>

                <div class="col-md-3">
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
                            <img src="{{ $homeContent->image_url }}" alt="Current Banner" class="rounded-3 border" style="height: 100px; object-fit: cover;">
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control rounded-3" accept="image/*">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">HTML Main Content <span class="text-danger">*</span></label>
                    <textarea name="content_html" class="form-control rounded-3" rows="8" required>{{ old('content_html', $homeContent->content_html) }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Custom CSS Styles</label>
                    <textarea name="custom_css" class="form-control rounded-3 font-monospace" rows="4">{{ old('custom_css', $homeContent->custom_css) }}</textarea>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-warning rounded-pill fw-bold px-5 py-2">UPDATE HOME CONTENT</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
