<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ZoneRequest;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ZoneController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Zones/Index', [
            'zones' => Zone::query()->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Zones/Create');
    }

    public function store(ZoneRequest $request): RedirectResponse
    {
        $zone = Zone::query()->create($request->validated());

        $this->audit->record('zone.created', $zone, after: $zone->toArray());

        return redirect()->route('admin.zones.index')->with('status', 'created');
    }

    public function edit(Zone $zone): Response
    {
        return Inertia::render('Admin/Zones/Edit', ['zone' => $zone]);
    }

    public function update(ZoneRequest $request, Zone $zone): RedirectResponse
    {
        $before = $zone->toArray();

        $zone->update($request->validated());

        $this->audit->record('zone.updated', $zone, $before, $zone->toArray());

        return redirect()->route('admin.zones.index')->with('status', 'saved');
    }

    public function destroy(Zone $zone): RedirectResponse
    {
        $before = $zone->toArray();

        $zone->delete();

        $this->audit->record('zone.deleted', before: $before);

        return redirect()->route('admin.zones.index')->with('status', 'deleted');
    }
}
