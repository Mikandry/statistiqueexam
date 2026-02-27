<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class CentreEcrit extends Model
{
    protected $fillable = ['centre_correction_id', 'nom', 'type_examen'];

    public function centreCorrection(): BelongsTo
    {
        return $this->belongsTo(CentreCorrection::class);
    }

    public function repartitions(): HasMany
    {
        return $this->hasMany(RepartitionSalle::class);
    }
}
