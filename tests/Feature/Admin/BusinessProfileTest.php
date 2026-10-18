<?php

use App\Models\AuditLog;
use App\Models\BusinessProfile;
use App\Models\User;

it('updates the business profile and records an audit entry', function () {
    $owner = User::factory()->create();

    $response = $this->actingAs($owner)->put(route('admin.business-profile.update'), [
        'name' => 'MamaPulih Kitchen',
        'email' => 'hello@mamapulih.test',
        'contact_phone' => '0123456789',
        'address' => 'Shah Alam, Selangor',
        'timezone' => 'Asia/Kuala_Lumpur',
        'currency' => 'MYR',
        'default_locale' => 'en',
    ]);

    $response->assertRedirect();

    $profile = BusinessProfile::current();
    expect($profile->name)->toBe('MamaPulih Kitchen');
    expect($profile->email)->toBe('hello@mamapulih.test');

    expect(AuditLog::query()->where('action', 'business_profile.updated')->exists())->toBeTrue();
});

it('validates required fields', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)
        ->put(route('admin.business-profile.update'), ['name' => ''])
        ->assertSessionHasErrors(['name', 'email']);
});
