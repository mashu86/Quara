@extends('layouts.admin')
@section('title', 'District Offers')
@section('styles')
<style>
    .district-offers-page { width: 100%; min-width: 0; }
    .district-offers-heading { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; }
    .district-offers-icon { display: grid; place-items: center; width: 52px; height: 52px; flex-shrink: 0; border-radius: 16px; background: #fff3d3; color: #9a6b12; font-size: 22px; }
    .district-offers-heading h4 { margin: 0 0 5px; font-weight: 700; }
    .district-offers-heading p { margin: 0; color: #6b7280; font-size: 14px; }
    .district-offers-card { width: 100%; min-height: max(520px, calc(100dvh - 230px)); padding: clamp(24px, 3vw, 44px); border-radius: 20px; }
    .district-offers-card .form-label { font-weight: 600; margin-bottom: 10px; }
    .district-offers-card .form-control, .district-offers-card .form-select { min-height: 50px; border-radius: 10px; width: 100%; min-width: 0; font-size: 16px; }
    .district-offers-card .form-control:focus, .district-offers-card .form-select:focus { border-color: #c9962e; box-shadow: 0 0 0 3px rgb(201 150 46 / 14%); }
    .district-offers-selector { padding-bottom: 24px; margin-bottom: 24px; border-bottom: 1px solid #eceef1; }
    .district-offers-editor { display: flex; flex: 1; flex-direction: column; gap: 20px; }
    .district-offers-grid { --bs-gutter-x: 28px; --bs-gutter-y: 24px; }
    .district-offers-note { color: #6b7280; font-size: 13px; line-height: 1.7; }
    .district-offers-footer { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-top: auto; padding-top: 24px; border-top: 1px solid #eceef1; }
    .district-offers-status { display: flex; align-items: center; gap: 12px; min-height: 48px; padding-left: 0; margin: 0; }
    .district-offers-status .form-check-input { width: 46px; height: 25px; margin: 0; float: none; cursor: pointer; flex-shrink: 0; }
    .district-offers-status .form-check-label { cursor: pointer; font-weight: 600; }
    .district-offers-update { min-height: 48px; min-width: 180px; border-radius: 10px; }
    @media (max-width: 575.98px) {
        .district-offers-heading { align-items: flex-start; gap: 10px; margin-bottom: 16px; }
        .district-offers-icon { width: 42px; height: 42px; border-radius: 12px; font-size: 18px; }
        .district-offers-heading h4 { font-size: 20px; }
        .district-offers-heading p { font-size: 13px; }
        .district-offers-card { min-height: 0; padding: 20px 16px; border-radius: 16px; }
        .district-offers-selector { margin-bottom: 20px; padding-bottom: 20px; }
        .district-offers-grid { --bs-gutter-x: 16px; --bs-gutter-y: 18px; }
        .district-offers-footer { flex-direction: column; align-items: stretch; gap: 12px; padding-top: 16px; }
        .district-offers-update { width: 100%; min-width: 0; }
    }
</style>
@endsection
@section('content')
<div class="district-offers-page">
<div class="district-offers-heading">
    <div class="district-offers-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></div>
    <div><h4>District Offers</h4><p>Select a district and manage its special order discount.</p></div>
</div>
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif
<div class="card card-body border-0 shadow-sm district-offers-card">
    <form method="GET" action="{{ route('admin.district-offers.index') }}" class="district-offers-selector">
        <label for="offer-district" class="form-label fw-bold">District</label>
        <select id="offer-district" name="district" class="form-select" onchange="this.form.submit()">
            <option value="">Select district</option>
            @foreach($districts as $district)
                <option value="{{ $district }}" @selected($selectedDistrict === $district)>{{ $district }}</option>
            @endforeach
        </select>
        <noscript><button type="submit" class="btn btn-outline-dark mt-2">View Offer</button></noscript>
    </form>
        <form method="POST" action="{{ $selectedDistrict ? route('admin.district-offers.update', $selectedDistrict) : '#' }}" class="district-offers-editor">
            @csrf @method('PUT')
            <div class="row district-offers-grid">
                <div class="col-sm-6">
                    <label for="offer-start" class="form-label">Start Date</label>
                    <input id="offer-start" class="form-control" type="date" name="start_date" value="{{ ($restoreInput ? old('start_date') : $offer?->start_date->format('Y-m-d')) }}" required>
                </div>
                <div class="col-sm-6">
                    <label for="offer-end" class="form-label">End Date</label>
                    <input id="offer-end" class="form-control" type="date" name="end_date" value="{{ ($restoreInput ? old('end_date') : $offer?->end_date->format('Y-m-d')) }}" required>
                </div>
                <div class="col-sm-6">
                    <label for="offer-method" class="form-label">Offer Method</label>
                    <select id="offer-method" name="method" class="form-select">
                        <option value="percentage" @selected(($restoreInput ? old('method') : $offer?->method) !== 'fixed')>Percentage (%)</option>
                        <option value="fixed" @selected(($restoreInput ? old('method') : $offer?->method) === 'fixed')>Price (₹)</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label for="offer-value" class="form-label">Value</label>
                    <input id="offer-value" type="number" class="form-control" name="value" min="0.01" step="0.01" value="{{ ($restoreInput ? old('value') : $offer?->value) }}" required>
                </div>
            </div>
            <div class="district-offers-note"><i class="fa-solid fa-circle-info me-1" aria-hidden="true"></i> Start and end dates are both included. Discount applies to the items total; shipping is added afterwards. Existing orders keep their original discount.</div>
            <div class="district-offers-footer">
                <div class="form-check form-switch district-offers-status">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" role="switch" type="checkbox" name="is_active" value="1" id="offer-status" @checked(($restoreInput ? old('is_active') : $offer?->is_active)) onchange="this.nextElementSibling.textContent = this.checked ? 'Active' : 'Inactive'">
                    <label class="form-check-label" for="offer-status">{{ ($restoreInput ? old('is_active') : $offer?->is_active) ? 'Active' : 'Inactive' }}</label>
                </div>
                <button class="btn btn-warning fw-bold px-4 district-offers-update" type="submit" @disabled(!$selectedDistrict)>Update Offer</button>
            </div>
        </form>
</div>
</div>
@endsection
