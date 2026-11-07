<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('finance.record') ?? false;
    }

    public function rules(): array
    {
        return [
            'donation_date' => ['required', 'date', 'before_or_equal:today'],
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_type' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['pending', 'confirmed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}