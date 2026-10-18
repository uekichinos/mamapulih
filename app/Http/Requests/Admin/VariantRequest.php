<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VariantRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'birth_type' => ['required', 'in:normal,csec'],
            'duration_days' => ['required', 'in:7,14,21,28'],
            'meal_plan' => ['required', 'in:lunch,lunch_dinner'],
            'meals_per_day' => ['required', 'integer', 'min:1', 'max:10'],
            'price_sen' => ['required', 'integer', 'min:1'],
            'active' => ['required', 'boolean'],
        ];
    }
}
