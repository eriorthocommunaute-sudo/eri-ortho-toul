<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('finance.record') ?? false;
    }

    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'min:2020', 'max:2030'],
            'month' => ['required', 'integer', 'min:1', 'max:12', Rule::unique('contributions')->where(fn ($q) => $q->where('year', $this->integer('year')))->ignore($this->route('contribution'))],
            'amount_per_member' => ['required', 'numeric', 'min:0', 'max:1000'],
            'status' => ['sometimes', Rule::in(['open', 'closed'])],
        ];
    }
}