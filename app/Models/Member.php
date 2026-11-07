<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;
    protected $fillable = ['member_code', 'title', 'given_name', 'family_name', 'display_name', 'phone', 'email', 'address', 'family_size', 'joined_at', 'status', 'restricted_notes', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['joined_at' => 'date'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function foundingCotisations(): HasMany
    {
        return $this->hasMany(FoundingCotisation::class);
    }

    public function memberContributions(): HasMany
    {
        return $this->hasMany(MemberContribution::class)->orderBy('period_month');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(fn (Builder $s) => $s->where('member_code', 'like', "%{$term}%")->orWhere('given_name', 'like', "%{$term}%")->orWhere('family_name', 'like', "%{$term}%")->orWhere('display_name', 'like', "%{$term}%")));
    }

    public function scopeOrderByReference(Builder $query): Builder
    {
        return $query->orderBy('member_code');
    }
}
