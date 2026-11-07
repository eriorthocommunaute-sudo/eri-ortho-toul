<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentAllocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'payment_id',
        'member_contribution_id',
        'allocated_amount',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function memberContribution(): BelongsTo
    {
        return $this->belongsTo(MemberContribution::class);
    }

    public function isFullAllocation(): bool
    {
        return $this->allocated_amount >= 10.00;
    }

    public function remainingAmount(): ?float
    {
        $contribution = $this->memberContribution;
        $paidSoFar = $contribution->allocated_amount ?? 0;
        return max(0, 10.00 - $paidSoFar);
    }
}