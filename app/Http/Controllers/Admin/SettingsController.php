<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Domain\Settings\SettingsManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    private const DEFAULTS = [
        'deposit_type' => 'percentage',
        'deposit_amount' => 20,
        'balance_due_days_before_start' => 3,
        'start_notice_days' => 2,
        'approval_hold_hours' => 24,
        'regular_capacity' => 4,
        'buffer_capacity' => 2,
        'twins_slots' => 1,
    ];

    public function __construct(
        private SettingsManager $settings,
        private AuditLogger $audit,
    ) {}

    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/Edit', [
            'settings' => $this->settings->get('booking', self::DEFAULTS),
        ]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        $before = $this->settings->get('booking', self::DEFAULTS);

        $this->settings->set('booking', $request->validated());

        $this->audit->record('settings.booking.updated', before: $before, after: $request->validated());

        return back()->with('status', 'saved');
    }
}
