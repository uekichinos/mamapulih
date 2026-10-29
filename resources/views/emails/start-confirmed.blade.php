<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Hi {{ $booking->customer->name }},</p>
    <p>
        Your package is confirmed to start on {{ optional($booking->start_date)->format('d M Y') }} and run through
        {{ optional($booking->end_date)->format('d M Y') }}.
    </p>
    <p>Thank you,<br>MamaPulih</p>
</body>
</html>
