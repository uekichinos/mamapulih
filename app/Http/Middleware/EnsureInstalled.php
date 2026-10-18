<?php

namespace App\Http\Middleware;

use App\Domain\Setup\InstallationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    public function __construct(private InstallationService $installer) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->installer->isInstalled()) {
            $this->installer->ensureSetupCode();

            return redirect()->route('install.index');
        }

        return $next($request);
    }
}
