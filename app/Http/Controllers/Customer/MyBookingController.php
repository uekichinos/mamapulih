<?php

namespace App\Http\Controllers\Customer;

use App\Domain\Booking\CancellationService;
use App\Domain\Kitchen\DishResolver;
use App\Domain\Payments\PaymentService;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingDay;
use App\Models\Customer;
use App\Models\PaymentReceipt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MyBookingController extends Controller
{
    /**
     * Statuses a customer can still cancel out of themselves — both occur
     * strictly before any payment is recorded (see dev plan P19).
     */
    private const SELF_CANCELLABLE_STATUSES = [BookingStatus::PendingApproval, BookingStatus::AwaitingPayment];

    /**
     * A booking whose lifecycle has ended — it's never shown as "current",
     * only ever under Archives (see dev plan P20).
     */
    private const INACTIVE_STATUSES = [
        BookingStatus::Rejected,
        BookingStatus::Expired,
        BookingStatus::Cancelled,
        BookingStatus::Completed,
    ];

    public function __construct(
        private PaymentService $payments,
        private CancellationService $cancellations,
        private DishResolver $dishes,
    ) {}

    public function show(Request $request): Response
    {
        $customer = $request->user('customers');

        $booking = $this->currentBooking($customer)
            ?->load(['variant.package', 'address.zone', 'addons.addon', 'paymentReceipts']);

        $archivedBookings = $customer->bookings()
            ->with('variant.package')
            ->when($booking, fn ($query) => $query->whereKeyNot($booking->id))
            ->latest()
            ->latest('id')
            ->get();

        return Inertia::render('Customer/MyBooking', [
            'booking' => $booking,
            'balanceDue' => $booking ? $this->payments->balanceDueSen($booking) : null,
            'canCancel' => $booking
                ? in_array($booking->status, self::SELF_CANCELLABLE_STATUSES, true)
                : false,
            'archivedBookings' => $archivedBookings,
        ]);
    }

    public function showArchived(Request $request, Booking $booking): Response
    {
        abort_unless($booking->customer_id === $request->user('customers')->id, 403);

        $booking->load(['variant.package', 'address.zone', 'addons.addon']);

        return Inertia::render('Customer/BookingArchiveShow', [
            'booking' => $booking,
            'amountPaid' => $this->payments->amountPaid($booking),
        ]);
    }

    public function viewReceipt(Request $request, PaymentReceipt $receipt): StreamedResponse
    {
        abort_unless($receipt->booking->customer_id === $request->user('customers')->id, 403);

        return Storage::disk('local')->response($receipt->path, $receipt->original_name);
    }

    public function schedule(Request $request): Response|RedirectResponse
    {
        $booking = $this->currentBooking($request->user('customers'));

        if (! $booking || $booking->status !== BookingStatus::Active) {
            return redirect()->route('customer.my-booking');
        }

        $booking->load('customer.dietProfile');

        $dietProfile = $booking->customer->dietProfile;
        $exclusions = $dietProfile === null
            ? []
            : array_merge($dietProfile->allergies ?? [], $dietProfile->exclusions ?? []);

        $meals = $booking->meal_plan === 'lunch_dinner' ? ['lunch', 'dinner'] : ['lunch'];

        $days = $booking->bookingDays()
            ->orderBy('date')
            ->get()
            ->map(fn (BookingDay $bookingDay) => $this->scheduleDay($booking, $bookingDay, $meals, $exclusions));

        return Inertia::render('Customer/BookingSchedule', [
            'booking' => [
                'ref_no' => $booking->ref_no,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
            ],
            'days' => $days,
        ]);
    }

    /**
     * @param  array<int, string>  $meals
     * @param  array<int, string>  $exclusions
     * @return array{date: string, status: string, meals: array<int, array{meal: string, dish: array{id: int, name: array<string, string>}, substituted: bool}>}
     */
    private function scheduleDay(Booking $booking, BookingDay $bookingDay, array $meals, array $exclusions): array
    {
        $mealsForDay = [];

        if ($bookingDay->status === 'scheduled' && $bookingDay->menu_rotation_id) {
            $dayNo = ($booking->start_date->diffInDays($bookingDay->date) % 7) + 1;

            foreach ($meals as $meal) {
                $resolved = $this->dishes->resolve($bookingDay->menu_rotation_id, $dayNo, $meal, $exclusions);

                if ($resolved) {
                    $mealsForDay[] = [
                        'meal' => $meal,
                        'dish' => ['id' => $resolved['dish']->id, 'name' => $resolved['dish']->name],
                        'substituted' => $resolved['substituted'],
                    ];
                }
            }
        }

        return [
            'date' => $bookingDay->date->toDateString(),
            'status' => $bookingDay->status,
            'meals' => $mealsForDay,
        ];
    }

    public function cancel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $booking = $this->currentBooking($request->user('customers'));

        abort_if(! $booking, 404);
        abort_unless(in_array($booking->status, self::SELF_CANCELLABLE_STATUSES, true), 422, 'This booking can no longer be cancelled yourself.');
        abort_unless($this->payments->amountPaid($booking) === 0, 422, 'A payment has already been recorded — please contact us to cancel.');

        $this->cancellations->cancel($booking, null, 'normal', $data['reason'] ?? null);

        return redirect()->route('customer.my-booking')->with('status', 'cancelled');
    }

    /**
     * The booking shown as "current" — the customer's latest booking that
     * hasn't reached an inactive, terminal status. Those are always
     * historical and belong under Archives, never as the one displayed up
     * front (see dev plan P20).
     */
    private function currentBooking(Customer $customer): ?Booking
    {
        return $customer->bookings()
            ->whereNotIn('status', self::INACTIVE_STATUSES)
            ->latest()
            ->latest('id')
            ->first();
    }
}
