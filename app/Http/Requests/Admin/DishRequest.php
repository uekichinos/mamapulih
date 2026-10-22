<?php

namespace App\Http\Requests\Admin;

use App\Enums\DietTag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DishRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name.en' => ['required', 'string', 'max:255'],
            'name.ms' => ['required', 'string', 'max:255'],
            'tags' => ['array'],
            'tags.*' => [Rule::in(DietTag::values())],
            'substitute_dish_id' => ['nullable', 'integer', 'exists:dishes,id'],
            'active' => ['required', 'boolean'],
        ];
    }
}
