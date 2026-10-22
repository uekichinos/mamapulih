<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Hi {{ $booking->customer->name }},</p>

    <p>
        The kitchen will be closed on {{ $closureDay->date->format('d M Y') }}
        ({{ $closureDay->reason ?: 'holiday' }}). There will be no delivery that day.
    </p>

    <p>
        Your booking {{ $booking->ref_no }}'s end date has moved out by one day to
        <strong>{{ optional($booking->end_date)->format('d M Y') ?? 'be confirmed' }}</strong>,
        so you still get every day you paid for.
    </p>

    <p>Thank you,<br>MamaPulih</p>
</body>
</html>
