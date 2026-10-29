<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>A customer cancelled their own booking before making any payment.</p>
    <p>
        {{ $booking->ref_no }} — {{ $booking->customer->name }} — EDD {{ $booking->edd->format('d M Y') }}
    </p>
    @if ($booking->sensitive_reason)
        <p>Reason given: {{ $booking->sensitive_reason }}</p>
    @endif
</body>
</html>
