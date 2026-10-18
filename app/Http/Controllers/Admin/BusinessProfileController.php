<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BusinessProfileRequest;
use App\Models\BusinessProfile;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BusinessProfileController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function edit(): Response
    {
        return Inertia::render('Admin/BusinessProfile/Edit', [
            'profile' => BusinessProfile::current(),
        ]);
    }

    public function update(BusinessProfileRequest $request): RedirectResponse
    {
        $profile = BusinessProfile::current();
        $before = $profile->toArray();

        $profile->update($request->validated());

        $this->audit->record('business_profile.updated', $profile, $before, $profile->toArray());

        return back()->with('status', 'saved');
    }
}
