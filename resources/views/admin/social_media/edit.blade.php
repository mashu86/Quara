@extends('layouts.admin')

@section('title', 'Edit Social Link - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Edit Social Entry: {{ strtoupper($socialMedia->type) }}</h3>
    <a href="{{ route('admin.social-media.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-3">&larr; Back</a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('admin.social-media.update', $socialMedia->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Platform Type <span class="text-danger">*</span></label>
                    <select name="type" id="platformType" class="form-select rounded-3" required onchange="toggleFields()">
                        <option value="whatsapp" {{ old('type', $socialMedia->type) === 'whatsapp' ? 'selected' : '' }}>WhatsApp Launcher</option>
                        <option value="instagram" {{ old('type', $socialMedia->type) === 'instagram' ? 'selected' : '' }}>Instagram</option>
                        <option value="facebook" {{ old('type', $socialMedia->type) === 'facebook' ? 'selected' : '' }}>Facebook</option>
                        <option value="youtube" {{ old('type', $socialMedia->type) === 'youtube' ? 'selected' : '' }}>YouTube</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="active" {{ old('status', $socialMedia->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $socialMedia->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- WhatsApp Fields -->
                <div id="whatsappFields" class="row g-3 col-12 m-0 p-0">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Country Code</label>
                        <input type="text" name="country_code" class="form-control rounded-3" value="{{ old('country_code', $socialMedia->country_code) }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control rounded-3" value="{{ old('phone_number', $socialMedia->phone_number) }}">
                    </div>
                </div>

                <!-- Profile URL Field -->
                <div id="urlFields" class="col-12 d-none">
                    <label class="form-label fw-bold">Full Profile URL</label>
                    <input type="url" name="url" class="form-control rounded-3" value="{{ old('url', $socialMedia->url) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control rounded-3" value="{{ old('sort_order', $socialMedia->sort_order) }}">
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-warning rounded-pill fw-bold px-5 py-2">UPDATE ENTRY</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleFields() {
        const type = document.getElementById('platformType').value;
        if (type === 'whatsapp') {
            document.getElementById('whatsappFields').classList.remove('d-none');
            document.getElementById('urlFields').classList.add('d-none');
        } else {
            document.getElementById('whatsappFields').classList.add('d-none');
            document.getElementById('urlFields').classList.remove('d-none');
        }
    }
    toggleFields();
</script>
@endsection
