<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapCaeResultCandidate extends Model
{
    protected $fillable = [
        'batch_id', 'source_number', 'registration_number', 'name', 'birth_date', 'birth_place',
        'dren', 'centre', 'general_order', 'centre_order', 'source_row', 'source_data',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'source_data' => 'array',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CapCaeResultBatch::class, 'batch_id');
    }
}
