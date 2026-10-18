<?php

use App\Models\SystemState;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    SystemState::current()->update(['installed_at' => null]);
});

it('prints a setup code when not installed', function () {
    $this->artisan('mamapulih:setup-code')->assertSuccessful();

    expect(SystemState::current()->setup_code_hash)->not->toBeNull();
});

it('refuses to print a setup code once installed', function () {
    SystemState::current()->update(['installed_at' => now()]);

    $this->artisan('mamapulih:setup-code')->assertFailed();
});

it('resets a staff member password from the console', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);

    $this->artisan('mamapulih:reset-admin-password', ['email' => $user->email])->assertSuccessful();

    expect(Hash::check('old-password', $user->fresh()->password))->toBeFalse();
});

it('fails cleanly for an unknown email', function () {
    $this->artisan('mamapulih:reset-admin-password', ['email' => 'nobody@example.test'])->assertFailed();
});
