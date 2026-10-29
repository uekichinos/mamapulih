<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Hi {{ $booking->customer->name }},</p>
    <p>We've received your booking {{ $booking->ref_no }}. It's now under review — we'll email you once it's approved.</p>
    <p>Thank you,<br>MamaPulih</p>
</body>
</html>
