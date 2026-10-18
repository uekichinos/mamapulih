<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StaffUserController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Staff/Index', [
            'staff' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Staff/Create');
    }

    public function store(StaffUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $staff = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => UserRole::from($data['role']),
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);

        $this->audit->record('staff.created', $staff, after: $staff->only(['name', 'email', 'role']));

        return redirect()->route('admin.staff.index')->with('status', 'created');
    }

    public function edit(User $staff): Response
    {
        return Inertia::render('Admin/Staff/Edit', ['staff' => $staff]);
    }

    public function update(StaffUserRequest $request, User $staff): RedirectResponse
    {
        $data = $request->validated();
        $before = $staff->only(['name', 'email', 'role']);

        $staff->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => UserRole::from($data['role']),
        ]);

        if (filled($data['password'] ?? null)) {
            $staff->password = $data['password'];
        }

        $staff->save();

        $this->audit->record('staff.updated', $staff, $before, $staff->only(['name', 'email', 'role']));

        return redirect()->route('admin.staff.index')->with('status', 'saved');
    }

    public function destroy(User $staff): RedirectResponse
    {
        if ($staff->is(auth()->user())) {
            throw ValidationException::withMessages(['staff' => 'You cannot remove your own account.']);
        }

        if ($staff->isOwner() && User::query()->where('role', UserRole::Owner)->count() <= 1) {
            throw ValidationException::withMessages(['staff' => 'At least one Owner account must remain.']);
        }

        $before = $staff->only(['name', 'email', 'role']);

        $staff->delete();

        $this->audit->record('staff.deleted', before: $before);

        return redirect()->route('admin.staff.index')->with('status', 'deleted');
    }
}
