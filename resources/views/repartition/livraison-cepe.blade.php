<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison CEPE par CISCO</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
    @endif
</head>
<body class="bg-slate-100 text-slate-900">
<div class="mx-auto max-w-[1800px] p-4 md:p-6 lg:p-8">
    <div class="flex flex-col gap-5 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-5 md:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">Livraison CEPE par CISCO</h1>
                            <p class="mt-1 text-sm text-slate-500">Calcul des besoins: cire, soubique, PE, GE, papier RAM, marqueur, ficelle.</p>
                        </div>
                        <a href="{{ route('repartition.livraison.cepe.excel', request()->query()) }}" class="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100">Exporter XLSX</a>
                    </div>
                </div>

                <div class="p-5 md:p-6">
                    <form method="GET" class="grid grid-cols-1 gap-3 lg:grid-cols-6">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="annee">Année</label>
                            <select id="annee" name="annee" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Toutes</option>
                                @foreach($annees as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="dren">DREN</label>
                            <select id="dren" name="dren" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Toutes</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="ge_par_soubique">GE / soubique</label>
                            <input id="ge_par_soubique" name="ge_par_soubique" type="number" min="1" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="{{ $params['ge_par_soubique'] }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="enveloppes_par_barre_cire">Enveloppes / barre cire</label>
                            <input id="enveloppes_par_barre_cire" name="enveloppes_par_barre_cire" type="number" min="1" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="{{ $params['enveloppes_par_barre_cire'] }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="pages_par_ram">Pages / RAM</label>
                            <input id="pages_par_ram" name="pages_par_ram" type="number" min="1" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="{{ $params['pages_par_ram'] }}">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Calculer</button>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="marqueur_fixe_par_cisco">Marqueur fixe / CISCO</label>
                            <input id="marqueur_fixe_par_cisco" name="marqueur_fixe_par_cisco" type="number" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="{{ $params['marqueur_fixe_par_cisco'] }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="marqueur_par_soubique">Marqueur / soubique</label>
                            <input id="marqueur_par_soubique" name="marqueur_par_soubique" type="number" step="0.1" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="{{ $params['marqueur_par_soubique'] }}">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="pages_francais">Pages Français</label>
                            <input id="pages_francais" name="pages_francais" type="number" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="{{ $pagesBySubject['francais'] }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="pages_connaissances_usuelles">Pages Connaissances usuelles (CU)</label>
                            <input id="pages_connaissances_usuelles" name="pages_connaissances_usuelles" type="number" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="{{ $pagesBySubject['connaissances_usuelles'] }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="pages_geographie">Pages Geographie</label>
                            <input id="pages_geographie" name="pages_geographie" type="number" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="{{ $pagesBySubject['geographie'] }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="pages_malagasy">Pages Malagasy</label>
                            <input id="pages_malagasy" name="pages_malagasy" type="number" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="{{ $pagesBySubject['malagasy'] }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="pages_operation">Pages Operation</label>
                            <input id="pages_operation" name="pages_operation" type="number" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="{{ $pagesBySubject['operation'] }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="pages_probleme">Pages Probleme</label>
                            <input id="pages_probleme" name="pages_probleme" type="number" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="{{ $pagesBySubject['probleme'] }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="pages_tffmom">Pages TFFMOM</label>
                            <input id="pages_tffmom" name="pages_tffmom" type="number" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="{{ $pagesBySubject['tffmom'] }}">
                        </div>
                    </form>

                    <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                        Formules: PE = salles, GE = somme GE des centres, Soubique = ceil(GE / GE par soubique), Ficelle = Soubique,
                        Cire = ceil((PE + GE + Soubique) / enveloppes par barre), Pages total = candidats x somme des 7 matières,
                        RAM = ceil(Pages total / pages par RAM), Marqueur = marqueur fixe + ceil(Soubique x marqueur/soubique).
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-5">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm">Candidats: <strong>{{ number_format($global['total_candidats'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm">Salles: <strong>{{ number_format($global['total_salles'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm">GE: <strong>{{ number_format($global['total_ge'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm">PE: <strong>{{ number_format($global['total_pe'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm">Pages/candidat (7 matières): <strong>{{ number_format($pagesTotalParCandidat, 0, ',', ' ') }}</strong></div>
                    </div>

                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full border-collapse text-sm">
                            <thead>
                            <tr class="bg-slate-100 text-left">
                                <th class="border border-slate-200 px-3 py-2">DREN</th>
                                <th class="border border-slate-200 px-3 py-2">CISCO</th>
                                <th class="border border-slate-200 px-3 py-2 text-right">Candidats</th>
                                <th class="border border-slate-200 px-3 py-2 text-right">Salles</th>
                                <th class="border border-slate-200 px-3 py-2 text-right">PE</th>
                                <th class="border border-slate-200 px-3 py-2 text-right">GE</th>
                                <th class="border border-slate-200 px-3 py-2 text-right">Soubique</th>
                                <th class="border border-slate-200 px-3 py-2 text-right">Ficelle</th>
                                <th class="border border-slate-200 px-3 py-2 text-right">Cire</th>
                                <th class="border border-slate-200 px-3 py-2 text-right">Pages total</th>
                                <th class="border border-slate-200 px-3 py-2 text-right">Papier RAM</th>
                                <th class="border border-slate-200 px-3 py-2 text-right">Marqueur</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td class="border border-slate-200 px-3 py-2">{{ $row['dren'] }}</td>
                                    <td class="border border-slate-200 px-3 py-2">{{ $row['cisco'] }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['candidats'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['salles'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['pe'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['ge'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['soubique'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['ficelle'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['cire'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['pages_total'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['papier_ram'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['marqueur'], 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-500" colspan="12">Aucune donnée CEPE pour les filtres sélectionnés.</td>
                                </tr>
                            @endforelse
                            </tbody>
                            @if(count($rows) > 0)
                                <tfoot>
                                <tr class="bg-slate-50 font-semibold">
                                    <td class="border border-slate-200 px-3 py-2" colspan="2">TOTAL</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($global['total_candidats'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($global['total_salles'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($global['total_pe'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($global['total_ge'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($global['total_soubique'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($global['total_ficelle'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($global['total_cire'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2"></td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($global['total_ram'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($global['total_marqueur'], 0, ',', ' ') }}</td>
                                </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
