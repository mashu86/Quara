@extends('layouts.admin')

@section('title', 'Master Settings - ' . $siteName . ' Admin')

@section('styles')
<style>
    .setting-card { border: 0; border-radius: 1rem; box-shadow: 0 4px 18px rgba(20, 20, 25, .06); }
    .setting-card .card-header { background: #fff; border-bottom: 1px solid #ececf1; padding: 1rem 1.25rem; }
    .setting-preview { min-height: 126px; background: #f7f7f9; border: 1px dashed #c9c9d2; border-radius: .8rem; display: flex; align-items: center; justify-content: center; padding: 1rem; }
    .setting-preview.logo-preview img { max-width: 100%; max-height: 88px; object-fit: contain; }
    .setting-preview.favicon-preview img { width: 64px; height: 64px; object-fit: contain; }
    .section-note { font-size: .78rem; color: #6c757d; }
    @media (max-width: 576px) {
        .settings-title { font-size: 1.2rem; }
        .setting-card .card-body { padding: 1rem !important; }
        .setting-card .card-header h5 { font-size: .92rem; }
        .form-label, .form-control, .form-select, .input-group-text { font-size: .8rem; }
    }
</style>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 settings-title"><i class="fa-solid fa-gears text-warning me-2"></i> Master Settings</h3>
        <p class="text-muted small mb-0">Manage the website branding, global email sender, and payment calculation from one place.</p>
    </div>
    <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-dark rounded-pill px-3 fw-bold w-100 w-md-auto">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Preview Website
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger rounded-4 shadow-sm">
        <div class="fw-bold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Please fix the following:</div>
        <ul class="mb-0 ps-3 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" onsubmit="return handleAdminFormSubmit(this)">
    @csrf

    <div class="card setting-card mb-4">
        <div class="card-header">
            <h5 class="fw-bold mb-1"><i class="fa-solid fa-palette text-primary me-2"></i> Website Branding</h5>
            <div class="section-note">A new logo or favicon becomes active across the customer website, admin panel, login screens, payment popup, and emails.</div>
        </div>
        <div class="card-body p-4">
            <div class="mb-4">
                <label for="site_name" class="form-label fw-bold">Website / Brand Name</label>
                <input type="text" id="site_name" name="site_name" class="form-control rounded-3 @error('site_name') is-invalid @enderror" value="{{ old('site_name', $siteName) }}" maxlength="100" required>
            </div>

            <div class="row g-4">
                <div class="col-md-7">
                    <label for="site_logo" class="form-label fw-bold">Master Logo</label>
                    <input type="file" id="site_logo" name="site_logo" class="form-control rounded-3 @error('site_logo') is-invalid @enderror" accept="image/png,image/jpeg,image/webp">
                    <div class="form-text">PNG, JPG, or WebP; maximum 4 MB. Leave blank to keep the current logo.</div>
                    <div class="setting-preview logo-preview mt-3"><img id="logoPreview" src="{{ $logoUrl }}" alt="Current website logo"></div>
                </div>
                <div class="col-md-5">
                    <label for="site_favicon" class="form-label fw-bold">Browser Favicon</label>
                    <input type="file" id="site_favicon" name="site_favicon" class="form-control rounded-3 @error('site_favicon') is-invalid @enderror" accept="image/x-icon,image/png,image/jpeg,image/webp,.ico">
                    <div class="form-text">ICO, PNG, JPG, or WebP; square image recommended; maximum 1 MB.</div>
                    <div class="setting-preview favicon-preview mt-3"><img id="faviconPreview" src="{{ $faviconUrl }}" alt="Current favicon"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card setting-card mb-4">
        <div class="card-header">
            <h5 class="fw-bold mb-1"><i class="fa-solid fa-envelope-circle-check text-success me-2"></i> Global Email Configuration</h5>
            <div class="section-note">The sender email saved here replaces the old email everywhere: OTP, order confirmation, cancellation, follow-up, password reset, and support contact.</div>
        </div>
        <div class="card-body p-4">
            <div class="alert alert-info border-0 rounded-3 small">
                <i class="fa-solid fa-circle-info me-1"></i> For Gmail, enable 2-Step Verification and use a Google App Password. Do not enter the normal Gmail password.
            </div>

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="mail_host" class="form-label fw-bold">SMTP Host</label>
                    <input type="text" id="mail_host" name="mail_host" class="form-control rounded-3" value="{{ old('mail_host', $mailHost) }}" placeholder="smtp.gmail.com" required>
                </div>
                <div class="col-md-4">
                    <label for="mail_port" class="form-label fw-bold">SMTP Port</label>
                    <input type="number" id="mail_port" name="mail_port" class="form-control rounded-3" value="{{ old('mail_port', $mailPort) }}" min="1" max="65535" required>
                </div>
                <div class="col-md-6">
                    <label for="mail_from_address" class="form-label fw-bold">Master Email Address</label>
                    <input type="email" id="mail_from_address" name="mail_from_address" class="form-control rounded-3" value="{{ old('mail_from_address', $mailFromAddress) }}" placeholder="store@example.com" autocomplete="username" required>
                    <div class="form-text">Used as admin login, SMTP username, sender, support, and recovery email everywhere.</div>
                </div>
                <div class="col-md-6">
                    <label for="mail_password" class="form-label fw-bold">SMTP Password / App Password</label>
                    <div class="input-group">
                        <input type="password" id="mail_password" name="mail_password" class="form-control rounded-start-3" autocomplete="new-password" placeholder="{{ $hasSavedMailPassword ? 'Saved securely — enter only to change' : 'Enter SMTP password' }}">
                        <button type="button" class="btn btn-outline-secondary toggle-mail-password" title="Show or hide password"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <div class="form-text">{{ $hasSavedMailPassword ? 'A password is already saved and encrypted. Leave blank to keep it.' : 'The current environment password remains active if this is left blank.' }}</div>
                </div>
                <div class="col-md-4">
                    <label for="mail_encryption" class="form-label fw-bold">Encryption</label>
                    <select id="mail_encryption" name="mail_encryption" class="form-select rounded-3" required>
                        <option value="tls" @selected(old('mail_encryption', $mailEncryption) === 'tls')>TLS / STARTTLS</option>
                        <option value="ssl" @selected(old('mail_encryption', $mailEncryption) === 'ssl')>SSL</option>
                        <option value="none" @selected(old('mail_encryption', $mailEncryption) === 'none')>None</option>
                    </select>
                    <div class="form-text">Gmail automatically uses TLS on port 587 or SSL on port 465.</div>
                </div>
                <div class="col-md-8">
                    <label for="mail_from_name" class="form-label fw-bold">Sender Name</label>
                    <input type="text" id="mail_from_name" name="mail_from_name" class="form-control rounded-3" value="{{ old('mail_from_name', $mailFromName) }}" maxlength="255" required>
                </div>
            </div>
        </div>
    </div>

    <div class="card setting-card mb-4">
        <div class="card-header">
            <h5 class="fw-bold mb-1"><i class="fa-solid fa-credit-card text-warning me-2"></i> Payment Gateway Settings</h5>
            <div class="section-note">Manage Razorpay API credentials and financial calculation rates without editing the .env file.</div>
        </div>
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <h6 class="fw-bold mb-0">Razorpay API Credentials</h6>
                <div class="d-flex align-items-center gap-2">
                    @if(str_starts_with(old('razorpay_key', $razorpayKey) ?? '', 'rzp_live_'))
                        <span class="badge bg-success">LIVE MODE</span>
                    @else
                        <span class="badge bg-warning text-dark">TEST MODE</span>
                    @endif
                    <a href="{{ route('admin.payment-check.index') }}" class="btn btn-sm btn-outline-dark rounded-pill fw-bold">
                        <i class="fa-solid fa-circle-check me-1"></i> Test Payment
                    </a>
                </div>
            </div>

            <div class="alert alert-warning border-0 rounded-3 small">
                <i class="fa-solid fa-shield-halved me-1"></i> Secret Key is encrypted before saving and is never displayed again. Key ID and Secret must belong to the same Razorpay mode.
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="razorpay_key" class="form-label fw-bold">Razorpay Key ID</label>
                    <input type="text" id="razorpay_key" name="razorpay_key" class="form-control rounded-3 @error('razorpay_key') is-invalid @enderror" value="{{ old('razorpay_key', $razorpayKey) }}" placeholder="rzp_test_xxxxxxxxxx" autocomplete="off" required>
                    <div class="form-text">Accepts <code>rzp_test_...</code> or <code>rzp_live_...</code>.</div>
                </div>
                <div class="col-md-6">
                    <label for="razorpay_secret" class="form-label fw-bold">Razorpay Secret Key</label>
                    <div class="input-group">
                        <input type="password" id="razorpay_secret" name="razorpay_secret" class="form-control rounded-start-3" autocomplete="new-password" placeholder="{{ $hasSavedRazorpaySecret ? 'Saved securely — enter only to change' : 'Enter Razorpay Secret Key' }}">
                        <button type="button" class="btn btn-outline-secondary toggle-razorpay-secret" title="Show or hide secret"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <div class="form-text">{{ $hasSavedRazorpaySecret ? 'A secret is already encrypted and saved. Leave blank to keep it.' : 'If left blank, the current environment secret remains active until one is saved.' }}</div>
                </div>
            </div>

            <hr class="my-4">
            <h6 class="fw-bold mb-3">Gateway Fee & Tax Calculation</h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Razorpay Base Fee (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" max="100" name="razorpay_fee_percent" class="form-control rounded-start-3" value="{{ old('razorpay_fee_percent', $razorpayFeePercent) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">GST on Razorpay Fee (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" max="100" name="razorpay_gst_percent" class="form-control rounded-start-3" value="{{ old('razorpay_gst_percent', $razorpayGstPercent) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="form-check form-switch mt-4 p-3 ps-5 bg-light rounded-3 border">
                <input class="form-check-input" type="checkbox" name="recalculate_past_orders" id="recalculatePastOrders" value="1" @checked(old('recalculate_past_orders'))>
                <label class="form-check-label fw-bold" for="recalculatePastOrders">Recalculate previous online orders using these rates</label>
            </div>
        </div>
    </div>

    <div class="d-grid d-sm-flex justify-content-sm-end pb-4">
        <button type="submit" class="btn btn-warning rounded-pill px-5 py-2 fw-bold shadow-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
            <i class="fa-solid fa-floppy-disk me-1"></i> Save Master Settings
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script>
    function bindImagePreview(inputId, previewId) {
        document.getElementById(inputId)?.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (file) document.getElementById(previewId).src = URL.createObjectURL(file);
        });
    }

    bindImagePreview('site_logo', 'logoPreview');
    bindImagePreview('site_favicon', 'faviconPreview');

    document.querySelector('.toggle-mail-password')?.addEventListener('click', function () {
        const input = document.getElementById('mail_password');
        const icon = this.querySelector('i');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    document.querySelector('.toggle-razorpay-secret')?.addEventListener('click', function () {
        const input = document.getElementById('razorpay_secret');
        const icon = this.querySelector('i');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    const mailHost = document.getElementById('mail_host');
    const mailPort = document.getElementById('mail_port');
    const mailEncryption = document.getElementById('mail_encryption');

    function enforceGmailSecurity() {
        if (!mailHost.value.toLowerCase().includes('gmail.com')) return;
        mailEncryption.value = Number(mailPort.value) === 465 ? 'ssl' : 'tls';
    }

    mailHost?.addEventListener('change', enforceGmailSecurity);
    mailPort?.addEventListener('change', enforceGmailSecurity);
</script>
@endsection
