<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class CentreCorrection extends Model
{
    protected $fillable = ['cisco_id', 'nom', 'type_examen', 'centre_type', 'is_eps_gym', 'eps_capacity'];

    public function cisco(): BelongsTo
    {
        return $this->belongsTo(Cisco::class);
    }

    public function centresEcrit(): HasMany
    {
        return $this->hasMany(CentreEcrit::class);
    }

    public function vacationAssignments(): HasMany
    {
        return $this->hasMany(Vacation2026Assignment::class, 'centre_correction_id');
    }
}
