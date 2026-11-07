<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contribution extends Model
{
    protected $fillable = ['reference', 'year', 'month', 'amount_per_member', 'expected_count', 'received_count', 'total_expected', 'total_received', 'status', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'amount_per_member' => 'decimal:2',
            'total_expected' => 'decimal:2',
            'total_received' => 'decimal:2',
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

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class, 'contribution_id');
    }

    public function notices(): HasMany
    {
        return $this->hasMany(ContributionNotice::class, 'contribution_id');
    }

    public function scopeForPeriod(Builder $query, int $year, int $month): Builder
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->total_expected == 0) return 0;
        return round(($this->total_received / $this->total_expected) * 100, 1);
    }
}