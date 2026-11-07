<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingMinute extends Model
{
    protected $fillable = ['reference', 'title', 'meeting_date', 'starts_at', 'ends_at', 'location', 'type', 'agenda', 'content', 'attendees', 'absentees', 'decisions', 'action_items', 'status', 'secretary_id', 'president_id', 'approved_at', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'attendees' => 'array',
            'absentees' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function secretary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretary_id');
    }

    public function president(): BelongsTo
    {
        return $this->belongsTo(User::class, 'president_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function getAttendeeMembersAttribute()
    {
        if (!$this->attendees) return collect();
        return Member::whereIn('id', $this->attendees)->get();
    }
}