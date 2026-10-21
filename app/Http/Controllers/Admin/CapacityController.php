<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Booking\BookingPolicy;
use App\Domain\Capacity\CapacityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CapacityController extends Controller
{
    public function __construct(
        private CapacityService $capacity,
        private BookingPolicy $policy,
    ) {}

    public function index(Request $request): Response
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : Carbon::today();
        $to = $from->copy()->addDays(29);

        $load = $this->capacity->loadForRange($from, $to);
        $regular = $this->policy->regularCapacity();
        $buffer = $this->policy->bufferCapacity();

        $days = collect($load)->map(fn (int $slots, string $date) => [
            'date' => $date,
            'slots' => $slots,
            'status' => $slots > $regular + $buffer ? 'full' : ($slots > $regular ? 'buffer' : 'regular'),
        ])->values();

        return Inertia::render('Admin/Capacity/Index', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'regularCapacity' => $regular,
            'bufferCapacity' => $buffer,
            'days' => $days,
        ]);
    }
}
