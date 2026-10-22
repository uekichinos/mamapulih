<?php

namespace App\Http\Controllers\Customer;

use App\Domain\Booking\BookingPolicy;
use App\Domain\Payments\ManualPaymentSettings;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        private BookingPolicy $policy,
        private ManualPaymentSettings $manual,
    ) {}

    public function show(Request $request): Response|RedirectResponse
    {
        $booking = $request->user('customers')->bookings()->with('variant.package')->latest()->first();

        if (! $booking || $booking->status !== BookingStatus::AwaitingPayment) {
            return redirect()->route('customer.my-booking');
        }

        return Inertia::render('Customer/Payment', [
            'booking' => $booking,
            'fullAmountSen' => $booking->price_sen,
            'depositAmountSen' => $this->policy->firstPaymentThresholdSen($booking->price_sen, 'deposit'),
            'manual' => $this->manual->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $booking = $request->user('customers')->bookings()->latest()->first();

        if (! $booking || $booking->status !== BookingStatus::AwaitingPayment) {
            return redirect()->route('customer.my-booking');
        }

        $data = $request->validate([
            'payment_option' => ['required', 'in:full,deposit'],
        ]);

        $booking->update([
            'payment_option' => $data['payment_option'],
            'payment_due_at' => now()->addHours($this->policy->paymentDeadlineHours()),
        ]);

        return redirect()->route('customer.payment')->with('status', 'option-saved');
    }
}
