<?php

namespace App\Http\Requests\Logistics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriestTransportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('finance.record') ?? false;
    }

    public function rules(): array
    {
        return [
            'priest_id' => ['nullable', 'integer', 'exists:priests,id'],
            'transport_date' => ['required', 'date', 'before_or_equal:today'],
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'occasion' => ['nullable', 'string', 'max:255'],
            'distance_km' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'transport_type' => ['required', Rule::in(['personal_vehicle', 'taxi', 'train', 'bus', 'flight', 'other'])],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', Rule::in(['cash', 'transfer', 'check', 'other'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}