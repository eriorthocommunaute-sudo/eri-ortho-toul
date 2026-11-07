<?php

namespace App\Http\Requests\Purchases;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequestRequest extends FormRequest
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
            'category' => ['required', Rule::in([
                'equipment', 'supplies', 'maintenance', 'transport',
                'train_ticket', 'services', 'other', 'operating_expense',
            ])],
            'estimated_amount' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'requester_name' => ['nullable', 'string', 'max:255'],
            'request_date' => ['nullable', 'string', 'max:20'],
            'requester_role' => ['nullable', 'string', 'max:255'],
            'requester_phone' => ['nullable', 'string', 'max:40'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'items' => ['nullable', 'array', 'max:30'],
            'items.*.designation' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'expense_details' => ['nullable', 'array'],
            'expense_details.equipment_type' => ['nullable', 'string', 'max:255'],
            'expense_details.installation_location' => ['nullable', 'string', 'max:255'],
            'expense_details.intended_use' => ['nullable', 'string', 'max:2000'],
            'expense_details.supply_type' => ['nullable', 'string', 'max:255'],
            'expense_details.supply_quantity' => ['nullable', 'string', 'max:100'],
            'expense_details.asset_or_location' => ['nullable', 'string', 'max:255'],
            'expense_details.repair_description' => ['nullable', 'string', 'max:2000'],
            'expense_details.service_description' => ['nullable', 'string', 'max:2000'],
            'expense_details.provider' => ['nullable', 'string', 'max:255'],
            'expense_details.passenger' => ['nullable', 'string', 'max:255'],
            'expense_details.departure' => ['nullable', 'string', 'max:255'],
            'expense_details.destination' => ['nullable', 'string', 'max:255'],
            'expense_details.departure_date' => ['nullable', 'date'],
            'expense_details.return_date' => ['nullable', 'date', 'after_or_equal:expense_details.departure_date'],
            'expense_details.transport_type' => ['nullable', 'string', 'max:100'],
            'expense_details.service_date' => ['nullable', 'date'],
            'expense_details.beneficiary' => ['nullable', 'string', 'max:255'],
            'expense_details.purpose' => ['nullable', 'string', 'max:2000'],
            'expense_details.justification' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
