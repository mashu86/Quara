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
                            <div class="fw-bold" id="payment-status-title">പേയ്‌മെന്റ് സ്ഥിരീകരിക്കുന്നു</div>
                            <div id="payment-status-message" class="small">പേയ്‌മെന്റ് സ്ഥിരീകരിക്കുന്നതുവരെ ദയവായി കാത്തിരിക്കുക. ഈ പേജ് പുതുക്കുകയോ അടയ്ക്കുകയോ പിന്നിലേക്ക് പോകുകയോ ചെയ്യരുത്. പരമാവധി 5 മിനിറ്റ് വരെ എടുത്തേക്കാം.</div>
                            <div class="fw-bold mt-2" id="payment-countdown" aria-label="ശേഷിക്കുന്ന സമയം">05:00</div>
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

                @if(str_starts_with($paymentResult['razorpay_order_id'] ?? '', 'order_mock_local_'))
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
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
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
            'പേയ്‌മെന്റ് സ്ഥിരീകരിക്കുന്നു',
            'പേയ്‌മെന്റ് സ്ഥിരീകരിക്കുന്നതുവരെ ദയവായി കാത്തിരിക്കുക. ഈ പേജ് പുതുക്കുകയോ അടയ്ക്കുകയോ പിന്നിലേക്ക് പോകുകയോ ചെയ്യരുത്. പരമാവധി 5 മിനിറ്റ് വരെ എടുത്തേക്കാം.',
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
                    'സ്ഥിരീകരണം വൈകുന്നു',
                    'പേയ്‌മെന്റ് നില ഇതുവരെ സ്ഥിരീകരിച്ചിട്ടില്ല. പണം അക്കൗണ്ടിൽ നിന്ന് പോയിട്ടുണ്ടെങ്കിൽ വീണ്ടും പേയ്‌മെന്റ് ചെയ്യരുത്. സ്ഥിരീകരണം ലഭിക്കുമ്പോൾ ഈ പേജ് ഫലം കാണിക്കും.',
                    true
                );
                timer.textContent = '05:00 കഴിഞ്ഞു';
            }
        }, 1000);
    }

    window.addEventListener('beforeunload', function (event) {
        if (!paymentWaiting) return;
        event.preventDefault();
        event.returnValue = '';
    });

    function submitMockLocalPayment() {
        startConfirmationWait();
        document.getElementById('razorpay_payment_id').value = 'pay_mock_local_' + Date.now();
        document.getElementById('razorpay_order_id').value = '{{ $paymentResult["razorpay_order_id"] ?? "" }}';
        document.getElementById('razorpay_signature').value = 'mock_signature_local';
        paymentWaiting = false;
        document.getElementById('razorpayForm').submit();
    }

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
                showPaymentStatus('പേയ്‌മെന്റ് സ്ഥിരീകരിക്കാനായില്ല', 'പേയ്‌മെന്റ് നില പരിശോധിക്കാൻ കഴിഞ്ഞില്ല. പണം അക്കൗണ്ടിൽ നിന്ന് പോയിട്ടുണ്ടെങ്കിൽ വീണ്ടും പണമടയ്ക്കരുത്.');
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
        rzp.open();
    }

    rzp.on('payment.failed', function (response){
        paymentWaiting = false;
        document.getElementById('rzp-button')?.removeAttribute('disabled');
        showPaymentStatus('പേയ്‌മെന്റ് പരാജയപ്പെട്ടു', 'ഈ പേയ്‌മെന്റ് ശ്രമം പരാജയപ്പെട്ടു. വീണ്ടും ശ്രമിക്കുന്നതിന് മുമ്പ് നിങ്ങളുടെ ബാങ്ക് അക്കൗണ്ടിലെ ഇടപാട് നില പരിശോധിക്കുക.');
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
</script>
@endsection
