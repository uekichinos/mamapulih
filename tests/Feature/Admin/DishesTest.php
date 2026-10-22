<?php

use App\Models\Dish;
use App\Models\User;

it('creates a dish with tags', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('admin.dishes.store'), [
        'name' => ['en' => 'Fish curry', 'ms' => 'Kari ikan'],
        'tags' => ['seafood', 'spicy'],
        'active' => true,
    ])->assertRedirect(route('admin.dishes.index'));

    $dish = Dish::query()->where('name->en', 'Fish curry')->first();
    expect($dish->tags)->toBe(['seafood', 'spicy']);
});

it('rejects an unknown tag', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('admin.dishes.store'), [
        'name' => ['en' => 'Fish curry', 'ms' => 'Kari ikan'],
        'tags' => ['not-a-real-tag'],
        'active' => true,
    ])->assertSessionHasErrors('tags.0');
});

it('updates a dish with a substitute', function () {
    $owner = User::factory()->create();
    $substitute = Dish::query()->create(['name' => ['en' => 'Soup', 'ms' => 'Sup'], 'tags' => []]);
    $dish = Dish::query()->create(['name' => ['en' => 'Fish', 'ms' => 'Ikan'], 'tags' => ['seafood']]);

    $this->actingAs($owner)->put(route('admin.dishes.update', $dish), [
        'name' => $dish->name,
        'tags' => $dish->tags,
        'substitute_dish_id' => $substitute->id,
        'active' => true,
    ])->assertRedirect(route('admin.dishes.index'));

    expect($dish->refresh()->substitute_dish_id)->toBe($substitute->id);
});
