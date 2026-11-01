<?php

use App\Models\DietProfile;
use App\Models\User;

it('lists customers with their booking count', function () {
    $owner = User::factory()->create();
    $booking = pendingBooking();

    $this->actingAs($owner)->get(route('admin.customers.index'))
        ->assertInertia(
            fn ($page) => $page
                ->component('Admin/Customers/Index')
                ->where('customers.data.0.id', $booking->customer_id)
                ->where('customers.data.0.bookings_count', 1),
        );
});

it('filters the bookings list by customer', function () {
    $owner = User::factory()->create();
    $booking = pendingBooking();
    $other = pendingBooking();

    $this->actingAs($owner)
        ->get(route('admin.bookings.index', ['customer_id' => $booking->customer_id]))
        ->assertInertia(
            fn ($page) => $page
                ->component('Admin/Bookings/Index')
                ->where('bookings.total', 1)
                ->where('bookings.data.0.id', $booking->id)
                ->where('customerFilter.id', $booking->customer_id),
        );

    expect($other->id)->not->toBe($booking->id);
});

it('denies kitchen staff from viewing the customers list', function () {
    $kitchen = User::factory()->kitchen()->create();

    $this->actingAs($kitchen)->get(route('admin.customers.index'))->assertForbidden();
});

it('shows a customer detail page with their contact info and bookings', function () {
    $owner = User::factory()->create();
    $booking = pendingBooking();

    $this->actingAs($owner)->get(route('admin.customers.show', $booking->customer_id))
        ->assertInertia(
            fn ($page) => $page
                ->component('Admin/Customers/Show')
                ->where('customer.id', $booking->customer_id)
                ->where('customer.email', $booking->customer->email)
                ->where('customer.bookings.0.id', $booking->id)
                ->where('customer.addresses.0.id', $booking->address_id)
                ->where('customer.bookings.0.diet_profile', null),
        );
});

it('shows the customer diet profile on the detail page', function () {
    $owner = User::factory()->create();
    $booking = pendingBooking();

    DietProfile::query()->create([
        'customer_id' => $booking->customer_id,
        'booking_id' => $booking->id,
        'allergies' => ['seafood'],
        'exclusions' => ['spicy'],
        'notes' => 'Prefers mild food.',
    ]);

    $this->actingAs($owner)->get(route('admin.customers.show', $booking->customer_id))
        ->assertInertia(
            fn ($page) => $page
                ->component('Admin/Customers/Show')
                ->where('customer.bookings.0.diet_profile.allergies', ['seafood'])
                ->where('customer.bookings.0.diet_profile.exclusions', ['spicy'])
                ->where('customer.bookings.0.diet_profile.notes', 'Prefers mild food.'),
        );
});

it('denies kitchen staff from viewing a customer detail page', function () {
    $kitchen = User::factory()->kitchen()->create();
    $booking = pendingBooking();

    $this->actingAs($kitchen)
        ->get(route('admin.customers.show', $booking->customer_id))
        ->assertForbidden();
});
