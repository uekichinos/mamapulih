<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Booking\BookingPolicy;
use App\Domain\Capacity\CapacityService;
use App\Domain\Settings\SettingsManager;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BusinessProfile;
use App\Models\Delivery;
use App\Models\Package;
use App\Models\Zone;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private SettingsManager $settings,
        private CapacityService $capacity,
        private BookingPolicy $policy,
    ) {}

    public function index(): Response
    {
        $profile = BusinessProfile::current();

        return Inertia::render('Admin/Dashboard', [
            'checklist' => [
                'business_profile' => filled($profile->name) && filled($profile->email),
                'email' => (bool) $this->settings->get('smtp'),
                'zones' => Zone::query()->exists(),
                'packages' => Package::query()->exists(),
            ],
            'stats' => [
                'active_mothers' => Booking::query()->where('status', BookingStatus::Active)->count(),
                'todays_deliveries' => Delivery::query()
                    ->whereHas('bookingDay', fn ($q) => $q->whereDate('date', today()))
                    ->count(),
                'awaiting_approval' => Booking::query()->where('status', BookingStatus::PendingApproval)->count(),
                'pending_payments' => Booking::query()->where('status', BookingStatus::AwaitingPayment)->count(),
            ],
            'bufferAlerts' => $this->bufferAlerts(),
        ]);
    }

    /**
     * @return array<int, array{date: string, slots: int}>
     */
    private function bufferAlerts(): array
    {
        $from = Carbon::today();
        $to = $from->copy()->addDays(6);

        $load = $this->capacity->loadForRange($from, $to);
        $regular = $this->policy->regularCapacity();

        return collect($load)
            ->filter(fn (int $slots) => $slots > $regular)
            ->map(fn (int $slots, string $date) => ['date' => $date, 'slots' => $slots])
            ->values()
            ->all();
    }
}
