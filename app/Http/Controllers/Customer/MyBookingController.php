<?php

namespace App\Http\Controllers\Customer;

use App\Domain\Payments\PaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyBookingController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function show(Request $request): Response
    {
        $booking = $request->user('customers')
            ->bookings()
            ->with(['variant.package', 'address.zone', 'addons.addon'])
            ->latest()
            ->first();

        return Inertia::render('Customer/MyBooking', [
            'booking' => $booking,
            'balanceDue' => $booking ? $this->payments->balanceDueSen($booking) : null,
        ]);
    }
}
