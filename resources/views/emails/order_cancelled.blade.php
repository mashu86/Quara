<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Cancellation - QUARA WALDROP</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #F8F9FA; margin: 0; padding: 20px; color: #111111; }
        .email-container { max-width: 650px; margin: 0 auto; background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #EAEAEA; }
        .header { background: #111111; padding: 30px; text-align: center; border-bottom: 3px solid #DC3545; }
        .header img { max-height: 55px; }
        .content { padding: 35px 30px; line-height: 1.6; }
        .cancel-badge { display: inline-block; background: #F8D7DA; border: 1px solid #F5C6CB; color: #721C24; padding: 6px 14px; border-radius: 20px; font-weight: bold; font-size: 14px; margin-bottom: 15px; }
        .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
        .items-table th { background: #F4F4F4; text-align: left; padding: 12px; border-bottom: 2px solid #DDDDDD; font-weight: bold; }
        .items-table td { padding: 12px; border-bottom: 1px solid #EEEEEE; }
        .btn-shop { display: inline-block; background: linear-gradient(135deg, #C9962E 0%, #9A6A12 100%); color: #FFFFFF !important; text-decoration: none; padding: 14px 32px; border-radius: 30px; font-weight: bold; margin: 25px 0; text-transform: uppercase; letter-spacing: 1px; }
        .footer { background: #F4F4F4; padding: 25px 30px; text-align: center; font-size: 12px; color: #777777; border-top: 1px solid #EEEEEE; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ $message->embed(public_path('assets/images/logo.png')) }}" alt="QUARA WALDROP">
        </div>
        <div class="content">
            <div class="cancel-badge">Order Cancelled: {{ $order->order_number }}</div>
            <h2 style="color: #111111; margin-top: 0;">Order Cancellation Notice</h2>
            <p>Dear {{ $order->customer_name }},</p>
            <p>Your order <strong>{{ $order->order_number }}</strong> placed on {{ $order->created_at->format('M d, Y') }} has been cancelled.</p>
            
            <p style="background: #FFF8E7; padding: 15px; border-left: 4px solid #C9962E; font-size: 14px; color: #856404; margin: 20px 0;">
                If you made a payment via Razorpay online, our support team will process your refund to the original payment method within 3–5 business days.
            </p>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Cancelled Item</th>
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
            </table>

            <p>If you have any questions regarding this cancellation, please contact our support team at <a href="mailto:quarawaldrop@gmail.com" style="color: #C9962E;">quarawaldrop@gmail.com</a>.</p>

            <div style="text-align: center;">
                <a href="{{ route('shop') }}" class="btn-shop">Explore Collection</a>
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
