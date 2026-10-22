<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RotationDayRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'day_no' => ['required', 'integer', 'min:1', 'max:7'],
            'meal' => ['required', 'in:lunch,dinner'],
            'dish_id' => ['required', 'integer', 'exists:dishes,id'],
        ];
    }
}
