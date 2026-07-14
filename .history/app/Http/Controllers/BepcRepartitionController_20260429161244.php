<?php

namespace App\Http\Controllers;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\AuditLog;
use App\Models\GlobalSetting;
use App\Models\RepartitionSalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BepcRepartitionController extends Controller
{
    private const STICKY_CONTEXT_SESSION_KEY = 'repartition_saisie_context';

    private const TYPE_BEPC = 'BEPC';

    private const TYPE_CEPE = 'CEPE';

    private const CEPE_KEY = 'TOTAL';

    public function create(Request $request)
    {
        $nombreSalles = max((int) $request->integer('nombre_salles', 1), 1);
        $typeExamen = strtoupper((string) $request->query('type_examen', self::TYPE_BEPC));

        if (! in_array($typeExamen, [self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $typeExamen = self::TYPE_BEPC;
        }

        $settings = GlobalSetting::query()->first();
        $dispatchingAxes = $this->parseConfiguredList((string) ($settings?->dispatching_axes ?? ''));
        $dispatchingDropPoints = $this->parseConfiguredList((string) ($settings?->dispatching_drop_points ?? ''));
        $stickyContext = (array) $request->session()->get(self::STICKY_CONTEXT_SESSION_KEY, []);

        $centresEcritDisponibles = CentreEcrit::query()
            ->join('centre_corrections as cc', 'cc.id', '=', 'centre_ecrits.centre_correction_id')
            ->where('centre_ecrits.type_examen', $typeExamen)
            ->whereDoesntHave('repartitions', function ($query) use ($typeExamen) {
                if ($typeExamen === self::TYPE_CEPE) {
                    $query->where('langue', self::CEPE_KEY);
                } else {
                    $query->where('langue', '!=', self::CEPE_KEY);
                }
            })
            ->orderBy('centre_ecrits.nom')
            ->get([
                'centre_ecrits.id',
                'centre_ecrits.centre_correction_id',
                'centre_ecrits.nom',
                'centre_ecrits.type_examen',
                'cc.cisco_id',
            ]);

        $specialCandidates = DB::table('repartition_salles_specifiques as rss')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rss.centre_ecrit_id')
            ->when($typeExamen, fn ($query) => $query->where('rss.type_examen', $typeExamen))
            ->when($request->query('annee', $stickyContext['annee'] ?? ''), fn ($query, $annee) => $query->where('rss.annee', $annee))
            ->orderBy('rss.annee')
            ->orderBy('ce.nom')
            ->orderBy('rss.numero_salle')
            ->get([
                'rss.centre_ecrit_id',
                'ce.nom as centre_ecrit_nom',
                'rss.annee',
                'rss.type_examen',
                'rss.numero_salle',
                'rss.type_handicap',
            ]);

        return view('bepc-repartition.create', [
            'langues' => RepartitionSalle::LANGUES,
            'nombreSalles' => $nombreSalles,
            'typeExamen' => $typeExamen,
            'drens' => Dren::query()->orderBy('nom')->get(['id', 'nom']),
            'ciscos' => Cisco::query()->orderBy('nom')->get(['id', 'dren_id', 'nom']),
            'centresCorrection' => CentreCorrection::query()
                ->where('type_examen', $typeExamen)
                ->orderBy('nom')
                ->get(['id', 'cisco_id', 'nom', 'type_examen']),
            'centresEcrit' => $centresEcritDisponibles,
            'dispatchingAxes' => $dispatchingAxes,
            'dispatchingDropPoints' => $dispatchingDropPoints,
            'stickyContext' => $stickyContext,
            'specialCandidates' => $specialCandidates,
        ]);
    }

    public function store(Request $request)
    {
        $langues = RepartitionSalle::LANGUES;
        $validated = $request->validate([
            'type_examen' => ['required', 'in:'.self::TYPE_BEPC.','.self::TYPE_CEPE],
            'dren_id' => ['required', 'integer', 'exists:drens,id'],
            'cisco_id' => ['required', 'integer', 'exists:ciscos,id'],
            'centre_correction_id' => ['required', 'integer', 'exists:centre_corrections,id'],
            'centre_ecrit_id' => ['required', 'integer', 'exists:centre_ecrits,id'],
            'axe_dispatching' => ['required', 'string', 'max:255'],
            'point_largage' => ['nullable', 'string', 'max:255'],
            'point_largage_other' => ['nullable', 'string', 'max:255'],
            'annee' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'nombre_salles' => ['required', 'integer', 'min:1', 'max:70'],
            'salles_inutilisables' => ['nullable', 'string', 'max:255'],
            'candidats_specifiques' => ['nullable', 'string', 'max:2000'],
            'has_foreign_candidates' => ['nullable', 'boolean'],
            'foreign_option_a_lv' => ['nullable', 'in:Allemand,Esp,Anglais'],
            'foreign_option_a_replace_malagasy' => ['nullable', 'string', 'max:255'],
            'foreign_option_b_replace_malagasy' => ['nullable', 'string', 'max:255'],
        ]);

        $nombreSalles = (int) $validated['nombre_salles'];
        $typeExamen = $validated['type_examen'];
        $pointLargage = trim((string) ($validated['point_largage'] ?? ''));
        $pointLargageOther = trim((string) ($validated['point_largage_other'] ?? ''));
        $shouldPersistCustomDropPoint = false;
        $totalCandidatsSaisis = 0;
        $sallesInutilisables = $this->parseRoomNumbers((string) ($validated['salles_inutilisables'] ?? ''), $nombreSalles);
        $sallesDisponibles = array_values(array_diff(range(1, $nombreSalles), $sallesInutilisables));

        if ($sallesDisponibles === []) {
            return back()
                ->withInput()
                ->withErrors(['salles_inutilisables' => 'Toutes les salles sont marquées comme inutilisables.']);
        }

        if ($typeExamen === self::TYPE_BEPC && $pointLargage === '__other__') {
            if ($pointLargageOther === '') {
                return back()
                    ->withInput()
                    ->withErrors(['point_largage_other' => 'Précisez le point de largage BEPC lorsque vous choisissez "Autre".']);
            }

            $validated['point_largage'] = $pointLargageOther;
            $shouldPersistCustomDropPoint = true;
        }

        if ($typeExamen === self::TYPE_BEPC) {
            $effectifs = $request->input('effectifs');
            if (! is_array($effectifs)) {
                return back()
                    ->withInput()
                    ->withErrors(['effectifs' => 'Le tableau des effectifs BEPC est obligatoire.']);
            }

            foreach ($langues as $langue) {
                if (! isset($effectifs[$langue]) || ! is_array($effectifs[$langue])) {
                    return back()
                        ->withInput()
                        ->withErrors(['effectifs' => "La ligne {$langue} est obligatoire."]);
                }

                foreach ($sallesDisponibles as $salle) {
                    $value = $effectifs[$langue][$salle] ?? null;

                    if ($value === null || $value === '' || ! is_numeric($value) || (int) $value < 0) {
                        return back()
                            ->withInput()
                            ->withErrors([
                                'effectifs' => "Valeur invalide pour {$langue} - Salle {$salle}.",
                            ]);
                    }

                    $totalCandidatsSaisis += (int) $value;
                }
            }

            $emptyRooms = [];
            foreach ($sallesDisponibles as $salle) {
                $roomTotal = 0;
                foreach ($langues as $langue) {
                    $roomTotal += (int) ($effectifs[$langue][$salle] ?? 0);
                }

                if ($roomTotal <= 0) {
                    $emptyRooms[] = $salle;
                }
            }

            if ($emptyRooms !== []) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'effectifs' => 'Salle(s) avec total 0: '.implode(', ', $emptyRooms).'. Veuillez saisir les effectifs ou ajuster le canevas/salles inutilisables.',
                    ]);
            }
        } else {
            $effectifsTotal = $request->input('effectifs_total');
            if (! is_array($effectifsTotal)) {
                return back()
                    ->withInput()
                    ->withErrors(['effectifs_total' => 'Le total CEPE par salle est obligatoire.']);
            }

            foreach ($sallesDisponibles as $salle) {
                $value = $effectifsTotal[$salle] ?? null;

                if ($value === null || $value === '' || ! is_numeric($value) || (int) $value < 0) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'effectifs_total' => "Valeur invalide pour CEPE - Salle {$salle}.",
                        ]);
                }

                $totalCandidatsSaisis += (int) $value;
            }

            $emptyRooms = collect($sallesDisponibles)
                ->filter(fn (int $salle) => (int) ($effectifsTotal[$salle] ?? 0) <= 0)
                ->values()
                ->all();

            if ($emptyRooms !== []) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'effectifs_total' => 'Salle(s) CEPE avec total 0: '.implode(', ', $emptyRooms).'. Veuillez saisir les effectifs ou ajuster le canevas/salles inutilisables.',
                    ]);
            }
        }

        $hasForeignCandidates = $typeExamen === self::TYPE_BEPC && (bool) $request->boolean('has_foreign_candidates');
        $foreignSettings = null;
        if ($hasForeignCandidates) {
            $optionALv = (string) $request->input('foreign_option_a_lv', '');
            $optionAReplace = trim((string) $request->input('foreign_option_a_replace_malagasy', ''));
            $optionBReplace = trim((string) $request->input('foreign_option_b_replace_malagasy', ''));
            $totalOptionA = 0;
            $totalOptionB = 0;

            if (! in_array($optionALv, ['Allemand', 'Esp', 'Anglais'], true)) {
                return back()
                    ->withInput()
                    ->withErrors(['foreign_option_a_lv' => 'Choisissez Allemand, Esp ou Anglais pour les étrangers Option A.']);
            }

            foreach ($sallesDisponibles as $salle) {
                $valueA = $request->input("foreign_effectifs.option_a.$salle", 0);
                $valueB = $request->input("foreign_effectifs.option_b.$salle", 0);
                if (! is_numeric($valueA) || (int) $valueA < 0 || ! is_numeric($valueB) || (int) $valueB < 0) {
                    return back()
                        ->withInput()
                        ->withErrors(['foreign_effectifs' => "Valeur invalide pour les candidats étrangers - Salle {$salle}."]);
                }

                if ((int) $valueA > 0 && (int) $valueB > 0) {
                    return back()
                        ->withInput()
                        ->withErrors(['foreign_effectifs' => "La salle {$salle} ne peut pas avoir étrangers Option A et Option B en même temps."]);
                }

                $totalOptionA += (int) $valueA;
                $totalOptionB += (int) $valueB;
                $totalCandidatsSaisis += (int) $valueA + (int) $valueB;
            }

            if ($totalOptionA === 0 && $totalOptionB === 0) {
                return back()
                    ->withInput()
                    ->withErrors(['foreign_effectifs' => 'Activez l\'exception étrangers uniquement si vous avez des effectifs à saisir.']);
            }

            if ($totalOptionA > 0 && $optionAReplace === '') {
                return back()
                    ->withInput()
                    ->withErrors(['foreign_option_a_replace_malagasy' => 'Renseignez la langue de remplacement Malagasy pour les étrangers Option A.']);
            }
            if ($totalOptionA > 0 && strcasecmp($optionAReplace, $optionALv) === 0) {
                return back()
                    ->withInput()
                    ->withErrors(['foreign_option_a_replace_malagasy' => 'La langue de remplacement Malagasy (Option A) doit être différente de la langue vivante choisie.']);
            }

            if ($totalOptionB > 0 && $optionBReplace === '') {
                return back()
                    ->withInput()
                    ->withErrors(['foreign_option_b_replace_malagasy' => 'Renseignez la langue de remplacement Malagasy pour les étrangers Option B.']);
            }

            $foreignSettings = [
                'option_a_lv' => $optionALv,
                'option_a_replace' => $optionAReplace,
                'option_b_replace' => $optionBReplace,
            ];
        }

        if ($totalCandidatsSaisis <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'effectifs' => 'Le total des candidats est 0. Veuillez saisir au moins une valeur supérieure à 0.',
                ]);
        }

        $dren = Dren::findOrFail((int) $validated['dren_id']);
        $cisco = Cisco::findOrFail((int) $validated['cisco_id']);
        $centreCorrection = CentreCorrection::query()
            ->where('type_examen', $typeExamen)
            ->whereKey((int) $validated['centre_correction_id'])
            ->firstOrFail();
        $centreEcrit = CentreEcrit::query()
            ->where('type_examen', $typeExamen)
            ->whereKey((int) $validated['centre_ecrit_id'])
            ->firstOrFail();

        if ($cisco->dren_id !== $dren->id ||
            $centreCorrection->cisco_id !== $cisco->id ||
            $centreEcrit->centre_correction_id !== $centreCorrection->id ||
            $centreCorrection->type_examen !== $typeExamen ||
            $centreEcrit->type_examen !== $typeExamen) {
            return back()
                ->withInput()
                ->withErrors(['centre_ecrit_id' => 'La hiérarchie DREN/CISCO/Centre sélectionnée est invalide.']);
        }

        if ($typeExamen === self::TYPE_CEPE) {
            $validated['point_largage'] = trim((string) $cisco->nom);
        } elseif (trim((string) ($validated['point_largage'] ?? '')) === '') {
            return back()
                ->withInput()
                ->withErrors(['point_largage' => 'Veuillez sélectionner un point de largage BEPC.']);
        }

        if ($typeExamen === self::TYPE_BEPC && $shouldPersistCustomDropPoint) {
            $this->appendConfiguredDropPoint((string) $validated['point_largage']);
        }

        $nomSaisie = trim((string) ($request->user()?->name ?? ''));

        if ($nomSaisie === '') {
            return back()
                ->withInput()
                ->withErrors(['auth' => 'Utilisateur connecté invalide. Veuillez vous reconnecter.']);
        }

        $candidatsSpecifiques = $this->parseSpecialCandidates(
            (string) $request->input('candidats_specifiques', ''),
            $sallesDisponibles
        );

        DB::transaction(function () use ($validated, $langues, $sallesDisponibles, $typeExamen, $request, $centreEcrit, $nomSaisie, $hasForeignCandidates, $foreignSettings, $candidatsSpecifiques) {
            $axeDispatching = trim((string) $validated['axe_dispatching']);
            $pointLargage = trim((string) $validated['point_largage']);

            RepartitionSalle::query()
                ->where('centre_ecrit_id', $centreEcrit->id)
                ->where('annee', $validated['annee'])
                ->delete();
            DB::table('repartition_salles_specifiques')
                ->where('centre_ecrit_id', $centreEcrit->id)
                ->where('annee', $validated['annee'])
                ->delete();

            if ($typeExamen === self::TYPE_BEPC) {
                foreach ($langues as $langue) {
                    foreach ($sallesDisponibles as $salle) {
                        RepartitionSalle::updateOrCreate(
                            [
                                'centre_ecrit_id' => $centreEcrit->id,
                                'annee' => $validated['annee'],
                                'langue' => $langue,
                                'numero_salle' => $salle,
                            ],
                            [
                                'effectif' => (int) $request->input("effectifs.$langue.$salle"),
                                'saisi_par' => $nomSaisie,
                                'axe_dispatching' => $axeDispatching,
                                'point_largage' => $pointLargage,
                            ]
                        );
                    }
                }

                if ($hasForeignCandidates && is_array($foreignSettings)) {
                    foreach ($sallesDisponibles as $salle) {
                        $optionACount = (int) $request->input("foreign_effectifs.option_a.$salle", 0);
                        $optionBCount = (int) $request->input("foreign_effectifs.option_b.$salle", 0);

                        if ($optionACount > 0) {
                            RepartitionSalle::updateOrCreate(
                                [
                                    'centre_ecrit_id' => $centreEcrit->id,
                                    'annee' => $validated['annee'],
                                    'langue' => "Etranger Option A (LV: {$foreignSettings['option_a_lv']}, Rempl. Malagasy: {$foreignSettings['option_a_replace']})",
                                    'numero_salle' => $salle,
                                ],
                                [
                                    'effectif' => $optionACount,
                                    'saisi_par' => $nomSaisie,
                                    'axe_dispatching' => $axeDispatching,
                                    'point_largage' => $pointLargage,
                                ]
                            );
                        }

                        if ($optionBCount > 0) {
                            RepartitionSalle::updateOrCreate(
                                [
                                    'centre_ecrit_id' => $centreEcrit->id,
                                    'annee' => $validated['annee'],
                                    'langue' => "Etranger Option B (Rempl. Malagasy: {$foreignSettings['option_b_replace']})",
                                    'numero_salle' => $salle,
                                ],
                                [
                                    'effectif' => $optionBCount,
                                    'saisi_par' => $nomSaisie,
                                    'axe_dispatching' => $axeDispatching,
                                    'point_largage' => $pointLargage,
                                ]
                            );
                        }
                    }
                }
            } else {
                foreach ($sallesDisponibles as $salle) {
                    RepartitionSalle::updateOrCreate(
                        [
                            'centre_ecrit_id' => $centreEcrit->id,
                            'annee' => $validated['annee'],
                            'langue' => self::CEPE_KEY,
                            'numero_salle' => $salle,
                        ],
                        [
                            'effectif' => (int) $request->input("effectifs_total.$salle"),
                            'saisi_par' => $nomSaisie,
                            'axe_dispatching' => $axeDispatching,
                            'point_largage' => $pointLargage,
                        ]
                    );
                }
            }

            if ($candidatsSpecifiques !== []) {
                $now = now();
                $rows = array_map(function (array $item) use ($centreEcrit, $validated, $typeExamen, $nomSaisie, $now) {
                    return [
                        'centre_ecrit_id' => $centreEcrit->id,
                        'annee' => $validated['annee'],
                        'type_examen' => $typeExamen,
                        'numero_salle' => $item['numero_salle'],
                        'type_handicap' => $item['type_handicap'],
                        'saisi_par' => $nomSaisie,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $candidatsSpecifiques);

                DB::table('repartition_salles_specifiques')->insert($rows);
            }
        });

        AuditLog::record($request, 'saisie_repartition', [
            'annee' => $validated['annee'],
            'type_examen' => $typeExamen,
            'dren_id' => (int) $dren->id,
            'cisco_id' => (int) $cisco->id,
            'centre_correction_id' => (int) $centreCorrection->id,
            'centre_ecrit_id' => (int) $centreEcrit->id,
            'salles' => $nombreSalles,
        ]);

        $request->session()->put(self::STICKY_CONTEXT_SESSION_KEY, [
            'type_examen' => $typeExamen,
            'annee' => $validated['annee'],
            'dren_id' => (int) $dren->id,
            'cisco_id' => (int) $cisco->id,
            'centre_correction_id' => (int) $centreCorrection->id,
            'axe_dispatching' => trim((string) $validated['axe_dispatching']),
            'point_largage' => trim((string) $validated['point_largage']),
            'point_largage_other' => $typeExamen === self::TYPE_BEPC && $pointLargage === '__other__'
                ? $pointLargageOther
                : '',
        ]);

        return redirect()
            ->route('bepc.repartition.create', [
                'nombre_salles' => $nombreSalles,
                'type_examen' => $typeExamen,
            ])
            ->with('status', "Saisie {$typeExamen} enregistrée avec succès.");
    }

    private function parseRoomNumbers(string $value, int $maxRoom): array
    {
        if (trim($value) === '') {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($part) => trim($part))
            ->filter(fn ($part) => $part !== '' && ctype_digit($part))
            ->map(fn ($part) => (int) $part)
            ->filter(fn (int $room) => $room >= 1 && $room <= $maxRoom)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function parseConfiguredList(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function appendConfiguredDropPoint(string $value): void
    {
        $value = trim($value);
        if ($value === '') {
            return;
        }

        $settings = GlobalSetting::query()->first();

        if (! $settings) {
            $settings = GlobalSetting::query()->create([
                'bepc_copy_margin_percent' => 5,
                'dispatching_drop_points' => $value,
            ]);

            return;
        }

        $points = $this->parseConfiguredList((string) ($settings->dispatching_drop_points ?? ''));
        $alreadyExists = collect($points)
            ->contains(fn (string $item) => strcasecmp($item, $value) === 0);

        if ($alreadyExists) {
            return;
        }

        $points[] = $value;

        $settings->update([
            'dispatching_drop_points' => implode("\n", $points),
        ]);
    }

    private function parseSpecialCandidates(string $value, array $sallesDisponibles): array
    {
        if (trim($value) === '') {
            return [];
        }

        $sallesDisponibles = array_flip($sallesDisponibles);

        return collect(preg_split('/\r\n|\r|\n|;/', $value))
            ->map(fn (string $line) => trim($line))
            ->filter(fn (string $line) => $line !== '')
            ->map(function (string $line) {
                $delimiter = str_contains($line, ',') ? ',' : (str_contains($line, '-') ? '-' : null);
                if ($delimiter === null) {
                    return null;
                }
                [$salleRaw, $handicapRaw] = array_pad(explode($delimiter, $line, 2), 2, '');
                $salle = (int) trim($salleRaw);
                $handicap = trim($handicapRaw);
                if ($salle <= 0 || $handicap === '') {
                    return null;
                }
                return [
                    'numero_salle' => $salle,
                    'type_handicap' => $handicap,
                ];
            })
            ->filter(fn ($item) => $item !== null && isset($sallesDisponibles[$item['numero_salle']]))
            ->unique(fn (array $item) => $item['numero_salle'].'|'.$item['type_handicap'])
            ->values()
            ->all();
    }
}
