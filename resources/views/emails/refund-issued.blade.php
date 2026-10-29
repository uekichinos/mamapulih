<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Hi {{ $refund->booking->customer->name }},</p>
    <p>
        A refund of RM {{ number_format($refund->amount_sen / 100, 2) }} has been issued for booking
        {{ $refund->booking->ref_no }} ({{ $refund->reason }}).
    </p>
    <p>Thank you,<br>MamaPulih</p>
</body>
</html>
