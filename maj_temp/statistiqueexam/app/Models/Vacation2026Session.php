<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacation2026Session extends Model
{
    protected $table = 'vacation_2026_sessions';
    protected $fillable = ['examen', 'annee', 'libelle', 'date_session', 'date_fin_session', 'level'];
    protected function casts(): array { return ['date_session' => 'date', 'date_fin_session' => 'date']; }
    public function plans(): HasMany { return $this->hasMany(Vacation2026Plan::class, 'session_id'); }
}
