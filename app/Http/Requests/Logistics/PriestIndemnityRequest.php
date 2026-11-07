<?php

namespace App\Http\Requests\Logistics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriestIndemnityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('finance.record') ?? false;
    }

    public function rules(): array
    {
        return [
            'priest_id' => ['nullable', 'integer', 'exists:priests,id'],
            'service_date' => ['required', 'date', 'before_or_equal:today'],
            'occasion' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'payment_method' => ['nullable', Rule::in(['cash', 'transfer', 'check', 'other'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}