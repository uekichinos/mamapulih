<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Audit\AuditLogger;
use App\Domain\Auth\TwoFactorAuthenticator;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorSetupController extends Controller
{
    public function __construct(
        private TwoFactorAuthenticator $twoFactor,
        private AuditLogger $audit,
    ) {}

    public function show(Request $request): Response
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return Inertia::render('Auth/TwoFactorSetup', [
                'enabled' => true,
                'required' => $user->isOwner(),
            ]);
        }

        $secret = $request->session()->get('two_factor.pending_secret')
            ?? $this->twoFactor->generateSecretKey();

        $request->session()->put('two_factor.pending_secret', $secret);

        return Inertia::render('Auth/TwoFactorSetup', [
            'enabled' => false,
            'required' => $user->isOwner(),
            'secret' => $secret,
            'qrCodeSvg' => $this->twoFactor->qrCodeSvg($secret, $user->email),
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        $secret = $request->session()->get('two_factor.pending_secret');

        abort_if(! $secret, 419);

        if (! $this->twoFactor->verify($secret, $data['code'])) {
            return back()->withErrors(['code' => __('auth.two_factor_invalid_code')]);
        }

        $user = $request->user();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('two_factor.pending_secret');

        $this->audit->record('two_factor.enabled', $user);

        return redirect()->route('dashboard')->with('status', 'two-factor-enabled');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user->isOwner(), 403, 'Two-factor authentication is required for the Owner account.');

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->audit->record('two_factor.disabled', $user);

        return back()->with('status', 'two-factor-disabled');
    }
}
