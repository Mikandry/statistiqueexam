<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrDocumentSetting extends Model
{
    protected $table = 'hr_document_settings';

    protected $fillable = [
        'ministere',
        'secretariat_general',
        'direction_generale',
        'direction',
        'service',

        'reference_prefix',
        'next_reference_number',
        'reference_year',

        'signataire',
        'signataire_qualite',
        'signataire_niveau',

        'ville',
    ];

    protected function casts(): array
    {
        return [
            'reference_year' => 'integer',
            'next_reference_number' => 'integer',
        ];
    }
}