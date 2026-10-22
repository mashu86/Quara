<!doctype html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f6f4ef;font-family:Arial,Helvetica,sans-serif;color:#222;">
    <div style="max-width:480px;margin:0 auto;background:#fff;border:1px solid #eadfbf;border-radius:14px;overflow:hidden;">
        <div style="padding:16px 24px;background:#171717;text-align:center;border-bottom:3px solid #d4af37;">
            <img src="{{ $message->embed($logoPath) }}" alt="{{ $siteName }}" width="150" style="display:block;width:150px;max-width:100%;height:auto;margin:0 auto 10px;border:0;">
            <div style="font-size:13px;color:#e8e0cc;">{{ $purpose }}</div>
        </div>
        <div style="padding:28px 24px;text-align:center;">
            <p style="margin:0 0 12px;font-size:15px;">Your verification code</p>
            <div style="display:inline-block;padding:13px 24px;background:#fff8e3;border:1px solid #e4c45e;border-radius:10px;color:#17130a;font-family:monospace;font-size:32px;font-weight:700;letter-spacing:9px;user-select:all;-webkit-user-select:all;">{{ $code }}</div>
            <p style="margin:12px 0 0;color:#777;font-size:12px;">Tap and hold the code to copy it.</p>
            <p style="margin:20px 0 0;color:#555;font-size:13px;">This code expires in 5 minutes. If you did not request it, you can ignore this email.</p>
            <p style="margin:18px 0 0;color:#999;font-size:11px;">Sent {{ $sentAt }}</p>
        </div>
    </div>
</body>
</html>
