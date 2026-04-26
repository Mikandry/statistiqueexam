<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalSetting extends Model
{
    protected $fillable = [
        'bepc_copy_margin_percent',
        'subject_soubique_ge_capacity',
        'subject_soubique_subject_capacity',
        'sord_sheet_page_capacity',
        'cepe_pages_francais',
        'cepe_pages_connaissances_usuelles',
        'cepe_pages_geographie',
        'cepe_pages_malagasy',
        'cepe_pages_operation',
        'cepe_pages_probleme',
        'cepe_pages_tffmom',
        'bepc_pages_malagasy',
        'bepc_pages_svt',
        'bepc_pages_francais',
        'bepc_pages_anglais',
        'bepc_pages_esp',
        'bepc_pages_pc',
        'bepc_pages_math',
        'bepc_pages_hg',
        'bepc_pages_all',
        'bepc_print_order',
        'cepe_print_order',
        'dispatching_axes',
        'dispatching_drop_points',
    ];

    protected function casts(): array
    {
        return [
            'bepc_copy_margin_percent' => 'float',
            'subject_soubique_ge_capacity' => 'integer',
            'subject_soubique_subject_capacity' => 'integer',
            'sord_sheet_page_capacity' => 'integer',
            'cepe_pages_francais' => 'integer',
            'cepe_pages_connaissances_usuelles' => 'integer',
            'cepe_pages_geographie' => 'integer',
            'cepe_pages_malagasy' => 'integer',
            'cepe_pages_operation' => 'integer',
            'cepe_pages_probleme' => 'integer',
            'cepe_pages_tffmom' => 'integer',
            'bepc_pages_malagasy' => 'integer',
            'bepc_pages_svt' => 'integer',
            'bepc_pages_francais' => 'integer',
            'bepc_pages_anglais' => 'integer',
            'bepc_pages_esp' => 'integer',
            'bepc_pages_pc' => 'integer',
            'bepc_pages_math' => 'integer',
            'bepc_pages_hg' => 'integer',
            'bepc_pages_all' => 'integer',
            'bepc_print_order' => 'string',
            'cepe_print_order' => 'string',
            'dispatching_axes' => 'string',
            'dispatching_drop_points' => 'string',
        ];
    }
}
