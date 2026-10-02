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
                    <label class="form-label fw-bold small">Product Display Location</label>
                    <select name="show_in_collection" class="form-select rounded-3">
                        <option value="1" {{ old('show_in_collection', '1') === '1' ? 'selected' : '' }}>Category and Product Collection</option>
                        <option value="0" {{ old('show_in_collection') === '0' ? 'selected' : '' }}>Only inside this category</option>
                    </select>
                    <div class="form-text small">Category-only products stay out of general shop, home, and search listings.</div>
                </div>

                <div class="col-md-6" id="statusSelectWrapper">
                    <label class="form-label fw-bold small">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select rounded-3">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <div class="form-text small">Status determines if this category is visible in shop.</div>
                </div>

                <!-- Offer Category Configuration -->
                <div class="col-12">
                    <div class="card border border-warning rounded-4 bg-light shadow-xs p-3 mt-2">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_offer_category" value="1" id="isOfferCategorySwitch" {{ old('is_offer_category') ? 'checked' : '' }} style="cursor: pointer; width: 2.5em; height: 1.25em;">
                            <label class="form-check-label fw-bold text-dark ms-2" for="isOfferCategorySwitch">
                                🏷️ Is this an Offer Category?
                            </label>
                        </div>
                        <p class="text-muted small mb-2" style="font-size: 0.78rem;">
                            Enabling this marks the category as an Offer Category. Offer activation is managed centrally in <strong>Offer Sale</strong>.
                        </p>

                        <div id="regularCategoryMinimumFields" class="row g-3 mb-3 {{ old('is_offer_category') ? 'd-none' : '' }}">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Does this category require a minimum purchase?</label>
                                <select name="minimum_purchase_required" id="minimum_purchase_required" class="form-select rounded-3">
                                    <option value="0" {{ (string) old('minimum_purchase_required', '0') === '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ (string) old('minimum_purchase_required', '0') === '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="col-md-6 {{ old('minimum_purchase_required') === '1' && !old('is_offer_category') ? '' : 'd-none' }}" id="minimumPurchaseCountWrapper">
                                <label class="form-label fw-bold small">Minimum products to purchase</label>
                                <input type="number" name="minimum_purchase_count" id="minimum_purchase_count" class="form-control rounded-3" value="{{ old('minimum_purchase_count') }}" min="1">
                            </div>
                        </div>

                        <div id="offerCategoryFieldsContainer" class="mt-3 {{ old('is_offer_category') ? '' : 'd-none' }}">
                            <div class="mb-3">
                                <label class="form-label fw-bold small d-block">Select Offer Type <span class="text-danger">*</span></label>
                                <div class="d-flex flex-wrap gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="offer_type" id="offerTypeCombo" value="combo" {{ old('offer_type', 'combo') === 'combo' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark" for="offerTypeCombo">
                                            👑 Combo Offer <span class="text-muted fw-normal small">(Buy X items for ₹Y package)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="offer_type" id="offerTypeDiscount" value="discount" {{ old('offer_type') === 'discount' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark" for="offerTypeDiscount">
                                            🏷️ Product Discount Offer <span class="text-muted fw-normal small">(% percentage or ₹ flat discount per item)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Combo Offer Specific Fields -->
                            <div id="comboFieldsSection" class="row g-3 border-top pt-3 {{ old('offer_type', 'combo') === 'combo' ? '' : 'd-none' }}">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Allow purchase before minimum count?</label>
                                    <select name="allow_pre_min_purchase" id="allow_pre_min_purchase" class="form-select rounded-3">
                                        <option value="0" {{ (string) old('allow_pre_min_purchase', '0') === '0' ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ old('allow_pre_min_purchase') === '1' ? 'selected' : '' }}>Yes</option>
                                    </select>
                                </div>
                                <div class="col-md-6 {{ old('allow_pre_min_purchase') === '1' ? '' : 'd-none' }}" id="preMinOfferPriceWrapper">
                                    <label class="form-label fw-bold small">Apply combo offer price before minimum count?</label>
                                    <select name="pre_min_purchase_offer_price" class="form-select rounded-3">
                                        <option value="0" {{ old('pre_min_purchase_offer_price', '0') === '0' ? 'selected' : '' }}>No — use actual product price</option>
                                        <option value="1" {{ old('pre_min_purchase_offer_price') === '1' ? 'selected' : '' }}>Yes — use combo offer price</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Minimum Count <span class="text-danger">*</span></label>
                                    <input type="number" name="min_count" id="min_count_input" class="form-control rounded-3" placeholder="e.g. 6" value="{{ old('min_count') }}" min="1">
                                    <div class="form-text small">Compulsory minimum items customer must select.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Combo Bundle Price (₹) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="combo_price" id="combo_price_input" class="form-control rounded-3" placeholder="e.g. 500" value="{{ old('combo_price') }}" min="0">
                                    <div class="form-text small">Total price for minimum count items.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Price Per Piece (&#8377;) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="unit_offer_price" id="unit_offer_price_input" class="form-control rounded-3" placeholder="Auto calculated" value="{{ old('unit_offer_price') }}" min="0">
                                    <div class="form-text small">Either price field updates the other.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Delivery Charge (if minimum count is reached)</label>
                                    <select name="delivery_charge_mode" id="delivery_charge_mode" class="form-select rounded-3">
                                        <option value="free" {{ old('delivery_charge_mode', 'free') === 'free' ? 'selected' : '' }}>Free</option>
                                        <option value="custom" {{ old('delivery_charge_mode') === 'custom' ? 'selected' : '' }}>Custom amount</option>
                                        <option value="master" {{ old('delivery_charge_mode') === 'master' ? 'selected' : '' }}>Website delivery price master</option>
                                    </select>
                                </div>
                                <div class="col-md-3 {{ old('delivery_charge_mode') === 'custom' ? '' : 'd-none' }}" id="customDeliveryChargeWrapper">
                                    <label class="form-label fw-bold small">Custom Delivery (&#8377;) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="delivery_charge" id="delivery_charge_input" class="form-control rounded-3" placeholder="e.g. 50" value="{{ old('delivery_charge') }}" min="0">
                                </div>
                                <div class="col-12 small text-muted d-none" id="masterDeliveryHelp">Delivery charge follows the active conditions in Website Delivery Price Master.</div>
                                <div class="col-12 small text-muted" id="freeDeliveryHelp">Customers get free delivery for this combo category.</div>
                            </div>

                            <!-- Product Discount Specific Fields -->
                            <div id="discountFieldsSection" class="row g-3 border-top pt-3 {{ old('offer_type') === 'discount' ? '' : 'd-none' }}">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Discount Type <span class="text-danger">*</span></label>
                                    <select name="discount_type" id="discount_type_select" class="form-select rounded-3">
                                        <option value="percentage" {{ old('discount_type', 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%) Discount</option>
                                        <option value="flat" {{ old('discount_type') === 'flat' ? 'selected' : '' }}>Flat Amount (₹) Discount</option>
                                    </select>
                                    <div class="form-text small">Deducted from individual product original prices.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Discount Value <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="discount_value" id="discount_value_input" class="form-control rounded-3" placeholder="e.g. 20 for 20% or 100 for ₹100 Off" value="{{ old('discount_value') }}" min="0">
                                    <div class="form-text small">Percentage % or Rupees ₹ depending on selection.</div>
                                </div>
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
        const switchEl = document.getElementById('isOfferCategorySwitch');
        const statusWrapper = document.getElementById('statusSelectWrapper');
        const offerContainer = document.getElementById('offerCategoryFieldsContainer');
        const comboRadio = document.getElementById('offerTypeCombo');
        const discountRadio = document.getElementById('offerTypeDiscount');
        const comboSection = document.getElementById('comboFieldsSection');
        const discountSection = document.getElementById('discountFieldsSection');
        const minCountInput = document.getElementById('min_count_input');
        const comboPriceInput = document.getElementById('combo_price_input');
        const unitPriceInput = document.getElementById('unit_offer_price_input');
        const deliveryMode = document.getElementById('delivery_charge_mode');
        const customDeliveryWrapper = document.getElementById('customDeliveryChargeWrapper');
        const customDeliveryInput = document.getElementById('delivery_charge_input');
        const discountValueInput = document.getElementById('discount_value_input');
        const preMinPurchase = document.getElementById('allow_pre_min_purchase');
        const preMinOfferPriceWrapper = document.getElementById('preMinOfferPriceWrapper');
        const regularMinimumFields = document.getElementById('regularCategoryMinimumFields');
        const minimumPurchaseRequired = document.getElementById('minimum_purchase_required');
        const minimumPurchaseCount = document.getElementById('minimum_purchase_count');
        const minimumPurchaseCountWrapper = document.getElementById('minimumPurchaseCountWrapper');

        function updateOfferFormState() {
            const isOffer = switchEl.checked;
            regularMinimumFields.classList.toggle('d-none', isOffer);
            minimumPurchaseRequired.disabled = isOffer;
            minimumPurchaseCount.disabled = isOffer || minimumPurchaseRequired.value !== '1';
            minimumPurchaseCount.required = !isOffer && minimumPurchaseRequired.value === '1';
            minimumPurchaseCountWrapper.classList.toggle('d-none', isOffer || minimumPurchaseRequired.value !== '1');
            if (isOffer) {
                statusWrapper.classList.add('d-none');
                offerContainer.classList.remove('d-none');
                if (comboRadio.checked) {
                    comboSection.classList.remove('d-none');
                    discountSection.classList.add('d-none');
                    minCountInput.setAttribute('required', 'required');
                    comboPriceInput.setAttribute('required', 'required');
                    unitPriceInput.setAttribute('required', 'required');
                    discountValueInput.removeAttribute('required');
                } else {
                    comboSection.classList.add('d-none');
                    discountSection.classList.remove('d-none');
                    minCountInput.removeAttribute('required');
                    comboPriceInput.removeAttribute('required');
                    unitPriceInput.removeAttribute('required');
                    discountValueInput.setAttribute('required', 'required');
                }
            } else {
                statusWrapper.classList.remove('d-none');
                offerContainer.classList.add('d-none');
                minCountInput.removeAttribute('required');
                comboPriceInput.removeAttribute('required');
                unitPriceInput.removeAttribute('required');
                discountValueInput.removeAttribute('required');
            }
        }

        minimumPurchaseRequired.addEventListener('change', updateOfferFormState);

        if (switchEl) {
            switchEl.addEventListener('change', updateOfferFormState);
            comboRadio.addEventListener('change', updateOfferFormState);
            discountRadio.addEventListener('change', updateOfferFormState);
            updateOfferFormState();
        }

        let lastPriceEdited = 'bundle';
        comboPriceInput?.addEventListener('input', () => {
            lastPriceEdited = 'bundle';
            const count = parseInt(minCountInput.value, 10);
            if (count > 0 && comboPriceInput.value !== '') unitPriceInput.value = (parseFloat(comboPriceInput.value) / count).toFixed(2);
        });
        unitPriceInput?.addEventListener('input', () => {
            lastPriceEdited = 'unit';
            const count = parseInt(minCountInput.value, 10);
            if (count > 0 && unitPriceInput.value !== '') comboPriceInput.value = (parseFloat(unitPriceInput.value) * count).toFixed(2);
        });
        minCountInput?.addEventListener('input', () => {
            const count = parseInt(minCountInput.value, 10);
            if (count > 0) {
                if (lastPriceEdited === 'unit' && unitPriceInput.value !== '') comboPriceInput.value = (parseFloat(unitPriceInput.value) * count).toFixed(2);
                else if (comboPriceInput.value !== '') unitPriceInput.value = (parseFloat(comboPriceInput.value) / count).toFixed(2);
            }
        });
        function updateDeliveryFields() {
            const mode = deliveryMode?.value;
            customDeliveryWrapper?.classList.toggle('d-none', mode !== 'custom');
            document.getElementById('masterDeliveryHelp')?.classList.toggle('d-none', mode !== 'master');
            document.getElementById('freeDeliveryHelp')?.classList.toggle('d-none', mode !== 'free');
            if (customDeliveryInput) {
                if (mode === 'custom' && switchEl.checked && comboRadio.checked) customDeliveryInput.setAttribute('required', 'required');
                else customDeliveryInput.removeAttribute('required');
            }
        }
        function updatePreMinFields() {
            preMinOfferPriceWrapper?.classList.toggle('d-none', preMinPurchase?.value !== '1');
        }
        preMinPurchase?.addEventListener('change', updatePreMinFields);
        comboRadio?.addEventListener('change', updatePreMinFields);
        switchEl?.addEventListener('change', updatePreMinFields);
        updatePreMinFields();
        deliveryMode?.addEventListener('change', updateDeliveryFields);
        switchEl?.addEventListener('change', updateDeliveryFields);
        comboRadio?.addEventListener('change', updateDeliveryFields);
        updateDeliveryFields();
    });
</script>
@endsection
