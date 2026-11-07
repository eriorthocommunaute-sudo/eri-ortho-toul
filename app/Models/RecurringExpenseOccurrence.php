<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringExpenseOccurrence extends Model
{
    protected $fillable = [
        'reference', 'template_id', 'period_year', 'period_month', 'amount_due', 'amount_paid',
        'due_date', 'paid_at', 'payment_method', 'payment_reference', 'proof_path', 'status',
        'financial_entry_id', 'paid_by', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_due' => 'decimal:2', 'amount_paid' => 'decimal:2',
            'due_date' => 'date', 'paid_at' => 'date',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(RecurringExpenseTemplate::class, 'template_id');
    }

    public function financialEntry(): BelongsTo
    {
        return $this->belongsTo(FinancialEntry::class);
    }
}
