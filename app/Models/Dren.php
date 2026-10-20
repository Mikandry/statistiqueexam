<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Dren extends Model
{
    protected $fillable = ['nom', 'province', 'is_chef_lieu_province'];

    protected function casts(): array
    {
        return ['is_chef_lieu_province' => 'boolean'];
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
