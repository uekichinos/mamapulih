<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RotationDayRequest;
use App\Models\MenuRotation;
use App\Models\RotationDay;
use Illuminate\Http\RedirectResponse;

class RotationDayController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function store(RotationDayRequest $request, MenuRotation $menuRotation): RedirectResponse
    {
        $rotationDay = $menuRotation->rotationDays()->updateOrCreate(
            ['day_no' => $request->validated('day_no'), 'meal' => $request->validated('meal')],
            ['dish_id' => $request->validated('dish_id')],
        );

        $this->audit->record('rotation_day.saved', $rotationDay, after: $rotationDay->toArray());

        return redirect()->route('admin.menu-rotations.edit', $menuRotation)->with('status', 'saved');
    }

    public function update(RotationDayRequest $request, RotationDay $rotationDay): RedirectResponse
    {
        $before = $rotationDay->toArray();

        $rotationDay->update(['dish_id' => $request->validated('dish_id')]);

        $this->audit->record('rotation_day.saved', $rotationDay, $before, $rotationDay->toArray());

        return redirect()->route('admin.menu-rotations.edit', $rotationDay->menu_rotation_id)->with('status', 'saved');
    }

    public function destroy(RotationDay $rotationDay): RedirectResponse
    {
        $menuRotationId = $rotationDay->menu_rotation_id;
        $before = $rotationDay->toArray();

        $rotationDay->delete();

        $this->audit->record('rotation_day.deleted', before: $before);

        return redirect()->route('admin.menu-rotations.edit', $menuRotationId)->with('status', 'deleted');
    }
}
