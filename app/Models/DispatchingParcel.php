<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Nombre de colis remis à un centre d'écrit (année donnée). */
class DispatchingParcel extends Model
{
    protected $fillable = ['centre_ecrit_id', 'annee', 'colis', 'saisi_par'];

    protected function casts(): array
    {
        return ['colis' => 'integer'];
    }
}
