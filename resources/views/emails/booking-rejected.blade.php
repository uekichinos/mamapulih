<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Hi {{ $booking->customer->name }},</p>
    <p>We're unable to proceed with booking {{ $booking->ref_no }} at this time.</p>
    @if ($booking->rejection_reason)
        <p>Reason: {{ $booking->rejection_reason }}</p>
    @endif
    <p>Nothing has been charged. You're welcome to submit another booking or try a different date.</p>
    <p>Thank you,<br>MamaPulih</p>
</body>
</html>
