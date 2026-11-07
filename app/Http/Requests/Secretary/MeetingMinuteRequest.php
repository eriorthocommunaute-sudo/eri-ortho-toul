<?php

namespace App\Http\Requests\Secretary;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MeetingMinuteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('secretary.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'meeting_date' => ['required', 'date'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['board', 'general_assembly', 'bureau', 'committee', 'other'])],
            'agenda' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'attendees' => ['nullable', 'array'],
            'attendees.*' => ['exists:members,id'],
            'absentees' => ['nullable', 'array'],
            'absentees.*' => ['exists:members,id'],
            'decisions' => ['nullable', 'string'],
            'action_items' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['draft', 'approved', 'archived'])],
            'secretary_id' => ['nullable', 'exists:users,id'],
            'president_id' => ['nullable', 'exists:users,id'],
        ];
    }
}