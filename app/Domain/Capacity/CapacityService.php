<?php

namespace App\Domain\Capacity;

use App\Enums\BookingStatus;
use App\Models\ClosureDay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Per-day load against the kitchen's regular capacity (docs §4: the
 * capacity query is deliberately raw SQL — Postgres' `daterange @>` and
 * `generate_series` have no Eloquent equivalent worth building here).
 */
class CapacityService
{
    private const LOCK_KEY = 'mamapulih.booking.capacity';

    /**
     * Extend [start, start+duration-1] by one day per full-day closure
     * inside the window, repeating until the window stops growing (a
     * closure in the newly-added tail can itself push the end further).
     *
     * @return array{start: Carbon, end: Carbon}
     */
    public function extendForClosures(Carbon $start, int $durationDays): array
    {
        $end = $start->copy()->addDays($durationDays - 1);

        do {
            $closures = ClosureDay::query()
                ->where('scope', 'all')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->count();

            $extended = $start->copy()->addDays($durationDays - 1 + $closures);
            $changed = ! $extended->equalTo($end);
            $end = $extended;
        } while ($changed);

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Slots already committed for each day in [from, to], excluding
     * full-day closures entirely (they never appear in the result).
     *
     * @return array<string, int> keyed by Y-m-d
     */
    public function loadForRange(Carbon $from, Carbon $to): array
    {
        $statuses = array_map(fn (BookingStatus $s) => $s->value, BookingStatus::occupyingCapacity());

        $placeholders = implode(',', array_fill(0, count($statuses), '?'));

        $rows = DB::select(
            "
            SELECT d::date AS day, coalesce(sum(b.slots_used), 0) AS slots
            FROM generate_series(?, ?, interval '1 day') AS d
            LEFT JOIN bookings b
                ON b.status IN ({$placeholders})
               AND b.projected_period @> d::date
            WHERE d::date NOT IN (SELECT date FROM closure_days WHERE scope = 'all')
            GROUP BY d
            ORDER BY d
            ",
            [$from->toDateString(), $to->toDateString(), ...$statuses]
        );

        return collect($rows)->mapWithKeys(fn ($row) => [
            Carbon::parse($row->day)->toDateString() => (int) $row->slots,
        ])->all();
    }

    /**
     * True if every non-closure day in [start, end] stays at or below
     * regular capacity once $slotsNeeded is added.
     */
    public function canAccept(Carbon $start, Carbon $end, int $slotsNeeded, int $regularCapacity): bool
    {
        $load = $this->loadForRange($start, $end);

        foreach ($load as $slots) {
            if ($slots + $slotsNeeded > $regularCapacity) {
                return false;
            }
        }

        return true;
    }

    /**
     * First EDD on/after $from whose (closure-extended) window fits
     * within regular capacity, or null if nothing in the search window
     * works.
     */
    public function nextAvailableStart(
        Carbon $from,
        int $durationDays,
        int $slotsNeeded,
        int $regularCapacity,
        int $searchDays = 90,
    ): ?Carbon {
        $candidate = $from->copy();

        for ($i = 0; $i < $searchDays; $i++) {
            $period = $this->extendForClosures($candidate->copy(), $durationDays);

            if ($this->canAccept($period['start'], $period['end'], $slotsNeeded, $regularCapacity)) {
                return $candidate;
            }

            $candidate->addDay();
        }

        return null;
    }

    /**
     * Serializes capacity checks so two simultaneous submissions for the
     * same window can't both see a free slot and both take it. Must be
     * called inside the same DB transaction as the capacity check + insert.
     */
    public function lock(): void
    {
        DB::select('SELECT pg_advisory_xact_lock(?)', [crc32(self::LOCK_KEY)]);
    }
}
