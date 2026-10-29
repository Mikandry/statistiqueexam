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
        'level',
        'phase',
        'dren_id',
        'cisco_id',
        'centre_correction_id',
        'centre_ecrit_id',
        'salle_id',
        'role',
        'start_date',
        'end_date',
        'nb_jours',
        'required_personnel',
        'status',
        'validated_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'taux' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'validated_at' => 'datetime',
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

    public function dren(): BelongsTo
    {
        return $this->belongsTo(Dren::class, 'dren_id');
    }

    public function cisco(): BelongsTo
    {
        return $this->belongsTo(Cisco::class, 'cisco_id');
    }

    public function centreCorrection(): BelongsTo
    {
        return $this->belongsTo(CentreCorrection::class, 'centre_correction_id');
    }

    public function centreEcrit(): BelongsTo
    {
        return $this->belongsTo(CentreEcrit::class, 'centre_ecrit_id');
    }

    public function salle(): BelongsTo
    {
        return $this->belongsTo(RepartitionSalle::class, 'salle_id');
    }
}
