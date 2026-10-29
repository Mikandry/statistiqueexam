<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Position (persistante) d'un point de largage dans l'ordre de livraison d'un axe. */
class DispatchingPointOrder extends Model
{
    protected $fillable = ['axe', 'point', 'position'];

    /** @return array<string,int> point => position, pour un axe */
    public static function positionsFor(string $axe): array
    {
        return static::query()->where('axe', $axe)->pluck('position', 'point')->map(fn ($p) => (int) $p)->all();
    }
}
