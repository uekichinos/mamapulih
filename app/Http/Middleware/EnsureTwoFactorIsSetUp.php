<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * P16: Owner cannot reach the dashboard or admin area until 2FA is
 * confirmed. Kitchen's 2FA stays optional, so this is a no-op for them.
 */
class EnsureTwoFactorIsSetUp
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->mustSetUpTwoFactor()) {
            return redirect()->route('two-factor.setup');
        }

        return $next($request);
    }
}
