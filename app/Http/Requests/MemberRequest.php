<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission($this->isMethod('post') ? 'members.create' : 'members.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:30'],
            'given_name' => ['required', 'string', 'max:120'],
            'family_name' => ['required', 'string', 'max:120'],
            'display_name' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'joined_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'former'])],
            'restricted_notes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
