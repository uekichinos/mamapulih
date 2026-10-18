<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ZoneRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'postcodes' => ['required', 'array', 'min:1'],
            'postcodes.*' => ['string', 'max:10'],
            'free_delivery' => ['required', 'boolean'],
            'transport_cost_per_trip_sen' => ['required', 'integer', 'min:0'],
            'one_trip_allowed' => ['required', 'boolean'],
        ];
    }
}
