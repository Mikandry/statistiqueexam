<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Centre de transcription : entité distincte des centres d'écrit et de correction.
 * Une CISCO peut en compter plusieurs (zones éloignées : 2 ou 3 centres).
 */
class TranscriptionCentre extends Model
{
    protected $fillable = ['cisco_id', 'nom', 'localisation', 'ordre', 'candidats_cepe', 'candidats_bepc', 'latitude', 'longitude'];

    public function cisco(): BelongsTo
    {
        return $this->belongsTo(Cisco::class);
    }
}
