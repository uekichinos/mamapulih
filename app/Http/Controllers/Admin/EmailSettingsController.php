<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Domain\Settings\SmtpSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmailSettingsRequest;
use App\Mail\SmtpTestMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class EmailSettingsController extends Controller
{
    public function __construct(
        private SmtpSettings $smtp,
        private AuditLogger $audit,
    ) {}

    public function edit(): Response
    {
        return Inertia::render('Admin/EmailSettings/Edit', [
            'settings' => $this->smtp->masked(),
        ]);
    }

    public function update(EmailSettingsRequest $request): RedirectResponse
    {
        $this->smtp->save($request->validated());

        $this->audit->record('settings.smtp.updated');

        return back()->with('status', 'saved');
    }

    public function test(): RedirectResponse
    {
        $this->smtp->applyToRuntimeConfig();

        Mail::to(Auth::guard('web')->user())->send(new SmtpTestMail);

        return back()->with('status', 'test-sent');
    }
}
