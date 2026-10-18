<?php

use App\Models\User;

it('lets an owner into the admin area', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->get(route('admin.dashboard'))->assertOk();
});

it('blocks a kitchen user from the admin area', function () {
    $kitchen = User::factory()->kitchen()->create();

    $this->actingAs($kitchen)->get(route('admin.dashboard'))->assertForbidden();
});

it('redirects an owner from /dashboard straight into the admin area', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
});

it('shows a kitchen user the plain dashboard instead of a dead end', function () {
    $kitchen = User::factory()->kitchen()->create();

    $this->actingAs($kitchen)->get(route('dashboard'))->assertOk();
});

it('blocks guests from the admin area', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});
