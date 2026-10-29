<?php

namespace App\Domain\Booking;

use App\Domain\Audit\AuditLogger;
use App\Domain\Capacity\CapacityFullException;
use App\Domain\Capacity\CapacityService;
use App\Domain\Kitchen\BookingDayGenerator;
use App\Domain\Notifications\BookingApprovedMail;
use App\Domain\Notifications\BookingRejectedMail;
use App\Domain\Notifications\BookingSubmittedMail;
use App\Domain\Notifications\CustomerMailer;
use App\Domain\Notifications\NewBookingToReviewMail;
use App\Domain\Settings\SmtpSettings;
use App\Enums\BookingStatus;
use App\Models\Addon;
use App\Models\Address;
use App\Models\Booking;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\DietProfile;
use App\Models\User;
use App\Models\Variant;
use App\Models\Zone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private CapacityService $capacity,
        private BookingPolicy $policy,
        private BookingDayGenerator $bookingDays,
        private CustomerMailer $mailer,
        private SmtpSettings $smtp,
        private AuditLogger $audit,
    ) {}

    /**
     * @param  array<int, array{addon_id: int, qty: int}>  $addonLines
     * @return array{price_sen: int, daily_rate_sen: int}
     */
    public function quote(Variant $variant, array $addonLines): array
    {
        $addonsTotal = 0;

        foreach ($addonLines as $line) {
            $addon = Addon::query()->findOrFail($line['addon_id']);
            $qty = max(1, (int) $line['qty']);
            $unit = $addon->per === 'day' ? $addon->price_sen * $variant->duration_days : $addon->price_sen;
            $addonsTotal += $unit * $qty;
        }

        return [
            'price_sen' => $variant->price_sen + $addonsTotal,
            'daily_rate_sen' => $variant->dailyRateSen(),
        ];
    }

    /**
     * @param  array{
     *     variant_id: int,
     *     edd: string,
     *     expected_birth_type: string,
     *     is_twins: bool,
     *     address: array{line1: string, line2: ?string, postcode: string, notes: ?string},
     *     diet: array{allergies?: array<int,string>, exclusions?: array<int,string>, notes?: ?string},
     *     addons?: array<int, array{addon_id: int, qty: int}>,
     * }  $data
     */
    public function submit(Customer $customer, array $data, ?User $createdBy = null): Booking
    {
        $variant = Variant::query()->where('active', true)->findOrFail($data['variant_id']);

        $zone = Zone::query()->whereJsonContains('postcodes', $data['address']['postcode'])->first();

        if (! $zone) {
            throw ValidationException::withMessages([
                'address.postcode' => __('booking.postcode_not_in_zone'),
            ]);
        }

        $slotsNeeded = ! empty($data['is_twins']) ? $this->policy->twinsSlots() : 1;
        $edd = Carbon::parse($data['edd'])->startOfDay();

        return DB::transaction(function () use ($customer, $data, $variant, $zone, $slotsNeeded, $edd, $createdBy) {
            $this->capacity->lock();

            $period = $this->capacity->extendForClosures($edd->copy(), $variant->duration_days);

            if (! $this->capacity->canAccept($period['start'], $period['end'], $slotsNeeded, $this->policy->regularCapacity())) {
                throw new CapacityFullException('No slot is available for this window.');
            }

            $address = Address::query()->create([
                'customer_id' => $customer->id,
                'zone_id' => $zone->id,
                'line1' => $data['address']['line1'],
                'line2' => $data['address']['line2'] ?? null,
                'postcode' => $data['address']['postcode'],
                'notes' => $data['address']['notes'] ?? null,
            ]);

            DietProfile::query()->updateOrCreate(
                ['customer_id' => $customer->id],
                [
                    'allergies' => $data['diet']['allergies'] ?? [],
                    'exclusions' => $data['diet']['exclusions'] ?? [],
                    'notes' => $data['diet']['notes'] ?? null,
                ],
            );

            $addonLines = $data['addons'] ?? [];
            $quote = $this->quote($variant, $addonLines);

            $booking = Booking::query()->create([
                'ref_no' => $this->generateRefNo(),
                'customer_id' => $customer->id,
                'variant_id' => $variant->id,
                'address_id' => $address->id,
                'edd' => $edd,
                'expected_birth_type' => $data['expected_birth_type'],
                'duration_days' => $variant->duration_days,
                'meal_plan' => $variant->meal_plan,
                'is_twins' => ! empty($data['is_twins']),
                'slots_used' => $slotsNeeded,
                'status' => BookingStatus::PendingApproval,
                'price_sen' => $quote['price_sen'],
                'daily_rate_sen' => $quote['daily_rate_sen'],
                'policy_snapshot' => $this->policy->current(),
                'created_by' => $createdBy?->id,
                'projected_period' => "[{$period['start']->toDateString()},{$period['end']->toDateString()}]",
            ]);

            foreach ($addonLines as $line) {
                $addon = Addon::query()->find($line['addon_id']);
                $qty = max(1, (int) $line['qty']);

                $booking->addons()->create([
                    'addon_id' => $line['addon_id'],
                    'price_sen' => $addon->per === 'day' ? $addon->price_sen * $variant->duration_days : $addon->price_sen,
                    'qty' => $qty,
                ]);
            }

            $this->audit->record('booking.created', $booking, after: $booking->toArray(), actor: $createdBy);

            $this->mailer->send($booking, fn () => new BookingSubmittedMail($booking));
            $this->notifyBusinessOfNewBooking($booking);

            return $booking;
        });
    }

    private function notifyBusinessOfNewBooking(Booking $booking): void
    {
        $businessEmail = BusinessProfile::current()->email;

        if (blank($businessEmail)) {
            return;
        }

        $this->smtp->applyToRuntimeConfig();
        Mail::to($businessEmail)->send(new NewBookingToReviewMail($booking));
    }

    public function approve(Booking $booking, User $admin): Booking
    {
        $before = $booking->toArray();

        $booking->update([
            'status' => BookingStatus::AwaitingPayment,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $this->audit->record('booking.approved', $booking, $before, $booking->toArray(), $admin);

        $this->mailer->send($booking, fn () => new BookingApprovedMail($booking));

        return $booking;
    }

    public function reject(Booking $booking, User $admin, string $reason): Booking
    {
        $before = $booking->toArray();

        $booking->update([
            'status' => BookingStatus::Rejected,
            'rejection_reason' => $reason,
        ]);

        $this->audit->record('booking.rejected', $booking, $before, $booking->toArray(), $admin);

        $this->mailer->send($booking, fn () => new BookingRejectedMail($booking));

        return $booking;
    }

    /**
     * Admin-done extension: adds days to the end of an already-started
     * booking. The extra charge is recorded separately as an
     * `extension`-type payment on the booking's payment screen.
     */
    public function extend(Booking $booking, int $days, User $admin): Booking
    {
        abort_if($days < 1, 422, 'Extension must be at least one day.');
        abort_unless(in_array($booking->status, [BookingStatus::Active, BookingStatus::Paused], true), 422, 'Only an active or paused booking can be extended.');

        $before = $booking->toArray();
        $oldEnd = $booking->end_date->copy();
        $newEnd = $oldEnd->copy()->addDays($days);

        $booking->update([
            'duration_days' => $booking->duration_days + $days,
            'end_date' => $newEnd,
            'price_sen' => $booking->price_sen + ($booking->daily_rate_sen * $days),
        ]);

        $this->bookingDays->extendTail($booking, $oldEnd->copy()->addDay(), $newEnd);

        $this->audit->record('booking.extended', $booking, $before, $booking->toArray(), $admin);

        return $booking;
    }

    private function generateRefNo(): string
    {
        do {
            $candidate = 'BK-'.now()->format('ymd').'-'.Str::upper(Str::random(4));
        } while (Booking::query()->where('ref_no', $candidate)->exists());

        return $candidate;
    }
}
