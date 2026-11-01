<?php

namespace App\Http\Controllers\Customer;

use App\Domain\Payments\PaymentService;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function index(Request $request): Response
    {
        $customer = $request->user('customers');

        $bookings = $customer->liveBookings()
            ->with('variant.package')
            ->get()
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'ref_no' => $booking->ref_no,
                'recipient_label' => $booking->recipient_name ?: $customer->name,
                'status' => $booking->status,
                'edd' => $booking->edd,
                'price_sen' => $booking->price_sen,
                'package_name' => $booking->variant->package->name,
                'balance_due' => $this->payments->balanceDueSen($booking),
                'pending_consent' => $booking->planChanges()->where('status', 'awaiting_consent')->exists(),
            ]);

        return Inertia::render('Customer/Dashboard', [
            'bookings' => $bookings,
        ]);
    }
}
