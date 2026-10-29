<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Dren extends Model
{
    protected $fillable = ['nom', 'province', 'province_id', 'is_chef_lieu_province'];

    protected function casts(): array
    {
        return ['is_chef_lieu_province' => 'boolean'];
    }

    public function provinceRef(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    /** Nom de la province : référence si renseignée, sinon ancien texte. */
    public function provinceNom(): ?string
    {
        return $this->provinceRef?->nom ?? ($this->province ?: null);
    }

    public function ciscos(): HasMany
    {
        return $this->hasMany(Cisco::class);
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function vacationAssignments(): HasMany
    {
        return $this->hasMany(Vacation2026Assignment::class, 'dren_id');
    }
}
