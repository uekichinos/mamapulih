<?php

use App\Domain\Setup\InstallationService;
use App\Enums\UserRole;
use App\Models\SystemState;
use App\Models\User;

beforeEach(function () {
    // Installer tests need the pre-install state that TestCase otherwise
    // short-circuits for every other feature test.
    SystemState::current()->update(['installed_at' => null]);
});

it('redirects every other route to the installer when not installed', function () {
    $this->get('/')->assertRedirect(route('install.index'));
    $this->get('/login')->assertRedirect(route('install.index'));
});

it('generates a setup code visible to the service and rejects a wrong one', function () {
    $this->get(route('install.index'));

    $state = SystemState::current();
    expect($state->setup_code_hash)->not->toBeNull();

    $this->post(route('install.verify'), ['code' => 'WRONG-CODE'])
        ->assertSessionHasErrors('code');

    expect(session('install.verified'))->toBeNull();
});

it('locks out after too many wrong codes', function () {
    $this->get(route('install.index'));

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('install.verify'), ['code' => 'WRONG-CODE']);
    }

    expect(SystemState::current()->isLocked())->toBeTrue();

    $this->post(route('install.verify'), ['code' => 'WRONG-CODE'])
        ->assertSessionHasErrors('code');
});

it('completes the full install flow and locks the installer afterwards', function () {
    $this->get(route('install.index'));

    $code = app(InstallationService::class)->generateSetupCode();

    $this->post(route('install.verify'), ['code' => $code])->assertRedirect(route('install.environment'));
    expect(session('install.verified'))->toBeTrue();

    $this->get(route('install.environment'))->assertOk();
    $this->get(route('install.database'))->assertOk();

    $this->post(route('install.database.admin'), [
        'name' => 'Kitchen Owner',
        'email' => 'owner@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('install.finish'));

    $this->get(route('install.finish'))->assertOk();

    $this->post(route('install.complete'), ['demo_data' => false])
        ->assertRedirect(route('dashboard'));

    expect(SystemState::current()->isInstalled())->toBeTrue();
    expect(User::query()->where('email', 'owner@example.test')->first())
        ->not->toBeNull()
        ->role->toBe(UserRole::Owner);

    // Once installed, the installer is permanently locked.
    $this->get(route('install.index'))->assertNotFound();
});
