<?php

namespace App\Http\Controllers\Customer;

use App\Domain\Booking\BookingPolicy;
use App\Domain\Booking\StartService;
use App\Domain\Payments\PaymentService;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClosureDay;
use App\Models\PlanChange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StartController extends Controller
{
    public function __construct(
        private StartService $start,
        private BookingPolicy $policy,
        private PaymentService $payments,
    ) {}

    public function show(Request $request): Response|RedirectResponse
    {
        $booking = $this->latestReservedOrFurther($request);

        if (! $booking) {
            return redirect()->route('customer.my-booking');
        }

        $pendingConsent = $booking->planChanges()->where('status', 'awaiting_consent')->exists();

        return Inertia::render('Customer/Start', [
            'booking' => $booking,
            'minStartDate' => now()->addDays($this->policy->startNoticeDays())->toDateString(),
            'pendingConsent' => $pendingConsent,
            'balanceDue' => $this->payments->balanceDueSen($booking),
            'canConfirm' => $this->start->canConfirm($booking),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $booking = $this->latestReservedOrFurther($request);

        if (! $booking) {
            return redirect()->route('customer.my-booking');
        }

        $data = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:'.now()->addDays($this->policy->startNoticeDays())->toDateString()],
            'actual_birth_type' => ['required', 'in:normal,csec'],
        ]);

        $startDate = Carbon::parse($data['start_date']);

        if (ClosureDay::query()->where('scope', 'all')->where('date', $startDate->toDateString())->exists()) {
            throw ValidationException::withMessages(['start_date' => __('booking.closure_day')]);
        }

        $result = $this->start->request($booking, $startDate, $data['actual_birth_type']);

        if ($result instanceof PlanChange) {
            return redirect()->route('customer.start')->with('status', 'awaiting-consent');
        }

        return redirect()->route('customer.my-booking')->with('status', 'started');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $booking = $this->latestReservedOrFurther($request);

        if (! $booking) {
            return redirect()->route('customer.my-booking');
        }

        $this->start->confirm($booking);

        return redirect()->route('customer.my-booking')->with('status', 'started');
    }

    private function latestReservedOrFurther(Request $request): ?Booking
    {
        $booking = $request->user('customers')->bookings()->latest()->first();

        return $booking && $booking->status === BookingStatus::Reserved ? $booking : null;
    }
}
