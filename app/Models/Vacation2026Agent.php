<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function assignment(): HasOne
    {
        return $this->hasOne(Vacation2026Assignment::class, 'agent_id');
    }
}
