<?php

namespace App\Http\Controllers\Install;

use App\Domain\Setup\InstallationService;
use App\Http\Controllers\Controller;
use App\Models\SystemState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InstallController extends Controller
{
    public function __construct(private InstallationService $installer) {}

    public function code(): Response|RedirectResponse
    {
        if (session('install.verified')) {
            return redirect()->route('install.environment');
        }

        $this->installer->ensureSetupCode();

        return Inertia::render('Install/Code', [
            'locked' => SystemState::current()->isLocked(),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        if (! $this->installer->verifyCode($data['code'])) {
            return back()->withErrors(['code' => 'That setup code is incorrect or has expired.']);
        }

        $request->session()->put('install.verified', true);

        return redirect()->route('install.environment');
    }

    public function environment(): Response|RedirectResponse
    {
        if ($redirect = $this->requireVerified()) {
            return $redirect;
        }

        return Inertia::render('Install/Environment', [
            'checks' => $this->installer->checkEnvironment(),
            'ready' => $this->installer->environmentIsReady(),
        ]);
    }

    public function database(): Response|RedirectResponse
    {
        if ($redirect = $this->requireVerified()) {
            return $redirect;
        }

        if (! $this->installer->environmentIsReady()) {
            return redirect()->route('install.environment');
        }

        Artisan::call('migrate', ['--force' => true]);

        return Inertia::render('Install/Database', [
            'output' => Artisan::output(),
        ]);
    }

    public function createAdmin(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireVerified()) {
            return $redirect;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->session()->put('install.admin', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        return redirect()->route('install.finish');
    }

    public function finish(): Response|RedirectResponse
    {
        if ($redirect = $this->requireVerified()) {
            return $redirect;
        }

        if (! $admin = session('install.admin')) {
            return redirect()->route('install.database');
        }

        return Inertia::render('Install/Finish', [
            'adminEmail' => $admin['email'],
        ]);
    }

    public function complete(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireVerified()) {
            return $redirect;
        }

        $admin = $request->session()->get('install.admin');

        abort_if(! $admin, 419);

        $data = $request->validate(['demo_data' => ['required', 'boolean']]);

        $owner = $this->installer->complete($admin, $data['demo_data']);

        $request->session()->forget(['install.verified', 'install.admin']);

        Auth::login($owner);

        return redirect()->route('dashboard');
    }

    private function requireVerified(): ?RedirectResponse
    {
        if ($this->installer->isInstalled()) {
            return redirect()->route('login');
        }

        if (! session('install.verified')) {
            return redirect()->route('install.index');
        }

        return null;
    }
}
