@extends('layouts.app')

@section('title', 'Complete Online Payment - QUARA WALDROP')

@section('content')
<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="bg-white p-5 rounded-4 shadow-sm border">
                <i class="fa-solid fa-credit-card text-gold display-3 mb-3"></i>
                <h3 class="font-serif fw-bold mb-2">Complete Online Payment</h3>
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
                    <div class="d-flex justify-content-between fs-5 fw-bold mt-2 pt-2 border-top">
                        <span>Total Amount:</span>
                        <span class="text-gold">₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>
                </div>

                <button id="rzp-button" class="btn btn-qw-gold btn-lg rounded-pill w-100 shadow">
                    PAY NOW WITH RAZORPAY (₹{{ number_format($order->grand_total, 2) }})
                </button>

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
    const options = {
        "key": "{{ $paymentResult['razorpay_key'] }}",
        "amount": "{{ $paymentResult['amount'] ?? ($order->grand_total * 100) }}",
        "currency": "INR",
        "name": "QUARA WALDROP",
        "description": "Order #{{ $order->order_number }} Payment",
        "image": (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') ? (window.location.origin + "/assets/images/logo.png") : "",
        @if(!empty($paymentResult['razorpay_order_id']) && str_starts_with($paymentResult['razorpay_order_id'], 'order_'))
        "order_id": "{{ $paymentResult['razorpay_order_id'] }}",
        @endif
        "handler": function (response){
            if (!response.razorpay_payment_id || !response.razorpay_signature) {
                alert("Payment was not completed or failed verification. Please try again.");
                window.location.href = "{{ route('checkout.index') }}";
                return;
            }
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id || '';
            document.getElementById('razorpay_order_id').value = response.razorpay_order_id || '{{ $paymentResult["razorpay_order_id"] ?? "" }}';
            document.getElementById('razorpay_signature').value = response.razorpay_signature || '';
            document.getElementById('razorpayForm').submit();
        },
        "modal": {
            "ondismiss": function() {
                console.log('Payment modal dismissed');
            }
        },
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

    rzp.on('payment.failed', function (response){
        alert('Payment Failed: ' + (response.error.description || 'Transaction failed or bank error'));
        window.location.href = "{{ route('checkout.index') }}";
    });

    document.getElementById('rzp-button').onclick = function(e){
        rzp.open();
        e.preventDefault();
    }

    // Auto trigger on page load
    window.onload = function() {
        rzp.open();
    };
</script>
@endsection
