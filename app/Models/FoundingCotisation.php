<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoundingCotisation extends Model
{
    protected $fillable = ['member_id', 'amount', 'remaining', 'notes', 'payment_method', 'payment_date', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'remaining' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(FoundingCotisationHistory::class)->orderByDesc('occurred_at');
    }
}
