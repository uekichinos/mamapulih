<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        td, th { padding: 6px 0; text-align: left; }
        .label { color: #6b7280; }
        .total { border-top: 1px solid #d1d5db; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Payment Receipt</h1>
    <p>Booking {{ $booking->ref_no }}</p>

    <table>
        <tr><td class="label">Customer</td><td>{{ $booking->customer->name }}</td></tr>
        <tr><td class="label">Package</td><td>{{ $booking->variant->package->name['en'] ?? '' }}</td></tr>
        <tr><td class="label">Total price</td><td>RM {{ number_format($booking->price_sen / 100, 2) }}</td></tr>
    </table>

    <table>
        <tr>
            <th class="label">Date</th>
            <th class="label">Type</th>
            <th class="label">Method</th>
            <th class="label">Reference</th>
            <th class="label">Amount</th>
        </tr>
        @foreach ($booking->payments as $payment)
            <tr>
                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $payment->type)) }}</td>
                <td>{{ $payment->method }}</td>
                <td>{{ $payment->reference }}</td>
                <td>RM {{ number_format($payment->amount_sen / 100, 2) }}</td>
            </tr>
        @endforeach
        <tr class="total">
            <td colspan="4">Total paid</td>
            <td>RM {{ number_format($booking->payments->sum('amount_sen') / 100, 2) }}</td>
        </tr>
    </table>
</body>
</html>
