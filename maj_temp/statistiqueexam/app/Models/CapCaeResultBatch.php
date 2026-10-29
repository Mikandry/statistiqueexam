<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CapCaeResultBatch extends Model
{
    protected $fillable = [
        'exam_type', 'list_status', 'year', 'source_filename', 'total_candidates', 'total_centres',
        'institution_lines', 'signer_function', 'signer_name', 'signer_place',
        'signature_date', 'pv_date', 'anomalies', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'institution_lines' => 'array',
            'anomalies' => 'array',
            'signature_date' => 'date',
            'pv_date' => 'date',
        ];
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(CapCaeResultCandidate::class, 'batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
