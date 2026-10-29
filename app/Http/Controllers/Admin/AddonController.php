<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddonRequest;
use App\Models\Addon;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AddonController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Addons/Index', [
            'addons' => Addon::query()->orderBy('id')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Addons/Create');
    }

    public function store(AddonRequest $request): RedirectResponse
    {
        $addon = Addon::query()->create($request->validated());

        $this->audit->record('addon.created', $addon, after: $addon->toArray());

        return redirect()->route('admin.addons.index')->with('status', 'created');
    }

    public function edit(Addon $addon): Response
    {
        return Inertia::render('Admin/Addons/Edit', ['addon' => $addon]);
    }

    public function update(AddonRequest $request, Addon $addon): RedirectResponse
    {
        $before = $addon->toArray();

        $addon->update($request->validated());

        $this->audit->record('addon.updated', $addon, $before, $addon->toArray());

        return redirect()->route('admin.addons.index')->with('status', 'saved');
    }

    public function destroy(Addon $addon): RedirectResponse
    {
        $before = $addon->toArray();

        $addon->delete();

        $this->audit->record('addon.deleted', before: $before);

        return redirect()->route('admin.addons.index')->with('status', 'deleted');
    }
}
