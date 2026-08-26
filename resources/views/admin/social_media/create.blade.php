@extends('layouts.admin')

@section('title', 'Add Social Link - QUARA WALDROP Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .back-soc-btn {
            padding: 0.25rem 0.55rem !important;
            font-size: 0.82rem !important;
            border-radius: 8px !important;
        }
        .soc-header-title {
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
        .submit-btn {
            padding: 0.55rem 1rem !important;
            font-size: 0.82rem !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
    <h3 class="fw-bold mb-0 soc-header-title">Add Social Media Link</h3>
    <a href="{{ route('admin.social-media.index') }}" class="btn btn-outline-dark rounded-3 px-2.5 px-md-3 py-1 py-md-1.5 back-soc-btn shadow-sm" title="Back to Social Media Master">
        &larr;<span class="d-none d-md-inline"> Back</span>
    </a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-3.5 p-md-5">
        <form action="{{ route('admin.social-media.store') }}" method="POST">
            @csrf

            <div class="row g-3 g-md-4">
                <div class="col-6 col-md-6">
                    <label class="form-label fw-bold">Platform Type <span class="text-danger">*</span></label>
                    <select name="type" id="platformType" class="form-select rounded-3" required onchange="toggleFields()">
                        <option value="whatsapp">WhatsApp Launcher</option>
                        <option value="instagram">Instagram</option>
                        <option value="facebook">Facebook</option>
                        <option value="youtube">YouTube</option>
                    </select>
                </div>

                <div class="col-6 col-md-6">
                    <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- WhatsApp Fields -->
                <div id="whatsappFields" class="row g-2 g-md-3 col-12 m-0 p-0">
                    <div class="col-4 col-md-4">
                        <label class="form-label fw-bold">Code</label>
                        <input type="text" name="country_code" class="form-control rounded-3" placeholder="+91" value="{{ old('country_code', '+91') }}">
                    </div>
                    <div class="col-8 col-md-8">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control rounded-3" placeholder="10-digit mobile" value="{{ old('phone_number') }}">
                    </div>
                </div>

                <!-- Profile URL Field -->
                <div id="urlFields" class="col-12 d-none">
                    <label class="form-label fw-bold">Full Profile URL</label>
                    <input type="url" name="url" class="form-control rounded-3" placeholder="https://instagram.com/quarawaldrop" value="{{ old('url') }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-bold">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control rounded-3" value="{{ old('sort_order', 0) }}">
                </div>

                <div class="col-12 mt-3 mt-md-4">
                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-2.5 py-md-3 shadow-sm text-dark submit-btn" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> SAVE ENTRY
                    </button>
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
