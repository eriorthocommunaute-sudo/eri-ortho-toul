<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MemberContribution extends Model
{
    use HasFactory;
    protected $fillable = ['member_id', 'period_year', 'period_month', 'expected_amount', 'allocated_amount', 'status', 'paid_count', 'total_paid'];

    protected function casts(): array
    {
        return [
            'expected_amount' => 'decimal:2',
            'allocated_amount' => 'decimal:2',
            'total_paid' => 'decimal:2',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeByPeriod(Builder $query, int $year, int $month): Builder
    {
        return $query->where('period_year', $year)->where('period_month', $month);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === 'partially_paid';
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    public function isCoveredInAdvance(): bool
    {
        return $this->status === 'covered_in_advance';
    }
}