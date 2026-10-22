<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Domain\Kitchen\ClosureNoticeMail;
use App\Domain\Kitchen\ClosureShiftService;
use App\Domain\Settings\SmtpSettings;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClosureDayRequest;
use App\Models\Booking;
use App\Models\ClosureDay;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ClosureDayController extends Controller
{
    public function __construct(
        private AuditLogger $audit,
        private ClosureShiftService $shifts,
        private SmtpSettings $smtp,
    ) {}

    public function index(): Response
    {
        $closureDays = ClosureDay::query()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->get();

        return Inertia::render('Admin/ClosureDays/Index', [
            'closureDays' => $closureDays->map(fn (ClosureDay $day) => [
                ...$day->toArray(),
                'affected_count' => $this->affectedBookings($day)->count(),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/ClosureDays/Create');
    }

    public function store(ClosureDayRequest $request): RedirectResponse
    {
        $closureDay = ClosureDay::query()->create([
            ...$request->validated(),
            'source' => 'manual',
            'created_by' => auth()->id(),
        ]);

        $this->audit->record('closure_day.created', $closureDay, after: $closureDay->toArray());

        $this->shifts->shiftForNewClosure($closureDay);

        return redirect()->route('admin.closure-days.index')->with('status', 'created');
    }

    public function notify(ClosureDay $closureDay): RedirectResponse
    {
        $bookings = $this->affectedBookings($closureDay)->get();

        $this->smtp->applyToRuntimeConfig();

        foreach ($bookings as $booking) {
            Mail::to($booking->customer->email)->send(new ClosureNoticeMail($booking, $closureDay));
        }

        $this->audit->record('closure_day.notified', $closureDay, after: ['notified_count' => $bookings->count()]);

        return back()->with('status', 'notified');
    }

    /**
     * Same window check as ClosureShiftService: either the daterange
     * (not-yet-started bookings) or start/end dates (already-started).
     */
    /**
     * @return Builder<Booking>
     */
    private function affectedBookings(ClosureDay $closureDay): Builder
    {
        return Booking::query()
            ->whereIn('status', BookingStatus::occupyingCapacity())
            ->whereHas('customer', fn ($q) => $q->whereNotNull('email'))
            ->where(function ($query) use ($closureDay) {
                $query->whereRaw('projected_period @> ?::date', [$closureDay->date->toDateString()])
                    ->orWhere(function ($q) use ($closureDay) {
                        $q->whereNotNull('start_date')
                            ->whereNotNull('end_date')
                            ->where('start_date', '<=', $closureDay->date->toDateString())
                            ->where('end_date', '>=', $closureDay->date->toDateString());
                    });
            });
    }

    public function destroy(ClosureDay $closureDay): RedirectResponse
    {
        $before = $closureDay->toArray();

        $closureDay->delete();

        $this->audit->record('closure_day.deleted', before: $before);

        return redirect()->route('admin.closure-days.index')->with('status', 'deleted');
    }
}
