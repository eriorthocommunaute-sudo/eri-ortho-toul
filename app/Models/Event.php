<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = ['reference', 'title', 'description', 'starts_at', 'ends_at', 'location', 'type', 'expected_attendees', 'actual_attendees', 'budget', 'actual_cost', 'financial_reference', 'status', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'budget' => 'decimal:2',
            'actual_cost' => 'decimal:2',
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

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now())->where('status', '!=', 'cancelled');
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->starts_at >= now() && $this->status !== 'cancelled';
    }
}