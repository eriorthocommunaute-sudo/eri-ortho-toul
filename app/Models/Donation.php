<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    protected $fillable = ['reference', 'donation_date', 'donor_name', 'donor_type', 'amount', 'purpose', 'financial_reference', 'notes', 'status', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'donation_date' => 'date',
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

    public function scopeForPeriod(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('donation_date', [$start, $end]);
    }

    public function scopeByDonor(Builder $query, string $donorName): Builder
    {
        return $query->where('donor_name', 'like', "%{$donorName}%");
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}