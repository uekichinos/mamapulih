<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\TwoFactorAuthenticator;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorChallengeController extends Controller
{
    public function __construct(private TwoFactorAuthenticator $twoFactor) {}

    public function show(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('two_factor.user_id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor.user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $data = $request->validate(['code' => ['required', 'string']]);

        $key = "two-factor-challenge:{$userId}";

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors(['code' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])]);
        }

        $user = User::query()->where('id', $userId)->first();

        if (! $user || ! $user->two_factor_secret || ! $this->twoFactor->verify($user->two_factor_secret, $data['code'])) {
            RateLimiter::hit($key);

            return back()->withErrors(['code' => __('auth.two_factor_invalid_code')]);
        }

        RateLimiter::clear($key);

        $remember = (bool) $request->session()->pull('two_factor.remember', false);
        $request->session()->forget('two_factor.user_id');
        $request->session()->regenerate();

        Auth::login($user, $remember);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
