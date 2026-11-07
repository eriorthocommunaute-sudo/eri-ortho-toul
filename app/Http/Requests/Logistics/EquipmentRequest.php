<?php

namespace App\Http\Requests\Logistics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('logistics.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', Rule::in(['audio', 'video', 'furniture', 'liturgical', 'IT', 'other'])],
            'description' => ['nullable', 'string'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'purchase_reference' => ['nullable', 'string', 'max:50'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'condition' => ['nullable', Rule::in(['new', 'good', 'fair', 'poor', 'disposed'])],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['nullable', Rule::in(['cash', 'transfer', 'check', 'other'])],
        ];
    }
}