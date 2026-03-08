<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacation2026Assignment extends Model
{
    use HasFactory;

    protected $table = 'vacation_2026_assignments';

    protected $fillable = [
        'agent_id',
        'activity_id',
        'taux',
    ];

    protected function casts(): array
    {
        return [
            'taux' => 'decimal:2',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Vacation2026Agent::class, 'agent_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Vacation2026Activity::class, 'activity_id');
    }
}
