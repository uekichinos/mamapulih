<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyBookingController extends Controller
{
    public function show(Request $request): Response
    {
        $booking = $request->user('customers')
            ->bookings()
            ->with(['variant.package', 'address.zone', 'addons.addon'])
            ->latest()
            ->first();

        return Inertia::render('Customer/MyBooking', [
            'booking' => $booking,
        ]);
    }
}
