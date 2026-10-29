<?php

use App\Models\Dish;
use App\Models\MenuRotation;
use App\Models\Package;
use App\Models\RotationDay;
use Database\Seeders\DemoDataSeeder;

it('seeds a dish catalogue and a full 7-day lunch/dinner rotation for every package', function () {
    (new DemoDataSeeder)->run();

    expect(Dish::query()->count())->toBe(12);
    expect(Package::query()->count())->toBe(2);

    $rotations = MenuRotation::query()->get();
    expect($rotations)->toHaveCount(2);

    foreach ($rotations as $rotation) {
        $days = RotationDay::query()->where('menu_rotation_id', $rotation->id)->get();

        expect($days)->toHaveCount(14);
        expect($days->pluck('day_no')->unique()->sort()->values()->all())->toBe([1, 2, 3, 4, 5, 6, 7]);
        expect($days->pluck('meal')->unique()->sort()->values()->all())->toBe(['dinner', 'lunch']);
    }
});

it('gives every tagged dish a substitute that carries none of its own tags', function () {
    (new DemoDataSeeder)->run();

    Dish::query()->whereNotNull('substitute_dish_id')->with('substitute')->get()
        ->each(function (Dish $dish) {
            expect(array_intersect($dish->tags, $dish->substitute->tags))->toBeEmpty();
        });
});

it('does not re-seed the catalogue on a second run', function () {
    (new DemoDataSeeder)->run();
    (new DemoDataSeeder)->run();

    expect(Dish::query()->count())->toBe(12);
    expect(Package::query()->count())->toBe(2);
});
