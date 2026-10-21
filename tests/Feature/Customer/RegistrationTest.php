<?php

use App\Models\Customer;

it('registers a customer and logs them in', function () {
    $response = $this->post(route('customer.register'), [
        'name' => 'Siti Aminah',
        'email' => 'siti@example.com',
        'phone' => '0123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('customer.booking.build'));
    $this->assertAuthenticated('customers');

    expect(Customer::query()->where('email', 'siti@example.com')->exists())->toBeTrue();
});

it('rejects a duplicate email', function () {
    Customer::factory()->create(['email' => 'taken@example.com']);

    $this->post(route('customer.register'), [
        'name' => 'Someone Else',
        'email' => 'taken@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');
});
