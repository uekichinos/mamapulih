<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddonRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name.en' => ['required', 'string', 'max:255'],
            'name.ms' => ['required', 'string', 'max:255'],
            'price_sen' => ['required', 'integer', 'min:1'],
            'per' => ['required', 'in:day,booking'],
            'active' => ['required', 'boolean'],
        ];
    }
}
