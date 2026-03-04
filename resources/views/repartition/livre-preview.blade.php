<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livre de Répartition</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
    @endif
    <style>
        .pdf-signature { display: none; }
        @media print {
            @page {
                size: A4 landscape;
                margin: 5mm;
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print { display: none !important; }
            .book-page { page-break-after: always; }
            .book-page:last-child { page-break-after: auto; }
            .avoid-break { break-inside: avoid; }
            .print-tight { padding: 2mm; }
            .pdf-table, .pdf-table th, .pdf-table td { border-color: #0f172a !important; }
            .pdf-compact { font-size: 10px !important; line-height: 1.25 !important; }
            .pdf-center-meta { table-layout: fixed; width: 100%; }
            .pdf-center-meta .col-cisco { width: 11%; }
            .pdf-center-meta .col-cc { width: 12%; }
            .pdf-center-meta .col-axe { width: 10%; }
            .pdf-center-meta .col-point { width: 10%; }
            .pdf-center-meta .col-cand { width: 8%; }
            .pdf-center-meta .col-salles { width: 7%; }
            .pdf-center-meta .col-pe { width: 6%; }
            .pdf-center-meta .col-ge { width: 6%; }
            .pdf-center-meta .col-ge-dist { width: 30%; }
            .pdf-signature {
                display: block;
                position: fixed;
                right: 8mm;
                bottom: 6mm;
                font-size: 10px;
                color: #334155;
                background: rgba(255, 255, 255, 0.9);
                padding: 2px 6px;
                border: 1px solid #cbd5e1;
                border-radius: 4px;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900" @if($pdfMode) onload="window.print()" @endif>
<div class="mx-auto max-w-[1800px] p-2 md:p-4 print-tight">
    <div class="flex flex-col gap-4 md:flex-row md:items-start">
        @if(!$pdfMode)
            <div class="no-print">
                @include('partials.sidebar')
            </div>
        @endif

        <main class="min-w-0 flex-1">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Livre de Répartition par Salle</h1>
                    <p class="mt-1 text-sm text-slate-500">Impression par DREN, 1ère page récap DREN puis pages centres</p>
                </div>
                <div class="no-print flex flex-wrap gap-2">
                    <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('bepc.repartition.create') }}">Saisie</a>
                    <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('repartition.dashboard', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Dashboard</a>
                    @if(auth()->user()?->isAdmin())
                        <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('repartition.vacations', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Vacations</a>
                    @endif
                    <a class="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100" href="{{ route('repartition.livre.excel', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Exporter XLSX</a>
                    <a class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700" href="{{ route('repartition.livre.pdf', ['annee' => $filters['annee'], 'type_examen' => 'BEPC', 'dren' => $filters['dren']]) }}">Imprimer PDF BEPC</a>
                    <a class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700" href="{{ route('repartition.livre.pdf', ['annee' => $filters['annee'], 'type_examen' => 'CEPE', 'dren' => $filters['dren']]) }}">Imprimer PDF CEPE</a>
                </div>
            </div>
        </div>

        @if(!$pdfMode)
            <div class="no-print border-b border-slate-200 p-5 md:p-6">
                <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div>
                        <label for="annee" class="mb-1 block text-sm font-medium text-slate-700">Année scolaire</label>
                        <select id="annee" name="annee" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Toutes</option>
                            @foreach($annees as $annee)
                                <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="type_examen" class="mb-1 block text-sm font-medium text-slate-700">Type d'examen</label>
                        <select id="type_examen" name="type_examen" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                            <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                            <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                        </select>
                    </div>
                    <div>
                        <label for="dren" class="mb-1 block text-sm font-medium text-slate-700">DREN (livre individuel)</label>
                        <select id="dren" name="dren" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Tous</option>
                            @foreach($drens as $dren)
                                <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Mettre à jour</button>
                    </div>
                </form>
                <div class="mt-3 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                    PE = nombre de salles. GE: paquets de 3 PE avec ajustement 2+2 si reste 1 (ex: 7 salles = 3GE,2GE,2GE).
                </div>
            </div>
        @endif

        <div class="p-5 md:p-6">
            @php
                $contentPages = collect($centrePagesByDren ?? [])->sum(fn($item) => count($item['pages'] ?? []));
                $recapPages = count($recapSheets ?? []);
                $allPages = $contentPages + $recapPages;
            @endphp
            @if(!$pdfMode)
                <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-5">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Total candidats</div>
                        <div class="mt-1 text-xl font-semibold">{{ number_format($globalStats['total_candidats'], 0, ',', ' ') }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Total salles</div>
                        <div class="mt-1 text-xl font-semibold">{{ number_format($globalStats['total_salles'], 0, ',', ' ') }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Total PE</div>
                        <div class="mt-1 text-xl font-semibold">{{ number_format($globalStats['total_pe'], 0, ',', ' ') }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Total GE</div>
                        <div class="mt-1 text-xl font-semibold">{{ number_format($globalStats['total_ge'], 0, ',', ' ') }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Pages</div>
                        <div class="mt-1 text-xl font-semibold">{{ number_format($allPages, 0, ',', ' ') }}</div>
                    </div>
                </div>
            @else
                <div class="mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                    Candidats: <strong>{{ number_format($globalStats['total_candidats'], 0, ',', ' ') }}</strong> |
                    Salles: <strong>{{ number_format($globalStats['total_salles'], 0, ',', ' ') }}</strong> |
                    PE: <strong>{{ number_format($globalStats['total_pe'], 0, ',', ' ') }}</strong> |
                    GE: <strong>{{ number_format($globalStats['total_ge'], 0, ',', ' ') }}</strong>
                </div>
            @endif

            @forelse(($recapSheets ?? []) as $recap)
                <section class="book-page mb-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-2 md:p-3">
                    @if(!$pdfMode)
                        <div class="mb-3 rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Récap DREN {{ $recap['dren'] }}</div>
                    @endif
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <h2 class="mb-3 text-lg font-bold">{{ $pdfMode ? 'Feuille récapitulative' : 'Feuille récapitulative DREN: '.$recap['dren'] }}</h2>
                        <div class="mb-3 grid grid-cols-2 gap-2 md:grid-cols-4">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm">Centres: <strong>{{ number_format($recap['total_centres'], 0, ',', ' ') }}</strong></div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm">Candidats: <strong>{{ number_format($recap['total_candidats'], 0, ',', ' ') }}</strong></div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm">Salles: <strong>{{ number_format($recap['total_salles'], 0, ',', ' ') }}</strong></div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm">GE total: <strong>{{ number_format($recap['total_ge'], 0, ',', ' ') }}</strong></div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="pdf-table min-w-full border border-slate-900 border-collapse text-xs">
                                <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="border border-slate-200 px-2 py-2">CISCO</th>
                                    <th class="border border-slate-200 px-2 py-2">Centre correction</th>
                                    <th class="border border-slate-200 px-2 py-2">Centre écrit</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">Candidats</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">Salles</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">GE total</th>
                                    <th class="border border-slate-200 px-2 py-2">Répartition GE</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($recap['rows'] as $row)
                                    <tr>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['cisco'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['centre_correction'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['centre_ecrit'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2 text-right">{{ number_format($row['candidats'], 0, ',', ' ') }}</td>
                                        <td class="border border-slate-200 px-2 py-2 text-right">{{ number_format($row['salles'], 0, ',', ' ') }}</td>
                                        <td class="border border-slate-200 px-2 py-2 text-right">{{ number_format($row['ge_total'], 0, ',', ' ') }}</td>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['ge_repartition'] !== '' ? $row['ge_repartition'] : '-' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @empty
                <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Aucune donnée de récapitulatif.</div>
            @endforelse

            @forelse(($centrePagesByDren ?? []) as $drenPages)
                @foreach(($drenPages['pages'] ?? []) as $pageIndex => $centres)
                    <section class="book-page mb-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-2 md:p-3">
                        @if(!$pdfMode)
                            <div class="mb-3 rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Répartition Page {{ $pageIndex + 1 }} - DREN {{ $drenPages['dren'] }}</div>
                        @endif

                        @foreach($centres as $centre)
                            <article class="avoid-break mb-4 rounded-xl border border-slate-200 bg-white p-3 md:p-4 {{ $pdfMode ? 'pdf-compact' : '' }}">
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                    <h3 class="text-lg font-bold">{{ $centre['centre_ecrit'] }} <span class="text-sm font-medium text-slate-500">({{ $centre['type_examen'] }})</span></h3>
                                    <div class="text-xs text-slate-500">{{ $centre['dren'] }} | {{ $centre['annee'] }}</div>
                                </div>

                                @foreach($centre['tables'] as $table)
                                    <div class="mb-2 text-xs font-medium text-slate-500">Tableau {{ $table['index'] }} / {{ count($centre['tables']) }}</div>
                                    <div class="mb-3 overflow-x-auto">
                                        <table class="pdf-table min-w-full border border-slate-900 border-collapse text-xs">
                                            <thead>
                                            <tr class="bg-slate-100">
                                                <th class="sticky left-0 border border-slate-200 bg-slate-100 px-2 py-2 text-left">Langue / Option</th>
                                                @foreach($table['salles'] as $salle)
                                                    <th class="border border-slate-200 px-2 py-2">S{{ $salle }}</th>
                                                @endforeach
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($table['rows'] as $row)
                                                <tr>
                                                    <td class="sticky left-0 border border-slate-200 bg-white px-2 py-2 font-semibold">{{ $row['label'] }}</td>
                                                    @foreach($table['salles'] as $salle)
                                                        <td class="border border-slate-200 px-2 py-2 text-center">{{ number_format((int) ($row['values'][$salle] ?? 0), 0, ',', ' ') }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                            <tr class="bg-slate-100">
                                                <td class="sticky left-0 border border-slate-200 bg-slate-100 px-2 py-2 font-bold">TOTAL SALLE</td>
                                                @foreach($table['salles'] as $salle)
                                                    <td class="border border-slate-200 px-2 py-2 text-center font-bold">{{ number_format((int) ($table['totaux_salles'][$salle] ?? 0), 0, ',', ' ') }}</td>
                                                @endforeach
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            </article>
                        @endforeach
                    </section>
                @endforeach
            @empty
                <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Aucune donnée disponible pour les filtres actuels.</div>
            @endforelse
            </div>
        </main>
    </div>
</div>
<div class="pdf-signature">
    produce by Andry Michael copyright 2026 version 1.0
</div>
</body>
</html>
