<?php

use App\Models\AuditLog;
use App\Models\User;

it('lets an owner log in as a kitchen staff account', function () {
    $owner = User::factory()->create();
    $kitchen = User::factory()->kitchen()->create();

    $this->actingAs($owner)
        ->post(route('admin.staff.impersonate', $kitchen))
        ->assertRedirect(route('dashboard'));

    $this->get(route('dashboard'))->assertRedirect(route('admin.kitchen.prep-sheet'));

    expect(auth()->id())->toBe($kitchen->id);
    expect(session('impersonator_id'))->toBe($owner->id);

    $log = AuditLog::query()->where('action', 'staff.impersonation_started')->sole();
    expect($log->actor_id)->toBe($owner->id);
    expect($log->subject_id)->toBe($kitchen->id);
});

it('lets the impersonated session return to the owner account', function () {
    $owner = User::factory()->create();
    $kitchen = User::factory()->kitchen()->create();

    $this->actingAs($owner)->post(route('admin.staff.impersonate', $kitchen));

    $this->post(route('impersonation.stop'))
        ->assertRedirect(route('admin.staff.index'));

    expect(auth()->id())->toBe($owner->id);
    expect(session()->has('impersonator_id'))->toBeFalse();

    $log = AuditLog::query()->where('action', 'staff.impersonation_ended')->sole();
    expect($log->actor_id)->toBe($owner->id);
    expect($log->subject_id)->toBe($kitchen->id);
});

it('prevents an owner from impersonating themselves', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)
        ->post(route('admin.staff.impersonate', $owner))
        ->assertForbidden();
});

it('prevents an owner from impersonating another owner', function () {
    $owner = User::factory()->create();
    $otherOwner = User::factory()->create();

    $this->actingAs($owner)
        ->post(route('admin.staff.impersonate', $otherOwner))
        ->assertForbidden();
});

it('prevents kitchen staff from starting impersonation', function () {
    $kitchen = User::factory()->kitchen()->create();
    $otherKitchen = User::factory()->kitchen()->create();

    $this->actingAs($kitchen)
        ->post(route('admin.staff.impersonate', $otherKitchen))
        ->assertForbidden();
});

it('rejects stopping impersonation when none is active', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)
        ->post(route('impersonation.stop'))
        ->assertForbidden();
});
