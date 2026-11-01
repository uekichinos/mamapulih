<?php

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

it('updates the customer profile', function () {
    $customer = Customer::factory()->create(['name' => 'Old Name']);

    $this->actingAs($customer, 'customers')
        ->patch(route('customer.profile.update'), [
            'name' => 'New Name',
            'email' => $customer->email,
            'phone' => $customer->phone,
        ])
        ->assertRedirect();

    expect($customer->refresh()->name)->toBe('New Name');
});

it('rejects an email already used by another customer', function () {
    $customer = Customer::factory()->create();
    $other = Customer::factory()->create();

    $this->actingAs($customer, 'customers')
        ->patch(route('customer.profile.update'), [
            'name' => $customer->name,
            'email' => $other->email,
            'phone' => $customer->phone,
        ])
        ->assertSessionHasErrors('email');
});

it('updates the customer password with the correct current password', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customers')
        ->put(route('customer.password.update'), [
            'current_password' => 'password',
            'password' => 'new-strong-password',
            'password_confirmation' => 'new-strong-password',
        ])
        ->assertRedirect();

    expect(Hash::check('new-strong-password', $customer->refresh()->password))->toBeTrue();
});

it('rejects a password update with the wrong current password', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customers')
        ->put(route('customer.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-strong-password',
            'password_confirmation' => 'new-strong-password',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('password', $customer->refresh()->password))->toBeTrue();
});
