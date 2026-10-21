<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Domain\Booking\BookingPolicy;
use App\Domain\Settings\SettingsManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(
        private SettingsManager $settings,
        private BookingPolicy $policy,
        private AuditLogger $audit,
    ) {}

    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/Edit', [
            'settings' => $this->policy->current(),
        ]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        $before = $this->policy->current();

        $this->settings->set('booking', $request->validated());

        $this->audit->record('settings.booking.updated', before: $before, after: $request->validated());

        return back()->with('status', 'saved');
    }
}
