<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation - QUARA WALDROP</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #F8F9FA; margin: 0; padding: 20px; color: #111111; }
        .email-container { max-width: 650px; margin: 0 auto; background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #EAEAEA; }
        .header { background: #111111; padding: 30px; text-align: center; border-bottom: 3px solid #C9962E; }
        .header img { max-height: 55px; }
        .content { padding: 35px 30px; line-height: 1.6; }
        .order-badge { display: inline-block; background: #FFF8E7; border: 1px solid #C9962E; color: #9A6A12; padding: 6px 14px; border-radius: 20px; font-weight: bold; font-size: 14px; margin-bottom: 15px; }
        .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
        .items-table th { background: #F4F4F4; text-align: left; padding: 12px; border-bottom: 2px solid #DDDDDD; font-weight: bold; }
        .items-table td { padding: 12px; border-bottom: 1px solid #EEEEEE; }
        .address-box { background: #F9F9F9; padding: 20px; border-radius: 8px; border: 1px solid #E0E0E0; margin-top: 20px; font-size: 14px; }
        .btn-track { display: inline-block; background: linear-gradient(135deg, #C9962E 0%, #9A6A12 100%); color: #FFFFFF !important; text-decoration: none; padding: 14px 32px; border-radius: 30px; font-weight: bold; margin: 25px 0; text-transform: uppercase; letter-spacing: 1px; }
        .footer { background: #F4F4F4; padding: 25px 30px; text-align: center; font-size: 12px; color: #777777; border-top: 1px solid #EEEEEE; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ $message->embed(public_path('assets/images/logo.png')) }}" alt="QUARA WALDROP">
        </div>
        <div class="content">
            <div class="order-badge">Order Confirmed: {{ $order->order_number }}</div>
            <h2 style="color: #111111; margin-top: 0;">Thank you for your order, {{ $order->customer_name }}!</h2>
            <p>We've received your order and are getting it ready for dispatch. Here are your order details:</p>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Size</th>
                        <th>Qty</th>
                        <th style="text-align: right;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td><strong>{{ $item->product_name }}</strong></td>
                            <td>{{ $item->size }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td style="text-align: right;">₹{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @if($order->discount > 0)
                        <tr>
                            <td colspan="3" style="text-align: right; color: #DC3545; font-weight: bold;">Discount:</td>
                            <td style="text-align: right; color: #DC3545; font-weight: bold;">-₹{{ number_format($order->discount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: bold; font-size: 16px;">Grand Total:</td>
                        <td style="text-align: right; font-weight: bold; font-size: 16px; color: #C9962E;">₹{{ number_format($order->grand_total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="address-box">
                <h4 style="margin-top: 0; color: #111111; border-bottom: 1px solid #E0E0E0; padding-bottom: 8px;">Delivery Details</h4>
                <p style="margin: 4px 0;"><strong>Name:</strong> {{ $order->customer_name }}</p>
                <p style="margin: 4px 0;"><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                <p style="margin: 4px 0;"><strong>Payment Method:</strong> {{ strtoupper($order->payment_method) }}</p>
                <p style="margin: 4px 0;"><strong>Address:</strong> {{ $order->full_address }}</p>
            </div>

            <div style="text-align: center;">
                <a href="{{ $trackingUrl }}" class="btn-track">Track Your Order Live</a>
            </div>
        </div>
        <div class="footer">
            <p style="margin: 0 0 5px 0; font-weight: bold; color: #111111;">QUARA WALDROP – Elegant & Affordable Ladies Wear</p>
            <p style="margin: 0;">Support Email: <a href="mailto:quarawaldrop@gmail.com" style="color: #C9962E; text-decoration: none;">quarawaldrop@gmail.com</a></p>
            <p style="margin: 5px 0 0 0; font-size: 11px;">&copy; {{ date('Y') }} QUARA WALDROP. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
