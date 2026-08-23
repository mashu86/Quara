<!-- Email OTP Verification Modal -->
<div class="modal fade" id="emailOtpModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="emailOtpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 bg-dark text-white p-4 position-relative">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-gold text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-serif fw-bold mb-1" id="emailOtpModalLabel">Email Verification</h5>
                        <span class="badge bg-gold text-dark small fw-bold"><i class="fa-solid fa-shield-halved me-1"></i> Quick 6-Digit OTP</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Guidance Info Card -->
                <div class="row g-2 mb-4">
                    <div class="col-12">
                        <div class="alert alert-warning border-0 rounded-3 small mb-0 shadow-sm" style="background-color: #FFFDF0; border-left: 4px solid #D4AF37 !important;">
                            <strong class="d-block text-dark fw-bold mb-1"><i class="fa-solid fa-circle-info me-1 text-warning"></i> Why Email Verification?</strong>
                            <span class="text-muted">Verifying your Email saves your delivery address and retrieves your complete <strong>My Orders</strong> history even if your session expires.</span>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Email Address Input -->
                <div id="emailOtpStep1">
                    <form id="sendEmailOtpForm" onsubmit="handleSendEmailOtp(event)">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-dark"><i class="fa-solid fa-at"></i></span>
                                <input type="email" id="emailInput" class="form-control form-control-lg rounded-end-3" placeholder="name@example.com" required value="{{ session('customer_email') }}">
                            </div>
                            <div class="form-text small">A 6-digit verification OTP will be sent to this email (valid for 60s).</div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" id="sendEmailOtpBtn" class="btn btn-dark rounded-pill py-3 fw-bold shadow-sm">
                                <i class="fa-solid fa-paper-plane text-gold me-2"></i> SEND 6-DIGIT OTP (60s)
                            </button>
                            
                            <div class="text-center my-1">
                                <span class="text-muted small fw-bold">&mdash; OR &mdash;</span>
                            </div>

                            <button type="button" class="btn btn-outline-secondary rounded-pill py-2 fw-semibold small" data-bs-dismiss="modal">
                                <i class="fa-solid fa-bolt text-warning me-1"></i> Continue as Guest (No OTP)
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Step 2: 6-Digit OTP Code Verification -->
                <div id="emailOtpStep2" style="display: none;">
                    <div class="text-center mb-3">
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small mb-2">
                            OTP Sent to: <strong id="sentEmailDisplay">example@domain.com</strong>
                        </span>
                        <p class="small text-muted mb-0">Enter the 6-digit OTP verification code sent to your inbox:</p>
                    </div>

                    <form id="verifyEmailOtpForm" onsubmit="handleVerifyEmailOtp(event)">
                        <div class="mb-3">
                            <input type="text" id="emailOtpCodeInput" class="form-control form-control-lg text-center fw-bold font-monospace tracking-widest rounded-3" placeholder="------" maxlength="6" pattern="[0-9]{6}" required style="font-size: 1.5rem; letter-spacing: 0.5rem;">
                        </div>

                        <!-- 60-Second Resend Countdown -->
                        <div class="text-center mb-3">
                            <span id="timerContainer" class="small text-muted">
                                <i class="fa-solid fa-clock text-warning me-1"></i> Resend OTP in: <strong id="resendCountdown" class="text-dark">60</strong>s
                            </span>
                            <button type="button" id="resendOtpBtn" class="btn btn-link text-decoration-none small p-0 fw-bold text-gold ms-2" onclick="handleSendEmailOtp(event)" style="display: none;">
                                <i class="fa-solid fa-rotate-right me-1"></i> Resend OTP Code
                            </button>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" id="verifyEmailOtpBtn" class="btn btn-warning rounded-pill py-3 fw-bold shadow-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                                VERIFY OTP & CONTINUE <i class="fa-solid fa-circle-check ms-2"></i>
                            </button>
                            <button type="button" class="btn btn-link text-muted btn-sm text-decoration-none" onclick="resetEmailOtpStep()">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Change Email Address
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Alert Notifications inside Modal -->
                <div id="emailOtpSuccessAlert" class="alert alert-success border-0 rounded-3 small mt-3 p-2 text-center" style="display: none;"></div>
                <div id="emailOtpErrorAlert" class="alert alert-danger border-0 rounded-3 small mt-3 p-2 text-center" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<script>
    let countdownInterval = null;

    function showOtpModal() {
        const modalEl = document.getElementById('emailOtpModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    function startResendTimer(durationInSeconds = 60) {
        let timer = durationInSeconds;
        const countdownEl = document.getElementById('resendCountdown');
        const timerContainer = document.getElementById('timerContainer');
        const resendBtn = document.getElementById('resendOtpBtn');

        if (timerContainer) timerContainer.style.display = 'inline-block';
        if (resendBtn) resendBtn.style.display = 'none';

        if (countdownInterval) clearInterval(countdownInterval);

        countdownInterval = setInterval(() => {
            timer--;
            if (countdownEl) countdownEl.innerText = timer;

            if (timer <= 0) {
                clearInterval(countdownInterval);
                if (timerContainer) timerContainer.style.display = 'none';
                if (resendBtn) resendBtn.style.display = 'inline-block';
            }
        }, 1000);
    }

    function handleSendEmailOtp(e) {
        if (e) e.preventDefault();
        const emailInput = document.getElementById('emailInput');
        const email = emailInput ? emailInput.value.trim() : '';
        const errorEl = document.getElementById('emailOtpErrorAlert');
        const successEl = document.getElementById('emailOtpSuccessAlert');

        if (errorEl) errorEl.style.display = 'none';
        if (successEl) successEl.style.display = 'none';

        if (!email || !email.includes('@')) {
            if (errorEl) {
                errorEl.innerText = 'Please enter a valid email address.';
                errorEl.style.display = 'block';
            }
            return;
        }

        const sendBtn = document.getElementById('sendEmailOtpBtn');
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Sending OTP...';
        }

        const sentEmailDisplay = document.getElementById('sentEmailDisplay');
        if (sentEmailDisplay) sentEmailDisplay.innerText = email;

        fetch("{{ route('email.send-otp') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ email: email })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('emailOtpStep1').style.display = 'none';
                document.getElementById('emailOtpStep2').style.display = 'block';

                if (successEl) {
                    let msg = data.message || 'OTP Code sent successfully!';
                    if (data.demo_otp) {
                        msg += ' [Code: ' + data.demo_otp + ']';
                    }
                    successEl.innerText = msg;
                    successEl.style.display = 'block';
                }

                startResendTimer(60);
            } else {
                if (errorEl) {
                    errorEl.innerText = data.message || 'Failed to send OTP code.';
                    errorEl.style.display = 'block';
                }
            }
        })
        .catch(err => {
            if (errorEl) {
                errorEl.innerText = 'Connection error. Please try again.';
                errorEl.style.display = 'block';
            }
        })
        .finally(() => {
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane text-gold me-2"></i> SEND 6-DIGIT OTP (60s)';
            }
        });
    }

    function handleVerifyEmailOtp(e) {
        if (e) e.preventDefault();
        const email = document.getElementById('emailInput').value.trim();
        const code = document.getElementById('emailOtpCodeInput').value.trim();
        const errorEl = document.getElementById('emailOtpErrorAlert');
        const successEl = document.getElementById('emailOtpSuccessAlert');

        if (errorEl) errorEl.style.display = 'none';
        if (successEl) successEl.style.display = 'none';

        if (code.length < 6) {
            if (errorEl) {
                errorEl.innerText = 'Please enter the complete 6-digit OTP code.';
                errorEl.style.display = 'block';
            }
            return;
        }

        const verifyBtn = document.getElementById('verifyEmailOtpBtn');
        if (verifyBtn) {
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Verifying...';
        }

        fetch("{{ route('email.verify-otp') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ email: email, code: code })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Close modal
                const modalEl = document.getElementById('emailOtpModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                // Auto-fill email field
                const emailInputOnPage = document.querySelector('input[name="customer_email"]');
                if (emailInputOnPage) emailInputOnPage.value = email;

                // Auto-fill past customer address details if present
                if (data.previous_details) {
                    const d = data.previous_details;
                    const nameInput = document.querySelector('input[name="customer_name"]');
                    const phoneInput = document.querySelector('input[name="customer_phone"]');
                    const houseInput = document.querySelector('input[name="house_building"]');
                    const streetInput = document.querySelector('input[name="street"]');
                    const areaInput = document.querySelector('input[name="area"]');
                    const cityInput = document.querySelector('input[name="city"]');
                    const districtInput = document.querySelector('input[name="district"]');
                    const pinInput = document.querySelector('input[name="pin_code"]');

                    if (nameInput && !nameInput.value) nameInput.value = d.customer_name || '';
                    if (phoneInput && !phoneInput.value) phoneInput.value = d.customer_phone || '';
                    if (houseInput && !houseInput.value) houseInput.value = d.house_building || '';
                    if (streetInput && !streetInput.value) streetInput.value = d.street || '';
                    if (areaInput && !areaInput.value) areaInput.value = d.area || '';
                    if (cityInput && !cityInput.value) cityInput.value = d.city || '';
                    if (districtInput && !districtInput.value) districtInput.value = d.district || '';
                    if (pinInput && !pinInput.value) pinInput.value = d.pin_code || '';
                }

                // Refresh page or redirect to my-orders / checkout
                if (window.location.pathname.includes('/checkout')) {
                    window.location.reload();
                } else if (window.location.pathname.includes('/my-orders')) {
                    window.location.reload();
                } else {
                    window.location.href = "{{ route('customer.my-orders') }}";
                }
            } else {
                if (errorEl) {
                    errorEl.innerText = data.message || 'OTP verification failed.';
                    errorEl.style.display = 'block';
                }
                if (verifyBtn) {
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = 'VERIFY OTP & CONTINUE <i class="fa-solid fa-circle-check ms-2"></i>';
                }
            }
        })
        .catch(err => {
            if (errorEl) {
                errorEl.innerText = 'Server connection error. Please try again.';
                errorEl.style.display = 'block';
            }
            if (verifyBtn) {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = 'VERIFY OTP & CONTINUE <i class="fa-solid fa-circle-check ms-2"></i>';
            }
        });
    }

    function resetEmailOtpStep() {
        document.getElementById('emailOtpStep2').style.display = 'none';
        document.getElementById('emailOtpStep1').style.display = 'block';
        const errorEl = document.getElementById('emailOtpErrorAlert');
        const successEl = document.getElementById('emailOtpSuccessAlert');
        if (errorEl) errorEl.style.display = 'none';
        if (successEl) successEl.style.display = 'none';
        if (countdownInterval) clearInterval(countdownInterval);
    }
</script>
