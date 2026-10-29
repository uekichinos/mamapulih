<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Booking\BookingService;
use App\Domain\Booking\CancellationService;
use App\Domain\Booking\ConsentService;
use App\Domain\Booking\PauseLimitExceededException;
use App\Domain\Booking\PauseService;
use App\Domain\Capacity\CapacityFullException;
use App\Domain\Notifications\CustomerMailer;
use App\Domain\Notifications\RefundIssuedMail;
use App\Domain\Payments\PaymentService;
use App\Enums\DietTag;
use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\PaymentReceipt;
use App\Models\Refund;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookings,
        private PaymentService $payments,
        private ConsentService $consents,
        private PauseService $pauses,
        private CancellationService $cancellations,
        private CustomerMailer $mailer,
    ) {}

    public function index(): Response
    {
        $bookings = Booking::query()
            ->with(['customer', 'variant.package'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function show(Booking $booking): Response
    {
        $booking->load([
            'customer', 'variant.package', 'address.zone', 'addons.addon', 'approver',
            'payments.recorder', 'paymentReceipts', 'refunds', 'planChanges.toVariant', 'planChanges.consent',
            'pauses.approver',
        ]);

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => $booking,
            'amountPaid' => $this->payments->amountPaid($booking),
            'balanceDue' => $this->payments->balanceDueSen($booking),
        ]);
    }

    public function recordPayment(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:full,deposit,balance,plan_change,extension'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'method' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->payments->record($booking, Auth::guard('web')->user(), [
            'type' => $data['type'],
            'amount_sen' => (int) round($data['amount'] * 100),
            'payment_date' => $data['payment_date'],
            'method' => $data['method'] ?? null,
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'payment-recorded');
    }

    public function markRefundPaid(Request $request, Booking $booking, Refund $refund): RedirectResponse
    {
        abort_unless($refund->booking_id === $booking->id, 404);

        $data = $request->validate([
            'method' => ['required', 'string', 'max:255'],
            'reference' => ['required', 'string', 'max:255'],
            'proof' => ['required', 'file', 'max:4096'],
        ]);

        $path = $request->file('proof')->store('refund-proofs', 'local');

        $refund->update([
            'method' => $data['method'],
            'reference' => $data['reference'],
            'proof_path' => $path,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->mailer->send($booking, fn () => new RefundIssuedMail($refund));

        return back()->with('status', 'refund-marked-paid');
    }

    public function downloadReceipt(Booking $booking, PaymentReceipt $receipt): StreamedResponse
    {
        abort_unless($receipt->booking_id === $booking->id, 404);

        return Storage::disk('local')->download($receipt->path, $receipt->original_name);
    }

    public function resendConsent(Booking $booking): RedirectResponse
    {
        $planChange = $booking->planChanges()->where('status', 'awaiting_consent')->latest()->firstOrFail();

        $this->consents->resend($planChange);

        return back()->with('status', 'consent-resent');
    }

    public function pause(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'in:normal,medical'],
            'override_limit' => ['boolean'],
        ]);

        try {
            $this->pauses->pause(
                $booking,
                $data['days'],
                $data['reason'],
                Auth::guard('web')->user(),
                $data['override_limit'] ?? false,
            );
        } catch (PauseLimitExceededException $e) {
            return back()->withErrors(['days' => $e->getMessage()]);
        }

        return back()->with('status', 'paused');
    }

    public function resume(Booking $booking): RedirectResponse
    {
        $this->pauses->resume($booking, Auth::guard('web')->user());

        return back()->with('status', 'resumed');
    }

    public function extend(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate(['days' => ['required', 'integer', 'min:1']]);

        $this->bookings->extend($booking, $data['days'], Auth::guard('web')->user());

        return back()->with('status', 'extended');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:normal,compassionate,do_not_contact'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'override_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->cancellations->cancel(
            $booking,
            Auth::guard('web')->user(),
            $data['type'],
            $data['reason'] ?? null,
            isset($data['override_amount']) ? (int) round($data['override_amount'] * 100) : null,
        );

        return back()->with('status', 'cancelled');
    }

    public function create(): Response
    {
        $packages = Package::query()
            ->where('active', true)
            ->with(['variants' => fn ($q) => $q->where('active', true)])
            ->get();

        $addons = Addon::query()->where('active', true)->get();

        return Inertia::render('Admin/Bookings/Create', [
            'packages' => $packages,
            'addons' => $addons,
            'dietTags' => array_map(fn (DietTag $tag) => ['value' => $tag->value, 'label' => $tag->label()], DietTag::cases()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:30'],
            'variant_id' => ['required', 'integer', 'exists:variants,id'],
            'expected_birth_type' => ['required', 'in:normal,csec'],
            'is_twins' => ['boolean'],
            'edd' => ['required', 'date', 'after_or_equal:today'],
            'address.line1' => ['required', 'string', 'max:255'],
            'address.line2' => ['nullable', 'string', 'max:255'],
            'address.postcode' => ['required', 'string', 'max:10'],
            'address.notes' => ['nullable', 'string', 'max:255'],
            'diet.allergies' => ['array'],
            'diet.allergies.*' => [Rule::in(DietTag::values())],
            'diet.exclusions' => ['array'],
            'diet.exclusions.*' => [Rule::in(DietTag::values())],
            'diet.notes' => ['nullable', 'string', 'max:1000'],
            'addons' => ['array'],
            'addons.*.addon_id' => ['required', 'integer', 'exists:addons,id'],
            'addons.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        $customer = Customer::query()->firstOrCreate(
            ['email' => $data['customer']['email']],
            ['name' => $data['customer']['name'], 'phone' => $data['customer']['phone'] ?? null],
        );

        try {
            $booking = $this->bookings->submit($customer, $data, Auth::guard('web')->user());
        } catch (CapacityFullException $e) {
            return back()->withErrors(['edd' => $e->getMessage()])->withInput();
        }

        return redirect()->route('admin.bookings.show', $booking)->with('status', 'created');
    }
}
