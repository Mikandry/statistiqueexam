<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class CentreCorrection extends Model
{
    protected $fillable = ['cisco_id', 'nom'];

    public function cisco(): BelongsTo
    {
        return $this->belongsTo(Cisco::class);
    }

    public function centresEcrit(): HasMany
    {
        return $this->hasMany(CentreEcrit::class);
    }
}
