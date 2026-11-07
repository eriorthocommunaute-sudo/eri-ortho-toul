<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriestTransport extends Model
{
    protected $fillable = ['reference', 'priest_id', 'transport_date', 'origin', 'destination', 'occasion', 'distance_km', 'amount', 'transport_type', 'driver_name', 'financial_reference', 'notes', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'transport_date' => 'date',
            'distance_km' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function priest(): BelongsTo
    {
        return $this->belongsTo(Priest::class);
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
        return $query->whereBetween('transport_date', [$start, $end]);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('transport_type', $type);
    }
}