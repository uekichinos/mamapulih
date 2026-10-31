<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security headers for every response (dev plan §8). HSTS only
 * goes out over an HTTPS request — sending it over plain HTTP would tell
 * the browser to force HTTPS on a host that doesn't have it yet, locking
 * an in-progress or misconfigured install out of itself.
 */
class SetSecurityHeaders
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // script-src needs 'unsafe-inline' for Ziggy's inline route-map
        // script (@routes in app.blade.php); style-src needs it for Vue's
        // scoped/dynamic styles. Tightening either to a nonce is tracked
        // as a follow-up, not done here.
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.bunny.net; font-src 'self' https://fonts.bunny.net; img-src 'self' data: blob:; connect-src 'self';",
        );

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
