<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClosureDayRequest;
use App\Models\ClosureDay;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClosureDayController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function index(): Response
    {
        return Inertia::render('Admin/ClosureDays/Index', [
            'closureDays' => ClosureDay::query()
                ->where('date', '>=', now()->toDateString())
                ->orderBy('date')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/ClosureDays/Create');
    }

    public function store(ClosureDayRequest $request): RedirectResponse
    {
        $closureDay = ClosureDay::query()->create([
            ...$request->validated(),
            'source' => 'manual',
            'created_by' => auth()->id(),
        ]);

        $this->audit->record('closure_day.created', $closureDay, after: $closureDay->toArray());

        return redirect()->route('admin.closure-days.index')->with('status', 'created');
    }

    public function destroy(ClosureDay $closureDay): RedirectResponse
    {
        $before = $closureDay->toArray();

        $closureDay->delete();

        $this->audit->record('closure_day.deleted', before: $before);

        return redirect()->route('admin.closure-days.index')->with('status', 'deleted');
    }
}
