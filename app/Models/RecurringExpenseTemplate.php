<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringExpenseTemplate extends Model
{
    protected $fillable = [
        'reference', 'name', 'category', 'amount', 'due_day', 'beneficiary',
        'payment_method', 'start_year', 'start_month', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(RecurringExpenseOccurrence::class, 'template_id');
    }
}
