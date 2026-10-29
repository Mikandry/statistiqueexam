<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Paramètres de calcul du module Besoins & Montants (clé / valeur).
 */
class Vacation2026Parametre extends Model
{
    protected $table = 'vacation_2026_parametres';

    protected $fillable = ['cle', 'valeur'];

    public const TRANSCRIPTION_TYPES_ALL = [
        'CENTRE DE TRANSCRIPTION',
        "CENTRE D'ECRIT ET CORRECTION JUMELES",
        'CENTRE DE CORRECTION SEULEMENT',
    ];

    /** Valeurs par défaut (toutes modifiables dans l'écran Paramètres). */
    public const DEFAULTS = [
        // Année d'examen lue dans repartition_salles.annee (LIKE 'année%').
        'annee' => '2026',
        // Art. 12 : préparation des enveloppes / sous-pli décentralisée CISCO (CEPE 2026 seulement).
        'cepe_enveloppes_actif' => '1',
        // Art. 12 : « un agent pour 1 à 500 candidats, pour 7 matières » => agents = matières × tranches de 500.
        'cepe_enveloppes_matieres' => '7',
        // BEPC écrit : 3 jours (option B seule) / 4 jours (options jumelées). Valeur utilisée quand
        // la répartition ne détaille pas les langues (format TOTAL_BEPC).
        'bepc_jours_ecrit_defaut' => '4',
        // Part (%) de candidats BEPC composant en anglais quand la répartition ne détaille pas les langues.
        'bepc_anglais_pct' => '100',
        // Types de centres qui assurent la transcription (JSON).
        'transcription_types' => '["CENTRE DE TRANSCRIPTION","CENTRE D\'ECRIT ET CORRECTION JUMELES"]',
        // Art. 7 : indemnité spéciale par examen aux responsables (DREN, chef service, chef CISCO, ...).
        'inclure_speciale' => '1',
        // Compter les DREN / CISCO même s'ils n'ont aucun centre saisi pour l'examen.
        'entites_sans_centre' => '0',
        // Nombre de provinces (« 3 agents du MEN par province »). 0 = déduit des DREN, sinon 6.
        'nb_provinces' => '0',
        // Centres EPS par CISCO quand aucun centre EPS n'est configuré.
        'eps_centres_par_cisco' => '1',
    ];

    /** @return array<string, string> */
    public static function values(): array
    {
        $stored = self::query()->pluck('valeur', 'cle')->all();

        return array_merge(self::DEFAULTS, array_map(fn ($v) => (string) $v, $stored));
    }

    public static function get(string $key, ?string $default = null): string
    {
        $all = self::values();

        return (string) ($all[$key] ?? $default ?? '');
    }

    public static function put(string $key, ?string $value): void
    {
        self::query()->updateOrCreate(['cle' => $key], ['valeur' => $value]);
    }
}
