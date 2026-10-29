<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Coordonnées d'un point de largage au sein d'un axe de dispatching. */
class DispatchingPointCoordinate extends Model
{
    protected $fillable = ['axe', 'point', 'latitude', 'longitude'];

    protected function casts(): array
    {
        return ['latitude' => 'float', 'longitude' => 'float'];
    }
}
