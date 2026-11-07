<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoundingCotisationHistory extends Model
{
    protected $fillable = ['founding_cotisation_id', 'user_id', 'motif', 'before_values', 'after_values', 'occurred_at'];

    protected function casts(): array
    {
        return [
            'before_values' => 'array',
            'after_values' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function foundingCotisation(): BelongsTo
    {
        return $this->belongsTo(FoundingCotisation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
