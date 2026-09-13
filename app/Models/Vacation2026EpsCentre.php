<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacation2026EpsCentre extends Model
{
    protected $table = 'vacation_2026_eps_centres';
    protected $fillable = ['cisco_id', 'name', 'candidate_count'];

    public function cisco(): BelongsTo
    {
        return $this->belongsTo(Cisco::class);
    }
}
