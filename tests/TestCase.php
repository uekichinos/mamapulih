<?php

namespace Tests;

use App\Models\SystemState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Feature tests assume a kitchen that has already completed installation,
     * since that's the state the app is in almost all the time in practice.
     * Installer-specific tests reset this themselves to test the pre-install
     * state.
     */
    protected function setUp(): void
    {
        parent::setUp();

        SystemState::current()->update(['installed_at' => now()]);
    }
}
