<?php

use App\Models\Customer;

it('shows nothing yet for a customer with no booking', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customers')
        ->get(route('customer.my-booking'))
        ->assertOk();
});

it('is not reachable without logging in as a customer', function () {
    $this->get(route('customer.my-booking'))->assertRedirect(route('customer.login'));
});
