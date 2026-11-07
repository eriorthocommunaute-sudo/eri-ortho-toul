<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OffrandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('finance.record') ?? false;
    }

    public function rules(): array
    {
        return [
            'offering_date' => ['required', 'date', 'before_or_equal:today'],
            'donor_name' => ['nullable', 'string', 'max:255'],
            'member_id' => ['nullable', 'exists:members,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'occasion' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['pending', 'confirmed', 'cancelled'])],
            'payment_method' => ['sometimes', Rule::in(['cash', 'transfer', 'check', 'other'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}