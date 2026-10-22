<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Hi {{ $booking->customer->name }},</p>

    <p>We've received your payment for booking {{ $booking->ref_no }}. The receipt is attached.</p>

    <table style="margin-top: 16px;">
        <tr><td style="color: #6b7280; padding-right: 12px;">Package</td><td>{{ $booking->variant->package->name['en'] ?? '' }}</td></tr>
        <tr><td style="color: #6b7280; padding-right: 12px;">Total</td><td>RM {{ number_format($booking->price_sen / 100, 2) }}</td></tr>
    </table>

    <p style="margin-top: 16px;">Thank you,<br>MamaPulih</p>
</body>
</html>
