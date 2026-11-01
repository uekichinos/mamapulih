<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function start(Request $request, User $staff): RedirectResponse
    {
        $owner = $request->user('web');

        abort_if($staff->is($owner), 403, 'You cannot log in as yourself.');
        abort_if($staff->role === UserRole::Owner, 403, 'Cannot log in as another Owner account.');
        abort_if($request->session()->has('impersonator_id'), 403, 'Already logged in as another account.');

        $request->session()->put('impersonator_id', $owner->id);

        Auth::guard('web')->login($staff);
        $request->session()->regenerate();

        $this->audit->record('staff.impersonation_started', $staff, actor: $owner);

        return redirect()->route('dashboard');
    }

    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->pull('impersonator_id');

        abort_if($impersonatorId === null, 403, 'You are not logged in as another account.');

        $staff = $request->user('web');
        $owner = User::query()->find($impersonatorId);

        abort_unless($owner instanceof User, 403, 'The account you were logged in from no longer exists.');

        Auth::guard('web')->login($owner);
        $request->session()->regenerate();

        $this->audit->record('staff.impersonation_ended', $staff, actor: $owner);

        return redirect()->route('admin.staff.index');
    }
}
