<style>
    #customerAuthModal .modal-content { border: 1px solid var(--qw-gold) !important; }
    #customerAuthModal .modal-header { background: linear-gradient(135deg, #111 0%, #2a2108 55%, #111 100%); border-bottom: 2px solid var(--qw-gold) !important; }
    #customerAuthModal .auth-mark { width: 38px; height: 38px; color: #17130a; background: var(--qw-gold); }
    #customerAuthModal .form-control:focus { border-color: var(--qw-gold); box-shadow: 0 0 0 .2rem rgba(212, 175, 55, .2); }
    #customerAuthModal .btn-link { color: var(--qw-gold-dark, #996515); }
    #customerAuthModal .btn-link:hover { color: var(--qw-gold, #D4AF37); }
    #customerAuthModal .qw-auth-primary { background: var(--qw-gold, #D4AF37); border-color: var(--qw-gold, #D4AF37); color: #17130a; }
    #customerAuthModal .qw-auth-primary:hover, #customerAuthModal .qw-auth-primary:focus { background: var(--qw-gold-dark, #996515); border-color: var(--qw-gold-dark, #996515); color: #fff; }
</style>
<div class="modal fade" id="customerAuthModal" tabindex="-1" aria-labelledby="customerAuthTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white border-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="auth-mark rounded-circle d-flex align-items-center justify-content-center"><i class="fa-regular fa-user"></i></div>
                    <div>
                        <h5 class="modal-title fw-bold" id="customerAuthTitle">Sign in</h5>
                        <div class="small text-white-50">Use your registered email and mobile number.</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="customerAuthDetailsStep">
                    <div id="customerAuthNameGroup" class="mb-3 d-none">
                        <label for="customerAuthName" class="form-label">Username</label>
                        <input id="customerAuthName" class="form-control" minlength="3" maxlength="50" pattern="[A-Za-z0-9._\-]+" autocomplete="username">
                    </div>
                    <div class="mb-3">
                        <label for="customerAuthEmail" class="form-label">Email Address</label>
                        <input id="customerAuthEmail" type="email" class="form-control" maxlength="255" autocomplete="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="customerAuthPhone" class="form-label">Mobile Number</label>
                        <input id="customerAuthPhone" type="tel" class="form-control" maxlength="20" inputmode="tel" autocomplete="tel" placeholder="10-digit mobile number" required>
                    </div>
                    <button id="customerAuthSendButton" type="button" class="btn qw-auth-primary rounded-pill w-100 fw-bold">Send Email OTP</button>
                    <div class="text-center mt-3 small" id="customerAuthSwitchText">Not yet registered?
                        <button type="button" class="btn btn-link p-0 fw-bold" id="customerAuthSwitch">Register</button>
                    </div>
                </div>
                <div id="customerAuthOtpStep" class="d-none">
                    <p class="small text-muted">Enter the 6-digit code sent to <strong id="customerAuthSentEmail"></strong>.</p>
                    <label for="customerAuthCode" class="form-label">Email OTP</label>
                    <input id="customerAuthCode" type="text" class="form-control text-center font-monospace fw-bold" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code">
                    <button id="customerAuthVerifyButton" type="button" class="btn qw-auth-primary rounded-pill w-100 fw-bold mt-3">Verify &amp; Continue</button>
                    <button id="customerAuthResendButton" type="button" class="btn btn-link w-100 mt-2">Resend code</button>
                    <button id="customerAuthBackButton" type="button" class="btn btn-link w-100 text-muted mt-2">Change details</button>
                </div>
                <div id="customerAuthMessage" class="alert d-none mt-3 mb-0 small" role="status" aria-live="polite"></div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const modalElement = document.getElementById('customerAuthModal');
    if (!modalElement) return;

    const detailsStep = document.getElementById('customerAuthDetailsStep');
    const otpStep = document.getElementById('customerAuthOtpStep');
    const nameGroup = document.getElementById('customerAuthNameGroup');
    const title = document.getElementById('customerAuthTitle');
    const switchText = document.getElementById('customerAuthSwitchText');
    const switchButton = document.getElementById('customerAuthSwitch');
    const message = document.getElementById('customerAuthMessage');
    const emailInput = document.getElementById('customerAuthEmail');
    const phoneInput = document.getElementById('customerAuthPhone');
    const usernameInput = document.getElementById('customerAuthName');
    const codeInput = document.getElementById('customerAuthCode');
    let flow = 'login';

    function showMessage(text, isError = false) {
        message.textContent = text;
        message.className = `alert mt-3 mb-0 small ${isError ? 'alert-danger' : 'alert-info'}`;
    }

    function setFlow(nextFlow) {
        flow = nextFlow;
        const registering = flow === 'register';
        title.textContent = registering ? 'Create Account' : 'Sign in';
        nameGroup.classList.toggle('d-none', !registering);
        usernameInput.required = registering;
        switchText.innerHTML = registering ? 'Already registered? ' : 'Not yet registered? ';
        switchText.appendChild(switchButton);
        switchButton.textContent = registering ? 'Sign in' : 'Register';
        message.classList.add('d-none');
    }

    switchButton.addEventListener('click', () => setFlow(flow === 'login' ? 'register' : 'login'));
    document.getElementById('customerAuthBackButton').addEventListener('click', () => {
        otpStep.classList.add('d-none');
        detailsStep.classList.remove('d-none');
        message.classList.add('d-none');
    });

    async function sendOtp(event) {
        const email = emailInput.value.trim();
        const phone = phoneInput.value.trim();
        if (!emailInput.reportValidity() || !phoneInput.reportValidity() || (flow === 'register' && !usernameInput.reportValidity())) return;

        const button = event.currentTarget;
        const originalLabel = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Sending email...';
        message.classList.add('d-none');
        try {
            const response = await fetch(@json(route('customer.auth.send-otp')), {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json'},
            body: JSON.stringify({flow, email, phone, username: usernameInput.value.trim()})
            });
            const data = await response.json();
            if (data.not_registered) {
                setFlow('register');
                detailsStep.classList.remove('d-none');
                otpStep.classList.add('d-none');
                showMessage(data.message || 'This email is not registered yet. Create an account to continue.', true);
                usernameInput.focus();
                return;
            }
            if (!response.ok || !data.success) {
                showMessage(data.message || 'Could not send the code. Please try again.', true);
                return;
            }
            document.getElementById('customerAuthSentEmail').textContent = email;
            detailsStep.classList.add('d-none');
            otpStep.classList.remove('d-none');
            codeInput.value = '';
            codeInput.focus();
            showMessage(data.message || 'Check your email for the verification code.');
        } catch (error) {
            showMessage('Connection error. Please try again.', true);
        } finally {
            button.innerHTML = originalLabel;
            button.disabled = false;
        }
    }
    document.getElementById('customerAuthSendButton').addEventListener('click', sendOtp);
    document.getElementById('customerAuthResendButton').addEventListener('click', sendOtp);

    document.getElementById('customerAuthVerifyButton').addEventListener('click', async () => {
        const button = document.getElementById('customerAuthVerifyButton');
        button.disabled = true;
        try {
            const response = await fetch(@json(route('customer.auth.verify-otp')), {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json'},
                body: JSON.stringify({flow, email: emailInput.value.trim(), phone: phoneInput.value.trim(), code: codeInput.value.trim()})
            });
            const data = await response.json();
            if (data.already_registered) {
                setFlow('login');
                detailsStep.classList.remove('d-none');
                otpStep.classList.add('d-none');
                showMessage(data.message, true);
                return;
            }
            if (data.username_taken) {
                otpStep.classList.add('d-none');
                detailsStep.classList.remove('d-none');
                showMessage(data.message, true);
                return;
            }
            if (!response.ok || !data.success) {
                showMessage(data.message || 'The code is invalid or expired.', true);
                return;
            }
            window.location.assign(data.redirect || @json(route('customer.my-orders')));
        } catch (error) {
            showMessage('Connection error. Please try again.', true);
        } finally {
            button.disabled = false;
        }
    });

    window.showCustomerLoginModal = function () {
        setFlow('login');
        detailsStep.classList.remove('d-none');
        otpStep.classList.add('d-none');
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    };

    document.querySelectorAll('[data-customer-login]').forEach(button => {
        button.addEventListener('click', window.showCustomerLoginModal);
    });

    modalElement.addEventListener('hidden.bs.modal', () => {
        detailsStep.classList.remove('d-none');
        otpStep.classList.add('d-none');
        codeInput.value = '';
        message.classList.add('d-none');
    });
})();
</script>
