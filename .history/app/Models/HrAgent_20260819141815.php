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
        'matricule', 'nom', 'prenoms', 'sexe', 'date_naissance', 'cin', 'telephone', 'email', 'adresse',
        'statut', 'corps', 'grade', 'categorie', 'echelon', 'fonction', 'date_recrutement', 'date_prise_service',
        'direction', 'service', 'bureau', 'superieur_hierarchique', 'situation_administrative', 'actif',
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
        return $this->hasMany(HrAssignment::class, 'agent_id')->latest('date_debut');
    }

    public function currentAssignment(): HasMany
    {
        return $this->hasMany(HrAssignment::class, 'agent_id')->where('current', true);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->nom.' '.$this->prenoms);
    }
}
