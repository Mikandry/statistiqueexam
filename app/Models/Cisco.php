<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Cisco extends Model
{
    protected $fillable = ['dren_id', 'nom'];

    public function dren(): BelongsTo
    {
        return $this->belongsTo(Dren::class);
    }

    public function centresCorrection(): HasMany
    {
        return $this->hasMany(CentreCorrection::class);
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }
}
