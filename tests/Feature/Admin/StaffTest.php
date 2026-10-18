<?php

use App\Enums\UserRole;
use App\Models\User;

it('creates a kitchen staff account', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('admin.staff.store'), [
        'name' => 'Kitchen Hand',
        'email' => 'kitchen@example.test',
        'role' => 'kitchen',
        'password' => 'password123',
    ])->assertRedirect(route('admin.staff.index'));

    $staff = User::query()->where('email', 'kitchen@example.test')->first();
    expect($staff->role)->toBe(UserRole::Kitchen);
});

it('prevents an owner from deleting their own account', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)
        ->delete(route('admin.staff.destroy', $owner))
        ->assertSessionHasErrors('staff');

    expect(User::query()->find($owner->id))->not->toBeNull();
});

it('allows deleting one owner when another owner remains', function () {
    $owner = User::factory()->create();
    $secondOwner = User::factory()->create();

    $this->actingAs($owner)
        ->delete(route('admin.staff.destroy', $secondOwner))
        ->assertRedirect(route('admin.staff.index'));

    expect(User::query()->find($secondOwner->id))->toBeNull();
});
