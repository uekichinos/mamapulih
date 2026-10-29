<?php

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

function validTwoFactorCode(string $secret): string
{
    return (new Google2FA)->getCurrentOtp($secret);
}

test('owner without two-factor is sent to setup before reaching the dashboard', function () {
    $owner = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($owner)->get(route('dashboard'))->assertRedirect(route('two-factor.setup'));
});

test('kitchen without two-factor is not forced into setup', function () {
    $kitchen = User::factory()->kitchen()->withoutTwoFactor()->create();

    $this->actingAs($kitchen)->get(route('dashboard'))->assertRedirect(route('admin.kitchen.prep-sheet'));
});

test('owner can confirm two-factor setup with a valid code and reach the dashboard', function () {
    $owner = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($owner)->get(route('two-factor.setup'));

    $secret = session('two_factor.pending_secret');
    expect($secret)->not->toBeNull();

    $response = $this->actingAs($owner)->post(route('two-factor.setup.confirm'), [
        'code' => validTwoFactorCode($secret),
    ]);

    $response->assertRedirect(route('dashboard'));
    expect($owner->refresh()->hasTwoFactorEnabled())->toBeTrue();

    $this->actingAs($owner)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
});

test('two-factor setup rejects an invalid code', function () {
    $owner = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($owner)->get(route('two-factor.setup'));

    $response = $this->actingAs($owner)->post(route('two-factor.setup.confirm'), [
        'code' => '000000',
    ]);

    $response->assertSessionHasErrors('code');
    expect($owner->refresh()->hasTwoFactorEnabled())->toBeFalse();
});

test('owner cannot disable two-factor authentication', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->delete(route('two-factor.setup.destroy'))->assertForbidden();
    expect($owner->refresh()->hasTwoFactorEnabled())->toBeTrue();
});

test('kitchen can disable two-factor authentication', function () {
    $kitchen = User::factory()->kitchen()->create();

    $this->actingAs($kitchen)->delete(route('two-factor.setup.destroy'));
    expect($kitchen->refresh()->hasTwoFactorEnabled())->toBeFalse();
});

test('login challenge accepts a valid code and logs the user in', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $response = $this->post(route('two-factor.challenge.store'), [
        'code' => validTwoFactorCode($user->two_factor_secret),
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('login challenge rejects an invalid code', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $response = $this->post(route('two-factor.challenge.store'), [
        'code' => '000000',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('code');
});
