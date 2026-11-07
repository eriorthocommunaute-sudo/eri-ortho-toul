<?php

namespace App\Http\Requests\Purchases;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExceptionalDemandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('purchases.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['nullable', Rule::in(['emergency', 'maintenance', 'legal', 'medical', 'other'])],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'urgency' => ['required', Rule::in(['normal', 'urgent', 'critical'])],
        ];
    }
}