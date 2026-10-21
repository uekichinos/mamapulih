<?php

use App\Models\Customer;

it('logs a customer in with correct credentials', function () {
    $customer = Customer::factory()->create(['email' => 'mama@example.com']);

    $response = $this->post(route('customer.login'), [
        'email' => 'mama@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('customer.booking.build'));
    $this->assertAuthenticatedAs($customer, 'customers');
});

it('rejects a wrong password', function () {
    Customer::factory()->create(['email' => 'mama@example.com']);

    $this->post(route('customer.login'), [
        'email' => 'mama@example.com',
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('customers');
});

it('logs a customer out', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customers')
        ->post(route('customer.logout'))
        ->assertRedirect('/');

    $this->assertGuest('customers');
});
