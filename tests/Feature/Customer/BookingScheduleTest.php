<?php

use App\Domain\Kitchen\BookingDayGenerator;
use App\Models\Customer;
use App\Models\Dish;
use App\Models\RotationDay;

it('shows the schedule with resolved dishes for an active booking', function () {
    $booking = kitchenTestBooking('lunch_dinner', 7);
    $rotation = $booking->variant->menuRotation;

    $lunch = Dish::query()->create(['name' => ['en' => 'Chicken rice', 'ms' => 'Nasi ayam'], 'tags' => []]);
    $dinner = Dish::query()->create(['name' => ['en' => 'Fish soup', 'ms' => 'Sup ikan'], 'tags' => []]);
    RotationDay::query()->create(['menu_rotation_id' => $rotation->id, 'day_no' => 1, 'meal' => 'lunch', 'dish_id' => $lunch->id]);
    RotationDay::query()->create(['menu_rotation_id' => $rotation->id, 'day_no' => 1, 'meal' => 'dinner', 'dish_id' => $dinner->id]);

    app(BookingDayGenerator::class)->generateFor($booking);

    $this->actingAs($booking->customer, 'customers')
        ->get(route('customer.my-booking.schedule'))
        ->assertInertia(fn ($page) => $page
            ->where('days.0.date', '2026-12-01')
            ->where('days.0.meals.0.dish.name.en', 'Chicken rice')
            ->where('days.0.meals.1.dish.name.en', 'Fish soup')
        );
});

it('substitutes the dish on the schedule when it matches the customer\'s own exclusion', function () {
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

    $this->actingAs($booking->customer, 'customers')
        ->get(route('customer.my-booking.schedule'))
        ->assertInertia(fn ($page) => $page
            ->where('days.0.meals.0.dish.name.en', 'Vegetable soup')
            ->where('days.0.meals.0.substituted', true)
        );
});

it('redirects to my-booking when the current booking is not active', function () {
    $booking = kitchenTestBooking();
    $booking->update(['status' => 'reserved']);

    $this->actingAs($booking->customer, 'customers')
        ->get(route('customer.my-booking.schedule'))
        ->assertRedirect(route('customer.my-booking'));
});

it('redirects to my-booking when the customer has no booking at all', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customers')
        ->get(route('customer.my-booking.schedule'))
        ->assertRedirect(route('customer.my-booking'));
});
