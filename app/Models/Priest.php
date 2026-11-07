<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Priest extends Model
{
    protected $fillable = ['name', 'notes', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function transports(): HasMany
    {
        return $this->hasMany(PriestTransport::class);
    }

    public function indemnities(): HasMany
    {
        return $this->hasMany(PriestIndemnity::class);
    }
}
