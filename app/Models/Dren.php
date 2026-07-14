<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Dren extends Model
{
    protected $fillable = ['nom'];

    public function ciscos(): HasMany
    {
        return $this->hasMany(Cisco::class);
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }
}
