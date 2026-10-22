<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Hi {{ $planChange->booking->customer->name }},</p>

    <p>
        Your baby's birth type is different from what you booked for
        {{ $planChange->booking->ref_no }}. Please review and confirm the plan change below.
    </p>

    <p>
        <a href="{{ $url }}" style="display:inline-block;padding:10px 16px;background:#4f46e5;color:#fff;border-radius:6px;text-decoration:none;">
            Review the change
        </a>
    </p>

    <p>This link will expire. If you don't act on it, nothing will change.</p>

    <p>Thank you,<br>MamaPulih</p>
</body>
</html>
