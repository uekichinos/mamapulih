<?php

use App\Domain\Delivery\DeliveryListService;
use App\Domain\Kitchen\BookingDayGenerator;
use App\Models\ClosureDay;
use Illuminate\Support\Carbon;

it('groups deliveries by run then zone with the documented columns', function () {
    $booking = kitchenTestBooking('lunch_dinner', 7);
    app(BookingDayGenerator::class)->generateFor($booking);

    $runs = app(DeliveryListService::class)->forDate(Carbon::parse('2026-12-01'));

    $zoneName = $booking->address->zone->name;
    $row = $runs['lunch'][$zoneName][0];

    expect($row['name'])->toBe($booking->customer->name);
    expect($row['phone'])->toBe($booking->customer->phone);
    expect($row['one_trip'])->toBeFalse();
    expect($runs)->toHaveKey('dinner');
});

it('skips closure days entirely', function () {
    $booking = kitchenTestBooking('lunch', 7);
    ClosureDay::query()->create(['date' => '2026-12-03', 'scope' => 'all', 'source' => 'manual']);
    app(BookingDayGenerator::class)->generateFor($booking);

    $runs = app(DeliveryListService::class)->forDate(Carbon::parse('2026-12-03'));

    expect($runs)->toBe([]);
});
