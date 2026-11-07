<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialEntry extends Model
{
    protected $fillable = ['reference', 'entry_date', 'type', 'category', 'source_type', 'source_id', 'description', 'amount', 'payment_method', 'notes', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function source()
    {
        return $this->morphTo('source', 'source_type', 'source_id');
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    public function scopeForPeriod(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('entry_date', [$start, $end]);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function getIsIncomeAttribute(): bool
    {
        return $this->type === 'income';
    }
}