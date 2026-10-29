<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageRequest;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PackageController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Packages/Index', [
            'packages' => Package::query()->withCount('variants')->orderBy('id')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Packages/Create');
    }

    public function store(PackageRequest $request): RedirectResponse
    {
        $package = Package::query()->create($request->validated());

        $this->audit->record('package.created', $package, after: $package->toArray());

        return redirect()->route('admin.packages.edit', $package)->with('status', 'created');
    }

    public function edit(Package $package): Response
    {
        return Inertia::render('Admin/Packages/Edit', [
            'package' => $package,
            'variants' => $package->variants()->orderBy('duration_days')->get(),
        ]);
    }

    public function update(PackageRequest $request, Package $package): RedirectResponse
    {
        $before = $package->toArray();

        $package->update($request->validated());

        $this->audit->record('package.updated', $package, $before, $package->toArray());

        return back()->with('status', 'saved');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $before = $package->toArray();

        $package->delete();

        $this->audit->record('package.deleted', before: $before);

        return redirect()->route('admin.packages.index')->with('status', 'deleted');
    }
}
