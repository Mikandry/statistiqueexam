<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrAgent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_agents';

    protected $fillable = [
        'matricule',
        'nom',
        'prenoms',
        'sexe',
        'date_naissance',
        'cin',
        'telephone',
        'email',
        'adresse',

        // Situation administrative
        'statut',
        'corps',
        'grade',
        'indice',
        'categorie',
        'echelon',

        // Fonction
        'fonction',
        'direction',
        'service',
        'bureau',
        'superieur_hierarchique',

        // Administration
        'budget',
        'chapitre',
        'date_recrutement',
        'date_prise_service',
        'situation_administrative',

        'actif',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'date_recrutement' => 'date',
            'date_prise_service' => 'date',
            'actif' => 'boolean',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(HrAssignment::class, 'agent_id')
            ->latest('date_debut');
    }

    public function currentAssignment(): HasMany
    {
        return $this->hasMany(HrAssignment::class, 'agent_id')
            ->where('current', true);
    }

    public function events(): HasMany
    {
        return $this->hasMany(HrEvent::class, 'agent_id')
            ->latest('date_debut');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->nom . ' ' . $this->prenoms);
    }

    /**
     * Situation administrative complète de l'agent.
     */
    public function getAdministrativeIdentityAttribute(): array
    {
        return [
            'nom' => $this->full_name,
            'matricule' => $this->matricule,
            'corps' => $this->corps,
            'grade' => $this->grade,
            'indice' => $this->indice,
            'budget' => $this->budget,
            'chapitre' => $this->chapitre,
            'date_recrutement' => $this->date_recrutement,
            'date_prise_service' => $this->date_prise_service,
            'direction' => $this->direction,
            'service' => $this->service,
            'bureau' => $this->bureau,
        ];
    }
    public function user(): HasOne
{
    return $this->hasOne(User::class, 'hr_agent_id');
}
}