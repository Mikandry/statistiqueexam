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

    public function centreDecisions(): HasMany
    {
        return $this->hasMany(CentreDecision::class);
    }

    public function hasRepartitionsForSession(?string $annee = null): bool
    {
        $query = $this->repartitions();

        if (filled($annee)) {
            $query->where('annee', $annee);
        }

        return $query->whereNotNull('saisi_par')
            ->where('saisi_par', '<>', '')
            ->exists();
    }
}
