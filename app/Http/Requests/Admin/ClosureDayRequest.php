<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ClosureDayRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'after_or_equal:today'],
            // V1 / Phase 1 supports full-day closures only; partial-run
            // closures (lunch/dinner) are Phase 2. The `scope` column
            // already supports them so no migration is needed later.
            'scope' => ['required', 'in:all'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
