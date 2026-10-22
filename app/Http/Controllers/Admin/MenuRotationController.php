<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuRotationRequest;
use App\Models\Dish;
use App\Models\MenuRotation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MenuRotationController extends Controller
{
    private const MEALS = ['lunch', 'dinner'];

    private const DAYS = [1, 2, 3, 4, 5, 6, 7];

    public function __construct(private AuditLogger $audit) {}

    public function index(): Response
    {
        return Inertia::render('Admin/MenuRotations/Index', [
            'menuRotations' => MenuRotation::query()->orderBy('id')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/MenuRotations/Create');
    }

    public function store(MenuRotationRequest $request): RedirectResponse
    {
        $menuRotation = MenuRotation::query()->create($request->validated());

        $this->audit->record('menu_rotation.created', $menuRotation, after: $menuRotation->toArray());

        return redirect()->route('admin.menu-rotations.edit', $menuRotation)->with('status', 'created');
    }

    public function edit(MenuRotation $menuRotation): Response
    {
        return Inertia::render('Admin/MenuRotations/Edit', [
            'menuRotation' => $menuRotation,
            'rotationDays' => $menuRotation->rotationDays()->with('dish')->get(),
            'dishes' => Dish::query()->where('active', true)->get(['id', 'name']),
            'days' => self::DAYS,
            'meals' => self::MEALS,
        ]);
    }

    public function update(MenuRotationRequest $request, MenuRotation $menuRotation): RedirectResponse
    {
        $before = $menuRotation->toArray();

        $menuRotation->update($request->validated());

        $this->audit->record('menu_rotation.updated', $menuRotation, $before, $menuRotation->toArray());

        return redirect()->route('admin.menu-rotations.edit', $menuRotation)->with('status', 'saved');
    }

    public function destroy(MenuRotation $menuRotation): RedirectResponse
    {
        $before = $menuRotation->toArray();

        $menuRotation->delete();

        $this->audit->record('menu_rotation.deleted', before: $before);

        return redirect()->route('admin.menu-rotations.index')->with('status', 'deleted');
    }
}
