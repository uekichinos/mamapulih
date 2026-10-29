<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Enums\DietTag;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DishRequest;
use App\Models\Dish;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DishController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Dishes/Index', [
            'dishes' => Dish::query()->orderBy('id')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Dishes/Create', [
            'dishes' => Dish::query()->where('active', true)->get(['id', 'name']),
            'tags' => $this->tagOptions(),
        ]);
    }

    public function store(DishRequest $request): RedirectResponse
    {
        $dish = Dish::query()->create($request->validated());

        $this->audit->record('dish.created', $dish, after: $dish->toArray());

        return redirect()->route('admin.dishes.index')->with('status', 'created');
    }

    public function edit(Dish $dish): Response
    {
        return Inertia::render('Admin/Dishes/Edit', [
            'dish' => $dish,
            'dishes' => Dish::query()->where('active', true)->where('id', '!=', $dish->id)->get(['id', 'name']),
            'tags' => $this->tagOptions(),
        ]);
    }

    public function update(DishRequest $request, Dish $dish): RedirectResponse
    {
        $before = $dish->toArray();

        $dish->update($request->validated());

        $this->audit->record('dish.updated', $dish, $before, $dish->toArray());

        return redirect()->route('admin.dishes.index')->with('status', 'saved');
    }

    public function destroy(Dish $dish): RedirectResponse
    {
        $before = $dish->toArray();

        $dish->delete();

        $this->audit->record('dish.deleted', before: $before);

        return redirect()->route('admin.dishes.index')->with('status', 'deleted');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function tagOptions(): array
    {
        return array_map(fn (DietTag $tag) => ['value' => $tag->value, 'label' => $tag->label()], DietTag::cases());
    }
}
