<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEvent extends Model
{
    public const TYPES = [
        'conge' => 'Congé',
        'autorisation_absence' => 'Autorisation d’absence',
        'mission' => 'Mission',
        'formation' => 'Formation',
        'mise_disposition' => 'Mise à disposition',
        'autre' => 'Autre indisponibilité',
    ];
public const STATUSES = [
        'brouillon' => 'Brouillon',
        'demande' => 'Demandé',
        'valide' => 'Validé',
        'refuse' => 'Refusé',
        'annule' => 'Annulé',
        'termine' => 'Terminé',
    ];

    protected $table = 'hr_events';

    protected $fillable = [
        'agent_id',
        'type',
        'status',
        'title',
        'motif',
        'date_debut',
        'date_fin',
        'heure_debut',
        'heure_fin',
        'duree_heures',
        'jours_semaine',
        'lieu',
        'organisme',
        'destination',
        'reference',
        'autorite',
        'observation',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'jours_semaine' => 'array',
            'duree_heures' => 'decimal:2',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(HrAgent::class, 'agent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function isApproved(): bool
    {
        return $this->status === 'valide';
    }

    public function isFullDay(): bool
    {
        return empty($this->heure_debut) && empty($this->heure_fin);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}