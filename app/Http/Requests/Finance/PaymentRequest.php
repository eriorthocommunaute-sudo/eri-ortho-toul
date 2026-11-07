<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('finance.record') ?? false;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'exists:members,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'payment_method' => ['required', 'in:cash,bank_transfer'],
            'months_covered' => ['required', 'array', 'min:1', 'max:36'],
            'months_covered.*' => ['date_format:Y-m'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.exists' => 'Le membre sélectionné est invalide.',
            'payment_date.date' => 'La date de paiement doit être une date valide.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant doit être supérieur à zéro.',
            'payment_method.in' => 'Méthode de paiement invalide.',
            'months_covered.required' => 'Au moins un mois doit être sélectionné.',
            'months_covered.*.date_format' => 'Veuillez sélectionner des mois valides.',
        ];
    }
}
