<?php

namespace App\Http\Controllers;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\RepartitionSalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BepcRepartitionController extends Controller
{
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

        $centresEcritDisponibles = CentreEcrit::query()
            ->whereDoesntHave('repartitions')
            ->orderBy('nom')
            ->get(['id', 'centre_correction_id', 'nom']);

        $axesSuggestions = DB::table('repartition_salles')
            ->whereNotNull('axe_dispatching')
            ->where('axe_dispatching', '!=', '')
            ->select('axe_dispatching')
            ->distinct()
            ->orderBy('axe_dispatching')
            ->pluck('axe_dispatching')
            ->values();

        $pointSuggestionsByAxe = DB::table('repartition_salles')
            ->whereNotNull('axe_dispatching')
            ->where('axe_dispatching', '!=', '')
            ->whereNotNull('point_largage')
            ->where('point_largage', '!=', '')
            ->select('axe_dispatching', 'point_largage')
            ->distinct()
            ->orderBy('axe_dispatching')
            ->orderBy('point_largage')
            ->get()
            ->groupBy('axe_dispatching')
            ->map(fn ($rows) => $rows->pluck('point_largage')->values())
            ->toArray();

        return view('bepc-repartition.create', [
            'langues' => RepartitionSalle::LANGUES,
            'nombreSalles' => $nombreSalles,
            'typeExamen' => $typeExamen,
            'drens' => Dren::query()->orderBy('nom')->get(['id', 'nom']),
            'ciscos' => Cisco::query()->orderBy('nom')->get(['id', 'dren_id', 'nom']),
            'centresCorrection' => CentreCorrection::query()->orderBy('nom')->get(['id', 'cisco_id', 'nom']),
            'centresEcrit' => $centresEcritDisponibles,
            'axesSuggestions' => $axesSuggestions,
            'pointSuggestionsByAxe' => $pointSuggestionsByAxe,
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
            'point_largage' => ['required', 'string', 'max:255'],
            'annee' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'nombre_salles' => ['required', 'integer', 'min:1', 'max:50'],
            'salles_inutilisables' => ['nullable', 'string', 'max:255'],
            'has_foreign_candidates' => ['nullable', 'boolean'],
            'foreign_option_a_lv' => ['nullable', 'in:ALL,Esp'],
            'foreign_option_a_replace_malagasy' => ['nullable', 'string', 'max:255'],
            'foreign_option_b_replace_malagasy' => ['nullable', 'string', 'max:255'],
        ]);

        $nombreSalles = (int) $validated['nombre_salles'];
        $typeExamen = $validated['type_examen'];
        $sallesInutilisables = $this->parseRoomNumbers((string) ($validated['salles_inutilisables'] ?? ''), $nombreSalles);
        $sallesDisponibles = array_values(array_diff(range(1, $nombreSalles), $sallesInutilisables));

        if ($sallesDisponibles === []) {
            return back()
                ->withInput()
                ->withErrors(['salles_inutilisables' => 'Toutes les salles sont marquées comme inutilisables.']);
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
                }
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
            }
        }

        $hasForeignCandidates = $typeExamen === self::TYPE_BEPC && (bool) $request->boolean('has_foreign_candidates');
        $foreignSettings = null;
        if ($hasForeignCandidates) {
            $optionALv = (string) $request->input('foreign_option_a_lv', '');
            $optionAReplace = trim((string) $request->input('foreign_option_a_replace_malagasy', ''));
            $optionBReplace = trim((string) $request->input('foreign_option_b_replace_malagasy', ''));

            if (! in_array($optionALv, ['ALL', 'Esp'], true)) {
                return back()
                    ->withInput()
                    ->withErrors(['foreign_option_a_lv' => 'Choisissez ALL ou Esp pour les étrangers Option A.']);
            }

            if ($optionAReplace === '' || $optionBReplace === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'foreign_option_a_replace_malagasy' => 'Renseignez les langues de remplacement du Malagasy pour Option A et Option B.',
                    ]);
            }

            foreach ($sallesDisponibles as $salle) {
                $valueA = $request->input("foreign_effectifs.option_a.$salle", 0);
                $valueB = $request->input("foreign_effectifs.option_b.$salle", 0);
                if (! is_numeric($valueA) || (int) $valueA < 0 || ! is_numeric($valueB) || (int) $valueB < 0) {
                    return back()
                        ->withInput()
                        ->withErrors(['foreign_effectifs' => "Valeur invalide pour les candidats étrangers - Salle {$salle}."]);
                }
            }

            $foreignSettings = [
                'option_a_lv' => $optionALv === 'ALL' ? 'Allemand' : 'Esp',
                'option_a_replace' => $optionAReplace,
                'option_b_replace' => $optionBReplace,
            ];
        }

        $dren = Dren::findOrFail((int) $validated['dren_id']);
        $cisco = Cisco::findOrFail((int) $validated['cisco_id']);
        $centreCorrection = CentreCorrection::findOrFail((int) $validated['centre_correction_id']);
        $centreEcrit = CentreEcrit::findOrFail((int) $validated['centre_ecrit_id']);

        if ($cisco->dren_id !== $dren->id ||
            $centreCorrection->cisco_id !== $cisco->id ||
            $centreEcrit->centre_correction_id !== $centreCorrection->id) {
            return back()
                ->withInput()
                ->withErrors(['centre_ecrit_id' => 'La hiérarchie DREN/CISCO/Centre sélectionnée est invalide.']);
        }

        if (RepartitionSalle::query()->where('centre_ecrit_id', $centreEcrit->id)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['centre_ecrit_id' => "Ce centre d'écrit a déjà des données enregistrées."]);
        }

        $nomSaisie = trim((string) ($request->user()?->name ?? ''));

        if ($nomSaisie === '') {
            return back()
                ->withInput()
                ->withErrors(['auth' => 'Utilisateur connecté invalide. Veuillez vous reconnecter.']);
        }

        DB::transaction(function () use ($validated, $langues, $sallesDisponibles, $typeExamen, $request, $centreEcrit, $nomSaisie, $hasForeignCandidates, $foreignSettings) {
            $axeDispatching = trim((string) $validated['axe_dispatching']);
            $pointLargage = trim((string) $validated['point_largage']);

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
        });

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
}
