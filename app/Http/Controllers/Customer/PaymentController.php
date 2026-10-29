<?php

namespace App\Http\Controllers\Customer;

use App\Domain\Booking\BookingPolicy;
use App\Domain\Payments\ManualPaymentSettings;
use App\Domain\Payments\PaymentService;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        private BookingPolicy $policy,
        private ManualPaymentSettings $manual,
        private PaymentService $payments,
    ) {}

    public function show(Request $request): Response|RedirectResponse
    {
        $booking = $request->user('customers')->bookings()
            ->with(['variant.package', 'paymentReceipts'])
            ->latest()
            ->first();

        if (! $booking || ! $this->isPayable($booking)) {
            return redirect()->route('customer.my-booking');
        }

        return Inertia::render('Customer/Payment', [
            'booking' => $booking,
            'fullAmountSen' => $booking->price_sen,
            'depositAmountSen' => $this->policy->firstPaymentThresholdSen($booking->price_sen, 'deposit'),
            'balanceDueSen' => $this->payments->balanceDueSen($booking),
            'manual' => $this->manual->get(),
            'receipts' => $booking->paymentReceipts,
        ]);
    }

    /**
     * A booking the customer can act on from the payment page — either the
     * first payment is still outstanding, or it's reserved with a balance
     * still due before it can start (see dev plan P22).
     */
    private function isPayable(Booking $booking): bool
    {
        if ($booking->status === BookingStatus::AwaitingPayment) {
            return true;
        }

        return $booking->status === BookingStatus::Reserved && $this->payments->balanceDueSen($booking) > 0;
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

    public function uploadReceipt(Request $request): RedirectResponse
    {
        $booking = $request->user('customers')->bookings()->latest()->first();

        if (! $booking || ! $this->isPayable($booking)) {
            return redirect()->route('customer.my-booking');
        }

        $data = $request->validate([
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $path = $data['receipt']->store('payment-receipts', 'local');

        $booking->paymentReceipts()->create([
            'path' => $path,
            'original_name' => $data['receipt']->getClientOriginalName(),
        ]);

        return redirect()->route('customer.payment')->with('status', 'receipt-uploaded');
    }
}
