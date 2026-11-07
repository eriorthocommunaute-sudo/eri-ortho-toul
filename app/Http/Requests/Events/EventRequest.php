<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('events.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['liturgy', 'meeting', 'celebration', 'formation', 'charity', 'other'])],
            'expected_attendees' => ['nullable', 'integer', 'min:0'],
            'actual_attendees' => ['nullable', 'integer', 'min:0'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'actual_cost' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'status' => ['sometimes', Rule::in(['planned', 'confirmed', 'completed', 'cancelled'])],
        ];
    }
}