<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacation2026Plan extends Model
{
    protected $table = 'vacation_2026_plans';
    protected $fillable = ['session_id', 'activity_id', 'ordre', 'date_debut', 'date_fin', 'duree_jours', 'chevauchement_autorise', 'dependency_ids', 'manuel', 'statut'];
    protected function casts(): array { return ['date_debut' => 'date', 'date_fin' => 'date', 'dependency_ids' => 'array', 'chevauchement_autorise' => 'boolean', 'manuel' => 'boolean']; }
    public function session(): BelongsTo { return $this->belongsTo(Vacation2026Session::class, 'session_id'); }
    public function activity(): BelongsTo { return $this->belongsTo(Vacation2026Activity::class, 'activity_id'); }
}
