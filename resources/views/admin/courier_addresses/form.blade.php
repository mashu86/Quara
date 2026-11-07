@extends('layouts.admin')

@section('title', $address->exists ? 'Edit Courier Address' : 'Add Courier Address')

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="fw-bold mb-1">{{ $address->exists ? 'Edit Courier Address' : 'Add Courier Address' }}</h2>
            <div class="text-muted small">For products sold outside the website.</div>
        </div>
        <a href="{{ route('admin.courier-addresses.index') }}" class="btn btn-outline-dark"><i class="fa-solid fa-list me-1"></i> Address list</a>
    </div>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="fw-bold mb-1"><i class="fa-solid fa-camera text-warning me-1"></i> Auto-fill from photo</div>
                    <div class="text-muted small mb-2">Upload a photo or screenshot of the address. AI fills the name, phone number and address below. Check the details before saving.</div>
                    <input type="file" id="addressPhotoInput" accept="image/*" class="d-none">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <button type="button" class="btn btn-warning fw-bold" id="scanAddressBtn"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Scan Photo</button>
                        <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#geminiApiKeyPickerModal" title="View or change active Google API key" aria-label="View or change active Google API key"><i class="fa-solid fa-eye"></i></button>
                        <span id="scanAddressStatus" class="small fw-semibold" role="status"></span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ $address->exists ? route('admin.courier-addresses.update', $address) : route('admin.courier-addresses.store') }}" class="card border-0 shadow-sm">
                @csrf
                @if($address->exists) @method('PUT') @endif
                <div class="card-body row g-3">
                    <div class="col-md-6"><label class="form-label fw-semibold" for="customer_name">Name <span class="text-danger">*</span></label><input class="form-control" id="customer_name" name="customer_name" value="{{ old('customer_name', $address->customer_name) }}" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold" for="customer_phone">Phone Number <span class="text-danger">*</span></label><input class="form-control" id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $address->customer_phone) }}" required></div>
                    <div class="col-12"><label class="form-label fw-semibold" for="house_building">House / Building <span class="text-danger">*</span></label><input class="form-control" id="house_building" name="house_building" value="{{ old('house_building', $address->house_building) }}" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold" for="street">Street / Area</label><input class="form-control" id="street" name="street" value="{{ old('street', $address->street) }}"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold" for="city">City / Town</label><input class="form-control" id="city" name="city" value="{{ old('city', $address->city) }}"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold" for="district">District</label><input class="form-control" id="district" name="district" value="{{ old('district', $address->district) }}"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold" for="state">State</label><input class="form-control" id="state" name="state" value="{{ old('state', $address->state) }}"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold" for="pin_code">PIN Code <span class="text-danger">*</span></label><input class="form-control" id="pin_code" name="pin_code" inputmode="numeric" maxlength="6" pattern="[1-9][0-9]{5}" value="{{ old('pin_code', $address->pin_code) }}" required></div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end flex-wrap gap-2">
                    @unless($address->exists)
                        <button class="btn btn-outline-dark fw-bold" type="submit" name="add_another" value="1">Save &amp; Add Another</button>
                    @endunless
                    <button class="btn btn-primary fw-bold px-4" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('admin.partials.gemini_api_key_picker_modal')
@endsection

@section('scripts')
<script>
(() => {
    const input = document.getElementById('addressPhotoInput');
    const button = document.getElementById('scanAddressBtn');
    const status = document.getElementById('scanAddressStatus');
    const fields = ['customer_name', 'customer_phone', 'house_building', 'street', 'city', 'district', 'state', 'pin_code'];
    const showStatus = (message, className) => { status.textContent = message; status.className = `small fw-semibold ${className}`; };

    button.addEventListener('click', () => input.click());
    input.addEventListener('change', async () => {
        if (!input.files[0]) return;
        const body = new FormData();
        body.append('_token', '{{ csrf_token() }}');
        body.append('image', input.files[0]);
        button.disabled = true;
        showStatus('Reading address...', 'text-muted');
        try {
            const response = await fetch('{{ route('admin.manual-sales.parse-address') }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body,
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.message);
            fields.forEach(name => { if (result.data[name]) document.getElementById(name).value = result.data[name]; });
            showStatus('Address filled. Please check it before saving.', 'text-success');
        } catch (error) {
            showStatus(error.message || 'Could not read this photo. Please try a clearer one.', 'text-danger');
        } finally {
            button.disabled = false;
            input.value = '';
        }
    });
})();
</script>
@endsection
