<?php

namespace App\Services;

use App\Models\Vacation2026Bareme;
use App\Models\Vacation2026Parametre;
use App\Support\VacationDecreeCatalogue as Cat;
use Illuminate\Support\Collection;

/**
 * Moteur « Besoins & Montants » de la Vacation 2026.
 *
 * Aucune affectation d'agent, aucun import : à partir des effectifs réels (candidats, salles,
 * centres, CISCO, DREN) et du décret, il calcule pour chaque activité le NOMBRE DE PERSONNES
 * MOBILISÉES, la durée, puis le MONTANT avec les barèmes saisis (arrêté).
 *
 * Résultat : une collection de « lignes » (une par rôle d'une activité d'un périmètre) que l'on
 * peut agréger par examen, niveau, phase, activité, DREN, CISCO ou centre.
 *
 * Ligne : exam, level, phase, activity_code, activity, role, rate_code, persons, days,
 *         person_days, mode, rate, amount, rate_missing, rule, ref,
 *         scope_kind (NATIONAL|DREN|CISCO|CENTRE|EPS), scope_key, scope_nom, centre_type,
 *         parent_nom, dren_id, dren_nom, cisco_id, cisco_nom, candidates
 */
class VacationBesoinsService
{
    private ?array $cache = null;

    public function __construct(private readonly VacationTopologyService $topology)
    {
    }

    // =====================================================================
    // Calcul complet (mémorisé pour la requête)
    // =====================================================================

    /** @return array{lines: Collection, warnings: array<int,string>, params: array, topology: array, rates_defined: int} */
    public function compute(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $params = Vacation2026Parametre::values();
        $annee = (string) $params['annee'];
        $topo = $this->topology->build($annee, $params);
        $rates = Vacation2026Bareme::table();

        $lines = [];
        $warnings = $topo['warnings'];

        $drens = $topo['drens'];
        $ciscos = $topo['ciscos'];
        $ciscoStats = $topo['cisco_stats'];
        $withoutCentre = $params['entites_sans_centre'] === '1';
        $withSpeciale = $params['inclure_speciale'] === '1';

        $drenName = fn (?int $id) => $id ? (string) ($drens->get($id)?->nom ?? '') : '';
        $ciscoName = fn (?int $id) => $id ? (string) ($ciscos->get($id)?->nom ?? '') : '';

        // ---- MEN central ------------------------------------------------
        $provinces = (int) $params['nb_provinces'];
        if ($provinces <= 0) {
            $provinces = 6; // Madagascar compte 6 provinces
        }
        foreach (Cat::EXAMS as $exam) {
            foreach (Cat::central($exam, $provinces) as $activity) {
                $this->emit($lines, $activity, [
                    'scope_kind' => 'NATIONAL', 'scope_key' => 'N', 'scope_nom' => 'MEN central',
                    'dren_id' => null, 'cisco_id' => null, 'candidates' => 0,
                ], $rates);
            }
        }

        // ---- DREN -------------------------------------------------------
        foreach ($drens as $dren) {
            $drenCiscos = $ciscos->where('dren_id', $dren->id);
            foreach (Cat::EXAMS as $exam) {
                $centresCount = 0;
                $ciscosCount = 0;
                foreach ($drenCiscos as $cisco) {
                    $st = $ciscoStats[(int) $cisco->id][$exam] ?? null;
                    if ($st !== null) {
                        $ciscosCount++;
                        $centresCount += (int) $st['centres'];
                    } elseif ($withoutCentre) {
                        $ciscosCount++;
                    }
                }
                if ($ciscosCount === 0 && ! $withoutCentre) {
                    continue;
                }
                $scope = [
                    'scope_kind' => 'DREN', 'scope_key' => 'D'.$dren->id, 'scope_nom' => (string) $dren->nom,
                    'dren_id' => (int) $dren->id, 'cisco_id' => null, 'candidates' => 0,
                ];
                foreach (Cat::dren($exam, [
                    'centres' => $centresCount, 'ciscos' => $ciscosCount, 'chef_lieu' => (bool) $dren->is_chef_lieu_province,
                ]) as $activity) {
                    $this->emit($lines, $activity, $scope, $rates);
                }
                if ($withSpeciale) {
                    $this->emit($lines, Cat::speciale($exam, 'DREN', 'DREN'), $scope, $rates);
                }
            }
        }

        // ---- CISCO ------------------------------------------------------
        foreach ($ciscos as $cisco) {
            foreach (Cat::EXAMS as $exam) {
                $st = $ciscoStats[(int) $cisco->id][$exam] ?? null;
                if ($st === null && ! $withoutCentre) {
                    continue;
                }
                $candidates = (int) ($st['candidates'] ?? 0);
                $scope = [
                    'scope_kind' => 'CISCO', 'scope_key' => 'S'.$cisco->id, 'scope_nom' => (string) $cisco->nom,
                    'dren_id' => (int) $cisco->dren_id ?: null, 'cisco_id' => (int) $cisco->id, 'candidates' => $candidates,
                ];
                foreach (Cat::cisco($exam, [
                    'candidates' => $candidates,
                    'eps_candidates' => (int) ($topo['eps_centres']['cisco_candidates'][(int) $cisco->id] ?? 0),
                    'annee' => (int) $annee,
                    'enveloppes_actif' => $params['cepe_enveloppes_actif'] === '1',
                    'enveloppes_matieres' => (int) $params['cepe_enveloppes_matieres'],
                ]) as $activity) {
                    $this->emit($lines, $activity, $scope, $rates);
                }
                if ($withSpeciale) {
                    $this->emit($lines, Cat::speciale($exam, 'CISCO', 'CISCO'), $scope, $rates);
                }
            }
        }

        // ---- Centres d'examen -------------------------------------------
        $transcriptionTypes = json_decode($params['transcription_types'], true) ?: [];
        $transcriptionCentres = 0;
        $centresWithoutData = 0;

        foreach ($topo['centres'] as $centre) {
            $exam = $centre['exam'];
            $type = $centre['type'];
            $ecrit = $centre['ecrit'];
            $corrCand = (int) $centre['corr_candidates'];
            $scope = [
                'scope_kind' => 'CENTRE', 'scope_key' => $centre['key'], 'scope_nom' => $centre['nom'],
                'centre_type' => $type, 'parent_nom' => $centre['parent_nom'],
                'dren_id' => $centre['dren_id'], 'cisco_id' => $centre['cisco_id'] ?: null,
                'candidates' => $ecrit ? (int) $ecrit['candidates'] : $corrCand,
            ];

            $before = count($lines);
            $hasEcritData = $ecrit !== null && ($ecrit['candidates'] > 0 || $ecrit['salles'] > 0);

            // écrit
            if ($hasEcritData && $type !== Cat::CENTRE_SOUS) {
                foreach (Cat::centreAvantSession($exam, $type === Cat::CENTRE_JUMELES ? Cat::CENTRE_JUMELES : Cat::CENTRE_ECRIT, (int) $ecrit['candidates']) as $a) {
                    $this->emit($lines, $a, $scope, $rates);
                }
            }
            if ($hasEcritData) {
                foreach (Cat::centrePendantSession($exam, [
                    'candidates' => (int) $ecrit['candidates'],
                    'salles' => (int) $ecrit['salles'],
                    'special' => (bool) $ecrit['special'],
                    'days' => $exam === 'CEPE' ? 1 : (int) $ecrit['days_bepc'],
                    'sous_centre' => $type === Cat::CENTRE_SOUS,
                ]) as $a) {
                    $this->emit($lines, $a, $scope, $rates);
                }
            }

            // correction
            if ($corrCand > 0 && in_array($type, [Cat::CENTRE_CORRECTION, Cat::CENTRE_JUMELES], true)) {
                if ($type === Cat::CENTRE_CORRECTION) {
                    foreach (Cat::centreAvantSession($exam, Cat::CENTRE_CORRECTION, $corrCand) as $a) {
                        $this->emit($lines, $a, $scope, $rates);
                    }
                }
                foreach (Cat::centreCorrection($exam, [
                    'candidates' => $corrCand,
                    'langues' => $centre['corr_langues'],
                    'anglais_pct' => (int) $params['bepc_anglais_pct'],
                ]) as $a) {
                    $this->emit($lines, $a, ['candidates' => $corrCand] + $scope, $rates);
                }
            }

            // transcription
            if (in_array($type, $transcriptionTypes, true)) {
                if ($corrCand > 0) {
                    foreach (Cat::centreTranscription($exam, $corrCand) as $a) {
                        $this->emit($lines, $a, ['candidates' => $corrCand] + $scope, $rates);
                    }
                    $transcriptionCentres++;
                } elseif ($type === Cat::CENTRE_TRANSCRIPTION) {
                    $warnings[] = "Le centre de transcription « {$centre['nom']} » n'a aucun candidat rattaché : aucun effectif calculé.";
                }
            }

            $emitted = count($lines) > $before;
            if (! $emitted) {
                $centresWithoutData++;
                continue;
            }

            if ($withSpeciale) {
                $level = match ($type) {
                    Cat::CENTRE_CORRECTION => 'CENTRE_CORRECTION',
                    Cat::CENTRE_TRANSCRIPTION => 'TRANSCRIPTION',
                    default => 'CENTRE_ECRIT',
                };
                $this->emit($lines, Cat::speciale($exam, $type === Cat::CENTRE_SOUS ? 'CENTRE_SOUS' : 'CENTRE', $level), $scope, $rates);
            }
        }

        // ---- EPS (BEPC) --------------------------------------------------
        $epsEstimes = 0;
        foreach ($topo['eps_centres']['centres'] as $eps) {
            if ($eps['candidates'] <= 0) {
                continue;
            }
            if ($eps['estime']) {
                $epsEstimes++;
            }
            $scope = [
                'scope_kind' => 'EPS', 'scope_key' => $eps['key'], 'scope_nom' => $eps['nom'], 'centre_type' => 'CENTRE EPS',
                'dren_id' => $eps['dren_id'], 'cisco_id' => $eps['cisco_id'], 'candidates' => (int) $eps['candidates'],
            ];
            foreach (Cat::centreEps((int) $eps['candidates']) as $a) {
                $this->emit($lines, $a, $scope, $rates);
            }
            if ($withSpeciale) {
                $this->emit($lines, Cat::speciale('BEPC', 'EPS', 'EPS'), $scope, $rates);
            }
        }

        // ---- Noms DREN / CISCO sur toutes les lignes ----------------------
        foreach ($lines as &$line) {
            $line['dren_nom'] = $drenName($line['dren_id']);
            $line['cisco_nom'] = $ciscoName($line['cisco_id']);
            if ($line['cisco_id'] && ! $line['dren_id']) {
                $line['dren_id'] = (int) ($ciscos->get($line['cisco_id'])?->dren_id ?? 0) ?: null;
                $line['dren_nom'] = $drenName($line['dren_id']);
            }
        }
        unset($line);

        // ---- Avertissements ------------------------------------------------
        $definedRates = array_sum(array_map(fn ($byExam) => count(array_filter($byExam, fn ($r) => $r['montant'] > 0)), $rates));
        if ($definedRates === 0) {
            $warnings[] = "Aucun taux n'est encore saisi (Barèmes) : les effectifs sont calculés, les montants restent à 0 Ar.";
        }
        $noProvince = $drens->filter(fn ($d) => empty($d->province))->count();
        if ($noProvince > 0) {
            $warnings[] = "{$noProvince} DREN sans province : renseignez-les dans Paramètres (travaux provinciaux CEPE).";
        }
        $chefLieux = $drens->where('is_chef_lieu_province', true)->count();
        if ($drens->isNotEmpty() && $chefLieux !== $provinces) {
            $warnings[] = "{$chefLieux} DREN chef-lieu de province définie(s) pour {$provinces} province(s) : vérifiez Paramètres (12 agents par province comptés au chef-lieu).";
        }
        if ($epsEstimes > 0) {
            $warnings[] = "{$epsEstimes} centre(s) EPS estimés (aucun centre EPS configuré pour leur CISCO) : candidats EPS = candidats BEPC − inaptes.";
        }
        if ($transcriptionCentres === 0 && $transcriptionTypes) {
            $warnings[] = 'Aucun centre de transcription trouvé avec les types retenus dans Paramètres : la transcription est à 0.';
        }
        if ($centresWithoutData > 0) {
            $warnings[] = "{$centresWithoutData} centre(s) sans candidats ni salles saisis : ignorés.";
        }

        $stats = ['candidates' => ['CEPE' => 0, 'BEPC' => 0], 'centres' => ['CEPE' => 0, 'BEPC' => 0], 'salles' => ['CEPE' => 0, 'BEPC' => 0]];
        foreach ($ciscoStats as $byExam) {
            foreach ($byExam as $e => $st) {
                $stats['candidates'][$e] += (int) $st['candidates'];
                $stats['centres'][$e] += (int) $st['centres'];
                $stats['salles'][$e] += (int) $st['salles'];
            }
        }

        return $this->cache = [
            'stats' => $stats,
            'lines' => collect($lines),
            'warnings' => array_values(array_unique($warnings)),
            'params' => $params,
            'topology' => ['drens' => $drens, 'ciscos' => $ciscos],
            'rates_defined' => $definedRates,
            'provinces' => $provinces,
        ];
    }

    // =====================================================================
    // Émission des lignes
    // =====================================================================

    private function emit(array &$lines, array $activity, array $scope, array $rates): void
    {
        foreach ($activity['roles'] as $role) {
            [$mode, $rate, $missing] = $this->resolveRate($rates, $role['rate'], $activity['exam']);
            $days = (int) $activity['days'];
            $persons = (int) $role['n'];

            $lines[] = [
                'exam' => $activity['exam'],
                'level' => $activity['level'],
                'phase' => $activity['phase'],
                'activity_code' => $activity['code'],
                'activity' => $activity['label'],
                'role' => $role['label'],
                'rate_code' => $role['rate'],
                'persons' => $persons,
                'days' => $days,
                'person_days' => $persons * $days,
                'mode' => $mode,
                'rate' => $rate,
                'amount' => $persons * $rate * ($mode === Vacation2026Bareme::MODE_FORFAIT ? 1 : $days),
                'rate_missing' => $missing,
                'rule' => $activity['rule'],
                'ref' => $activity['ref'],
                'scope_kind' => $scope['scope_kind'],
                'scope_key' => $scope['scope_key'],
                'scope_nom' => $scope['scope_nom'],
                'centre_type' => $scope['centre_type'] ?? '',
                'parent_nom' => $scope['parent_nom'] ?? null,
                'dren_id' => $scope['dren_id'] ?? null,
                'dren_nom' => '',
                'cisco_id' => $scope['cisco_id'] ?? null,
                'cisco_nom' => '',
                'candidates' => (int) ($scope['candidates'] ?? 0),
            ];
        }
    }

    /**
     * Taux d'un code pour un examen : taux propre > taux par défaut (sauf indemnités spéciales).
     *
     * @return array{0:string,1:float,2:bool} [mode, taux, manquant]
     */
    private function resolveRate(array $rates, string $code, string $exam): array
    {
        $defaultMode = Cat::RATE_CODES[$code]['mode'] ?? Vacation2026Bareme::MODE_JOUR;

        $own = $rates[$code][$exam] ?? null;
        if ($own !== null && $own['montant'] > 0) {
            return [$own['mode'], $own['montant'], false];
        }

        if (! str_starts_with($code, 'speciale.')) {
            $default = $rates[Vacation2026Bareme::DEFAULT_CODE][$exam] ?? null;
            if ($default !== null && $default['montant'] > 0) {
                return [$default['mode'], $default['montant'], false];
            }
        }

        return [$own['mode'] ?? $defaultMode, 0.0, true];
    }

    // =====================================================================
    // Filtres et agrégations
    // =====================================================================

    /**
     * @param  array{exam?:string, level?:string, phase?:string, dren_id?:?int, cisco_id?:?int, scope_key?:string, kind?:string}  $f
     */
    public function lines(array $f = []): Collection
    {
        return $this->compute()['lines']->filter(function (array $l) use ($f) {
            if (! empty($f['exam']) && $l['exam'] !== $f['exam']) {
                return false;
            }
            if (! empty($f['level']) && $l['level'] !== $f['level']) {
                return false;
            }
            if (! empty($f['phase']) && $l['phase'] !== $f['phase']) {
                return false;
            }
            if (! empty($f['dren_id']) && (int) $l['dren_id'] !== (int) $f['dren_id']) {
                return false;
            }
            if (! empty($f['cisco_id']) && (int) $l['cisco_id'] !== (int) $f['cisco_id']) {
                return false;
            }
            if (! empty($f['scope_key']) && $l['scope_key'] !== $f['scope_key']) {
                return false;
            }
            if (! empty($f['kind']) && $l['scope_kind'] !== $f['kind']) {
                return false;
            }

            return true;
        })->values();
    }

    /** Totaux : postes mobilisés, jours-agents, montant. */
    public function totals(Collection $lines): array
    {
        return [
            'persons' => (int) $lines->sum('persons'),
            'person_days' => (int) $lines->sum('person_days'),
            'amount' => (float) $lines->sum('amount'),
            'activities' => $lines->pluck('activity_code')->unique()->count(),
            'missing_rates' => $lines->where('rate_missing', true)->pluck('rate_code')->unique()->count(),
        ];
    }

    /** @return Collection<string, array> clé = valeur du champ de regroupement */
    public function groupTotals(Collection $lines, string $field): Collection
    {
        return $lines->groupBy($field)->map(fn (Collection $g) => $this->totals($g));
    }

    /** Une ligne par (examen, niveau, activité) : personnes mobilisées sur tout le périmètre. */
    public function byActivity(Collection $lines): Collection
    {
        return $lines->groupBy(fn ($l) => $l['exam'].'|'.$l['level'].'|'.$l['activity_code'])
            ->map(function (Collection $g) {
                $f = $g->first();
                $persons = (int) $g->sum('persons');
                $personDays = (int) $g->sum('person_days');
                $days = $g->pluck('days');
                $units = $g->pluck('scope_key')->unique()->count();

                return [
                    'exam' => $f['exam'], 'level' => $f['level'], 'phase' => $f['phase'],
                    'activity_code' => $f['activity_code'], 'activity' => $f['activity'], 'rule' => $f['rule'], 'ref' => $f['ref'],
                    'units' => $units,
                    'persons' => $persons,
                    'persons_per_unit' => $units > 0 ? round($persons / $units, 1) : 0,
                    'days_min' => (int) $days->min(), 'days_max' => (int) $days->max(),
                    'person_days' => $personDays,
                    'amount' => (float) $g->sum('amount'),
                    'missing_rates' => $g->where('rate_missing', true)->isNotEmpty(),
                    'roles' => $g->groupBy('role')->map(fn (Collection $r) => [
                        'role' => $r->first()['role'],
                        'rate_code' => $r->first()['rate_code'],
                        'persons' => (int) $r->sum('persons'),
                        'person_days' => (int) $r->sum('person_days'),
                        'amount' => (float) $r->sum('amount'),
                    ])->values(),
                ];
            })->values()
            ->map(fn (array $row, int $i) => $row + ['_sort' => $row['exam'].sprintf('%02d', $this->levelOrder($row['level'])).sprintf('%04d', $i)])
            ->sortBy('_sort')->values();
    }

    /** Totaux par rôle (pour le détail d'un périmètre). */
    public function byRole(Collection $lines): Collection
    {
        return $lines->groupBy(fn ($l) => $l['level'].'|'.$l['rate_code'].'|'.$l['role'])
            ->map(function (Collection $g) {
                $f = $g->first();

                return [
                    'level' => $f['level'], 'role' => $f['role'], 'rate_code' => $f['rate_code'],
                    'persons' => (int) $g->sum('persons'), 'person_days' => (int) $g->sum('person_days'),
                    'amount' => (float) $g->sum('amount'),
                ];
            })->sortByDesc('amount')->values();
    }

    /**
     * Totaux par DREN, ventilés par niveau (DREN / CISCO / centres / EPS).
     */
    public function byDren(Collection $lines): Collection
    {
        return $this->byScope($lines, 'dren_id', 'dren_nom');
    }

    public function byCisco(Collection $lines): Collection
    {
        return $this->byScope($lines->filter(fn ($l) => $l['cisco_id']), 'cisco_id', 'cisco_nom', ['dren_id', 'dren_nom']);
    }

    public function byCentre(Collection $lines): Collection
    {
        return $lines->filter(fn ($l) => in_array($l['scope_kind'], ['CENTRE', 'EPS'], true))
            ->groupBy('scope_key')
            ->map(function (Collection $g) {
                $f = $g->first();

                return [
                    'key' => $f['scope_key'], 'nom' => $f['scope_nom'], 'kind' => $f['scope_kind'],
                    'type' => $f['centre_type'], 'parent_nom' => $f['parent_nom'],
                    'dren_id' => $f['dren_id'], 'dren_nom' => $f['dren_nom'],
                    'cisco_id' => $f['cisco_id'], 'cisco_nom' => $f['cisco_nom'],
                    'exams' => $g->pluck('exam')->unique()->sort()->values()->all(),
                    'candidates' => (int) $g->max('candidates'),
                    'persons' => (int) $g->sum('persons'),
                    'person_days' => (int) $g->sum('person_days'),
                    'amount' => (float) $g->sum('amount'),
                ];
            })->sortByDesc('amount')->values();
    }

    private function byScope(Collection $lines, string $idField, string $nameField, array $extra = []): Collection
    {
        return $lines->filter(fn ($l) => $l[$idField])
            ->groupBy($idField)
            ->map(function (Collection $g, $id) use ($nameField, $extra) {
                $f = $g->first();
                $row = [
                    'id' => (int) $id, 'nom' => $f[$nameField],
                    'persons' => (int) $g->sum('persons'), 'person_days' => (int) $g->sum('person_days'),
                    'amount' => (float) $g->sum('amount'),
                    'by_level' => $g->groupBy('level')->map(fn (Collection $x) => [
                        'persons' => (int) $x->sum('persons'), 'amount' => (float) $x->sum('amount'),
                    ]),
                    'by_exam' => $g->groupBy('exam')->map(fn (Collection $x) => [
                        'persons' => (int) $x->sum('persons'), 'amount' => (float) $x->sum('amount'),
                    ]),
                    'centres' => $g->whereIn('scope_kind', ['CENTRE', 'EPS'])->pluck('scope_key')->unique()->count(),
                    'candidates' => (int) $g->where('scope_kind', 'CISCO')
                        ->groupBy(fn ($x) => $x['cisco_id'].'|'.$x['exam'])->map(fn ($x) => $x->max('candidates'))->sum(),
                ];
                foreach ($extra as $field) {
                    $row[$field] = $f[$field];
                }

                return $row;
            })->sortByDesc('amount')->values();
    }

    public function levelOrder(string $level): int
    {
        return array_search($level, array_keys(Cat::LEVELS), true) ?: 0;
    }
}
