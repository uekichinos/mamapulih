<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Hi {{ $booking->customer->name }},</p>
    <p>
        Your confinement package ({{ $booking->ref_no }}) is now complete. Thank you for choosing MamaPulih —
        we'd love to hear how it went.
    </p>
    <p>With love,<br>MamaPulih</p>
</body>
</html>
