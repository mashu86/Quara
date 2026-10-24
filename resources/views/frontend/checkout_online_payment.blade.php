@extends('layouts.app')

@section('title', 'Complete Online Payment - ' . $siteName)

@section('content')
<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="bg-white p-5 rounded-4 shadow-sm border">
                <i class="fa-solid fa-credit-card text-gold display-3 mb-3"></i>
                <h3 class="font-serif fw-bold mb-2">Complete Online Payment</h3>
                <div class="alert alert-info text-start small mb-4">
                    Please wait until the payment process is complete. Do not close this page.
                </div>
                <div id="payment-status" class="alert alert-warning text-start mb-4" role="status" aria-live="polite" hidden>
                    <div class="d-flex align-items-center gap-3">
                        <div class="spinner-border spinner-border-sm" aria-hidden="true"></div>
                        <div>
                            <div class="fw-bold" id="payment-status-title">Confirming payment</div>
                            <div id="payment-status-message" class="small">Please wait while we confirm your payment. Do not refresh, close or go back from this page. This can take up to 5 minutes.</div>
                            <div class="fw-bold mt-2" id="payment-countdown" aria-label="Time remaining">05:00</div>
                        </div>
                    </div>
                </div>
                <p class="text-muted mb-4">Please click the button below if the Razorpay payment window does not open automatically.</p>

                <div class="card bg-light border-0 rounded-3 p-3 mb-4 text-start">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Order Number:</span>
                        <strong class="text-gold">{{ $order->order_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Customer Name:</span>
                        <strong>{{ $order->customer_name }}</strong>
                    </div>
                    @include('partials.district_offer_summary')
                    <div class="d-flex justify-content-between fs-5 fw-bold mt-2 pt-2 border-top">
                        <span>Total Amount:</span>
                        <span class="text-gold">₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>
                </div>

                @if(str_starts_with($paymentResult['razorpay_order_id'] ?? '', 'order_mock_local_') && \App\Services\PaymentService::localTestPaymentsAllowed())
                    <div class="alert alert-info border-0 rounded-3 p-3 mb-4 text-start">
                        <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-flask text-primary me-2"></i> Local Development Test Mode</div>
                        <div class="small text-muted mb-3">Razorpay Live API is bypassed in local environment so you can test checkout & orders safely.</div>
                        <button type="button" onclick="submitMockLocalPayment()" class="btn btn-primary rounded-pill w-100 fw-bold py-2 shadow-sm">
                            <i class="fa-solid fa-circle-check me-2"></i> COMPLETE LOCAL TEST PAYMENT (₹{{ number_format($order->grand_total, 2) }})
                        </button>
                    </div>
                @else
                    <button id="rzp-button" class="btn btn-qw-gold rounded-pill w-100 shadow-sm py-2.5 fw-bold d-flex align-items-center justify-content-center gap-2" style="font-size: 0.84rem; letter-spacing: 0.3px;">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>PAY NOW WITH RAZORPAY (₹{{ number_format($order->grand_total, 2) }})</span>
                    </button>
                @endif

                <!-- Hidden Verification Form -->
                <form action="{{ route('checkout.verify_online_payment') }}" method="POST" id="razorpayForm">
                    @csrf
                    <input type="hidden" name="order_number" value="{{ $order->order_number }}">
                    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                    <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
                    <input type="hidden" name="razorpay_signature" id="razorpay_signature">
                </form>
                <form action="{{ route('checkout.order.abandon', $order->order_number) }}" method="POST" id="abandonOrderForm">@csrf</form>
                <form action="{{ route('checkout.order.continue', $order->order_number) }}" method="POST" id="continueOrderForm">@csrf</form>
            </div>
        </div>
    </div>
</div>
@include('frontend.partials.sold_out_modal')
@endsection

@section('scripts')
@unless(str_starts_with($paymentResult['razorpay_order_id'] ?? '', 'order_mock_local_'))
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endunless
<script>
    const isLocalMockPayment = @json(str_starts_with($paymentResult['razorpay_order_id'] ?? '', 'order_mock_local_'));
    let paymentWaiting = false;
    let countdownInterval = null;

    function showPaymentStatus(title, message, showTimer = false) {
        const box = document.getElementById('payment-status');
        box.hidden = false;
        document.getElementById('payment-status-title').textContent = title;
        document.getElementById('payment-status-message').textContent = message;
        document.querySelector('#payment-status .spinner-border').hidden = !showTimer;
    }

    function startConfirmationWait() {
        if (paymentWaiting) return;
        paymentWaiting = true;
        document.getElementById('rzp-button')?.setAttribute('disabled', 'disabled');
        showPaymentStatus(
            'Confirming payment',
            'Please wait while we confirm your payment. Do not refresh, close or go back from this page. This can take up to 5 minutes.',
            true
        );

        let remaining = 5 * 60;
        const timer = document.getElementById('payment-countdown');
        countdownInterval = window.setInterval(() => {
            remaining -= 1;
            timer.textContent = `${String(Math.floor(remaining / 60)).padStart(2, '0')}:${String(remaining % 60).padStart(2, '0')}`;
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                showPaymentStatus(
                    'Confirmation is taking longer',
                    'Your payment is not confirmed yet. If money has left your account, please do not pay again. This page will show the result once it is confirmed.',
                    true
                );
                timer.textContent = '05:00 elapsed';
            }
        }, 1000);
    }

    // Unpaid orders hold no stock: confirm every item is still available before taking payment.
    async function whenItemsStillAvailable(pay) {
        let result;
        try {
            const response = await fetch(@json(route('checkout.order.stock-check', $order->order_number)), {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
            });
            if (!response.ok) throw new Error('Stock check failed');
            result = await response.json();
        } catch (e) {
            showPaymentStatus('Could not start payment', 'Please check your connection and try again.');
            return;
        }
        if (result.available) {
            pay();
            return;
        }
        window.showSoldOutModal(
            result.sold_items,
            () => document.getElementById('abandonOrderForm').submit(),
            result.remaining_count > 0 ? () => document.getElementById('continueOrderForm').submit() : null
        );
    }

    window.addEventListener('beforeunload', function (event) {
        if (!paymentWaiting) return;
        event.preventDefault();
        event.returnValue = '';
    });

    @if(str_starts_with($paymentResult['razorpay_order_id'] ?? '', 'order_mock_local_') && \App\Services\PaymentService::localTestPaymentsAllowed())
    {{-- Local test shortcut: only rendered on localhost in local test mode, never on the live site. --}}
    function submitMockLocalPayment() {
        whenItemsStillAvailable(completeMockLocalPayment);
    }

    function completeMockLocalPayment() {
        startConfirmationWait();
        document.getElementById('razorpay_payment_id').value = 'pay_mock_local_' + Date.now();
        document.getElementById('razorpay_order_id').value = '{{ $paymentResult["razorpay_order_id"] ?? "" }}';
        document.getElementById('razorpay_signature').value = 'mock_signature_local';
        paymentWaiting = false;
        document.getElementById('razorpayForm').submit();
    }
    @endif

    if (!isLocalMockPayment) {
    const options = {
        "key": "{{ $paymentResult['razorpay_key'] }}",
        "amount": "{{ $paymentResult['amount'] ?? ($order->grand_total * 100) }}",
        "currency": "INR",
        "name": @js($siteName),
        "description": "Order #{{ $order->order_number }} Payment",
        "image": (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') ? @js($siteLogoUrl) : "",
        @if(!empty($paymentResult['razorpay_order_id']) && str_starts_with($paymentResult['razorpay_order_id'], 'order_'))
        "order_id": "{{ $paymentResult['razorpay_order_id'] }}",
        @endif
        "handler": function (response){
            if (!response.razorpay_payment_id || !response.razorpay_signature) {
                showPaymentStatus('Could not confirm payment', 'We could not check your payment status. If money has left your account, please do not pay again.');
                return;
            }
            startConfirmationWait();
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id || '';
            document.getElementById('razorpay_order_id').value = response.razorpay_order_id || '{{ $paymentResult["razorpay_order_id"] ?? "" }}';
            document.getElementById('razorpay_signature').value = response.razorpay_signature || '';
            paymentWaiting = false;
            document.getElementById('razorpayForm').submit();
        },
        "modal": { "ondismiss": function() { console.log('Payment modal dismissed'); } },
        "prefill": {
            "name": "{{ $order->customer_name }}",
            "email": "{{ $order->customer_email }}",
            "contact": "{{ $order->customer_phone }}"
        },
        "theme": {
            "color": "#D4AF37"
        }
    };

    const rzp = new Razorpay(options);

    function openPayment() {
        whenItemsStillAvailable(() => rzp.open());
    }

    rzp.on('payment.failed', function (response){
        paymentWaiting = false;
        document.getElementById('rzp-button')?.removeAttribute('disabled');
        showPaymentStatus('Payment failed', 'This payment attempt failed. Please check the transaction status in your bank account before trying again.');
        document.querySelector('#payment-status .spinner-border').hidden = true;
        document.getElementById('payment-countdown').hidden = true;
        window.setTimeout(() => { window.location.href = "{{ route('checkout.index') }}"; }, 3000);
    });

    document.getElementById('rzp-button').onclick = function(e){
        openPayment();
        e.preventDefault();
    }

    // Auto trigger on page load
    window.onload = function() {
        openPayment();
    };
    }
</script>
@endsection
