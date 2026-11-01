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
        $customer = $request->user('customers');

        $allBookings = $customer->bookings()
            ->with(['variant.package', 'paymentReceipts'])
            ->get();

        $payableBookings = $allBookings
            ->filter(fn (Booking $booking) => $this->isPayable($booking))
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'ref_no' => $booking->ref_no,
                'recipient_label' => $booking->recipient_name ?: $customer->name,
                'status' => $booking->status,
                'payment_option' => $booking->payment_option,
                'full_amount_sen' => $booking->price_sen,
                'deposit_amount_sen' => $this->policy->firstPaymentThresholdSen($booking->price_sen, 'deposit'),
                'balance_due_sen' => $this->payments->balanceDueSen($booking),
            ])
            ->values();

        $receiptsByBooking = $allBookings
            ->filter(fn (Booking $booking) => $booking->paymentReceipts->isNotEmpty())
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'ref_no' => $booking->ref_no,
                'recipient_label' => $booking->recipient_name ?: $customer->name,
                'receipts' => $booking->paymentReceipts,
            ])
            ->values();

        if ($payableBookings->isEmpty() && $receiptsByBooking->isEmpty()) {
            return redirect()->route('customer.my-booking');
        }

        return Inertia::render('Customer/Payment', [
            'payableBookings' => $payableBookings,
            'manual' => $this->manual->get(),
            'receiptsByBooking' => $receiptsByBooking,
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
        $data = $request->validate([
            'booking_id' => ['required', 'integer'],
            'payment_option' => ['required', 'in:full,deposit'],
        ]);

        $booking = $request->user('customers')->bookings()->findOrFail((int) $data['booking_id']);

        if ($booking->status !== BookingStatus::AwaitingPayment) {
            return redirect()->route('customer.my-booking');
        }

        $booking->update([
            'payment_option' => $data['payment_option'],
            'payment_due_at' => now()->addHours($this->policy->paymentDeadlineHours()),
        ]);

        return redirect()->route('customer.payment')->with('status', 'option-saved');
    }

    public function uploadReceipt(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'booking_id' => ['required', 'integer'],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $booking = $request->user('customers')->bookings()->findOrFail((int) $data['booking_id']);

        if (! $this->isPayable($booking)) {
            return redirect()->route('customer.my-booking');
        }

        $path = $data['receipt']->store('payment-receipts', 'local');

        $booking->paymentReceipts()->create([
            'path' => $path,
            'original_name' => $data['receipt']->getClientOriginalName(),
        ]);

        return redirect()->route('customer.payment')->with('status', 'receipt-uploaded');
    }
}
