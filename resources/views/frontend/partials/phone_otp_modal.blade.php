<div class="modal fade" id="customerPhoneLoginModal" tabindex="-1" aria-labelledby="customerPhoneLoginTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm-custom" style="max-width: 440px;">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 bg-dark text-white p-3 p-md-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-gold text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;"><i class="fa-solid fa-mobile-screen-button"></i></div>
                    <div>
                        <h5 class="modal-title font-serif fw-bold mb-0" id="customerPhoneLoginTitle">Sign in with mobile</h5>
                        <span class="small text-white-50">We will send a one-time code by SMS.</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div id="customerPhoneStepNumber">
                    <form id="customerPhoneSendForm">
                        <label for="customerPhoneNumber" class="form-label fw-semibold">Indian mobile number</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text fw-bold">+91</span>
                            <input type="tel" id="customerPhoneNumber" class="form-control" inputmode="numeric" autocomplete="tel-national" maxlength="10" pattern="[6-9][0-9]{9}" placeholder="10-digit mobile number" required>
                        </div>
                        <div class="form-text">Standard SMS charges may apply.</div>
                        <div id="customerPhoneRecaptcha" class="mt-3"></div>
                        <button type="submit" id="customerPhoneSendButton" class="btn btn-dark rounded-pill w-100 py-2 mt-3 fw-bold">
                            Send OTP <i class="fa-solid fa-arrow-right ms-1"></i>
                        </button>
                    </form>
                </div>

                <div id="customerPhoneStepCode" class="d-none">
                    <p class="text-center small text-muted mb-3">Enter the six-digit code sent to <strong id="customerPhoneDestination"></strong>.</p>
                    <form id="customerPhoneVerifyForm">
                        <label for="customerPhoneCode" class="form-label fw-semibold">One-time code</label>
                        <input type="text" id="customerPhoneCode" class="form-control form-control-lg text-center fw-bold font-monospace rounded-3" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="------" required style="letter-spacing: .55rem;">
                        <button type="submit" id="customerPhoneVerifyButton" class="btn btn-warning rounded-pill w-100 py-2 mt-3 fw-bold">Verify OTP</button>
                    </form>
                    <div class="text-center mt-3 small">
                        <span id="customerPhoneCountdown" class="text-muted">Resend OTP in <strong>60</strong>s</span>
                        <button type="button" id="customerPhoneResendButton" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold d-none">Resend OTP</button>
                    </div>
                    <button type="button" id="customerPhoneChangeButton" class="btn btn-link text-muted btn-sm text-decoration-none w-100 mt-1">Change number</button>
                </div>

                <div id="customerPhoneAlert" class="alert d-none rounded-3 small mt-3 mb-0" role="alert" aria-live="polite"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://www.gstatic.com/firebasejs/13.1.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/13.1.0/firebase-auth-compat.js"></script>
<script>
(() => {
    const modal = document.getElementById('customerPhoneLoginModal');
    if (!modal) return;

    const config = @json(config('services.firebase.web'));
    const firebaseSdkAvailable = typeof firebase !== 'undefined';
    const firebaseConfigured = Boolean(firebaseSdkAvailable && config.apiKey && config.authDomain && config.projectId && config.appId);
    if (firebaseConfigured && !firebase.apps.length) firebase.initializeApp(config);
    const auth = firebaseConfigured ? firebase.auth() : null;
    if (auth) auth.useDeviceLanguage();

    const numberStep = document.getElementById('customerPhoneStepNumber');
    const codeStep = document.getElementById('customerPhoneStepCode');
    const phoneInput = document.getElementById('customerPhoneNumber');
    const codeInput = document.getElementById('customerPhoneCode');
    const sendButton = document.getElementById('customerPhoneSendButton');
    const verifyButton = document.getElementById('customerPhoneVerifyButton');
    const resendButton = document.getElementById('customerPhoneResendButton');
    const countdown = document.getElementById('customerPhoneCountdown');
    const alertBox = document.getElementById('customerPhoneAlert');
    let confirmationResult = null;
    let signedInFirebaseUser = null;
    let recaptchaVerifier = null;
    let resendTimer = null;
    let secondsLeft = 0;
    let sendingOtp = false;

    function showMessage(message, type = 'danger') {
        alertBox.textContent = message;
        alertBox.className = `alert alert-${type} rounded-3 small mt-3 mb-0`;
    }

    function clearMessage() {
        alertBox.textContent = '';
        alertBox.className = 'alert d-none rounded-3 small mt-3 mb-0';
    }

    function resetRecaptcha() {
        if (recaptchaVerifier) {
            recaptchaVerifier.clear();
            recaptchaVerifier = null;
        }
    }

    function startResendCountdown() {
        clearInterval(resendTimer);
        secondsLeft = 60;
        resendButton.classList.add('d-none');
        countdown.classList.remove('d-none');
        countdown.innerHTML = `Resend OTP in <strong>${secondsLeft}</strong>s`;
        resendTimer = setInterval(() => {
            secondsLeft -= 1;
            countdown.innerHTML = `Resend OTP in <strong>${secondsLeft}</strong>s`;
            if (secondsLeft <= 0) {
                clearInterval(resendTimer);
                countdown.classList.add('d-none');
                resendButton.classList.remove('d-none');
            }
        }, 1000);
    }

    function firebaseErrorMessage(error) {
        const messages = {
            'auth/invalid-phone-number': 'Enter a valid 10-digit Indian mobile number.',
            'auth/too-many-requests': 'Too many attempts. Please wait before trying again.',
            'auth/quota-exceeded': 'SMS sending limit reached. Please try again later.',
            'auth/captcha-check-failed': 'Security check failed. Please try again.',
            'auth/invalid-verification-code': 'That OTP is incorrect. Check the SMS and try again.',
            'auth/code-expired': 'That OTP has expired. Request a new code.',
            'auth/session-expired': 'The verification session expired. Request a new code.',
            'auth/network-request-failed': 'Network error. Check your connection and try again.'
        };
        return messages[error?.code] || 'Phone verification failed. Please try again.';
    }

    async function sendOtp(event) {
        if (event) event.preventDefault();
        if (sendingOtp) return;
        clearMessage();
        const phone = phoneInput.value.replace(/\D/g, '');
        if (!/^[6-9][0-9]{9}$/.test(phone)) {
            showMessage('Enter a valid 10-digit Indian mobile number.');
            phoneInput.focus();
            return;
        }
        if (!auth) {
            showMessage('Phone sign-in is not configured yet. Please contact the store.');
            return;
        }

        sendingOtp = true;
        sendButton.disabled = true;
        resendButton.disabled = true;
        sendButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Sending OTP…';
        try {
            if (!recaptchaVerifier) {
                recaptchaVerifier = new firebase.auth.RecaptchaVerifier('customerPhoneRecaptcha', { size: 'invisible' });
            }
            confirmationResult = await auth.signInWithPhoneNumber(`+91${phone}`, recaptchaVerifier);
            document.getElementById('customerPhoneDestination').textContent = `+91 ${phone}`;
            numberStep.classList.add('d-none');
            codeStep.classList.remove('d-none');
            codeInput.value = '';
            codeInput.focus();
            startResendCountdown();
            showMessage('OTP sent. Check your SMS messages.', 'success');
        } catch (error) {
            resetRecaptcha();
            showMessage(firebaseErrorMessage(error));
        } finally {
            sendButton.disabled = false;
            resendButton.disabled = false;
            sendingOtp = false;
            sendButton.innerHTML = 'Send OTP <i class="fa-solid fa-arrow-right ms-1"></i>';
        }
    }

    async function verifyOtp(event) {
        event.preventDefault();
        clearMessage();
        const code = codeInput.value.trim();
        if (!/^[0-9]{6}$/.test(code)) {
            showMessage('Enter the complete six-digit OTP.');
            codeInput.focus();
            return;
        }
        verifyButton.disabled = true;
        verifyButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Verifying…';
        try {
            if (!signedInFirebaseUser) {
                const credential = await confirmationResult.confirm(code);
                signedInFirebaseUser = credential.user;
            }
            const idToken = await signedInFirebaseUser.getIdToken(true);
            const response = await fetch(@json(route('customer.phone-login')), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    id_token: idToken,
                    return_to: document.querySelector('[data-customer-login]')?.dataset.returnTo || 'my-orders'
                })
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                if (response.status === 401 || response.status === 404 || response.status === 409) {
                    await auth.signOut();
                    signedInFirebaseUser = null;
                }
                throw new Error(data.message || 'Could not sign in. Please try again.');
            }
            await auth.signOut();
            window.location.assign(data.redirect_url);
        } catch (error) {
            showMessage(error.code ? firebaseErrorMessage(error) : (error.message || 'Could not sign in. Please try again.'));
        } finally {
            verifyButton.disabled = false;
            verifyButton.textContent = 'Verify OTP';
        }
    }

    async function changeNumber() {
        clearInterval(resendTimer);
        clearMessage();
        if (auth && signedInFirebaseUser) await auth.signOut().catch(() => {});
        signedInFirebaseUser = null;
        confirmationResult = null;
        codeStep.classList.add('d-none');
        numberStep.classList.remove('d-none');
        codeInput.value = '';
        resetRecaptcha();
        phoneInput.focus();
    }

    window.showCustomerLoginModal = function (button) {
        const returnTo = button?.dataset.returnTo || 'my-orders';
        modal.querySelectorAll('[data-customer-login]').forEach(element => element.dataset.returnTo = returnTo);
        bootstrap.Modal.getOrCreateInstance(modal).show();
    };

    document.getElementById('customerPhoneSendForm').addEventListener('submit', sendOtp);
    document.getElementById('customerPhoneVerifyForm').addEventListener('submit', verifyOtp);
    resendButton.addEventListener('click', sendOtp);
    document.getElementById('customerPhoneChangeButton').addEventListener('click', changeNumber);
    modal.addEventListener('hidden.bs.modal', changeNumber);
})();
</script>
