@if($order->payment_status === 'paid' && $order->order_status !== 'cancelled' && !empty($order->payment?->response_payload['stock_review']))
    <div class="alert alert-warning mb-3" role="alert">
        <strong>Payment received — stock needs review.</strong>
        This order has not been confirmed and stock has not been deducted.
        Check physical availability, then use Razorpay Sync to confirm, or arrange a refund if the item cannot be supplied.
    </div>
@endif
