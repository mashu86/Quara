@extends('layouts.admin')

@section('title', 'Razorpay Payment Check - ' . $siteName . ' Admin')

@section('styles')
<style>
    .payment-check-card { border: 0; border-radius: 1rem; box-shadow: 0 4px 18px rgba(20, 20, 25, .07); }
    .payment-icon { width: 72px; height: 72px; border-radius: 50%; display: grid; place-items: center; background: rgba(212, 175, 55, .16); color: #996515; font-size: 1.8rem; }
    .credential-row { background: #f8f9fa; border: 1px solid #ececf1; border-radius: .75rem; }
    .test-amount { font-size: 1.65rem; font-weight: 700; }
</style>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-credit-card text-warning me-2"></i> Razorpay Payment Check</h3>
        <p class="text-muted small mb-0">Create a small payment to confirm the credentials saved in Master Settings.</p>
    </div>
    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-dark rounded-pill px-3 fw-bold">
        <i class="fa-solid fa-gear me-1"></i> Razorpay Settings
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-xl-7 col-lg-9">
        <div class="card payment-check-card">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex flex-column align-items-center text-center mb-4">
                    <div class="payment-icon mb-3"><i class="fa-solid fa-shield-halved"></i></div>
                    <h4 class="fw-bold mb-2">Test Razorpay Checkout</h4>
                    <p class="text-muted small mb-0">A real Razorpay checkout opens and the server verifies both the callback signature and final payment status.</p>
                </div>

                <div class="credential-row p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                        <span class="text-muted small">Configuration</span>
                        @if($isConfigured)
                            <span class="badge bg-success">READY</span>
                        @else
                            <span class="badge bg-danger">NOT CONFIGURED</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                        <span class="text-muted small">Mode</span>
                        <span class="badge {{ $razorpayMode === 'live' ? 'bg-success' : 'bg-warning text-dark' }}">{{ strtoupper($razorpayMode) }} MODE</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <span class="text-muted small">Key ID</span>
                        <code class="text-dark">{{ $maskedKey }}</code>
                    </div>
                </div>

                @if($razorpayMode === 'live')
                    <div class="alert alert-danger rounded-3 small">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        LIVE MODE is active. This test will make an actual ₹1 charge using the selected payment method.
                    </div>
                @else
                    <div class="alert alert-info rounded-3 small">
                        <i class="fa-solid fa-flask me-1"></i>
                        TEST MODE is active. Use Razorpay test payment details; no real money will be charged.
                    </div>
                @endif

                <form id="paymentCheckForm">
                    <label for="testAmount" class="form-label fw-bold">Test Amount</label>
                    <div class="input-group mb-2">
                        <span class="input-group-text test-amount">₹</span>
                        <input type="number" id="testAmount" class="form-control test-amount" value="1" min="1" max="100" step="1" required>
                    </div>
                    <div class="form-text mb-4">₹1 is selected by default. Maximum allowed test amount is ₹100.</div>

                    <button type="submit" id="payButton" class="btn btn-warning rounded-pill w-100 py-3 fw-bold shadow-sm" @disabled(!$isConfigured)>
                        <i class="fa-solid fa-lock me-1"></i> PAY ₹1 & CHECK RAZORPAY
                    </button>
                </form>

                <div id="paymentResult" class="alert mt-4 mb-0 d-none" role="alert"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    const paymentForm = document.getElementById('paymentCheckForm');
    const amountInput = document.getElementById('testAmount');
    const payButton = document.getElementById('payButton');
    const resultBox = document.getElementById('paymentResult');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const defaultButtonHtml = payButton?.innerHTML;

    function showResult(type, message, details = '') {
        resultBox.className = `alert alert-${type} mt-4 mb-0`;
        resultBox.replaceChildren();

        const messageElement = document.createElement('div');
        messageElement.className = 'fw-bold';
        messageElement.textContent = message;
        resultBox.appendChild(messageElement);

        if (details) {
            const detailElement = document.createElement('div');
            detailElement.className = 'small mt-1 font-monospace';
            detailElement.textContent = details;
            resultBox.appendChild(detailElement);
        }
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload),
        });
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
            throw new Error(validationMessage || data.message || 'The request could not be completed.');
        }

        return data;
    }

    amountInput?.addEventListener('input', function () {
        const amount = Number(this.value) || 1;
        payButton.innerHTML = `<i class="fa-solid fa-lock me-1"></i> PAY ₹${amount} & CHECK RAZORPAY`;
    });

    paymentForm?.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (typeof Razorpay === 'undefined') {
            showResult('danger', 'Razorpay checkout could not load. Check the browser internet connection and reload the page.');
            return;
        }

        payButton.disabled = true;
        payButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> CONNECTING TO RAZORPAY...';
        resultBox.classList.add('d-none');

        try {
            const order = await postJson(@js(route('admin.payment-check.order')), {
                amount: amountInput.value,
            });

            const checkout = new Razorpay({
                key: order.key,
                amount: order.amount,
                currency: order.currency,
                order_id: order.order_id,
                name: @js($siteName),
                description: 'Admin Razorpay configuration check',
                image: ['localhost', '127.0.0.1'].includes(window.location.hostname) ? '' : @js($siteLogoUrl),
                prefill: {
                    name: @js(auth()->user()->name),
                    email: @js(auth()->user()->email),
                },
                theme: { color: '#D4AF37' },
                handler: async function (response) {
                    showResult('info', 'Payment received. Verifying it with Razorpay...');

                    try {
                        const result = await postJson(@js(route('admin.payment-check.verify')), response);
                        showResult('success', result.message, `Payment ID: ${result.payment_id} · Status: ${result.status}`);
                    } catch (error) {
                        showResult('danger', error.message);
                    } finally {
                        payButton.disabled = false;
                        payButton.innerHTML = defaultButtonHtml;
                        amountInput.dispatchEvent(new Event('input'));
                    }
                },
                modal: {
                    ondismiss: function () {
                        payButton.disabled = false;
                        payButton.innerHTML = defaultButtonHtml;
                        amountInput.dispatchEvent(new Event('input'));
                        showResult('secondary', 'Payment window was closed before completing the test.');
                    },
                },
            });

            checkout.on('payment.failed', function (response) {
                const error = response.error || {};
                showResult('danger', error.description || 'The test payment failed.', error.reason ? `Reason: ${error.reason}` : '');
                payButton.disabled = false;
                amountInput.dispatchEvent(new Event('input'));
            });

            checkout.open();
        } catch (error) {
            showResult('danger', error.message);
            payButton.disabled = false;
            payButton.innerHTML = defaultButtonHtml;
            amountInput.dispatchEvent(new Event('input'));
        }
    });
</script>
@endsection
