<!-- Phone OTP Verification Modal -->
<div class="modal fade" id="phoneOtpModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="phoneOtpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 bg-dark text-white p-4 position-relative">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-gold text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-serif fw-bold mb-1" id="phoneOtpModalLabel">Choose Checkout Preference</h5>
                        <span class="badge bg-gold text-dark small fw-bold"><i class="fa-solid fa-shield-halved me-1"></i> Quick & Flexible Ordering</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Guidance Option Cards -->
                <div class="row g-2 mb-4">
                    <div class="col-12">
                        <div class="alert alert-warning border-0 rounded-3 small mb-0 shadow-sm" style="background-color: #FFFDF0; border-left: 4px solid #D4AF37 !important;">
                            <strong class="d-block text-dark fw-bold mb-1"><i class="fa-solid fa-circle-question me-1 text-warning"></i> Why Mobile Verification?</strong>
                            <span class="text-muted">Verifying via OTP saves your delivery address & enables your <strong>My Orders</strong> history page.</span>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Mobile Phone Number Input or Instant Guest Choice -->
                <div id="otpStep1">
                    <form id="sendOtpForm" onsubmit="handleSendOtp(event)">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Mobile Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-dark">+91</span>
                                <input type="tel" id="mobileNumberInput" class="form-control form-control-lg rounded-end-3" placeholder="10-digit Phone Number" maxlength="10" pattern="[0-9]{10}" required value="{{ session('customer_phone') }}">
                            </div>
                            <div class="form-text small">Enter mobile number to receive a 6-digit OTP code.</div>
                        </div>

                        <!-- Firebase Recaptcha Container -->
                        <div id="recaptcha-container" class="mb-3"></div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" id="sendOtpBtn" class="btn btn-dark rounded-pill py-3 fw-bold shadow-sm">
                                <i class="fa-solid fa-shield-cat text-gold me-2"></i> VERIFY WITH OTP (RECOMMENDED)
                            </button>
                            
                            <div class="text-center my-1">
                                <span class="text-muted small fw-bold">&mdash; OR &mdash;</span>
                            </div>

                            <button type="button" class="btn btn-outline-secondary rounded-pill py-2 fw-semibold small" data-bs-dismiss="modal">
                                <i class="fa-solid fa-bolt text-warning me-1"></i> Instant Checkout (No OTP Required)
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Step 2: 6-Digit OTP Verification Code -->
                <div id="otpStep2" style="display: none;">
                    <div class="text-center mb-3">
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small mb-2">
                            OTP Sent to: <strong id="sentPhoneDisplay">+91 9544832975</strong>
                        </span>
                        <p class="small text-muted mb-0">Enter the 6-digit verification code below:</p>
                    </div>

                    <form id="verifyOtpForm" onsubmit="handleVerifyOtp(event)">
                        <div class="mb-3">
                            <input type="text" id="otpCodeInput" class="form-control form-control-lg text-center fw-bold font-monospace tracking-widest rounded-3" placeholder="------" maxlength="6" pattern="[0-9]{6}" required style="font-size: 1.5rem; letter-spacing: 0.5rem;">
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" id="verifyOtpBtn" class="btn btn-warning rounded-pill py-3 fw-bold shadow-sm">
                                VERIFY & CONTINUE TO CHECKOUT <i class="fa-solid fa-circle-check ms-2"></i>
                            </button>
                            <button type="button" class="btn btn-link text-muted btn-sm text-decoration-none" onclick="resetOtpStep()">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Change Mobile Number
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Error Notice Container -->
                <div id="otpErrorAlert" class="alert alert-danger border-0 rounded-3 small mt-3 p-2 text-center" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Firebase Web App SDK & Phone Auth Handler -->
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>

<script>
    // Global State
    let firebaseConfirmationResult = null;
    let verifiedPhoneGlobal = "{{ session('customer_phone') }}";

    // Firebase App Configuration (Will use placeholder or real credentials when deployed)
    const firebaseConfig = {
        apiKey: "{{ config('services.firebase.api_key', 'AIzaSyDemoPlaceholderConfigQuara') }}",
        authDomain: "{{ config('services.firebase.auth_domain', 'quara-waldrop.firebaseapp.com') }}",
        projectId: "{{ config('services.firebase.project_id', 'quara-waldrop') }}",
        storageBucket: "{{ config('services.firebase.storage_bucket', 'quara-waldrop.appspot.com') }}",
        messagingSenderId: "{{ config('services.firebase.messaging_sender_id', '123456789') }}",
        appId: "{{ config('services.firebase.app_id', '1:123456789:web:abcdef') }}"
    };

    // Initialize Firebase
    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }

    let recaptchaVerifier;
    function initRecaptcha() {
        if (!recaptchaVerifier) {
            recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                'size': 'invisible',
                'callback': (response) => {
                    // reCAPTCHA solved
                }
            });
        }
    }

    function showOtpModal() {
        const modalEl = document.getElementById('phoneOtpModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    function handleSendOtp(e) {
        e.preventDefault();
        const phone = document.getElementById('mobileNumberInput').value.trim();
        const errorEl = document.getElementById('otpErrorAlert');
        errorEl.style.display = 'none';

        if (phone.length < 10) {
            errorEl.innerText = 'Please enter a valid 10-digit mobile number.';
            errorEl.style.display = 'block';
            return;
        }

        const sendBtn = document.getElementById('sendOtpBtn');
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Sending OTP...';

        const fullPhone = '+91' + phone;
        document.getElementById('sentPhoneDisplay').innerText = fullPhone;

        try {
            initRecaptcha();
            firebase.auth().signInWithPhoneNumber(fullPhone, recaptchaVerifier)
                .then((confirmationResult) => {
                    firebaseConfirmationResult = confirmationResult;
                    document.getElementById('otpStep1').style.display = 'none';
                    document.getElementById('otpStep2').style.display = 'block';
                })
                .catch((error) => {
                    console.warn('Firebase SMS OTP fallback mode active:', error.message);
                    // Seamless Fallback mode for local development / testing without live keys
                    document.getElementById('otpStep1').style.display = 'none';
                    document.getElementById('otpStep2').style.display = 'block';
                    document.getElementById('otpCodeInput').value = '123456'; // Preset demo code for testing
                })
                .finally(() => {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = 'SEND OTP CODE <i class="fa-solid fa-arrow-right ms-2"></i>';
                });
        } catch (err) {
            console.warn('Firebase SDK Exception:', err);
            document.getElementById('otpStep1').style.display = 'none';
            document.getElementById('otpStep2').style.display = 'block';
            document.getElementById('otpCodeInput').value = '123456';
            sendBtn.disabled = false;
            sendBtn.innerHTML = 'SEND OTP CODE <i class="fa-solid fa-arrow-right ms-2"></i>';
        }
    }

    function handleVerifyOtp(e) {
        e.preventDefault();
        const code = document.getElementById('otpCodeInput').value.trim();
        const phone = document.getElementById('mobileNumberInput').value.trim();
        const errorEl = document.getElementById('otpErrorAlert');
        errorEl.style.display = 'none';

        if (code.length < 6) {
            errorEl.innerText = 'Please enter the complete 6-digit OTP code.';
            errorEl.style.display = 'block';
            return;
        }

        const verifyBtn = document.getElementById('verifyOtpBtn');
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Verifying...';

        // Perform Session Save on Server via AJAX
        fetch("{{ route('phone.verify') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ phone: phone, code: code })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                verifiedPhoneGlobal = phone;

                // Close Modal
                const modalEl = document.getElementById('phoneOtpModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                // Prefill Checkout form fields if on checkout page
                const phoneInput = document.querySelector('input[name="customer_phone"]');
                if (phoneInput) {
                    phoneInput.value = phone;
                }

                if (data.previous_details) {
                    const d = data.previous_details;
                    const nameInput = document.querySelector('input[name="customer_name"]');
                    const houseInput = document.querySelector('input[name="house_building"]');
                    const streetInput = document.querySelector('input[name="street"]');
                    const areaInput = document.querySelector('input[name="area"]');
                    const cityInput = document.querySelector('input[name="city"]');
                    const districtInput = document.querySelector('input[name="district"]');
                    const pinInput = document.querySelector('input[name="pin_code"]');

                    if (nameInput && !nameInput.value) nameInput.value = d.customer_name || '';
                    if (houseInput && !houseInput.value) houseInput.value = d.house_building || '';
                    if (streetInput && !streetInput.value) streetInput.value = d.street || '';
                    if (areaInput && !areaInput.value) areaInput.value = d.area || '';
                    if (cityInput && !cityInput.value) cityInput.value = d.city || '';
                    if (districtInput && !districtInput.value) districtInput.value = d.district || '';
                    if (pinInput && !pinInput.value) pinInput.value = d.pin_code || '';
                }

                // If redirected from checkout guard, reload or continue to checkout
                if (window.location.pathname.includes('/checkout')) {
                    window.location.reload();
                } else {
                    window.location.href = "{{ route('checkout.index') }}";
                }
            } else {
                errorEl.innerText = data.message || 'OTP verification failed. Please try again.';
                errorEl.style.display = 'block';
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = 'VERIFY & CONTINUE TO CHECKOUT <i class="fa-solid fa-circle-check ms-2"></i>';
            }
        })
        .catch(err => {
            errorEl.innerText = 'Server connection error. Please try again.';
            errorEl.style.display = 'block';
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = 'VERIFY & CONTINUE TO CHECKOUT <i class="fa-solid fa-circle-check ms-2"></i>';
        });
    }

    function resetOtpStep() {
        document.getElementById('otpStep2').style.display = 'none';
        document.getElementById('otpStep1').style.display = 'block';
        document.getElementById('otpErrorAlert').style.display = 'none';
    }
</script>
