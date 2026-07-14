<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Statistique N / N-1</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')

    <style>
        body { font-family: var(--app-font-sans); }
        .bar-track { background: #eef2ff; }
        .bar-fill { background: linear-gradient(90deg, #6366f1, #8b5cf6); }
        .bar-fill.negative { background: linear-gradient(90deg, #f97316, #ef4444); }
        .no-print { display: {{ ($pdfMode ?? false) ? 'none' : 'block' }}; }
        .pdf-only { display: {{ ($pdfMode ?? false) ? 'block' : 'none' }}; }
        @media print {
            .no-print { display: none !important; }
            .pdf-only { display: block !important; }
        }
    </style>
</head>
<body class="h-full text-slate-900">
    <div class="flex min-h-screen">
        <div class="no-print">
            @include('partials.sidebar')
        </div>

        <main class="flex-1 min-w-0 overflow-auto p-4 lg:p-8">
            <div class="max-w-[1600px] mx-auto space-y-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <nav class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">
                            <span>Examens</span>
                            <span class="text-slate-300">/</span>
                            <span class="text-indigo-600">Rapport Statistique</span>
                        </nav>
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Rapport N / N-1</h1>
                        <p class="mt-1 text-slate-500 font-medium">Comparaison des centres, candidats, salles, PE et GE.</p>
                    </div>

                    <div class="no-print flex flex-wrap items-center gap-2">
                        <a class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-slate-200 transition-all hover:bg-slate-800" href="{{ route('repartition.dashboard', ['annee' => $filters['annee_n'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco']]) }}">
                            Retour Dashboard
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 transition-all hover:bg-emerald-100" href="{{ route('repartition.stats.report.centres.excel', ['annee_n' => $filters['annee_n'], 'annee_n1' => $filters['annee_n1'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco']]) }}">
                            Export Excel Centres
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700 transition-all hover:bg-rose-100" href="{{ route('repartition.stats.report.word', ['annee_n' => $filters['annee_n'], 'annee_n1' => $filters['annee_n1'], 'annee_n1_dren' => $filters['annee_n1_dren'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco']]) }}">
                            Export Word
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl bg-blue-50 px-4 py-3 text-sm font-bold text-blue-700 transition-all hover:bg-blue-100" href="{{ route('repartition.stats.report.simple.word', ['annee' => $filters['annee_n'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco']]) }}">
                            Export Word Simple
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl bg-purple-50 px-4 py-3 text-sm font-bold text-purple-700 transition-all hover:bg-purple-100" href="{{ route('repartition.stats.report.simple.pdf', ['annee' => $filters['annee_n'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco']]) }}">
                            Export PDF Simple
                        </a>
                    </div>
                </div>

                @if(session('status'))
                    <div class="no-print rounded-lg border border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-white px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="no-print rounded-lg border border-rose-200/80 bg-gradient-to-r from-rose-50 to-white px-4 py-3 text-sm font-medium text-rose-700 shadow-sm">{{ $errors->first() }}</div>
                @endif

                @if(session('import_rejects'))
                    <div class="no-print rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        <div class="font-bold mb-2">Rejets d'import (extraits)</div>
                        <ul class="list-disc pl-5">
                            @foreach(session('import_rejects') as $reject)
                                <li>{{ $reject }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="no-print rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                    <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-7 items-end">
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Année N</label>
                            <select name="annee_n" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                @foreach($anneesCurrent as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee_n'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Année N-1</label>
                            <select name="annee_n1" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                @foreach($anneesImport as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee_n1'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Année N-1 (DREN)</label>
                            <select name="annee_n1_dren" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                @foreach($anneesImportDren as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee_n1_dren'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Type Examen</label>
                            <select name="type_examen" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                                <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">DREN</label>
                            <select name="dren" id="drenFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                <option value="">Toutes</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">CISCO</label>
                            <select name="cisco" id="ciscoFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                <option value="">Tous</option>
                                @foreach($ciscos ?? [] as $cisco)
                                    <option value="{{ $cisco }}" {{ $filters['cisco'] === $cisco ? 'selected' : '' }}>{{ $cisco }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700">Appliquer</button>
                    </form>

                    @if(auth()->user()?->isAdmin())
                        <div class="border-t border-slate-100 pt-4">
                            <form method="POST" action="{{ route('repartition.stats.report.import') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 md:grid-cols-5 items-end">
                                @csrf
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Import statistiques N-1 (CSV)</label>
                                    <input type="file" name="stats_file" accept=".csv,.txt" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <p class="mt-1 text-xs text-slate-500">Colonnes attendues: annee, type_examen, dren, cisco, centre_correction, centre_ecrit, total_salles, total_candidats.</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Année (optionnel)</label>
                                    <input type="text" name="annee_import" placeholder="2024-2025" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Type examen (optionnel)</label>
                                    <select name="type_examen_import" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                        <option value="">Auto (colonne)</option>
                                        <option value="BEPC">BEPC</option>
                                        <option value="CEPE">CEPE</option>
                                    </select>
                                </div>
                                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Importer</button>
                            </form>
                            <form method="POST" action="{{ route('repartition.stats.report.import-dren') }}" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-5 items-end">
                                @csrf
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Import récap DREN N-1 (CSV)</label>
                                    <input type="file" name="dren_recap_file" accept=".csv,.txt" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <p class="mt-1 text-xs text-slate-500">Colonnes: annee, dren, total_candidats, total_salles, ALL/allemand, ANG/anglais, ESP/espagnol, option_b.</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Année (optionnel)</label>
                                    <input type="text" name="annee_import_dren" placeholder="2024-2025" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                </div>
                                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Importer</button>
                            </form>
                        </div>
                    @endif
                </div>

                @php
                    $currentCandidats = (int) ($globalStats['current_candidats'] ?? 0);
                    $previousCandidats = (int) ($globalStats['previous_candidats'] ?? 0);
                    $deltaCandidats = $currentCandidats - $previousCandidats;
                    $trendLabel = $deltaCandidats > 0 ? 'progression' : ($deltaCandidats < 0 ? 'recul' : 'stabilité');
                    $currentSalles = (int) ($globalStats['current_salles'] ?? 0);
                    $previousSalles = (int) ($globalStats['previous_salles'] ?? 0);
                    $deltaSalles = $currentSalles - $previousSalles;
                @endphp

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">Candidats Année N</p>
                        <div class="mt-2 text-3xl font-black text-slate-900">{{ number_format($currentCandidats, 0, ',', ' ') }}</div>
                        <div class="mt-2 text-xs text-slate-500">Année: {{ $filters['annee_n'] ?: '-' }}</div>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">Candidats Année N-1</p>
                        <div class="mt-2 text-3xl font-black text-slate-900">{{ number_format($previousCandidats, 0, ',', ' ') }}</div>
                        <div class="mt-2 text-xs text-slate-500">Année: {{ $filters['annee_n1'] ?: '-' }}</div>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">Salles Année N</p>
                        <div class="mt-2 text-3xl font-black text-slate-900">{{ number_format($currentSalles, 0, ',', ' ') }}</div>
                        <div class="mt-2 text-xs text-slate-500">Écart: {{ $deltaSalles >= 0 ? '+' : '' }}{{ number_format($deltaSalles, 0, ',', ' ') }}</div>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">Besoins Spéciaux</p>
                        <div class="mt-2 text-3xl font-black text-slate-900">{{ number_format((int) ($globalStats['total_handicap'] ?? 0), 0, ',', ' ') }}</div>
                        <div class="mt-2 text-xs text-slate-500">Candidats spécifiques déclarés</div>
                    </div>
                </div>

                @php
                    $recapDrenSorted = collect($recapByDren ?? [])->sortByDesc('total_candidats')->values();
                    $recapDrenMax = $recapDrenSorted->first();
                    $recapDrenMin = $recapDrenSorted->last();
                    $recapCiscoSorted = collect($recapByCisco ?? [])->sortByDesc('total_candidats')->values();
                    $recapCiscoMax = $recapCiscoSorted->first();
                    $recapCiscoMin = $recapCiscoSorted->last();
                    $peGeSorted = collect($peGeByDren ?? [])->sortByDesc('total_pe')->values();
                    $peMax = $peGeSorted->first();
                    $geMax = collect($peGeByDren ?? [])->sortByDesc('total_ge')->values()->first();
                    $diffSorted = collect($diffByDrenChart ?? [])->sortByDesc('value')->values();
                    $diffMax = $diffSorted->first();
                    $diffMin = $diffSorted->last();
                    $newCentres = collect($comparisonRows ?? [])->where('status', 'Nouveau centre')->count();
                    $removedCentres = collect($comparisonRows ?? [])->where('status', "Centre n'existe plus")->count();
                    $topLangue = collect($languesComparisonChart ?? [])->sortByDesc('value')->values()->first();
                @endphp

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-extrabold text-slate-900">Interprétation Globale</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        La comparaison N/N-1 montre une {{ $trendLabel }} globale de
                        {{ $deltaCandidats >= 0 ? '+' : '' }}{{ number_format($deltaCandidats, 0, ',', ' ') }} candidats.
                        L'année N est {{ $filters['annee_n'] ?: '-' }}, et l'année N-1 est {{ $filters['annee_n1'] ?: '-' }}.
                    </p>
                    @if($recapDrenMax && $recapDrenMin && $recapDrenMax['dren'] !== $recapDrenMin['dren'])
                        <p class="mt-2 text-sm text-slate-600">
                            La DREN {{ $recapDrenMax['dren'] }} concentre le plus de candidats
                            ({{ number_format($recapDrenMax['total_candidats'], 0, ',', ' ') }}), tandis que
                            {{ $recapDrenMin['dren'] }} est la plus faible
                            ({{ number_format($recapDrenMin['total_candidats'], 0, ',', ' ') }}).
                        </p>
                    @endif
                    @if($recapCiscoMax && $recapCiscoMin && $recapCiscoMax['cisco'] !== $recapCiscoMin['cisco'])
                        <p class="mt-2 text-sm text-slate-600">
                            Au niveau CISCO, {{ $recapCiscoMax['dren'] }} / {{ $recapCiscoMax['cisco'] }} affiche la charge la plus élevée,
                            alors que {{ $recapCiscoMin['dren'] }} / {{ $recapCiscoMin['cisco'] }} est la plus basse.
                        </p>
                    @endif
                    @if($peMax || $geMax)
                        <p class="mt-2 text-sm text-slate-600">
                            La pression logistique est la plus forte en PE à {{ $peMax['dren'] ?? '-' }}
                            ({{ number_format((int) ($peMax['total_pe'] ?? 0), 0, ',', ' ') }} PE),
                            et en GE à {{ $geMax['dren'] ?? '-' }}
                            ({{ number_format((int) ($geMax['total_ge'] ?? 0), 0, ',', ' ') }} GE).
                        </p>
                    @endif
                    @if($diffMax || $diffMin)
                        <p class="mt-2 text-sm text-slate-600">
                            L'écart le plus positif se situe à {{ $diffMax['label'] ?? '-' }}
                            ({{ number_format((int) ($diffMax['value'] ?? 0), 0, ',', ' ') }} candidats),
                            tandis que la plus forte baisse est observée à {{ $diffMin['label'] ?? '-' }}
                            ({{ number_format((int) ($diffMin['value'] ?? 0), 0, ',', ' ') }} candidats).
                        </p>
                    @endif
                    @if($newCentres > 0 || $removedCentres > 0)
                        <p class="mt-2 text-sm text-slate-600">
                            {{ $newCentres }} centre(s) sont nouveaux en année N et
                            {{ $removedCentres }} centre(s) n'existent plus par rapport à N-1.
                        </p>
                    @endif
                    @if($topLangue && ($showLangueComparison ?? false))
                        <p class="mt-2 text-sm text-slate-600">
                            Pour le BEPC, la langue la plus demandée est {{ $topLangue['label'] }}
                            avec {{ number_format((int) $topLangue['value'], 0, ',', ' ') }} candidats.
                        </p>
                    @endif
                </div>

                @if(!($pdfMode ?? false))
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                            <h2 class="text-lg font-extrabold text-slate-900">Comparaison Centres (N vs N-1)</h2>
                            <p class="text-xs text-slate-500">Cette section est exportée en Excel uniquement.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-100">
                                        <th class="px-3 py-2 text-left">DREN</th>
                                        <th class="px-3 py-2 text-left">CISCO</th>
                                        <th class="px-3 py-2 text-left">C. correction</th>
                                        <th class="px-3 py-2 text-left">C. écrit</th>
                                        <th class="px-3 py-2 text-left">Examen</th>
                                        <th class="px-3 py-2 text-right">Candidats N</th>
                                        <th class="px-3 py-2 text-right">Candidats N-1</th>
                                        <th class="px-3 py-2 text-right">Écart</th>
                                        <th class="px-3 py-2 text-right">Progression</th>
                                        <th class="px-3 py-2 text-left">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($comparisonRows as $row)
                                        <tr class="border-t">
                                            <td class="px-3 py-2">{{ $row['dren'] }}</td>
                                            <td class="px-3 py-2">{{ $row['cisco'] }}</td>
                                            <td class="px-3 py-2">{{ $row['centre_correction'] }}</td>
                                            <td class="px-3 py-2">{{ $row['centre_ecrit'] }}</td>
                                            <td class="px-3 py-2">{{ $row['type_examen'] }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['current_candidats'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['previous_candidats'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right {{ $row['ecart_candidats'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                                {{ $row['ecart_candidats'] >= 0 ? '+' : '' }}{{ number_format($row['ecart_candidats'], 0, ',', ' ') }}
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                {{ $row['progression_candidats'] >= 0 ? '+' : '' }}{{ number_format($row['progression_candidats'], 1, ',', ' ') }}%
                                            </td>
                                            <td class="px-3 py-2 font-semibold text-indigo-700">{{ $row['status'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-3 py-6 text-center text-slate-500">Aucune comparaison disponible.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 text-xs text-slate-500 border-t border-slate-100">
                            Interprétation: les centres marqués "Nouveau centre" n'existaient pas en N-1. "Centre n'existe plus" signifie absence en N.
                        </div>
                    </div>
                @endif

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-extrabold text-slate-900">Comparaison DREN (N vs N-1)</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="px-3 py-2 text-left">DREN</th>
                                    <th class="px-3 py-2 text-right">Candidats N</th>
                                    <th class="px-3 py-2 text-right">Candidats N-1</th>
                                    <th class="px-3 py-2 text-right">Écart</th>
                                    <th class="px-3 py-2 text-right">Salles N</th>
                                    <th class="px-3 py-2 text-right">Salles N-1</th>
                                    <th class="px-3 py-2 text-right">Écart</th>
                                    @if($filters['type_examen'] !== 'CEPE')
                                        <th class="px-3 py-2 text-right">ANG N</th>
                                        <th class="px-3 py-2 text-right">ANG N-1</th>
                                        <th class="px-3 py-2 text-right">ESP N</th>
                                        <th class="px-3 py-2 text-right">ESP N-1</th>
                                        <th class="px-3 py-2 text-right">ALL N</th>
                                        <th class="px-3 py-2 text-right">ALL N-1</th>
                                        <th class="px-3 py-2 text-right">Option B N</th>
                                        <th class="px-3 py-2 text-right">Option B N-1</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($drenComparison as $row)
                                    <tr class="border-t">
                                        <td class="px-3 py-2 font-semibold">{{ $row['dren'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['current_candidats'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['previous_candidats'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right {{ $row['ecart_candidats'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                            {{ $row['ecart_candidats'] >= 0 ? '+' : '' }}{{ number_format($row['ecart_candidats'], 0, ',', ' ') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['current_salles'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['previous_salles'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right {{ $row['ecart_salles'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                            {{ $row['ecart_salles'] >= 0 ? '+' : '' }}{{ number_format($row['ecart_salles'], 0, ',', ' ') }}
                                        </td>
                                        @if($filters['type_examen'] !== 'CEPE')
                                            <td class="px-3 py-2 text-right">{{ number_format($row['current_anglais'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['previous_anglais'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['current_espagnol'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['previous_espagnol'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['current_allemand'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['previous_allemand'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['current_option_b'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['previous_option_b'], 0, ',', ' ') }}</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="16" class="px-3 py-6 text-center text-slate-500">Aucune comparaison DREN disponible.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 text-xs text-slate-500 border-t border-slate-100">
                        Interprétation: la comparaison DREN met en évidence la croissance ou la baisse globale par région, ainsi que les langues BEPC dominantes.
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-extrabold text-slate-900">Comparaison CISCO (N vs N-1)</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="px-3 py-2 text-left">DREN</th>
                                    <th class="px-3 py-2 text-left">CISCO</th>
                                    <th class="px-3 py-2 text-right">Candidats N</th>
                                    <th class="px-3 py-2 text-right">Candidats N-1</th>
                                    <th class="px-3 py-2 text-right">Écart</th>
                                    <th class="px-3 py-2 text-right">Salles N</th>
                                    <th class="px-3 py-2 text-right">Salles N-1</th>
                                    <th class="px-3 py-2 text-right">Écart</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ciscoComparison as $row)
                                    <tr class="border-t">
                                        <td class="px-3 py-2">{{ $row['dren'] }}</td>
                                        <td class="px-3 py-2 font-semibold">{{ $row['cisco'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['current_candidats'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['previous_candidats'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right {{ $row['ecart_candidats'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                            {{ $row['ecart_candidats'] >= 0 ? '+' : '' }}{{ number_format($row['ecart_candidats'], 0, ',', ' ') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['current_salles'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['previous_salles'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right {{ $row['ecart_salles'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                            {{ $row['ecart_salles'] >= 0 ? '+' : '' }}{{ number_format($row['ecart_salles'], 0, ',', ' ') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-slate-500">Aucune comparaison CISCO disponible.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 text-xs text-slate-500 border-t border-slate-100">
                        Interprétation: les CISCO en hausse nécessitent un renforcement local, ceux en baisse doivent être suivis pour éviter les déséquilibres.
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-extrabold text-slate-900">Différence par DREN</h2>
                        @php
                            $maxDiff = max(1, (int) collect($diffByDrenChart)->max('value'));
                        @endphp
                        <div class="mt-4 space-y-3">
                            @forelse($diffByDrenChart as $item)
                                @php
                                    $value = (int) ($item['value'] ?? 0);
                                    $width = min(100, abs($value) * 100 / $maxDiff);
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                                        <span>{{ $item['label'] }}</span>
                                        <span class="{{ $value < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $value >= 0 ? '+' : '' }}{{ number_format($value, 0, ',', ' ') }}</span>
                                    </div>
                                    <div class="bar-track h-2 rounded-full mt-1">
                                        <div class="bar-fill {{ $value < 0 ? 'negative' : '' }} h-2 rounded-full" style="width: {{ $width }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Aucune donnée pour le graphique.</p>
                            @endforelse
                        </div>
                        @if($diffMax && $diffMin && ($diffMax['label'] ?? '') !== ($diffMin['label'] ?? ''))
                            <p class="mt-4 text-xs text-slate-500">
                                Interprétation: {{ $diffMax['label'] }} tire la hausse globale,
                                tandis que {{ $diffMin['label'] }} enregistre le recul le plus marqué.
                            </p>
                        @endif
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-extrabold text-slate-900">Langues & Options (BEPC)</h2>
                        @if($showLangueComparison)
                            @php
                                $maxLang = max(1, (int) collect($languesComparisonChart)->max('value'));
                                $maxOpt = max(1, (int) collect($optionsComparisonChart)->max('value'));
                            @endphp
                            <div class="space-y-4">
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Langues</div>
                                    <div class="mt-3 space-y-2">
                                        @forelse($languesComparisonChart as $item)
                                            <div>
                                                <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                                                    <span>{{ $item['label'] }}</span>
                                                    <span>{{ number_format((int) $item['value'], 0, ',', ' ') }}</span>
                                                </div>
                                                <div class="bar-track h-2 rounded-full mt-1">
                                                    <div class="bar-fill h-2 rounded-full" style="width: {{ min(100, ((int) $item['value'] * 100 / $maxLang)) }}%"></div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-sm text-slate-500">Aucune langue disponible.</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Options A / B</div>
                                    <div class="mt-3 space-y-2">
                                        @foreach($optionsComparisonChart as $item)
                                            <div>
                                                <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                                                    <span>{{ $item['label'] }}</span>
                                                    <span>{{ number_format((int) $item['value'], 0, ',', ' ') }}</span>
                                                </div>
                                                <div class="bar-track h-2 rounded-full mt-1">
                                                    <div class="bar-fill h-2 rounded-full" style="width: {{ min(100, ((int) $item['value'] * 100 / $maxOpt)) }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @if($topLangue)
                                <p class="mt-4 text-xs text-slate-500">
                                    Interprétation: la langue dominante est {{ $topLangue['label'] }},
                                    indiquant la préférence principale des candidats BEPC.
                                </p>
                            @endif
                        @else
                            <p class="text-sm text-slate-500">Disponible pour BEPC uniquement.</p>
                        @endif
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-extrabold text-slate-900">Récapitulatif par DREN</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="px-3 py-2 text-left">DREN</th>
                                    <th class="px-3 py-2 text-right">Candidats</th>
                                    <th class="px-3 py-2 text-right">Salles</th>
                                    <th class="px-3 py-2 text-right">C. correction</th>
                                    <th class="px-3 py-2 text-right">C. écrit</th>
                                    <th class="px-3 py-2 text-right">Handicap</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recapByDren as $row)
                                    <tr class="border-t">
                                        <td class="px-3 py-2 font-semibold">{{ $row['dren'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_candidats'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_salles'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_correction'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_ecrit'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_handicap'], 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-6 text-center text-slate-500">Aucune donnée DREN.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($recapDrenMax && $recapDrenMin)
                        <div class="px-6 py-4 text-xs text-slate-500 border-t border-slate-100">
                            Interprétation: {{ $recapDrenMax['dren'] }} domine le volume régional, tandis que
                            {{ $recapDrenMin['dren'] }} reste le plus faible, ce qui guide les priorités de renfort.
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-extrabold text-slate-900">Récapitulatif par CISCO</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="px-3 py-2 text-left">DREN</th>
                                    <th class="px-3 py-2 text-left">CISCO</th>
                                    <th class="px-3 py-2 text-right">Candidats</th>
                                    <th class="px-3 py-2 text-right">Salles</th>
                                    <th class="px-3 py-2 text-right">C. correction</th>
                                    <th class="px-3 py-2 text-right">C. écrit</th>
                                    <th class="px-3 py-2 text-right">Handicap</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recapByCisco as $row)
                                    <tr class="border-t">
                                        <td class="px-3 py-2">{{ $row['dren'] }}</td>
                                        <td class="px-3 py-2 font-semibold">{{ $row['cisco'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_candidats'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_salles'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_correction'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_ecrit'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_handicap'], 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-6 text-center text-slate-500">Aucune donnée CISCO.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($recapCiscoMax && $recapCiscoMin)
                        <div class="px-6 py-4 text-xs text-slate-500 border-t border-slate-100">
                            Interprétation: {{ $recapCiscoMax['dren'] }} / {{ $recapCiscoMax['cisco'] }} concentre la charge,
                            alors que {{ $recapCiscoMin['dren'] }} / {{ $recapCiscoMin['cisco'] }} reste le plus léger.
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-extrabold text-slate-900">Récapitulatif PE / GE par DREN</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="px-3 py-2 text-left">DREN</th>
                                    <th class="px-3 py-2 text-right">PE</th>
                                    <th class="px-3 py-2 text-right">GE</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($peGeByDren as $row)
                                    <tr class="border-t">
                                        <td class="px-3 py-2 font-semibold">{{ $row['dren'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_pe'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_ge'], 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-6 text-center text-slate-500">Aucune donnée PE/GE.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($peMax || $geMax)
                        <div class="px-6 py-4 text-xs text-slate-500 border-t border-slate-100">
                            Interprétation: la DREN {{ $peMax['dren'] ?? '-' }} porte le plus d'organisation en salles,
                            et {{ $geMax['dren'] ?? '-' }} le plus de GE, ce qui nécessite un encadrement renforcé.
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    @if(!($pdfMode ?? false))
        <script>
            const ciscosByDren = @json($ciscosByDren ?? []);
            function syncCiscoFilterOptions() {
                const drenSelect = document.getElementById('drenFilter');
                const ciscoSelect = document.getElementById('ciscoFilter');
                if (!drenSelect || !ciscoSelect) return;
                const selectedDren = drenSelect.value || '';
                const selectedCisco = ciscoSelect.value || '';
                const ciscos = selectedDren ? (ciscosByDren[selectedDren] || []) : Object.values(ciscosByDren).flat();
                const uniqueCiscos = [...new Set(ciscos)].sort((a, b) => a.localeCompare(b, 'fr'));

                ciscoSelect.innerHTML = '';
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Tous';
                ciscoSelect.appendChild(defaultOption);

                uniqueCiscos.forEach((cisco) => {
                    const option = document.createElement('option');
                    option.value = cisco;
                    option.textContent = cisco;
                    if (cisco === selectedCisco) option.selected = true;
                    ciscoSelect.appendChild(option);
                });

                if (selectedCisco && !uniqueCiscos.includes(selectedCisco)) {
                    ciscoSelect.value = '';
                }
            }
            document.getElementById('drenFilter')?.addEventListener('change', syncCiscoFilterOptions);
            document.addEventListener('DOMContentLoaded', syncCiscoFilterOptions);
        </script>
    @endif
</body>
</html>
