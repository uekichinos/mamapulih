<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Hi {{ $booking->customer->name }},</p>
    <p>Booking {{ $booking->ref_no }} wasn't reviewed or paid in time, so the slot has been released.</p>
    <p>You're welcome to submit a new booking whenever you're ready.</p>
    <p>Thank you,<br>MamaPulih</p>
</body>
</html>
