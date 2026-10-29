<?php

namespace App\Services;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\Province;
use App\Models\TranscriptionCentre;
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
        $provinces = Province::query()->orderBy('nom')->get()->keyBy('id');
        $drens = Dren::query()->with('provinceRef')->orderBy('nom')->get()->keyBy('id');
        $ciscos = Cisco::query()->orderBy('nom')->get()->keyBy('id');

        // Province de chaque DREN et de chaque CISCO. Une CISCO historique sans DREN se rattache
        // directement à sa province ; sinon elle hérite de celle de sa DREN.
        $provinceOfDren = [];
        foreach ($drens as $d) {
            $provinceOfDren[(int) $d->id] = ['id' => $d->province_id ? (int) $d->province_id : null, 'nom' => $d->provinceNom()];
        }
        $provinceOfCisco = [];
        foreach ($ciscos as $c) {
            $fromDren = $c->dren_id ? ($provinceOfDren[(int) $c->dren_id] ?? null) : null;
            if ($fromDren && $fromDren['nom']) {
                $provinceOfCisco[(int) $c->id] = $fromDren;
            } elseif ($c->province_id && $provinces->has((int) $c->province_id)) {
                $provinceOfCisco[(int) $c->id] = ['id' => (int) $c->province_id, 'nom' => (string) $provinces[(int) $c->province_id]->nom];
            } else {
                $provinceOfCisco[(int) $c->id] = ['id' => null, 'nom' => null];
            }
        }

        $corrections = CentreCorrection::query()->get()->keyBy('id');
        $ecrits = CentreEcrit::query()->get();

        [$salleStats, $specialIds] = $this->salleStats($annee, (int) ($params['bepc_jours_ecrit_defaut'] ?? 4));
        $rawTotals = ['CEPE' => 0, 'BEPC' => 0];
        foreach ($salleStats as $byExam) {
            $rawTotals['CEPE'] += $byExam['CEPE']['candidates'];
            $rawTotals['BEPC'] += $byExam['BEPC']['candidates'];
        }

        $warnings = [];
        $sites = []; // sites d'écrit exploitables
        $orphans = 0;

        foreach ($ecrits as $ecrit) {
            if ($this->isEpsOnly($ecrit)) {
                continue; // site EPS seul : ses candidats viennent de eps_capacity, jamais des salles
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
                'same_site' => (bool) $ecrit->same_site_as_correction,
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
            if ($this->isEpsOnly($c)) {
                continue; // centre EPS seul : traité dans epsCentres()
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
                if (! $s['sous_centre'] && ($s['same_site'] || $this->norm($s['nom']) === $this->norm((string) $c->nom))) {
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
                'province_id' => $provinceOfCisco[$ciscoId]['id'] ?? null,
                'province' => $provinceOfCisco[$ciscoId]['nom'] ?? null,
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
                'province_id' => $provinceOfCisco[$ciscoId]['id'] ?? null,
                'province' => $provinceOfCisco[$ciscoId]['nom'] ?? null,
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

        $epsCentres = $this->epsCentres($ciscos, $corrections, $ciscoStats, $params, $provinceOfCisco, $warnings);
        $transcription = $this->transcriptionCentres($ciscos, $ciscoStats, $provinceOfCisco);

        // Table de correspondance centre <-> clé de périmètre (pour rapprocher les affectations réelles)
        $siteKey = [];
        $correctionKey = [];
        foreach ($centres as $centre) {
            if ($centre['ecrit']) {
                $siteKey[$centre['ecrit']['id']] = $centre['key'];
            }
            if (str_starts_with($centre['key'], 'C')) {
                $correctionKey[(int) substr($centre['key'], 1)] = $centre['key'];
            }
        }
        foreach ($corrections as $c) {
            if (! isset($correctionKey[(int) $c->id])) {
                foreach ($sitesByCorrection[(int) $c->id] ?? [] as $sid) {
                    if (isset($siteKey[$sid])) {
                        $correctionKey[(int) $c->id] = $siteKey[$sid];
                        break;
                    }
                }
            }
        }
        foreach ($epsCentres['centres'] as $eps) {
            if (! empty($eps['correction_id'])) {
                $correctionKey['eps'.$eps['correction_id']] = $eps['key'];
            }
        }

        // Contrôle de cohérence : total brut des salles vs total rattaché à un centre exploitable
        $attached = ['CEPE' => 0, 'BEPC' => 0];
        foreach ($sites as $site) {
            $attached[$site['exam']] += $site['candidates'];
        }
        // Une salle saisie pour un examen différent de celui du centre n'est pas rattachée : on le détecte
        $unattachedCepe = max(0, $rawTotals['CEPE'] - $attached['CEPE']);
        $unattachedBepc = max(0, $rawTotals['BEPC'] - $attached['BEPC']);
        if ($unattachedCepe > 0 || $unattachedBepc > 0) {
            $warnings[] = "Écart de cohérence : {$unattachedCepe} candidat(s) CEPE et {$unattachedBepc} candidat(s) BEPC saisis par salle ne sont rattachés à aucun centre exploitable "
                .'(centre d\'écrit sans centre de correction, centre EPS seul ou type d\'examen différent).';
        }

        if ($orphans > 0) {
            $warnings[] = "{$orphans} centre(s) d'écrit sans centre de correction rattaché ont été ignorés.";
        }
        $noGeo = $ciscos->filter(fn ($c) => ! $c->dren_id && ! $c->province_id)->count();
        if ($noGeo > 0) {
            $warnings[] = "{$noGeo} CISCO sans DREN ni province : renseignez au moins la province (Références).";
        }

        return [
            'provinces' => $provinces,
            'province_of_dren' => $provinceOfDren,
            'province_of_cisco' => $provinceOfCisco,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'centres' => $centres,
            'eps_centres' => $epsCentres,
            'transcription' => $transcription,
            'site_key' => $siteKey,
            'correction_key' => $correctionKey,
            'cisco_stats' => $ciscoStats,
            'checks' => ['raw' => $rawTotals, 'attached' => $attached],
            'warnings' => $warnings,
        ];
    }

    /** Centre (écrit ou correction) marqué « EPS seul » dans les références. */
    private function isEpsOnly($centre): bool
    {
        return (bool) $centre->is_eps_gym || strtoupper(trim((string) $centre->centre_type)) === Cat::CENTRE_EPS;
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
     * Centres EPS et candidats EPS par CISCO. Source de vérité, par ordre de priorité :
     *   1. centres EPS configurés par la CISCO (vacation_2026_eps_centres)
     *   2. centres « EPS seul » des références : centre_corrections.eps_capacity (champ existant)
     *   3. saisie globale de la CISCO (ciscos.manual_eps_candidates)
     *   4. estimation : candidats BEPC de la CISCO moins les inaptes EPS
     * Une seule source est retenue par CISCO : jamais de cumul, donc pas de double comptage.
     *
     * @return array{centres: array<int,array>, cisco_candidates: array<int,int>, sources: array<int,string>}
     */
    private function epsCentres($ciscos, $corrections, array $ciscoStats, array $params, array $provinceOfCisco, array &$warnings): array
    {
        $configured = Schema::hasTable('vacation_2026_eps_centres')
            ? Vacation2026EpsCentre::query()->get()->groupBy('cisco_id')
            : collect();
        $epsOnly = $corrections
            ->filter(fn ($c) => $this->isEpsOnly($c) && strtoupper((string) $c->type_examen) !== 'CEPE')
            ->groupBy('cisco_id');
        $defaultN = max(1, (int) ($params['eps_centres_par_cisco'] ?? 1));

        $centres = [];
        $ciscoCandidates = [];
        $sources = [];

        foreach ($ciscos as $cisco) {
            $cid = (int) $cisco->id;
            $geo = ['dren_id' => (int) $cisco->dren_id ?: null, 'province_id' => $provinceOfCisco[$cid]['id'] ?? null, 'province' => $provinceOfCisco[$cid]['nom'] ?? null];
            $stats = $ciscoStats[$cid]['BEPC'] ?? null;
            $aptes = max(0, (int) ($stats['candidates'] ?? 0) - (int) ($stats['eps_inapt'] ?? 0));

            $cfg = $configured->get($cid, collect());
            $cfgTotal = (int) $cfg->sum('candidate_count');
            $own = $epsOnly->get($cid, collect());
            $ownTotal = (int) $own->sum(fn ($c) => (int) ($c->eps_capacity ?? 0));
            $manual = (int) ($cisco->manual_eps_candidates ?? 0);

            if ($cfgTotal > 0 && $ownTotal > 0 && $cfgTotal !== $ownTotal) {
                $warnings[] = "CISCO {$cisco->nom} : candidats EPS saisis deux fois ({$cfgTotal} dans les centres EPS configurés, {$ownTotal} dans les références). Seuls les centres EPS configurés sont retenus.";
            }

            if ($cfgTotal > 0) {
                foreach ($cfg as $c) {
                    $centres[] = ['key' => 'P'.$c->id, 'nom' => (string) $c->name, 'cisco_id' => $cid, 'candidates' => (int) $c->candidate_count,
                        'estime' => false, 'source' => 'configure', 'correction_id' => null] + $geo;
                }
                $total = $cfgTotal;
                $sources[$cid] = 'configure';
            } elseif ($ownTotal > 0) {
                foreach ($own as $c) {
                    $n = (int) ($c->eps_capacity ?? 0);
                    if ($n > 0) {
                        $centres[] = ['key' => 'G'.$c->id, 'nom' => (string) $c->nom, 'cisco_id' => $cid, 'candidates' => $n,
                            'estime' => false, 'source' => 'centre', 'correction_id' => (int) $c->id] + $geo;
                    }
                }
                $total = $ownTotal;
                $sources[$cid] = 'centre';
            } else {
                $total = $manual > 0 ? $manual : $aptes;
                if ($total <= 0) {
                    $ciscoCandidates[$cid] = 0;
                    continue;
                }
                $names = $own->pluck('nom')->all();
                $n = $names ? count($names) : $defaultN;
                $each = (int) ceil($total / $n);
                for ($i = 0; $i < $n; $i++) {
                    $share = $i < $n - 1 ? min($each, $total) : max(0, $total - $each * ($n - 1));
                    $centres[] = [
                        'key' => 'X'.$cid.'-'.($i + 1),
                        'nom' => $names[$i] ?? ('Centre EPS '.($i + 1).' — '.$cisco->nom),
                        'cisco_id' => $cid, 'candidates' => $share, 'estime' => true,
                        'source' => $manual > 0 ? 'cisco' : 'estime', 'correction_id' => $own->values()->get($i)?->id,
                    ] + $geo;
                }
                $sources[$cid] = $manual > 0 ? 'cisco' : 'estime';
            }
            $ciscoCandidates[$cid] = $total;
        }

        return ['centres' => $centres, 'cisco_candidates' => $ciscoCandidates, 'sources' => $sources];
    }

    // ---------------------------------------------------------------------
    // Centres de transcription (distincts des centres d'écrit et de correction)
    // ---------------------------------------------------------------------

    /**
     * Candidats transcrits par centre et par examen.
     * Candidats saisis sur le centre si renseignés ; sinon les candidats de la CISCO sont
     * répartis à parts égales entre ses centres de transcription (le reste va aux premiers).
     *
     * @return array{centres: array<int,array>, cisco_ids: array<int,bool>}
     */
    private function transcriptionCentres($ciscos, array $ciscoStats, array $provinceOfCisco): array
    {
        if (! Schema::hasTable('transcription_centres')) {
            return ['centres' => [], 'cisco_ids' => []];
        }

        $byCisco = TranscriptionCentre::query()->orderBy('ordre')->orderBy('nom')->get()->groupBy('cisco_id');
        $out = [];
        $ciscoIds = [];

        foreach ($byCisco as $cid => $list) {
            $cisco = $ciscos->get((int) $cid);
            if ($cisco === null) {
                continue;
            }
            $ciscoIds[(int) $cid] = true;
            $perExam = [];
            foreach (['CEPE' => 'candidats_cepe', 'BEPC' => 'candidats_bepc'] as $exam => $col) {
                $total = (int) ($ciscoStats[(int) $cid][$exam]['candidates'] ?? 0);
                $explicit = $list->filter(fn ($c) => $c->{$col} !== null);
                $free = $list->filter(fn ($c) => $c->{$col} === null)->values();
                $remaining = max(0, $total - (int) $explicit->sum($col));
                $share = [];
                if ($free->isNotEmpty()) {
                    $base = intdiv($remaining, $free->count());
                    $extra = $remaining - $base * $free->count();
                    foreach ($free as $i => $c) {
                        $share[$c->id] = $base + ($i < $extra ? 1 : 0);
                    }
                }
                foreach ($list as $c) {
                    $perExam[$c->id][$exam] = $c->{$col} !== null ? (int) $c->{$col} : (int) ($share[$c->id] ?? 0);
                }
            }
            foreach ($list as $c) {
                $out[] = [
                    'id' => (int) $c->id, 'key' => 'T'.$c->id, 'nom' => (string) $c->nom, 'localisation' => $c->localisation,
                    'cisco_id' => (int) $cid, 'dren_id' => (int) $cisco->dren_id ?: null,
                    'province_id' => $provinceOfCisco[(int) $cid]['id'] ?? null, 'province' => $provinceOfCisco[(int) $cid]['nom'] ?? null,
                    'candidates' => $perExam[$c->id],
                    'explicit' => $c->candidats_cepe !== null || $c->candidats_bepc !== null,
                ];
            }
        }

        return ['centres' => $out, 'cisco_ids' => $ciscoIds];
    }

    private function norm(string $name): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/', ' ', $name)));
    }
}
