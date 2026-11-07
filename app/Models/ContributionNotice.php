<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionNotice extends Model
{
    protected $fillable = ['reference', 'member_id', 'contribution_id', 'notice_date', 'due_date', 'amount', 'status', 'sent_date', 'notes', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'notice_date' => 'date',
            'due_date' => 'date',
            'sent_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(Contribution::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', 'paid')->where('due_date', '<', now()->toDateString());
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['generated', 'sent']);
    }
}