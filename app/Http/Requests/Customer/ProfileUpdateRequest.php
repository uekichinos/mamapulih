<?php

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(Customer::class)->ignore($this->user('customers')->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique(Customer::class)->ignore($this->user('customers')->id),
            ],
        ];
    }
}
