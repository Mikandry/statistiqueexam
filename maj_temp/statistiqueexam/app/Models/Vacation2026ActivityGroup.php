<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacation2026ActivityGroup extends Model
{
    protected $table = 'vacation_2026_activity_groups';

    protected $fillable = ['activity_id', 'groupe', 'personnel', 'nb_jours', 'taux'];

    protected function casts(): array
    {
        return [
            'taux' => 'decimal:2',
            'nb_jours' => 'integer',
            'personnel' => 'integer',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Vacation2026Activity::class, 'activity_id');
    }
}
