<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livre de Répartition</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')
    <style>
        .pdf-signature { display: none; }
        .book-page {
            position: relative;
        }
        .page-footer {
            display: none;
        }
        .pdf-preview-mode {
            background: #e2e8f0;
        }
        .pdf-preview-mode .no-print {
            display: none !important;
        }
        .pdf-preview-mode .book-page {
            background: #ffffff;
            border: 1px solid #94a3b8;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            min-height: calc(100vh - 28px);
            padding-bottom: 18mm !important;
        }
        .pdf-preview-mode .pdf-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #0f172a;
        }
        .pdf-preview-mode .pdf-table th,
        .pdf-preview-mode .pdf-table td {
            border: 1px solid #0f172a;
            color: #0f172a;
        }
        .pdf-preview-mode .pdf-table thead th,
        .pdf-preview-mode .pdf-table .bg-slate-100,
        .pdf-preview-mode .pdf-table .bg-slate-100 td,
        .pdf-preview-mode .pdf-table .bg-slate-100 th {
            background: #e2e8f0 !important;
        }
        .pdf-preview-mode .pdf-centre-card,
        .pdf-preview-mode .pdf-centre-table,
        .pdf-preview-mode .pdf-centre-summary > div,
        .pdf-preview-mode .rounded-xl.border,
        .pdf-preview-mode .rounded-lg.border {
            border-color: #475569 !important;
        }
        .pdf-preview-mode .pdf-centre-summary {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .pdf-preview-mode .pdf-centre-summary .right {
            margin-left: auto;
            min-width: 220px;
            text-align: right;
        }
        .pdf-preview-mode .pdf-centre-summary .langue-line {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 16px;
            padding: 2px 0;
            white-space: nowrap;
        }
        .pdf-preview-mode .pdf-centre-card,
        .pdf-preview-mode .pdf-centre-table,
        .pdf-preview-mode .pdf-centre-table table {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .pdf-preview-mode .pdf-section-title {
            background: #0f172a !important;
            color: #ffffff !important;
            border: 1px solid #0f172a;
        }
        @media print {
            @page {
                size: A4 landscape;
                margin: 3mm;
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print { display: none !important; }
            .book-page { page-break-after: always; }
            .book-page:last-child { page-break-after: auto; }
            .book-page {
                min-height: 196mm;
                padding-bottom: 14mm !important;
            }
            .avoid-break { break-inside: avoid; }
            .print-tight { padding: 2mm; }
            .pdf-centre-card { padding: 6px !important; margin-bottom: 6px !important; }
            .pdf-centre-summary { margin-bottom: 6px !important; }
            .pdf-centre-table { margin-bottom: 6px !important; }
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
            .pdf-centre-summary { display: flex; flex-direction: row; align-items: flex-start; justify-content: space-between; gap: 8px; }
            .pdf-centre-summary .right { min-width: 220px; text-align: right; }
            .pdf-centre-summary,
            .pdf-centre-card,
            .pdf-centre-table,
            .pdf-centre-table table {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            .pdf-centre-summary .langue-line {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                gap: 12px;
                white-space: nowrap;
            }
            .pdf-signature {
                display: none;
            }
            .page-footer {
                display: block;
                position: absolute;
                right: 6mm;
                bottom: 3mm;
                font-size: 9px;
                color: #334155;
                text-align: right;
            }
        }
    </style>
</head>
<body class="{{ $pdfMode ? 'pdf-preview-mode bg-slate-200' : 'bg-slate-100' }} text-slate-900" @if($pdfMode && ($autoPrint ?? true)) onload="window.print()" @endif>
<div class="mx-auto max-w-[1800px] p-2 md:p-4 print-tight">
    <div class="flex flex-col gap-4 md:flex-row md:items-start">
        @if(!$pdfMode)
            <div class="no-print">
                @include('partials.sidebar')
            </div>
        @endif

        <main class="min-w-0 flex-1">
            @php
                $isBepcLanguesVariant = ($bookVariant ?? 'default') === 'langues' && (($filters['type_examen'] ?? '') === 'BEPC');
                $currentCiscoOrder = request('cisco_order', '');
                $pdfBaseParams = [
                    'annee' => $filters['annee'],
                    'type_examen' => $filters['type_examen'],
                    'dren' => $filters['dren'],
                    'cisco' => $filters['cisco'] ?? '',
                    'centre_search' => $filters['centre_search'] ?? '',
                ];
                if ($currentCiscoOrder) {
                    $pdfBaseParams['cisco_order'] = $currentCiscoOrder;
                }
                $pdfVariantParams = $isBepcLanguesVariant ? array_merge($pdfBaseParams, ['variant' => 'langues']) : $pdfBaseParams;
            @endphp

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ $isBepcLanguesVariant ? 'Livre de Répartition BEPC - Langues' : 'Livre de Répartition par Salle' }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $isBepcLanguesVariant ? 'Impression BEPC dédiée aux langues vivantes, sans Option B ni ligne Total.' : 'Impression par DREN, 1ère page récap DREN puis pages centres' }}
                    </p>
                </div>
                <div class="no-print flex flex-wrap gap-2">
                    <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('bepc.repartition.create') }}">Saisie</a>
                    <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('repartition.dashboard', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco'] ?? '', 'centre_search' => $filters['centre_search'] ?? '']) }}">Dashboard</a>
                    <a class="rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100" href="{{ route('repartition.livre.controle', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco'] ?? '', 'centre_search' => $filters['centre_search'] ?? '']) }}">Fiche contrôle</a>
                    <a class="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100" href="{{ route('repartition.livre.controle.auto', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco'] ?? '', 'centre_search' => $filters['centre_search'] ?? '']) }}">Traçabilité auto</a>
                    @if(auth()->user()?->isAdmin())
                        <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('repartition.vacations', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco'] ?? '', 'centre_search' => $filters['centre_search'] ?? '']) }}">Vacations</a>
                    @endif
                    <a class="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100" href="{{ route('repartition.livre.excel', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco'] ?? '', 'centre_search' => $filters['centre_search'] ?? '']) }}">Exporter XLSX</a>
                    <a class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700" href="{{ route('repartition.livre.pdf', array_merge($pdfBaseParams, ['type_examen' => 'BEPC'])) }}">Imprimer PDF BEPC</a>
                    <a class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-800 hover:bg-blue-100" href="{{ route('repartition.livre.pdf', array_merge($pdfBaseParams, ['type_examen' => 'BEPC', 'download' => 1])) }}">Télécharger PDF BEPC</a>
                    <a class="rounded-lg border border-yellow-300 bg-yellow-300 px-3 py-2 text-sm font-semibold text-yellow-950 hover:bg-yellow-400" href="{{ route('repartition.livre.pdf', array_merge($pdfVariantParams, ['type_examen' => 'BEPC'])) }}">Imprimer PDF BEPC Langues</a>
                    <a class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100" href="{{ route('repartition.livre.pdf', array_merge($pdfVariantParams, ['type_examen' => 'BEPC', 'download' => 1])) }}">Télécharger PDF Langues</a>
                    <a class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700" href="{{ route('repartition.livre.pdf', array_merge($pdfBaseParams, ['type_examen' => 'CEPE'])) }}">Imprimer PDF CEPE</a>
                    <a class="rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-200" href="{{ route('repartition.livre.pdf', array_merge($pdfBaseParams, ['type_examen' => 'CEPE', 'download' => 1])) }}">Télécharger PDF CEPE</a>
                </div>
            </div>
        </div>

        @if(!$pdfMode)
            <div class="no-print border-b border-slate-200 p-5 md:p-6">
                <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-6">
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
                        <select id="dren" name="dren" onchange="document.getElementById('cisco').value = ''; this.form.submit();" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Tous</option>
                            @foreach($drens as $dren)
                                <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="cisco" class="mb-1 block text-sm font-medium text-slate-700">CISCO</label>
                        <select id="cisco" name="cisco" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Tous</option>
                            @foreach(($ciscos ?? []) as $cisco)
                                <option value="{{ $cisco }}" {{ ($filters['cisco'] ?? '') === $cisco ? 'selected' : '' }}>{{ $cisco }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="centre_search" class="mb-1 block text-sm font-medium text-slate-700">Recherche centre</label>
                        <input id="centre_search" name="centre_search" type="text" value="{{ $filters['centre_search'] ?? '' }}" placeholder="Nom du centre" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Mettre à jour</button>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100">Rechercher centre</button>
                    </div>
                </form>
                <div class="mt-3 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                    PE = nombre de salles. GE BEPC: paquets de 3 PE avec ajustement 2+2 si reste 1 (ex: 7 salles = 3GE,2GE,2GE). GE CEPE: paquets de 6 PE.
                </div>
            </div>
        @endif

        <div class="p-5 md:p-6">
            @php
                $contentPages = collect($centrePagesByDren ?? [])->sum(fn($item) => count($item['pages'] ?? []));
                $recapPages = count($recapSheets ?? []);
                $specialPageCount = collect($specialCandidates ?? [])->isNotEmpty() ? 1 : 0;
                $allPages = $contentPages + $recapPages + $specialPageCount;
                $pageNumber = 0;
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
            @endif

            @if($filters['dren'] !== '')
                @php
                    // Récupérer la liste des CISCO pour cette DREN
                    $drenCiscos = collect($centrePagesByDren ?? [])->firstWhere('dren', $filters['dren']);
                    $ciscoList = collect($drenCiscos['pages'] ?? [])->map(function($page) {
                        return $page[0]['cisco'] ?? '';
                    })->unique()->filter()->values();
                    $currentOrder = array_filter(explode(',', request('cisco_order', '')));
                    $orderedCiscos = !empty($currentOrder) ? $currentOrder : $ciscoList->toArray();
                @endphp

                <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <h3 class="mb-3 text-sm font-semibold text-amber-800">Ordre d'impression des CISCO pour {{ $filters['dren'] }}</h3>
                    <p class="mb-3 text-xs text-amber-700">Réorganisez l'ordre des CISCO pour contrôler quelle CISCO apparaît en première page du PDF.</p>

                    <form method="GET" id="cisco-order-form" class="space-y-3">
                        @foreach($pdfBaseParams as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endphp
                        @if($isBepcLanguesVariant)
                            <input type="hidden" name="variant" value="langues">
                        @endif

                        <div class="flex flex-wrap gap-2" id="cisco-list">
                            @foreach($orderedCiscos as $index => $cisco)
                                <div class="flex items-center gap-1 rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm">
                                    <span class="cursor-move text-amber-500">⋮⋮</span>
                                    <span>{{ $cisco }}</span>
                                    <input type="hidden" name="cisco_order[]" value="{{ $cisco }}">
                                    <button type="button" class="ml-1 text-red-500 hover:text-red-700" onclick="removeCisco(this)">×</button>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex gap-2">
                            <select id="add-cisco-select" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Ajouter une CISCO...</option>
                                @foreach($ciscoList->diff($orderedCiscos) as $cisco)
                                    <option value="{{ $cisco }}">{{ $cisco }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="addCisco()" class="rounded-lg bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-700">Ajouter</button>
                            <button type="submit" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">Appliquer l'ordre</button>
                        </div>
                    </form>
                </div>
            @endif

            @if(!$pdfMode)
                <div class="mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800">
                    Candidats: <strong>{{ number_format($globalStats['total_candidats'], 0, ',', ' ') }}</strong> |
                    Salles: <strong>{{ number_format($globalStats['total_salles'], 0, ',', ' ') }}</strong> |
                    PE: <strong>{{ number_format($globalStats['total_pe'], 0, ',', ' ') }}</strong> |
                    GE: <strong>{{ number_format($globalStats['total_ge'], 0, ',', ' ') }}</strong>
                </div>
            @endif
                @php
                    $pageNumber++;
                @endphp
                <section class="book-page mb-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-2 md:p-3">
                    @if(!$pdfMode)
                        <div class="pdf-section-title mb-3 rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Récap DREN {{ $recap['dren'] }}</div>
                    @endif
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h2 class="text-lg font-extrabold">{{ $pdfMode ? 'DREN '.$recap['dren'] : 'Feuille récapitulative DREN: '.$recap['dren'] }}</h2>
                            <div class="text-xs font-bold text-slate-800">
                                Candidats: {{ number_format($recap['total_candidats'], 0, ',', ' ') }} |
                                Salle: {{ number_format($recap['total_salles'], 0, ',', ' ') }} |
                                GE: {{ number_format($recap['total_ge'], 0, ',', ' ') }}
                            </div>
                        </div>
                        @if(($filters['type_examen'] ?? '') === 'BEPC')
                            <div class="mb-3 rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm font-semibold">
                                <span>C correction: <strong>{{ number_format($recap['total_correction'], 0, ',', ' ') }}</strong></span>
                                <span class="ml-4">Centre d'ecrit: <strong>{{ number_format($recap['total_ecrit'], 0, ',', ' ') }}</strong></span>
                                <span class="ml-4">Total GE/Matiere: <strong>{{ number_format($recap['total_ge'], 0, ',', ' ') }}</strong></span>
                            </div>
                        @else
                            <div class="mb-3 grid grid-cols-2 gap-2 md:grid-cols-4">
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm">Centres: <strong>{{ number_format($recap['total_centres'], 0, ',', ' ') }}</strong></div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm">Candidats: <strong>{{ number_format($recap['total_candidats'], 0, ',', ' ') }}</strong></div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm">Salles: <strong>{{ number_format($recap['total_salles'], 0, ',', ' ') }}</strong></div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm">GE total: <strong>{{ number_format($recap['total_ge'], 0, ',', ' ') }}</strong></div>
                            </div>
                        @endif
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
                    <div class="page-footer">
                        <div>Page {{ $pageNumber }} / {{ $allPages }}</div>
                        <div>produced by RAMAROSON Andry Michael V1.0</div>
                    </div>
                </section>
                
            @empty
                <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Aucune donnée de récapitulatif.</div>
            @endforelse

            @forelse(($centrePagesByDren ?? []) as $drenPages)
                @foreach(($drenPages['pages'] ?? []) as $pageIndex => $centres)
                    @php
                        $pageNumber++;
                        $pageCisco = (string) ($centres[0]['cisco'] ?? '');
                        $previousPageCisco = $pageIndex > 0 ? (string) ($drenPages['pages'][$pageIndex - 1][0]['cisco'] ?? '') : '';
                        $startsCisco = $pageIndex === 0 || $pageCisco !== $previousPageCisco;
                    @endphp
                    <section class="book-page mb-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-2 md:p-3">
                        @if(!$pdfMode)
                            <div class="pdf-section-title mb-3 rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Répartition Page {{ $pageIndex + 1 }} - DREN {{ $drenPages['dren'] }}</div>
                        @endif
                        @if($startsCisco)
                            <div class="mb-3 rounded-lg border border-slate-900 bg-slate-200 px-3 py-2 text-xs font-black uppercase tracking-widest text-slate-900">
                                CISCO {{ $pageCisco !== '' ? $pageCisco : '-' }}
                            </div>
                        @endif

                        @foreach($centres as $centre)
                            @php
                                $examType = $filters['type_examen'] ?? '';
                                $isBepcType = $examType === 'BEPC';
                                $isCepeType = $examType === 'CEPE';
                            @endphp
                            <article class="avoid-break mb-4 rounded-xl border border-slate-200 bg-white p-3 md:p-4 {{ $pdfMode ? 'pdf-compact pdf-centre-card shadow-sm' : '' }}">
                                @unless($isBepcType)
                                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                        <h3 class="text-lg font-bold">{{ $centre['centre_ecrit'] }} <span class="text-sm font-medium text-slate-500">({{ $centre['type_examen'] }})</span></h3>
                                        <div class="text-xs text-slate-500">{{ $centre['dren'] }} | {{ $centre['annee'] }}</div>
                                    </div>

                                    @if($isCepeType)
                                        @php
                                            $geProblemeRepartition = collect($centre['ge_distribution_probleme'] ?? [])
                                                ->map(fn ($n) => (string) $n)
                                                ->implode('+');
                                            $geAutresRepartition = collect($centre['ge_distribution_autres'] ?? [])
                                                ->map(fn ($n) => (string) $n)
                                                ->implode('+');
                                        @endphp
                                        <div class="mb-3 rounded-lg border border-slate-200 bg-slate-50 p-2 text-xs font-semibold text-slate-800">
                                            <div>GE problème (3PE): <strong>{{ count($centre['ge_distribution_probleme'] ?? []) }}</strong> |
                                                Répartition: <strong>{{ $geProblemeRepartition !== '' ? $geProblemeRepartition : '-' }}</strong>
                                            </div>
                                            <div>GE autres matières (6PE): <strong>{{ count($centre['ge_distribution_autres'] ?? []) }}</strong> |
                                                Répartition: <strong>{{ $geAutresRepartition !== '' ? $geAutresRepartition : '-' }}</strong>
                                            </div>
                                        </div>
                                    @endif
                                @endunless

                                @foreach($centre['tables'] as $table)
                                    <div class="mb-3 overflow-x-auto avoid-break pdf-centre-table">
                                        @if($isBepcType && $loop->first)
                                            <div class="mb-2 rounded-lg border border-slate-200 bg-slate-50 p-2 text-[11px] leading-tight text-slate-800 pdf-centre-summary">
                                                @php
                                                    $langueLabels = [
                                                        'Anglais' => 'ANG',
                                                        'Allemand' => 'ALL',
                                                        'Esp' => 'ESP',
                                                        'Option B' => 'B',
                                                    ];
                                                @endphp
                                                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                                                    <div class="min-w-0 flex-1 whitespace-nowrap">
                                                        <strong>Langues:</strong>
                                                        @forelse(($centre['langue_totals'] ?? []) as $langue => $total)
                                                            <span>
                                                                <strong>{{ $langueLabels[$langue] ?? $langue }}:</strong>
                                                                {{ number_format($total, 0, ',', ' ') }}
                                                                <span class="text-slate-500">| Salle:</span>
                                                                {{ number_format((int) ($centre['langue_salles'][$langue] ?? 0), 0, ',', ' ') }}
                                                            </span>
                                                            @unless($loop->last)<span class="text-slate-400">;</span>@endunless
                                                        @empty
                                                            <span class="text-slate-500">Aucune langue.</span>
                                                        @endforelse
                                                    </div>
                                                    <div class="shrink-0 whitespace-nowrap font-semibold text-slate-900">
                                                        Candidats: <strong>{{ number_format($centre['total_candidats'], 0, ',', ' ') }}</strong>
                                                        <span class="text-slate-400">|</span>
                                                        Salle: <strong>{{ number_format($centre['total_salles'], 0, ',', ' ') }}</strong>
                                                        <span class="text-slate-400">|</span>
                                                        GE/matiere: <strong>{{ number_format($centre['ge_count'], 0, ',', ' ') }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="mb-2 text-xs font-medium text-slate-500">Tableau {{ $table['index'] }} / {{ count($centre['tables']) }}</div>
                                        <table class="pdf-table min-w-full border border-slate-900 border-collapse text-xs">
                                            @if(($filters['type_examen'] ?? '') === 'BEPC')
                                                @php $rowspan = count($table['rows']) + 2; @endphp
                                                <tbody>
                                                <tr class="bg-slate-100">
                                                    <td class="border border-slate-200 bg-slate-100 px-2 py-2 font-semibold" rowspan="{{ $rowspan }}">{{ $centre['centre_ecrit'] }}</td>
                                                    <td class="border border-slate-200 bg-slate-100 px-2 py-2 font-semibold">Salles</td>
                                                    @foreach($table['salles'] as $salle)
                                                        <td class="border border-slate-200 px-2 py-2">S{{ $salle }}</td>
                                                    @endforeach
                                                </tr>
                                                @foreach($table['rows'] as $row)
                                                    <tr>
                                                        <td class="border border-slate-200 bg-white px-2 py-2 font-semibold">{{ $row['label'] }}</td>
                                                        @foreach($table['salles'] as $salle)
                                                            <td class="border border-slate-200 px-2 py-2 text-center">{{ number_format((int) ($row['values'][$salle] ?? 0), 0, ',', ' ') }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                                @unless($isBepcLanguesVariant)
                                                    <tr class="bg-slate-100">
                                                        <td class="border border-slate-200 bg-slate-100 px-2 py-2 font-bold">Total</td>
                                                        @foreach($table['salles'] as $salle)
                                                            <td class="border border-slate-200 px-2 py-2 text-center font-bold">{{ number_format((int) ($table['totaux_salles'][$salle] ?? 0), 0, ',', ' ') }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endunless
                                                </tbody>
                                            @else
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
                                            @endif
                                        </table>
                                    </div>
                                @endforeach
                            </article>
                        @endforeach
                        <div class="page-footer">
                            <div>Page {{ $pageNumber }} / {{ $allPages }}</div>
                            <div>produced by RAMAROSON Andry Michael V1.0</div>
                        </div>
                    </section>
                @endforeach
            @empty
                <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Aucune donnée disponible pour les filtres actuels.</div>
            @endforelse

            @if(collect($specialCandidates ?? [])->isNotEmpty())
                @php $pageNumber++; @endphp
                <section class="book-page mb-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-2 md:p-3">
                    @if(!$pdfMode)
                        <div class="pdf-section-title mb-3 rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Candidats à besoins spécifiques</div>
                    @endif
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <h2 class="text-lg font-extrabold">Liste des candidats à besoins spécifiques</h2>
                            <div class="text-xs font-bold text-slate-800">{{ number_format(collect($specialCandidates)->count(), 0, ',', ' ') }} entrées</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="pdf-table min-w-full border border-slate-900 border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-100 text-left">
                                        <th class="border border-slate-200 px-2 py-2">Année</th>
                                        <th class="border border-slate-200 px-2 py-2">Type</th>
                                        <th class="border border-slate-200 px-2 py-2">DREN</th>
                                        <th class="border border-slate-200 px-2 py-2">CISCO</th>
                                        <th class="border border-slate-200 px-2 py-2">Centre écrit</th>
                                        <th class="border border-slate-200 px-2 py-2 text-right">Salle</th>
                                        <th class="border border-slate-200 px-2 py-2">Handicap</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(collect($specialCandidates) as $candidate)
                                        <tr>
                                            <td class="border border-slate-200 px-2 py-2">{{ $candidate->annee }}</td>
                                            <td class="border border-slate-200 px-2 py-2">{{ $candidate->type_examen }}</td>
                                            <td class="border border-slate-200 px-2 py-2">{{ $candidate->dren }}</td>
                                            <td class="border border-slate-200 px-2 py-2">{{ $candidate->cisco }}</td>
                                            <td class="border border-slate-200 px-2 py-2">{{ $candidate->centre_ecrit }}</td>
                                            <td class="border border-slate-200 px-2 py-2 text-right">{{ number_format($candidate->numero_salle, 0, ',', ' ') }}</td>
                                            <td class="border border-slate-200 px-2 py-2">{{ $candidate->type_handicap }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="page-footer">
                        <div>Page {{ $pageNumber }} / {{ $allPages }}</div>
                        <div>produced by RAMAROSON Andry Michael V1.0</div>
                    </div>
                </section>
            @endif

            </div>
        </main>
    </div>
</div>
<div class="pdf-signature">produced by RAMAROSON Andry Michael V1.0</div>

@if(!$pdfMode)
<script>
function addCisco() {
    const select = document.getElementById('add-cisco-select');
    const ciscoName = select.value;
    if (!ciscoName) return;

    const ciscoList = document.getElementById('cisco-list');
    const ciscoDiv = document.createElement('div');
    ciscoDiv.className = 'flex items-center gap-1 rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm';
    ciscoDiv.innerHTML = `
        <span class="cursor-move text-amber-500">⋮⋮</span>
        <span>${ciscoName}</span>
        <input type="hidden" name="cisco_order[]" value="${ciscoName}">
        <button type="button" class="ml-1 text-red-500 hover:text-red-700" onclick="removeCisco(this)">×</button>
    `;
    ciscoList.appendChild(ciscoDiv);
    select.removeChild(select.querySelector(`option[value="${ciscoName}"]`));
    select.value = '';
    makeSortable();
}

function removeCisco(button) {
    const ciscoDiv = button.parentElement;
    const ciscoName = ciscoDiv.querySelector('input').value;
    const select = document.getElementById('add-cisco-select');
    const option = document.createElement('option');
    option.value = ciscoName;
    option.textContent = ciscoName;
    select.appendChild(option);
    ciscoDiv.remove();
}

function makeSortable() {
    const ciscoList = document.getElementById('cisco-list');
    let draggedElement = null;

    ciscoList.querySelectorAll('.cursor-move').forEach(handle => {
        handle.addEventListener('mousedown', (e) => {
            draggedElement = handle.parentElement;
            draggedElement.classList.add('opacity-50');
            e.preventDefault();
        });
    });

    document.addEventListener('mousemove', (e) => {
        if (!draggedElement) return;

        const afterElement = getDragAfterElement(ciscoList, e.clientY);
        if (afterElement == null) {
            ciscoList.appendChild(draggedElement);
        } else {
            ciscoList.insertBefore(draggedElement, afterElement);
        }
    });

    document.addEventListener('mouseup', () => {
        if (draggedElement) {
            draggedElement.classList.remove('opacity-50');
            draggedElement = null;
        }
    });
}

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.cursor-move')].map(handle => handle.parentElement);

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

document.addEventListener('DOMContentLoaded', makeSortable);
</script>
@endif
</body>
</html>
