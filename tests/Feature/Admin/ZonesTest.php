<?php

use App\Models\User;
use App\Models\Zone;

it('creates a zone with its postcodes', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('admin.zones.store'), [
        'name' => 'Shah Alam',
        'postcodes' => ['40100', '40150'],
        'free_delivery' => true,
        'transport_cost_per_trip_sen' => 0,
        'one_trip_allowed' => true,
    ])->assertRedirect(route('admin.zones.index'));

    $zone = Zone::query()->where('name', 'Shah Alam')->first();
    expect($zone->postcodes)->toBe(['40100', '40150']);
});

it('deletes a zone', function () {
    $owner = User::factory()->create();
    $zone = Zone::query()->create([
        'name' => 'Putra Heights',
        'postcodes' => ['47130'],
        'free_delivery' => true,
        'one_trip_allowed' => true,
    ]);

    $this->actingAs($owner)
        ->delete(route('admin.zones.destroy', $zone))
        ->assertRedirect(route('admin.zones.index'));

    expect(Zone::query()->find($zone->id))->toBeNull();
});
