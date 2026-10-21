<?php

namespace App\Http\Controllers\Customer;

use App\Domain\Booking\BookingPolicy;
use App\Domain\Booking\BookingService;
use App\Domain\Capacity\CapacityFullException;
use App\Domain\Capacity\CapacityService;
use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Variant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A simple session-backed wizard (build -> slot -> details -> review),
 * so each step is a normal Inertia page visit without needing a frontend
 * state-management library. The draft lives in session('booking.draft')
 * until the final submit.
 */
class BookingController extends Controller
{
    private const DRAFT_KEY = 'booking.draft';

    public function __construct(
        private BookingService $bookings,
        private CapacityService $capacity,
        private BookingPolicy $policy,
    ) {}

    public function build(): Response
    {
        $packages = Package::query()
            ->where('active', true)
            ->with(['variants' => fn ($q) => $q->where('active', true)])
            ->get();

        $addons = Addon::query()->where('active', true)->get();

        return Inertia::render('Customer/Booking/BuildPackage', [
            'packages' => $packages,
            'addons' => $addons,
            'draft' => session(self::DRAFT_KEY),
        ]);
    }

    public function storeBuild(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:variants,id'],
            'expected_birth_type' => ['required', 'in:normal,csec'],
            'is_twins' => ['boolean'],
            'addons' => ['array'],
            'addons.*.addon_id' => ['required', 'integer', 'exists:addons,id'],
            'addons.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        $request->session()->put(self::DRAFT_KEY, array_merge(
            (array) $request->session()->get(self::DRAFT_KEY, []),
            $data,
        ));

        return redirect()->route('customer.booking.slot');
    }

    public function slot(Request $request): Response|RedirectResponse
    {
        if ($redirect = $this->requireDraftKey($request, 'variant_id')) {
            return $redirect;
        }

        return Inertia::render('Customer/Booking/SlotCheck', [
            'draft' => $request->session()->get(self::DRAFT_KEY),
        ]);
    }

    public function storeSlot(Request $request): Response|RedirectResponse
    {
        if ($redirect = $this->requireDraftKey($request, 'variant_id')) {
            return $redirect;
        }

        $data = $request->validate([
            'edd' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $draft = $request->session()->get(self::DRAFT_KEY);
        $variant = Variant::query()->findOrFail((int) $draft['variant_id']);
        $slotsNeeded = ! empty($draft['is_twins']) ? $this->policy->twinsSlots() : 1;
        $edd = Carbon::parse($data['edd'])->startOfDay();

        $period = $this->capacity->extendForClosures($edd->copy(), $variant->duration_days);

        if ($this->capacity->canAccept($period['start'], $period['end'], $slotsNeeded, $this->policy->regularCapacity())) {
            $request->session()->put(self::DRAFT_KEY, array_merge($draft, ['edd' => $data['edd']]));

            return redirect()->route('customer.booking.details');
        }

        $next = $this->capacity->nextAvailableStart($edd->copy()->addDay(), $variant->duration_days, $slotsNeeded, $this->policy->regularCapacity());

        return Inertia::render('Customer/Booking/SlotCheck', [
            'draft' => $draft,
            'full' => true,
            'nextAvailable' => $next?->toDateString(),
        ]);
    }

    public function details(Request $request): Response|RedirectResponse
    {
        if ($redirect = $this->requireDraftKey($request, 'edd')) {
            return $redirect;
        }

        return Inertia::render('Customer/Booking/Details', [
            'draft' => $request->session()->get(self::DRAFT_KEY),
        ]);
    }

    public function storeDetails(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireDraftKey($request, 'edd')) {
            return $redirect;
        }

        $data = $request->validate([
            'address.line1' => ['required', 'string', 'max:255'],
            'address.line2' => ['nullable', 'string', 'max:255'],
            'address.postcode' => ['required', 'string', 'max:10'],
            'address.notes' => ['nullable', 'string', 'max:255'],
            'diet.allergies' => ['array'],
            'diet.exclusions' => ['array'],
            'diet.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->session()->put(self::DRAFT_KEY, array_merge(
            (array) $request->session()->get(self::DRAFT_KEY, []),
            $data,
        ));

        return redirect()->route('customer.booking.review');
    }

    public function review(Request $request): Response|RedirectResponse
    {
        if ($redirect = $this->requireDraftKey($request, 'address')) {
            return $redirect;
        }

        $draft = $request->session()->get(self::DRAFT_KEY);
        $variant = Variant::query()->with('package')->findOrFail((int) $draft['variant_id']);
        $quote = $this->bookings->quote($variant, $draft['addons'] ?? []);

        return Inertia::render('Customer/Booking/Review', [
            'draft' => $draft,
            'variant' => $variant,
            'quote' => $quote,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireDraftKey($request, 'address')) {
            return $redirect;
        }

        $draft = $request->session()->get(self::DRAFT_KEY);

        try {
            $booking = $this->bookings->submit($request->user('customers'), $draft);
        } catch (CapacityFullException) {
            return redirect()->route('customer.booking.slot')->with('status', 'slot-taken');
        }

        $request->session()->forget(self::DRAFT_KEY);

        return redirect()->route('customer.my-booking')->with('status', 'submitted');
    }

    private function requireDraftKey(Request $request, string $key): ?RedirectResponse
    {
        /** @var array<string, mixed>|null $draft */
        $draft = $request->session()->get(self::DRAFT_KEY);

        if (! $draft || ! array_key_exists($key, $draft)) {
            return redirect()->route('customer.booking.build');
        }

        return null;
    }
}
