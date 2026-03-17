<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacation2026Agent extends Model
{
    use HasFactory;

    protected $table = 'vacation_2026_agents';

    protected $fillable = [
        'nom',
        'im',
        'localite_service',
        'cin',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(Vacation2026Assignment::class, 'agent_id');
    }
}
