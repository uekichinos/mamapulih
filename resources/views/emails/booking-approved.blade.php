<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Hi {{ $booking->customer->name }},</p>
    <p>Good news — your booking {{ $booking->ref_no }} has been approved.</p>
    <p>Please log in to My Booking to choose your payment option and see the bank/QR instructions.</p>
    <p>Thank you,<br>MamaPulih</p>
</body>
</html>
