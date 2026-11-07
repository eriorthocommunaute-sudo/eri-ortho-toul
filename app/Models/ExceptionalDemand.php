<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExceptionalDemand extends Model
{
    protected $fillable = ['reference', 'title', 'description', 'category', 'amount', 'urgency', 'status', 'requested_by', 'reviewed_by', 'reviewed_at', 'approved_by', 'approved_at', 'approval_notes', 'financial_reference', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', 'under_review');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByUrgency(Builder $query, string $urgency): Builder
    {
        return $query->where('urgency', $urgency);
    }
}