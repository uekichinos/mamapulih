<?php

use App\Models\ClosureDay;
use App\Models\User;

it('creates a full-day closure', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('admin.closure-days.store'), [
        'date' => now()->addDays(10)->toDateString(),
        'scope' => 'all',
        'reason' => 'Hari Raya',
    ])->assertRedirect(route('admin.closure-days.index'));

    expect(ClosureDay::query()->where('reason', 'Hari Raya')->exists())->toBeTrue();
});

it('rejects a partial-run closure in V1', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('admin.closure-days.store'), [
        'date' => now()->addDays(10)->toDateString(),
        'scope' => 'lunch',
    ])->assertSessionHasErrors('scope');
});

it('rejects a closure day in the past', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('admin.closure-days.store'), [
        'date' => now()->subDay()->toDateString(),
        'scope' => 'all',
    ])->assertSessionHasErrors('date');
});
