<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAssignment extends Model
{
    protected $table = 'hr_assignments';

    protected $fillable = [
        'agent_id', 'direction', 'service', 'bureau', 'fonction', 'date_debut', 'date_fin',
        'motif', 'reference', 'current', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'current' => 'boolean',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(HrAgent::class, 'agent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
