<?php

use App\Models\Dish;
use App\Models\MenuRotation;
use App\Models\RotationDay;
use App\Models\User;

it('creates a menu rotation', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('admin.menu-rotations.store'), [
        'name' => ['en' => 'Standard', 'ms' => 'Standard'],
    ])->assertRedirect();

    expect(MenuRotation::query()->where('name->en', 'Standard')->exists())->toBeTrue();
});

it('sets a dish for a day and meal slot, and updates it on a second save', function () {
    $owner = User::factory()->create();
    $rotation = MenuRotation::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $dish = Dish::query()->create(['name' => ['en' => 'Rice', 'ms' => 'Nasi'], 'tags' => []]);
    $otherDish = Dish::query()->create(['name' => ['en' => 'Noodles', 'ms' => 'Mi'], 'tags' => []]);

    $this->actingAs($owner)->post(route('admin.menu-rotations.rotation-days.store', $rotation), [
        'day_no' => 1, 'meal' => 'lunch', 'dish_id' => $dish->id,
    ])->assertRedirect();

    expect(RotationDay::query()->where('menu_rotation_id', $rotation->id)->count())->toBe(1);

    $this->actingAs($owner)->post(route('admin.menu-rotations.rotation-days.store', $rotation), [
        'day_no' => 1, 'meal' => 'lunch', 'dish_id' => $otherDish->id,
    ]);

    expect(RotationDay::query()->where('menu_rotation_id', $rotation->id)->count())->toBe(1);
    expect(RotationDay::query()->where('menu_rotation_id', $rotation->id)->first()->dish_id)->toBe($otherDish->id);
});
