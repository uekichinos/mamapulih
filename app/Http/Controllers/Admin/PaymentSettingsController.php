<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Domain\Payments\ManualPaymentSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PaymentSettingsController extends Controller
{
    public function __construct(
        private ManualPaymentSettings $settings,
        private AuditLogger $audit,
    ) {}

    public function edit(): Response
    {
        return Inertia::render('Admin/PaymentSettings/Edit', [
            'settings' => $this->settings->get(),
        ]);
    }

    public function update(PaymentSettingsRequest $request): RedirectResponse
    {
        $before = $this->settings->get();

        $this->settings->save($request->validated(), $request->file('qr_image'));

        $this->audit->record('settings.payment.updated', before: $before, after: $this->settings->get());

        return back()->with('status', 'saved');
    }
}
