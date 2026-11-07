<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = ['reference', 'receipt_type', 'member_id', 'payer_name', 'description', 'contribution_id', 'source_type', 'source_id', 'payment_date', 'amount', 'payment_method', 'financial_reference', 'notes', 'status', 'confirmed_by', 'confirmed_at', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    public function source(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(Contribution::class);
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeForMember(Builder $query, int $memberId): Builder
    {
        return $query->where('member_id', $memberId);
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
}