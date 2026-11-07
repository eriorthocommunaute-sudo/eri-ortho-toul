<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['actor_id', 'action', 'subject_type', 'subject_id', 'before_values', 'after_values', 'occurred_at'];

    protected function casts(): array
    {
        return ['before_values' => 'array', 'after_values' => 'array', 'occurred_at' => 'datetime'];
    }
}
