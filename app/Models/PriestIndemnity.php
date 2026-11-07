<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriestIndemnity extends Model
{
    protected $fillable = ['reference', 'priest_id', 'service_date', 'occasion', 'location', 'amount', 'financial_reference', 'notes', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
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
        return $query->whereBetween('service_date', [$start, $end]);
    }

    public function scopeByOccasion(Builder $query, string $occasion): Builder
    {
        return $query->where('occasion', $occasion);
    }
}