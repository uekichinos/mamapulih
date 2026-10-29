<?php

use App\Models\User;

it('lets a kitchen user reach the prep sheet', function () {
    $kitchen = User::factory()->kitchen()->create();

    $this->actingAs($kitchen)->get(route('admin.kitchen.prep-sheet'))->assertOk();
});

it('lets a kitchen user reach the delivery lists', function () {
    $kitchen = User::factory()->kitchen()->create();

    $this->actingAs($kitchen)->get(route('admin.delivery-lists.index'))->assertOk();
});

it('lets an owner reach the prep sheet and delivery lists too', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->get(route('admin.kitchen.prep-sheet'))->assertOk();
    $this->actingAs($owner)->get(route('admin.delivery-lists.index'))->assertOk();
});

it('blocks a kitchen user from bookings, payments, and settings screens', function () {
    $kitchen = User::factory()->kitchen()->create();

    $this->actingAs($kitchen)->get(route('admin.bookings.index'))->assertForbidden();
    $this->actingAs($kitchen)->get(route('admin.settings.edit'))->assertForbidden();
    $this->actingAs($kitchen)->get(route('admin.audit-log.index'))->assertForbidden();
    $this->actingAs($kitchen)->get(route('admin.staff.index'))->assertForbidden();
});
