<?php

use App\Models\Package;
use App\Models\User;
use App\Models\Variant;

it('creates a package with both locales required', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('admin.packages.store'), [
        'name' => ['en' => 'Standard', 'ms' => 'Standard BM'],
        'description' => ['en' => null, 'ms' => null],
        'active' => true,
    ])->assertRedirect();

    expect(Package::query()->where('name->en', 'Standard')->exists())->toBeTrue();
});

it('rejects a package missing the Malay name', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('admin.packages.store'), [
        'name' => ['en' => 'Standard', 'ms' => ''],
        'active' => true,
    ])->assertSessionHasErrors(['name.ms']);
});

it('adds and deletes a variant under a package', function () {
    $owner = User::factory()->create();
    $package = Package::query()->create([
        'name' => ['en' => 'Standard', 'ms' => 'Standard BM'],
        'active' => true,
    ]);

    $this->actingAs($owner)->post(route('admin.packages.variants.store', $package), [
        'birth_type' => 'normal',
        'duration_days' => 14,
        'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2,
        'price_sen' => 69900,
        'active' => true,
    ])->assertRedirect(route('admin.packages.edit', $package));

    $variant = $package->variants()->first();
    expect($variant)->not->toBeNull();
    expect($variant->dailyRateSen())->toBe(4993);

    $this->actingAs($owner)
        ->delete(route('admin.variants.destroy', $variant))
        ->assertRedirect(route('admin.packages.edit', $package));

    expect(Variant::query()->find($variant->id))->toBeNull();
});
