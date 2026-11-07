<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequest extends Model
{
    protected $fillable = ['reference', 'title', 'description', 'category', 'estimated_amount', 'supplier', 'financial_manager', 'signature', 'priority', 'status', 'requested_by', 'approved_by', 'approved_at', 'approval_notes', 'equipment_reference', 'financial_reference', 'requester_name', 'request_date', 'requester_role', 'requester_phone', 'items', 'expense_details', 'decision', 'authorized_amount', 'authorizing_manager', 'authorizing_signature', 'paid_amount', 'payment_date', 'payment_method', 'payment_reference', 'accounting_document_number', 'supporting_document_type', 'actual_spent_amount', 'remaining_amount', 'proof_submitted_date', 'requester_signature', 'treasurer_signature', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'estimated_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'items' => 'array', 'expense_details' => 'array',
            'payment_date' => 'date',
            'proof_submitted_date' => 'date',
            'authorized_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'actual_spent_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
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

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function canBeApproved(): bool
    {
        return in_array($this->status, ['submitted', 'under_review']);
    }
}
