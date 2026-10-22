<?php

use App\Domain\Kitchen\BookingDayGenerator;
use App\Domain\Kitchen\PrepSheetService;
use App\Models\Dish;
use App\Models\RotationDay;
use Illuminate\Support\Carbon;

it('counts portions per dish for the date', function () {
    $booking = kitchenTestBooking('lunch', 7);
    $rotation = $booking->variant->menuRotation;

    $dish = Dish::query()->create(['name' => ['en' => 'Chicken rice', 'ms' => 'Nasi ayam'], 'tags' => []]);
    RotationDay::query()->create(['menu_rotation_id' => $rotation->id, 'day_no' => 1, 'meal' => 'lunch', 'dish_id' => $dish->id]);

    app(BookingDayGenerator::class)->generateFor($booking);

    $meals = app(PrepSheetService::class)->forDate(Carbon::parse('2026-12-01'));

    expect($meals['lunch'][$dish->id]['count'])->toBe(1);
    expect($meals['lunch'][$dish->id]['substituted'])->toBe(0);
});

it('substitutes the dish for a customer with a matching exclusion', function () {
    $booking = kitchenTestBooking('lunch', 7);
    $rotation = $booking->variant->menuRotation;

    $substitute = Dish::query()->create(['name' => ['en' => 'Vegetable soup', 'ms' => 'Sup sayur'], 'tags' => []]);
    $dish = Dish::query()->create([
        'name' => ['en' => 'Fish curry', 'ms' => 'Kari ikan'],
        'tags' => ['seafood'],
        'substitute_dish_id' => $substitute->id,
    ]);
    RotationDay::query()->create(['menu_rotation_id' => $rotation->id, 'day_no' => 1, 'meal' => 'lunch', 'dish_id' => $dish->id]);

    $booking->customer->dietProfile()->create(['allergies' => [], 'exclusions' => ['seafood']]);

    app(BookingDayGenerator::class)->generateFor($booking);

    $meals = app(PrepSheetService::class)->forDate(Carbon::parse('2026-12-01'));

    expect($meals['lunch'])->toHaveKey($substitute->id);
    expect($meals['lunch'][$substitute->id]['count'])->toBe(1);
    expect($meals['lunch'][$substitute->id]['substituted'])->toBe(1);
    expect($meals['lunch'])->not->toHaveKey($dish->id);
});

it('does not substitute when the customer has no matching exclusion', function () {
    $booking = kitchenTestBooking('lunch', 7);
    $rotation = $booking->variant->menuRotation;

    $dish = Dish::query()->create(['name' => ['en' => 'Fish curry', 'ms' => 'Kari ikan'], 'tags' => ['seafood']]);
    RotationDay::query()->create(['menu_rotation_id' => $rotation->id, 'day_no' => 1, 'meal' => 'lunch', 'dish_id' => $dish->id]);

    $booking->customer->dietProfile()->create(['allergies' => [], 'exclusions' => []]);

    app(BookingDayGenerator::class)->generateFor($booking);

    $meals = app(PrepSheetService::class)->forDate(Carbon::parse('2026-12-01'));

    expect($meals['lunch'][$dish->id]['count'])->toBe(1);
    expect($meals['lunch'][$dish->id]['substituted'])->toBe(0);
});
