<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BepcCopyDistribution extends Model
{
    protected $fillable = [
        'annee',
        'cisco_id',
        'code_postal',
    ];

    public function cisco(): BelongsTo
    {
        return $this->belongsTo(Cisco::class);
    }
}
