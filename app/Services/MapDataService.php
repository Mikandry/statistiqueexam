<?php

namespace App\Services;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\DispatchingPointCoordinate;
use App\Models\DispatchingPointOrder;
use App\Models\RepartitionSalle;
use App\Models\TranscriptionCentre;
use App\Models\Vacation2026Parametre;
use App\Support\VacationDecreeCatalogue as Cat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Données de la carte : une seule source (VacationBesoinsService / topologie) pour les candidats,
 * les salles et les agents mobilisés, afin que la carte, la statistique et la vacation concordent.
 *
 * - Les centres sont positionnés par leurs coordonnées (latitude/longitude) saisies dans Carte > Coordonnées.
 * - Un centre sans coordonnées n'est pas dessiné mais reste listé dans « sans coordonnées ».
 * - Les agents sont des BESOINS issus du décret (postes), par phase ; « affectés » et « validés » viennent
 *   des affectations réelles, en lecture seule. Le paiement n'est pas suivi.
 */
class MapDataService
{
    /** Emprise approximative de Madagascar, pour détecter une latitude/longitude inversée ou erronée. */
    public const LAT_MIN = -26.0;
    public const LAT_MAX = -11.5;
    public const LNG_MIN = 42.5;
    public const LNG_MAX = 51.0;

    private const PHASE_GROUP = [
        'AVANT_SESSION' => 'avant', 'AVANT_EPS' => 'avant',
        'PENDANT_SESSION' => 'pendant', 'PENDANT_EPS' => 'pendant',
        'APRES_SESSION' => 'apres', 'APRES_EPS' => 'apres',
        'PENDANT_APRES_SESSION' => 'pendant_apres',
        'SPECIALE' => 'speciale',
    ];

    public function __construct(private readonly VacationBesoinsService $besoins)
    {
    }

    public static function validCoordinates(mixed $lat, mixed $lng): bool
    {
        return is_numeric($lat) && is_numeric($lng)
            && (float) $lat >= self::LAT_MIN && (float) $lat <= self::LAT_MAX
            && (float) $lng >= self::LNG_MIN && (float) $lng <= self::LNG_MAX;
    }

    /**
     * @param  array{exam?:string, dren_id?:?int, cisco_id?:?int, type?:string}  $f
     * @param  bool  $withStaff  inclure les agents mobilisés (module Vacation, réservé à l'administrateur)
     * @return array{centres: array<int,array>, without_coordinates: array<int,array>, totals: array}
     */
    public function centres(array $f = [], bool $withStaff = false): array
    {
        $data = $this->besoins->compute();
        $topo = $data['topology'];
        $drens = $topo['drens'];
        $ciscos = $topo['ciscos'];

        $corrections = CentreCorrection::query()->get(['id', 'latitude', 'longitude'])->keyBy('id');
        $ecrits = CentreEcrit::query()->get(['id', 'latitude', 'longitude'])->keyBy('id');
        $transcriptions = TranscriptionCentre::query()->get(['id', 'latitude', 'longitude'])->keyBy('id');

        $linesByScope = $withStaff ? $data['lines']->groupBy('scope_key') : collect();
        $examFilter = $f['exam'] ?? '';

        $items = [];

        foreach ($topo['centres'] as $c) {
            $site = $c['ecrit'] ?? null;
            $isCorrectionRow = str_starts_with($c['key'], 'C');
            $coords = null;
            if ($isCorrectionRow) {
                $coords = $this->pair($corrections->get((int) substr($c['key'], 1)));
                $coords ??= $site ? $this->pair($ecrits->get((int) $site['id'])) : null;
            } elseif ($site) {
                $coords = $this->pair($ecrits->get((int) $site['id']));
            }

            [$roleCode, $roleLabel] = $this->role($c['type']);
            $items[] = [
                'key' => $c['key'],
                'nom' => $c['nom'],
                'kind' => 'CENTRE',
                'type' => $c['type'],
                'role' => $roleCode,
                'role_label' => $roleLabel,
                'parent_nom' => $c['parent_nom'] ?? null,
                'exam' => $c['exam'],
                'province' => $c['province'] ?? null,
                'dren_id' => $c['dren_id'],
                'dren' => $c['dren_id'] ? (string) ($drens->get($c['dren_id'])?->nom ?? '') : '',
                'cisco_id' => $c['cisco_id'],
                'cisco' => (string) ($ciscos->get($c['cisco_id'])?->nom ?? ''),
                // Salles et candidats de l'écrit ; pour un centre de correction seul : candidats corrigés.
                'salles' => $site ? (int) $site['salles'] : 0,
                'candidates' => $site ? (int) $site['candidates'] : (int) ($c['corr_candidates'] ?? 0),
                'corrected' => (int) ($c['corr_candidates'] ?? 0),
                'lat' => $coords['lat'] ?? null,
                'lng' => $coords['lng'] ?? null,
            ];
        }

        foreach ($topo['eps'] as $e) {
            if (empty($e['correction_id'])) {
                continue; // seuls les centres EPS adossés à un centre enregistré ont une position
            }
            $coords = $this->pair($corrections->get((int) $e['correction_id']));
            $items[] = [
                'key' => $e['key'], 'nom' => $e['nom'], 'kind' => 'EPS', 'type' => 'EPS/GYM', 'role' => 'eps', 'role_label' => 'Centre EPS',
                'parent_nom' => null, 'exam' => 'BEPC', 'province' => $e['province'] ?? null,
                'dren_id' => $e['dren_id'] ?? null, 'dren' => ($e['dren_id'] ?? null) ? (string) ($drens->get($e['dren_id'])?->nom ?? '') : '',
                'cisco_id' => $e['cisco_id'], 'cisco' => (string) ($ciscos->get($e['cisco_id'])?->nom ?? ''),
                'salles' => 0, 'candidates' => (int) $e['candidates'], 'corrected' => 0,
                'lat' => $coords['lat'] ?? null, 'lng' => $coords['lng'] ?? null,
            ];
        }

        foreach ($topo['transcription'] as $t) {
            $coords = $this->pair($transcriptions->get((int) $t['id']));
            $cand = is_array($t['candidates']) ? ($examFilter !== '' ? (int) ($t['candidates'][$examFilter] ?? 0) : (int) array_sum($t['candidates'])) : (int) $t['candidates'];
            $items[] = [
                'key' => $t['key'], 'nom' => $t['nom'], 'kind' => 'TRANSCRIPTION', 'type' => Cat::CENTRE_TRANSCRIPTION, 'role' => 'transcription', 'role_label' => 'Centre de transcription',
                'parent_nom' => $t['localisation'] ?? null, 'exam' => $examFilter, 'province' => $t['province'] ?? null,
                'dren_id' => $t['dren_id'], 'dren' => $t['dren_id'] ? (string) ($drens->get($t['dren_id'])?->nom ?? '') : '',
                'cisco_id' => $t['cisco_id'], 'cisco' => (string) ($ciscos->get($t['cisco_id'])?->nom ?? ''),
                'salles' => 0, 'candidates' => $cand, 'corrected' => 0,
                'lat' => $coords['lat'] ?? null, 'lng' => $coords['lng'] ?? null,
            ];
        }

        $items = collect($items)
            ->filter(fn ($i) => $examFilter === '' || $i['kind'] === 'TRANSCRIPTION' || $i['exam'] === $examFilter)
            ->filter(fn ($i) => empty($f['dren_id']) || (int) $i['dren_id'] === (int) $f['dren_id'])
            ->filter(fn ($i) => empty($f['cisco_id']) || (int) $i['cisco_id'] === (int) $f['cisco_id'])
            ->filter(fn ($i) => empty($f['type']) || $i['role'] === $f['type'])
            ->map(function (array $i) use ($withStaff, $linesByScope) {
                if ($withStaff) {
                    $i['staff'] = $this->staff($linesByScope->get($i['key'], collect()), $i['key']);
                }

                return $i;
            })
            ->values();

        $located = $items->filter(fn ($i) => $i['lat'] !== null)->values();

        return [
            'centres' => $located->all(),
            'without_coordinates' => $items->filter(fn ($i) => $i['lat'] === null)->map(fn ($i) => ['key' => $i['key'], 'nom' => $i['nom'], 'cisco' => $i['cisco'], 'role_label' => $i['role_label']])->values()->all(),
            'totals' => [
                'centres' => $items->count(),
                'located' => $located->count(),
                'candidates' => (int) $located->sum('candidates'),
                'salles' => (int) $located->sum('salles'),
            ],
        ];
    }

    /** @return array{avant:int, pendant:int, apres:int, pendant_apres:int, speciale:int, total:int, amount:float, assigned:int, validated:int} */
    private function staff(Collection $lines, string $key): array
    {
        $by = ['avant' => 0, 'pendant' => 0, 'apres' => 0, 'pendant_apres' => 0, 'speciale' => 0];
        foreach ($lines as $l) {
            $g = self::PHASE_GROUP[$l['phase']] ?? 'pendant';
            $by[$g] += (int) $l['persons'];
        }
        $assign = $this->besoins->assignmentTotals(['scope_key' => $key]);

        return $by + [
            'total' => (int) $lines->sum('persons'),
            'amount' => (float) $lines->sum('amount'),
            'assigned' => $assign['assigned'],
            'validated' => $assign['validated'],
        ];
    }

    /** @return array{0:string,1:string} */
    private function role(string $type): array
    {
        return match ($type) {
            Cat::CENTRE_JUMELES => ['ecrit_correction', "Centre d'écrit ET de correction"],
            Cat::CENTRE_CORRECTION => ['correction', 'Centre de correction'],
            Cat::CENTRE_SOUS => ['sous_centre', "Sous-centre (écrit)"],
            Cat::CENTRE_TRANSCRIPTION => ['transcription', 'Centre de transcription'],
            default => ['ecrit', "Centre d'écrit"],
        };
    }

    /** @return array{lat:float,lng:float}|null */
    private function pair($model): ?array
    {
        if (! $model || $model->latitude === null || $model->longitude === null) {
            return null;
        }
        if (! self::validCoordinates($model->latitude, $model->longitude)) {
            return null;
        }

        return ['lat' => (float) $model->latitude, 'lng' => (float) $model->longitude];
    }

    // =====================================================================
    // Trajet du dispatching
    // =====================================================================

    /**
     * Axes de dispatching avec leurs points de largage, dans l'ordre enregistré (Dispatching > ordre de livraison).
     * Position d'un point : coordonnées saisies pour (axe, point) ; à défaut, centre de gravité des centres qu'il dessert.
     *
     * @param  array<int,array>  $centres  résultat de centres()['centres'] (centres localisés)
     * @return array{depart: array, axes: array<int,array>}
     */
    public function dispatching(array $f = []): array
    {
        $params = Vacation2026Parametre::values();
        $depart = [
            'nom' => $params['carte_depart_nom'],
            'lat' => is_numeric($params['carte_depart_lat']) ? (float) $params['carte_depart_lat'] : null,
            'lng' => is_numeric($params['carte_depart_lng']) ? (float) $params['carte_depart_lng'] : null,
        ];

        if (! Schema::hasTable('repartition_salles')) {
            return ['depart' => $depart, 'axes' => []];
        }

        $all = $this->centres(array_intersect_key($f, array_flip(['exam', 'dren_id', 'cisco_id'])), false);
        $byEcrit = [];
        foreach ($this->centresByEcritId() as $ecritId => $key) {
            $byEcrit[$ecritId] = $key;
        }
        $everyCentre = collect($all['centres'])->keyBy('key');
        // Les centres sans coordonnées doivent aussi apparaître dans les points (liste), pas sur le tracé.
        $unlocated = collect($all['without_coordinates'])->keyBy('key');

        $annee = (string) $params['annee'];
        $rows = RepartitionSalle::query()
            ->where('annee', 'like', $annee.'%')
            ->whereNotNull('axe_dispatching')->where('axe_dispatching', '!=', '')
            ->get(['centre_ecrit_id', 'axe_dispatching', 'point_largage', 'langue', 'effectif']);

        $coords = DispatchingPointCoordinate::query()->get()->keyBy(fn ($c) => $c->axe.'|'.$c->point);
        $orders = DispatchingPointOrder::query()->get()->groupBy('axe')->map(fn ($g) => $g->pluck('position', 'point')->all());

        $axes = [];
        foreach ($rows->groupBy('axe_dispatching') as $axe => $axeRows) {
            $points = [];
            foreach ($axeRows->groupBy(fn ($r) => trim((string) $r->point_largage) ?: 'POINT NON RENSEIGNE') as $point => $pRows) {
                $served = [];
                $cand = 0;
                $salles = 0;
                foreach ($pRows as $r) {
                    $key = $byEcrit[(int) $r->centre_ecrit_id] ?? null;
                    $centre = $key ? $everyCentre->get($key) : null;
                    if ($centre) {
                        $served[$key] ??= ['key' => $key, 'nom' => $centre['nom'], 'lat' => $centre['lat'], 'lng' => $centre['lng'], 'candidates' => 0, 'salles' => 0];
                    } elseif ($key && $unlocated->has($key)) {
                        $served[$key] ??= ['key' => $key, 'nom' => $unlocated[$key]['nom'], 'lat' => null, 'lng' => null, 'candidates' => 0, 'salles' => 0];
                    }
                    // Candidats et salles réellement déposés à ce point (lignes « total » uniquement, sans double compte par langue).
                    if (in_array($r->langue, ['TOTAL', RepartitionSalle::BEPC_TOTAL_KEY], true)) {
                        $cand += (int) $r->effectif;
                        $salles++;
                        if ($key && isset($served[$key])) {
                            $served[$key]['candidates'] += (int) $r->effectif;
                            $served[$key]['salles']++;
                        }
                    }
                }
                $saved = $coords->get($axe.'|'.$point);
                $position = null;
                $source = 'absent';
                if ($saved && $saved->latitude !== null && self::validCoordinates($saved->latitude, $saved->longitude)) {
                    $position = ['lat' => (float) $saved->latitude, 'lng' => (float) $saved->longitude];
                    $source = 'saisi';
                } else {
                    $with = collect($served)->filter(fn ($s) => $s['lat'] !== null);
                    if ($with->isNotEmpty()) {
                        $position = ['lat' => round($with->avg('lat'), 6), 'lng' => round($with->avg('lng'), 6)];
                        $source = 'centres';
                    }
                }
                $points[] = [
                    'point' => (string) $point,
                    'lat' => $position['lat'] ?? null, 'lng' => $position['lng'] ?? null, 'source' => $source,
                    'candidates' => $cand, 'salles' => $salles,
                    'centres' => array_values($served),
                ];
            }

            // Ordre enregistré ; les points non classés suivent par ordre alphabétique naturel.
            $saved = $orders->get($axe, []);
            usort($points, function ($a, $b) use ($saved) {
                $pa = $saved[$a['point']] ?? 100000;
                $pb = $saved[$b['point']] ?? 100000;

                return $pa <=> $pb ?: strnatcasecmp($a['point'], $b['point']);
            });
            foreach ($points as $i => &$p) {
                $p['order'] = $i + 1;
            }
            unset($p);

            $axes[] = [
                'axe' => (string) $axe,
                'points' => $points,
                'candidates' => array_sum(array_column($points, 'candidates')),
                'ordered_by_user' => $saved !== [],
            ];
        }
        usort($axes, fn ($a, $b) => strnatcasecmp($a['axe'], $b['axe']));

        return ['depart' => $depart, 'axes' => $axes];
    }

    /** @return array<int,string> id du centre d'écrit => clé de centre (C… ou E…) */
    private function centresByEcritId(): array
    {
        $out = [];
        foreach ($this->besoins->compute()['topology']['centres'] as $c) {
            if (! empty($c['ecrit']['id'])) {
                $out[(int) $c['ecrit']['id']] = $c['key'];
            }
        }

        return $out;
    }
}
