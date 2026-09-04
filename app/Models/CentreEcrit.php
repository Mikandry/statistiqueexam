<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class CentreEcrit extends Model
{
    protected $fillable = ['centre_correction_id', 'nom', 'type_examen', 'centre_type', 'is_eps_gym'];

    public function centreCorrection(): BelongsTo
    {
        return $this->belongsTo(CentreCorrection::class);
    }

    public function repartitions(): HasMany
    {
        return $this->hasMany(RepartitionSalle::class);
    }

    public function vacationAssignments(): HasMany
    {
        return $this->hasMany(Vacation2026Assignment::class, 'centre_ecrit_id');
    }
}
