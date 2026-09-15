@extends('layouts.admin')

@section('title', 'Add Category - ' . $siteName . ' Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 gap-2">
    <h4 class="fw-bold mb-0" style="font-size: 0.95rem;">Add New Category</h4>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-2.5 px-sm-3 py-1 text-nowrap" style="font-size: 0.78rem;">
        <i class="fa-solid fa-arrow-left me-0 me-sm-1"></i><span class="d-none d-sm-inline"> Back to Categories</span>
    </a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-3 p-md-5">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" onsubmit="handleAdminFormSubmit(this)">
            @csrf

            <div class="row g-3 g-md-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Korean Tops, Western Dresses" value="{{ old('name') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Text Color (Hex) <span class="text-danger">*</span></label>
                    <input type="color" name="text_color" class="form-control form-control-color w-100 rounded-3" value="{{ old('text_color', '#d4af37') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Background Image (Optional)</label>
                    <input type="file" name="background_image" class="form-control rounded-3" accept="image/*">
                    <div class="form-text small">Uploaded image will serve as category card background.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Combo Offer Configuration -->
                <div class="col-12">
                    <div class="card border border-warning rounded-4 bg-light shadow-xs p-3 mt-2">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_combo_offer" value="1" id="isComboOfferSwitch" {{ old('is_combo_offer') ? 'checked' : '' }} style="cursor: pointer; width: 2.5em; height: 1.25em;">
                            <label class="form-check-label fw-bold text-dark ms-2" for="isComboOfferSwitch">
                                👑 Is this a Combo Offer Category?
                            </label>
                        </div>
                        <p class="text-muted small mb-2" style="font-size: 0.78rem;">
                            Enabling combo offer allows customers to select a bundle of products from this category at a special package price.
                        </p>

                        <div id="comboFieldsContainer" class="row g-3 mt-1 {{ old('is_combo_offer') ? '' : 'd-none' }}">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Minimum Count <span class="text-danger">*</span></label>
                                <input type="number" name="min_count" id="min_count_input" class="form-control rounded-3" placeholder="e.g. 6" value="{{ old('min_count') }}" min="1">
                                <div class="form-text small">Compulsory minimum items to select.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Combo Bundle Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="combo_price" id="combo_price_input" class="form-control rounded-3" placeholder="e.g. 500" value="{{ old('combo_price') }}" min="0">
                                <div class="form-text small">Price for minimum count items.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Delivery Charge (₹)</label>
                                <input type="number" step="0.01" name="delivery_charge" class="form-control rounded-3" placeholder="0 for Free Delivery" value="{{ old('delivery_charge', '0.00') }}" min="0">
                                <div class="form-text small">Enter 0 to offer Free Delivery.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3 mt-md-4">
                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 w-sm-auto px-4 px-sm-5 py-2.5 py-sm-2 shadow-sm" style="font-size: 0.82rem; background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> SAVE CATEGORY
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const switchEl = document.getElementById('isComboOfferSwitch');
        const containerEl = document.getElementById('comboFieldsContainer');
        const minCountInput = document.getElementById('min_count_input');
        const comboPriceInput = document.getElementById('combo_price_input');

        if (switchEl && containerEl) {
            switchEl.addEventListener('change', function () {
                if (this.checked) {
                    containerEl.classList.remove('d-none');
                    minCountInput.setAttribute('required', 'required');
                    comboPriceInput.setAttribute('required', 'required');
                } else {
                    containerEl.classList.add('d-none');
                    minCountInput.removeAttribute('required');
                    comboPriceInput.removeAttribute('required');
                }
            });
        }
    });
</script>
@endsection
