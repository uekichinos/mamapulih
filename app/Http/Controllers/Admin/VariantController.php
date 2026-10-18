<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VariantRequest;
use App\Models\Package;
use App\Models\Variant;
use Illuminate\Http\RedirectResponse;

class VariantController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function store(VariantRequest $request, Package $package): RedirectResponse
    {
        $variant = $package->variants()->create($request->validated());

        $this->audit->record('variant.created', $variant, after: $variant->toArray());

        return redirect()->route('admin.packages.edit', $package)->with('status', 'created');
    }

    public function update(VariantRequest $request, Variant $variant): RedirectResponse
    {
        $before = $variant->toArray();

        $variant->update($request->validated());

        $this->audit->record('variant.updated', $variant, $before, $variant->toArray());

        return redirect()->route('admin.packages.edit', $variant->package_id)->with('status', 'saved');
    }

    public function destroy(Variant $variant): RedirectResponse
    {
        $packageId = $variant->package_id;
        $before = $variant->toArray();

        $variant->delete();

        $this->audit->record('variant.deleted', before: $before);

        return redirect()->route('admin.packages.edit', $packageId)->with('status', 'deleted');
    }
}
