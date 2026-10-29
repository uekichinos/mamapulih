<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Hi {{ $booking->customer->name }},</p>
    <p>
        Your package ({{ $booking->ref_no }}) has 2 days left. If you'd like to extend, just reply to this
        email or contact us — we'll take care of the rest.
    </p>
    <p>Thank you,<br>MamaPulih</p>
</body>
</html>
