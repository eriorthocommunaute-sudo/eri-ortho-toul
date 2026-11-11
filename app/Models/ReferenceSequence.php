<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceSequence extends Model
{
    protected $fillable = ['prefix', 'sequence_year', 'sequence_month', 'last_number'];
}
