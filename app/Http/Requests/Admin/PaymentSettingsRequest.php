<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PaymentSettingsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'instructions.en' => ['nullable', 'string', 'max:2000'],
            'instructions.ms' => ['nullable', 'string', 'max:2000'],
            'qr_image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
