<?php

use App\Models\User;

it('shows the admin a per-day capacity list', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->get(route('admin.capacity.index'))->assertOk();
});
