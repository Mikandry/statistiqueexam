<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Taux d'indemnité (arrêté interministériel — Art. 5 et 7 du décret).
 *
 * code   : code de rôle (voir App\Support\VacationDecreeCatalogue::RATE_CODES)
 *          ou « DEFAULT » (taux appliqué aux rôles sans taux propre).
 * examen : CEPE | BEPC
 * mode   : JOUR    -> montant × agents × jours
 *          FORFAIT -> montant × agents (indemnité forfaitaire par activité / par examen)
 */
class Vacation2026Bareme extends Model
{
    public const MODE_JOUR = 'JOUR';
    public const MODE_FORFAIT = 'FORFAIT';
    public const DEFAULT_CODE = 'DEFAULT';

    protected $table = 'vacation_2026_baremes';

    protected $fillable = ['code', 'examen', 'mode', 'montant'];

    protected function casts(): array
    {
        return ['montant' => 'decimal:2'];
    }

    /**
     * Table de résolution [code][examen] => ['mode' => ..., 'montant' => ...].
     *
     * @return array<string, array<string, array{mode:string, montant:float}>>
     */
    public static function table(): array
    {
        $table = [];
        foreach (self::query()->get(['code', 'examen', 'mode', 'montant']) as $row) {
            $table[$row->code][$row->examen] = [
                'mode' => $row->mode ?: self::MODE_JOUR,
                'montant' => (float) $row->montant,
            ];
        }

        return $table;
    }
}
