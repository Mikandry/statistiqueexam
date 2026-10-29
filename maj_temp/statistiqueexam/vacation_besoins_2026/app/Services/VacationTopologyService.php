<?php

namespace App\Services;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\Vacation2026EpsCentre;
use App\Support\VacationDecreeCatalogue as Cat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lit la carte administrative et les effectifs réels (DREN -> CISCO -> centres -> salles ->
 * candidats) et en déduit les « centres logiques » sur lesquels le décret s'applique.
 *
 * Règles de lecture des candidats (identiques aux écrans existants) :
 *   - CEPE : lignes repartition_salles.langue = 'TOTAL'
 *   - BEPC : lignes 'TOTAL_BEPC' si elles existent pour le centre, sinon toutes les langues
 *            sauf 'TOTAL' et 'Etranger (anglais obligatoire)'
 *   - Salles = numéros de salle distincts ; besoins spécifiques = repartition_salles_specifiques
 *     (ou drapeau has_special_needs_candidates).
 *
 * Centres logiques :
 *   - JUMELES    : un centre de correction et un centre d'écrit de même nom (même CISCO, même examen)
 *   - CORRECTION : centre de correction sans centre d'écrit de même nom
 *   - ECRIT      : centre d'écrit rattaché à un centre de correction de nom différent
 *   - SOUS-CENTRE: centre d'écrit (ou de correction) typé sous-centre
 *   - Les candidats corrigés par un centre = tous les centres d'écrit qui lui sont rattachés
 *     (y compris ceux de ses sous-centres).
 */
class VacationTopologyService
{
    /**
     * @param  array<string,string>  $params  paramètres (voir Vacation2026Parametre::DEFAULTS)
     * @return array<string,mixed>
     */
    public function build(string $annee, array $params): array
    {
        $drens = Dren::query()->orderBy('nom')->get()->keyBy('id');
        $ciscos = Cisco::query()->orderBy('nom')->get()->keyBy('id');
        $corrections = CentreCorrection::query()->get()->keyBy('id');
        $ecrits = CentreEcrit::query()->get();

        [$salleStats, $specialIds] = $this->salleStats($annee, (int) ($params['bepc_jours_ecrit_defaut'] ?? 4));

        $warnings = [];
        $sites = []; // sites d'écrit exploitables
        $orphans = 0;

        foreach ($ecrits as $ecrit) {
            if ($ecrit->is_eps_gym) {
                continue; // salle de sport : traité par le niveau EPS
            }
            $correction = $corrections->get((int) $ecrit->centre_correction_id);
            if ($correction === null) {
                $orphans++;
                continue;
            }
            $stats = $salleStats[(int) $ecrit->id] ?? ['CEPE' => $this->emptyExam(), 'BEPC' => $this->emptyExam()];
            $exam = $this->examOf((string) $ecrit->type_examen, (string) $correction->type_examen, $stats);
            $examStats = $stats[$exam];

            $sous = $this->isSousCentre($ecrit, $correction);
            $sites[(int) $ecrit->id] = [
                'id' => (int) $ecrit->id,
                'nom' => (string) $ecrit->nom,
                'exam' => $exam,
                'correction_id' => (int) $correction->id,
                'cisco_id' => (int) $correction->cisco_id,
                'sous_centre' => $sous,
                'candidates' => $examStats['candidates'],
                'salles' => $examStats['salles'],
                'eps_inapt' => $examStats['eps_inapt'],
                'langues' => $examStats['langues'],
                'days_bepc' => $examStats['days_bepc'],
                'special' => isset($specialIds[(int) $ecrit->id]) || $examStats['special'],
            ];
        }

        // Pools de correction : centre de correction (+ ses sous-centres) -> sites d'écrit rattachés
        $children = [];
        foreach ($corrections as $c) {
            if ($c->parent_centre_correction_id) {
                $children[(int) $c->parent_centre_correction_id][] = (int) $c->id;
            }
        }

        $sitesByCorrection = [];
        foreach ($sites as $site) {
            $sitesByCorrection[$site['correction_id']][] = $site['id'];
        }

        $centres = [];
        $usedSites = [];

        foreach ($corrections as $c) {
            if ($c->is_eps_gym) {
                continue;
            }
            $declared = strtoupper(trim((string) $c->centre_type));
            $isSousRow = $declared === Cat::CENTRE_SOUS || $c->parent_centre_correction_id;
            if ($isSousRow) {
                continue; // les sous-centres sont portés par leurs sites d'écrit
            }

            $exam = strtoupper((string) ($c->type_examen ?: 'BEPC'));
            $correctionIds = array_merge([(int) $c->id], $children[(int) $c->id] ?? []);
            $pool = [];
            foreach ($correctionIds as $cid) {
                foreach ($sitesByCorrection[$cid] ?? [] as $siteId) {
                    if ($sites[$siteId]['exam'] === $exam) {
                        $pool[] = $siteId;
                    }
                }
            }

            // site d'écrit homonyme => jumelés
            $twin = null;
            foreach ($pool as $siteId) {
                $s = $sites[$siteId];
                if (! $s['sous_centre'] && $this->norm($s['nom']) === $this->norm((string) $c->nom)) {
                    $twin = $s;
                    break;
                }
            }

            $corrCandidates = array_sum(array_map(fn ($id) => $sites[$id]['candidates'], $pool));
            $langues = $this->mergeLangues(array_map(fn ($id) => $sites[$id], $pool));

            $type = match (true) {
                $declared === Cat::CENTRE_TRANSCRIPTION => Cat::CENTRE_TRANSCRIPTION,
                $twin !== null => Cat::CENTRE_JUMELES,
                default => Cat::CENTRE_CORRECTION,
            };

            $ciscoId = (int) $c->cisco_id;
            $centres[] = [
                'key' => 'C'.$c->id,
                'nom' => (string) $c->nom,
                'exam' => $exam,
                'type' => $type,
                'cisco_id' => $ciscoId,
                'dren_id' => (int) ($ciscos->get($ciscoId)?->dren_id ?? 0) ?: null,
                'parent_nom' => null,
                'ecrit' => $twin,
                'corr_candidates' => $corrCandidates,
                'corr_langues' => $langues,
                'corr_sites' => count($pool),
            ];

            if ($twin !== null) {
                $usedSites[$twin['id']] = true;
            }
        }

        // Sites d'écrit restants : centres d'écrit seuls et sous-centres
        foreach ($sites as $site) {
            if (isset($usedSites[$site['id']])) {
                continue;
            }
            $correction = $corrections->get($site['correction_id']);
            $parentNom = null;
            if ($site['sous_centre'] && $correction) {
                $parent = $correction->parent_centre_correction_id ? $corrections->get((int) $correction->parent_centre_correction_id) : $correction;
                $parentNom = $parent ? (string) $parent->nom : null;
            }
            // cisco : celui du centre de correction rattaché (ou de son parent pour un sous-centre)
            $ciscoId = (int) $site['cisco_id'];
            $centres[] = [
                'key' => 'E'.$site['id'],
                'nom' => $site['nom'],
                'exam' => $site['exam'],
                'type' => $site['sous_centre'] ? Cat::CENTRE_SOUS : Cat::CENTRE_ECRIT,
                'cisco_id' => $ciscoId,
                'dren_id' => (int) ($ciscos->get($ciscoId)?->dren_id ?? 0) ?: null,
                'parent_nom' => $parentNom,
                'ecrit' => $site,
                'corr_candidates' => 0,
                'corr_langues' => null,
                'corr_sites' => 0,
            ];
        }

        // Statistiques par CISCO / examen (à partir des sites d'écrit)
        $ciscoStats = [];
        foreach ($sites as $site) {
            $cid = (int) $site['cisco_id'];
            $e = $site['exam'];
            $ciscoStats[$cid][$e] ??= ['candidates' => 0, 'centres' => 0, 'salles' => 0, 'eps_inapt' => 0];
            $ciscoStats[$cid][$e]['candidates'] += $site['candidates'];
            $ciscoStats[$cid][$e]['salles'] += $site['salles'];
            $ciscoStats[$cid][$e]['centres']++;
            $ciscoStats[$cid][$e]['eps_inapt'] += $site['eps_inapt'];
        }
        // CISCO possédant un centre de correction pour un examen (sans site d'écrit saisi)
        foreach ($centres as $centre) {
            $ciscoStats[$centre['cisco_id']][$centre['exam']] ??= ['candidates' => 0, 'centres' => 0, 'salles' => 0, 'eps_inapt' => 0];
        }

        $epsCentres = $this->epsCentres($ciscos, $corrections, $ciscoStats, $params);

        if ($orphans > 0) {
            $warnings[] = "{$orphans} centre(s) d'écrit sans centre de correction rattaché ont été ignorés.";
        }

        return [
            'drens' => $drens,
            'ciscos' => $ciscos,
            'centres' => $centres,
            'eps_centres' => $epsCentres,
            'cisco_stats' => $ciscoStats,
            'warnings' => $warnings,
        ];
    }

    // ---------------------------------------------------------------------
    // Effectifs par centre d'écrit
    // ---------------------------------------------------------------------

    /**
     * @return array{0: array<int, array{CEPE:array, BEPC:array}>, 1: array<int,bool>}
     */
    private function salleStats(string $annee, int $bepcDaysDefault): array
    {
        $acc = [];
        $special = [];

        if (! Schema::hasTable('repartition_salles')) {
            return [[], []];
        }

        $columns = ['centre_ecrit_id', 'langue', 'numero_salle', 'effectif'];
        $hasInapt = Schema::hasColumn('repartition_salles', 'eps_inapt_effectif');
        $hasSpecialFlag = Schema::hasColumn('repartition_salles', 'has_special_needs_candidates');
        if ($hasInapt) {
            $columns[] = 'eps_inapt_effectif';
        }
        if ($hasSpecialFlag) {
            $columns[] = 'has_special_needs_candidates';
        }

        $query = DB::table('repartition_salles')->where('annee', 'like', $annee.'%')->select($columns);
        foreach ($query->cursor() as $row) {
            $id = (int) $row->centre_ecrit_id;
            $langue = (string) $row->langue;
            $acc[$id][$langue]['sum'] = ($acc[$id][$langue]['sum'] ?? 0) + (int) $row->effectif;
            $acc[$id][$langue]['inapt'] = ($acc[$id][$langue]['inapt'] ?? 0) + (int) ($row->eps_inapt_effectif ?? 0);
            $acc[$id][$langue]['rooms'][(int) $row->numero_salle] = true;
            if ($hasSpecialFlag && ! empty($row->has_special_needs_candidates)) {
                $special[$id] = true;
            }
        }

        if (Schema::hasTable('repartition_salles_specifiques')) {
            foreach (DB::table('repartition_salles_specifiques')->where('annee', 'like', $annee.'%')->pluck('centre_ecrit_id') as $id) {
                $special[(int) $id] = true;
            }
        }

        $out = [];
        foreach ($acc as $id => $byLangue) {
            // CEPE
            $cepe = $this->emptyExam();
            if (isset($byLangue['TOTAL'])) {
                $cepe['candidates'] = (int) $byLangue['TOTAL']['sum'];
                $cepe['salles'] = count($byLangue['TOTAL']['rooms']);
            }

            // BEPC
            $bepc = $this->emptyExam();
            $simplified = isset($byLangue['TOTAL_BEPC']);
            $primary = $simplified
                ? ['TOTAL_BEPC']
                : array_values(array_diff(array_keys($byLangue), ['TOTAL', 'TOTAL_BEPC', 'Etranger (anglais obligatoire)']));
            $rooms = [];
            foreach ($primary as $langue) {
                $bepc['candidates'] += (int) $byLangue[$langue]['sum'];
                $bepc['eps_inapt'] += (int) $byLangue[$langue]['inapt'];
                $rooms += $byLangue[$langue]['rooms'];
            }
            $bepc['salles'] = count($rooms);
            if (! $simplified && $bepc['candidates'] > 0) {
                $bepc['langues'] = [
                    'Anglais' => (int) ($byLangue['Anglais']['sum'] ?? 0),
                    'Allemand' => (int) ($byLangue['Allemand']['sum'] ?? 0),
                    'Esp' => (int) ($byLangue['Esp']['sum'] ?? 0),
                    'Option B' => (int) ($byLangue['Option B']['sum'] ?? 0),
                ];
                $withLv = $bepc['langues']['Anglais'] + $bepc['langues']['Allemand'] + $bepc['langues']['Esp'];
                $bepc['days_bepc'] = $withLv > 0 ? 4 : 3;
            } else {
                $bepc['days_bepc'] = $bepcDaysDefault;
            }

            $out[$id] = ['CEPE' => $cepe, 'BEPC' => $bepc];
        }

        // drapeau « besoins spécifiques » par examen (le décret ne le distingue pas : +2 par centre)
        foreach ($out as $id => &$byExam) {
            foreach (['CEPE', 'BEPC'] as $e) {
                $byExam[$e]['special'] = isset($special[$id]);
            }
        }
        unset($byExam);

        return [$out, $special];
    }

    private function emptyExam(): array
    {
        return ['candidates' => 0, 'salles' => 0, 'eps_inapt' => 0, 'langues' => null, 'days_bepc' => 4, 'special' => false];
    }

    private function examOf(string $ecritType, string $correctionType, array $stats): string
    {
        $type = strtoupper(trim($ecritType));
        if (in_array($type, ['CEPE', 'BEPC'], true)) {
            return $type;
        }
        $type = strtoupper(trim($correctionType));
        if (in_array($type, ['CEPE', 'BEPC'], true)) {
            return $type;
        }

        return ($stats['CEPE']['candidates'] ?? 0) > 0 && ($stats['BEPC']['candidates'] ?? 0) === 0 ? 'CEPE' : 'BEPC';
    }

    private function isSousCentre(CentreEcrit $ecrit, CentreCorrection $correction): bool
    {
        if (strtoupper(trim((string) $ecrit->centre_type)) === Cat::CENTRE_SOUS) {
            return true;
        }
        if (strtoupper(trim((string) $correction->centre_type)) === Cat::CENTRE_SOUS || $correction->parent_centre_correction_id) {
            return true;
        }

        return (bool) preg_match('/-\d+\s+sous[ -]?centre/i', (string) $ecrit->nom);
    }

    /**
     * Somme des candidats par langue (BEPC) sur un ensemble de sites ; null si aucun site
     * n'a de répartition détaillée (le décompte des correcteurs utilisera alors le % d'anglais).
     *
     * @param  array<int,array>  $sites
     */
    private function mergeLangues(array $sites): ?array
    {
        $detailed = array_filter($sites, fn ($s) => $s['langues'] !== null);
        if (! $detailed) {
            return null;
        }

        $sum = ['Anglais' => 0, 'Allemand' => 0, 'Esp' => 0, 'Option B' => 0];
        $simplifiedCandidates = 0;
        foreach ($sites as $s) {
            if ($s['langues'] === null) {
                $simplifiedCandidates += $s['candidates'];
                continue;
            }
            foreach ($sum as $k => $_) {
                $sum[$k] += (int) ($s['langues'][$k] ?? 0);
            }
        }
        // sites en répartition simplifiée : considérés comme « anglais » (le % est appliqué plus tard)
        $sum['Anglais'] += $simplifiedCandidates;

        return $sum;
    }

    // ---------------------------------------------------------------------
    // EPS
    // ---------------------------------------------------------------------

    /**
     * Centres EPS : ceux configurés par CISCO (vacation_2026_eps_centres) ; à défaut des centres
     * « EPS/GYM » existants ; à défaut N centres estimés (paramètre eps_centres_par_cisco).
     * Candidats EPS d'une CISCO = somme des centres configurés, sinon saisie manuelle,
     * sinon candidats BEPC moins inaptes EPS.
     *
     * @return array{centres: array<int,array>, cisco_candidates: array<int,int>}
     */
    private function epsCentres($ciscos, $corrections, array $ciscoStats, array $params): array
    {
        $configured = Schema::hasTable('vacation_2026_eps_centres')
            ? Vacation2026EpsCentre::query()->get()->groupBy('cisco_id')
            : collect();
        $legacy = $corrections->filter(fn ($c) => $c->is_eps_gym && strtoupper((string) $c->type_examen) === 'BEPC')->groupBy('cisco_id');
        $defaultN = max(1, (int) ($params['eps_centres_par_cisco'] ?? 1));

        $centres = [];
        $ciscoCandidates = [];

        foreach ($ciscos as $cisco) {
            $stats = $ciscoStats[(int) $cisco->id]['BEPC'] ?? null;
            $bepcCandidates = (int) ($stats['candidates'] ?? 0);
            $aptes = max(0, $bepcCandidates - (int) ($stats['eps_inapt'] ?? 0));

            $cfg = $configured->get($cisco->id, collect());
            $manual = (int) ($cisco->manual_eps_candidates ?? 0);

            if ($cfg->isNotEmpty() && (int) $cfg->sum('candidate_count') > 0) {
                $total = (int) $cfg->sum('candidate_count');
                foreach ($cfg as $c) {
                    $centres[] = ['key' => 'P'.$c->id, 'nom' => (string) $c->name, 'cisco_id' => (int) $cisco->id,
                        'dren_id' => (int) $cisco->dren_id ?: null, 'candidates' => (int) $c->candidate_count, 'estime' => false];
                }
            } else {
                $total = $manual > 0 ? $manual : $aptes;
                if ($total <= 0 && $cfg->isEmpty()) {
                    $ciscoCandidates[(int) $cisco->id] = 0;
                    continue;
                }
                $names = $cfg->isNotEmpty()
                    ? $cfg->pluck('name')->all()
                    : ($legacy->has($cisco->id) ? $legacy->get($cisco->id)->pluck('nom')->all() : []);
                $n = $names ? count($names) : $defaultN;
                $each = (int) ceil($total / $n);
                for ($i = 0; $i < $n; $i++) {
                    $share = $i < $n - 1 ? min($each, $total) : max(0, $total - $each * ($n - 1));
                    $centres[] = [
                        'key' => 'X'.$cisco->id.'-'.($i + 1),
                        'nom' => $names[$i] ?? ('Centre EPS '.($i + 1).' — '.$cisco->nom),
                        'cisco_id' => (int) $cisco->id,
                        'dren_id' => (int) $cisco->dren_id ?: null,
                        'candidates' => $share,
                        'estime' => true,
                    ];
                }
            }
            $ciscoCandidates[(int) $cisco->id] = $total;
        }

        return ['centres' => $centres, 'cisco_candidates' => $ciscoCandidates];
    }

    private function norm(string $name): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/', ' ', $name)));
    }
}
