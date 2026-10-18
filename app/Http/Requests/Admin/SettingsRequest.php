<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'deposit_type' => ['required', 'in:fixed,percentage'],
            'deposit_amount' => ['required', 'integer', 'min:1'],
            'balance_due_days_before_start' => ['required', 'integer', 'min:0'],
            'start_notice_days' => ['required', 'integer', 'min:0'],
            'approval_hold_hours' => ['required', 'integer', 'min:1'],
            'regular_capacity' => ['required', 'integer', 'min:1'],
            'buffer_capacity' => ['required', 'integer', 'min:0'],
            'twins_slots' => ['required', 'in:1,2'],
        ];
    }
}
