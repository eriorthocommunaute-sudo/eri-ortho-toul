<?php

namespace App\Http\Requests\Secretary;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContributionNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('secretary.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'exists:members,id'],
            'contribution_id' => ['required', 'exists:contributions,id'],
            'notice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:notice_date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'status' => ['sometimes', Rule::in(['generated', 'sent', 'paid', 'overdue', 'cancelled'])],
            'sent_date' => ['nullable', 'date'],
        ];
    }
}