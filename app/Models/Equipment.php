<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipment extends Model
{
    protected $fillable = ['reference', 'name', 'category', 'description', 'purchase_date', 'purchase_price', 'purchase_reference', 'financial_reference', 'supplier', 'location', 'condition', 'serial_number', 'notes', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'purchase_price' => 'decimal:2',
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

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeByCondition(Builder $query, string $condition): Builder
    {
        return $query->where('condition', $condition);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereIn('condition', ['new', 'good']);
    }
}