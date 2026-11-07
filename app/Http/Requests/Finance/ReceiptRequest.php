<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('finance.record') ?? false;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'exists:members,id'],
            'contribution_ids' => ['required', 'array', 'min:1', 'max:3'],
            'contribution_ids.*' => ['exists:contributions,id'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'check', 'other'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}