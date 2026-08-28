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
        'user_id',
        'matricule',
        'nom',
        'prenoms',
        'sexe',
        'date_naissance',
        'cin',
        'telephone',
        'email',
        'adresse',
        'statut',
        'corps',
        'grade',
        'indice',
        'categorie',
        'echelon',
        'fonction',
        'date_recrutement',
        'date_prise_service',
        'direction',
        'service',
        'bureau',
        'superieur_hierarchique',
        'situation_administrative',
        'actif',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(HrAssignment::class, 'agent_id')
            ->orderByDesc('date_debut');
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(HrAssignment::class, 'agent_id')
            ->where('current', true)
            ->latestOfMany('date_debut');
    }

    public function events(): HasMany
    {
        return $this->hasMany(HrEvent::class, 'agent_id')
            ->orderByDesc('date_debut');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->nom . ' ' . $this->prenoms);
    }

    public function isLinkedToUser(?User $user): bool
    {
        return $user
            && $this->user_id !== null
            && (int) $this->user_id === (int) $user->id;
    }
}
