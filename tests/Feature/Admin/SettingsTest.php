<?php

use App\Domain\Settings\SettingsManager;
use App\Models\Setting;
use App\Models\User;

it('saves booking settings as a new version each time', function () {
    $owner = User::factory()->create();

    $payload = [
        'deposit_type' => 'fixed',
        'deposit_amount' => 150,
        'balance_due_days_before_start' => 3,
        'start_notice_days' => 2,
        'approval_hold_hours' => 24,
        'regular_capacity' => 4,
        'buffer_capacity' => 2,
        'twins_slots' => 1,
    ];

    $this->actingAs($owner)->put(route('admin.settings.update'), $payload)->assertRedirect();

    $settings = app(SettingsManager::class);
    expect($settings->get('booking'))->toMatchArray($payload);

    // Saving again creates a new version rather than overwriting the old one.
    $this->actingAs($owner)->put(route('admin.settings.update'), [...$payload, 'deposit_amount' => 200]);

    expect($settings->get('booking')['deposit_amount'])->toBe(200);
    expect(Setting::query()->where('key', 'booking')->count())->toBe(2);
});

it('rejects an invalid twins_slots value', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->put(route('admin.settings.update'), [
        'deposit_type' => 'fixed',
        'deposit_amount' => 150,
        'balance_due_days_before_start' => 3,
        'start_notice_days' => 2,
        'approval_hold_hours' => 24,
        'regular_capacity' => 4,
        'buffer_capacity' => 2,
        'twins_slots' => 3,
    ])->assertSessionHasErrors('twins_slots');
});
