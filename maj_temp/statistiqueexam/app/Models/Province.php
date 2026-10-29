<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $fillable = ['nom'];

    public function drens(): HasMany
    {
        return $this->hasMany(Dren::class);
    }

    /** CISCO rattachées directement à la province (enregistrements historiques sans DREN). */
    public function ciscosDirectes(): HasMany
    {
        return $this->hasMany(Cisco::class)->whereNull('dren_id');
    }
}
