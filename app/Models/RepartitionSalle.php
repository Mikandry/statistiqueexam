<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class RepartitionSalle extends Model
{
    public const LANGUES = ['Allemand', 'Esp', 'Anglais', 'Option B', 'Etranger Option A', 'Etranger Option B'];

    protected $fillable = [
        'centre_ecrit_id',
        'annee',
        'langue',
        'numero_salle',
        'effectif',
        'saisi_par',
        'axe_dispatching',
        'point_largage',
    ];

    public function centreEcrit(): BelongsTo
    {
        return $this->belongsTo(CentreEcrit::class);
    }

    public function vacationAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Vacation2026Assignment::class, 'salle_id');
    }
}
