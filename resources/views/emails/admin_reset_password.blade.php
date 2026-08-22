<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Admin Password - QUARA WALDROP</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #F8F9FA; margin: 0; padding: 20px; color: #111111; }
        .email-container { max-width: 600px; margin: 0 auto; background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #EAEAEA; }
        .header { background: #111111; padding: 30px; text-align: center; border-bottom: 3px solid #C9962E; }
        .header img { max-height: 55px; }
        .content { padding: 40px 30px; line-height: 1.6; }
        .btn-reset { display: inline-block; background: linear-gradient(135deg, #C9962E 0%, #9A6A12 100%); color: #FFFFFF !important; text-decoration: none; padding: 14px 32px; border-radius: 30px; font-weight: bold; margin: 25px 0; text-transform: uppercase; letter-spacing: 1px; }
        .footer { background: #F4F4F4; padding: 20px 30px; text-align: center; font-size: 12px; color: #777777; border-top: 1px solid #EEEEEE; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ $message->embed(public_path('assets/images/logo.png')) }}" alt="QUARA WALDROP">
        </div>
        <div class="content">
            <h2 style="color: #111111; margin-top: 0;">Admin Password Reset Request</h2>
            <p>Hello Admin,</p>
            <p>We received a request to reset your password for the <strong>QUARA WALDROP Admin Portal</strong>.</p>
            <p>Click the button below to set a new password for your account:</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="btn-reset">Reset My Password</a>
            </div>

            <p style="font-size: 13px; color: #666666;">If you did not request a password reset, no further action is required. This password reset link will expire in 60 minutes.</p>

            <p style="margin-top: 30px; font-size: 12px; color: #888888; word-break: break-all;">
                If you're having trouble clicking the button, copy and paste the URL below into your web browser:<br>
                <a href="{{ $resetUrl }}" style="color: #C9962E;">{{ $resetUrl }}</a>
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0 0 5px 0; font-weight: bold; color: #111111;">QUARA WALDROP – Elegant & Affordable Ladies Wear</p>
            <p style="margin: 0;">Email: <a href="mailto:quarawaldrop@gmail.com" style="color: #C9962E; text-decoration: none;">quarawaldrop@gmail.com</a> | Instant WhatsApp Support Available</p>
            <p style="margin: 5px 0 0 0; font-size: 11px;">&copy; {{ date('Y') }} QUARA WALDROP. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
