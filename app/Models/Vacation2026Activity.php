<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacation2026Activity extends Model
{
    use HasFactory;

    protected $table = 'vacation_2026_activities';

    protected $fillable = [
        'examen',
        'level',
        'phase',
        'libelle',
        'max_agents',
        'nb_jours',
        'taux_activite',
        'ordre',
        'year',
        'activity_code',
        'rule_key',
        'source_rule',
        'is_special_rule',
        'applicable_year',
    ];

    protected function casts(): array
    {
        return [
            'taux_activite' => 'decimal:2',
            'is_special_rule' => 'boolean',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Vacation2026Assignment::class, 'activity_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Vacation2026ActivityGroup::class, 'activity_id');
    }
}
