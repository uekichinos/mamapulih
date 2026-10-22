<?php

namespace App\Domain\Booking;

use App\Domain\Audit\AuditLogger;
use App\Domain\Settings\SmtpSettings;
use App\Models\Booking;
use App\Models\Consent;
use App\Models\PlanChange;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ConsentService
{
    private const TERMS_VERSION = 'v1';

    public function __construct(
        private BookingPolicy $policy,
        private AuditLogger $audit,
        private SmtpSettings $smtp,
    ) {}

    /**
     * Raises a birth-type change at start: finds the matching variant,
     * computes the full-duration daily-rate difference (always "before
     * start" in V1 — there is no mid-package change yet), and emails a
     * token-secured consent link.
     */
    public function raiseAtStart(Booking $booking, string $actualBirthType): PlanChange
    {
        $toVariant = Variant::query()
            ->where('package_id', $booking->variant->package_id)
            ->where('birth_type', $actualBirthType)
            ->where('duration_days', $booking->duration_days)
            ->where('meal_plan', $booking->meal_plan)
            ->where('active', true)
            ->first();

        if (! $toVariant) {
            throw new VariantUnavailableException(__('booking.variant_unavailable'));
        }

        $newDailyRate = $toVariant->dailyRateSen();
        $amountSen = ($newDailyRate - $booking->daily_rate_sen) * $booking->duration_days;

        $planChange = $booking->planChanges()->create([
            'from_variant_id' => $booking->variant_id,
            'to_variant_id' => $toVariant->id,
            'effective_date' => $booking->start_date ?? now(),
            'remaining_days' => $booking->duration_days,
            'amount_sen' => $amountSen,
            'status' => 'awaiting_consent',
        ]);

        $this->audit->record('plan_change.raised', $planChange, after: $planChange->toArray());

        $this->sendConsentLink($planChange);

        return $planChange;
    }

    public function sendConsentLink(PlanChange $planChange): Consent
    {
        $booking = $planChange->booking;
        $token = Str::random(40);

        $consent = $planChange->consent()->create([
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours($this->policy->consentExpiryHours()),
            'snapshot' => [
                'from' => ['birth_type' => $booking->variant->birth_type, 'duration_days' => $booking->duration_days],
                'to' => ['birth_type' => $planChange->toVariant->birth_type, 'duration_days' => $booking->duration_days],
                'amount_sen' => $planChange->amount_sen,
            ],
            'terms_version' => self::TERMS_VERSION,
        ]);

        if (filled($booking->customer->email)) {
            $this->smtp->applyToRuntimeConfig();
            Mail::to($booking->customer->email)->send(new ConsentMail($planChange, $token));
        }

        return $consent;
    }

    public function decide(Consent $consent, string $decision, Request $request): PlanChange
    {
        $consent->update([
            'decision' => $decision,
            'decided_at' => now(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $planChange = $consent->planChange;
        $booking = $planChange->booking;

        if ($decision === 'agree') {
            $before = $booking->toArray();

            $booking->update([
                'variant_id' => $planChange->to_variant_id,
                'actual_birth_type' => $planChange->toVariant->birth_type,
                'daily_rate_sen' => $planChange->toVariant->dailyRateSen(),
                'price_sen' => $booking->price_sen + $planChange->amount_sen,
            ]);

            $planChange->update(['status' => 'agreed']);
            $this->audit->record('plan_change.agreed', $planChange, $before, $booking->toArray());

            if ($planChange->amount_sen < 0) {
                $booking->refunds()->create([
                    'plan_change_id' => $planChange->id,
                    'reason' => 'Birth type change downgrade',
                    'amount_sen' => abs($planChange->amount_sen),
                    'status' => 'pending',
                ]);
            }
        } else {
            $planChange->update(['status' => 'declined']);
            $this->audit->record('plan_change.declined', $planChange);
        }

        return $planChange;
    }
}
