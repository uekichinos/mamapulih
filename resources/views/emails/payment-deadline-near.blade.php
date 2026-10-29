<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Hi {{ $booking->customer->name }},</p>
    <p>
        Just a reminder — your booking {{ $booking->ref_no }} is still awaiting payment, and your slot
        will be released if payment isn't received before the deadline.
    </p>
    <p>Please log in to My Booking to complete payment.</p>
    <p>Thank you,<br>MamaPulih</p>
</body>
</html>
