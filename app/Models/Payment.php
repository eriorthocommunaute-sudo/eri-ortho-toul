<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'payment_reference',
        'receipt_reference',
        'member_id',
        'payment_date',
        'amount',
        'payment_method',
        'status',
        'notes',
        'evidence_path',
        'created_by',
        'updated_by',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function financialEntry(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\FinancialEntry::class, 'source_id')
            ->where('source_type', self::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function cancel(): bool
    {
        if ($this->status === 'cancelled') {
            return false;
        }
        $this->status = 'cancelled';
        $this->save();
        return true;
    }
}