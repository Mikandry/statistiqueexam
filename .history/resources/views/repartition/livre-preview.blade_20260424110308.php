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
        .book-page { position: relative; }
        .page-footer { display: none; }
        .pdf-preview-mode { background: #e2e8f0; }
        .pdf-preview-mode .no-print { display: none !important; }
        .pdf-preview-mode .book-page {
            background: #ffffff;
            border: 1px solid #94a3b8;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            min-height: calc(100vh - 28px);
            padding-bottom: 18mm !important;
            margin-bottom: 20px;
        }
        .pdf-preview-mode .pdf-table { width: 100%; border-collapse: collapse; border: 1.5px solid #0f172a; }
        .pdf-preview-mode .pdf-table th, .pdf-preview-mode .pdf-table td { border: 1px solid #0f172a; color: #0f172a; }
        
        .pdf-centre-summary { display: flex; flex-direction: row; justify-content: space-between; gap: 12px; }
        .pdf-centre-summary .right { text-align: right; min-width: 200px; }
        .langue-line { display: flex; justify-content: space-between; gap: 16px; padding: 1px 0; }

        @media print {
            @page { size: A4 landscape; margin: 3mm; }
            .no-print { display: none !important; }
            .book-page { page-break-after: always; position: relative; min-height: 190mm; }
            .page-footer {
                display: block;
                position: absolute;
                right: 5mm;
                bottom: 3mm;
                font-size: 9px;
                color: #334155;
            }
            .pdf-table { page-break-inside: auto; }
            .pdf-table tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body class="{{ $pdfMode ? 'pdf-preview-mode bg-slate-200' : 'bg-slate-100' }} text-slate-900" @if($pdfMode && ($autoPrint ?? true)) onload="window.print()" @endif>

<div class="mx-auto max-w-[1800px] p-2 md:p-4">
    @if(!$pdfMode)
        <div class="no-print mb-4">
            @include('partials.sidebar')
        </div>
    @endif

    <main class="min-w-0 flex-1">
        @php
            $isBepcLanguesVariant = ($bookVariant ?? 'default') === 'langues' && (($filters['type_examen'] ?? '') === 'BEPC');
            $pageNumber = 0;
            $allPages = collect($centrePagesByDren ?? [])->sum(fn($item) => count($item['pages'] ?? [])) + count($recapSheets ?? []);
        @endphp

        {{-- Section Titre et Filtres (Hors PDF) --}}
        @if(!$pdfMode)
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-6">
            <div class="border-b border-slate-200 p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-bold">{{ $isBepcLanguesVariant ? 'Livre BEPC - Langues' : 'Livre de Répartition' }}</h1>
                    </div>
                    <div class="flex gap-2">
                        <a class="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white" href="{{ route('repartition.livre.pdf', array_merge($filters, ['type_examen' => 'BEPC'])) }}">PDF BEPC</a>
                        <a class="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white" href="{{ route('repartition.livre.pdf', array_merge($filters, ['type_examen' => 'CEPE'])) }}">PDF CEPE</a>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Champs de filtres identiques à votre snippet --}}
                    <input type="hidden" name="type_examen" value="{{ $filters['type_examen'] }}">
                    <div>
                        <label class="block text-sm font-medium">DREN</label>
                        <select name="dren" class="w-full rounded-lg border-slate-300">
                            @foreach($drens as $d)
                                <option value="{{ $d }}" {{ $filters['dren'] == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-lg w-full">Filtrer</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- 1. Feuilles Récapitulatives DREN --}}
        @foreach(($recapSheets ?? []) as $recap)
            @php($pageNumber++)
            <section class="book-page p-4 bg-white mb-6 border">
                <h2 class="text-xl font-bold mb-4">Récapitulatif DREN : {{ $recap['dren'] }}</h2>
                <table class="pdf-table w-full text-xs">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="p-2 border">CISCO</th>
                            <th class="p-2 border">Centre Écrit</th>
                            <th class="p-2 border text-right">Candidats</th>
                            <th class="p-2 border text-right">Salles</th>
                            <th class="p-2 border">Répartition GE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recap['rows'] as $row)
                            <tr>
                                <td class="p-2 border">{{ $row['cisco'] }}</td>
                                <td class="p-2 border">{{ $row['centre_ecrit'] }}</td>
                                <td class="p-2 border text-right">{{ number_format($row['candidats'], 0, ',', ' ') }}</td>
                                <td class="p-2 border text-right">{{ number_format($row['salles'], 0, ',', ' ') }}</td>
                                <td class="p-2 border">{{ $row['ge_repartition'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="page-footer">
                    Page {{ $pageNumber }} / {{ $allPages }} | RAMAROSON Andry Michael
                </div>
            </section>
        @endforeach

        {{-- 2. Pages de Répartition par Centre --}}
        @foreach(($centrePagesByDren ?? []) as $drenPages)
            @foreach(($drenPages['pages'] ?? []) as $pageIndex => $centres)
                @php($pageNumber++)
                <section class="book-page p-4 bg-white mb-6 border">
                    @foreach($centres as $centre)
                        @php $isBepc = ($filters['type_examen'] ?? '') === 'BEPC'; @endphp
                        
                        <article class="mb-6 avoid-break border p-4 rounded-lg">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-bold">{{ $centre['centre_ecrit'] }} ({{ $filters['type_examen'] }})</h3>
                                <span class="text-xs">{{ $centre['dren'] }} - {{ $filters['annee'] }}</span>
                            </div>

                            {{-- Stats spécifiques BEPC --}}
                            @if($isBepc)
                                <div class="pdf-centre-summary bg-slate-50 p-2 text-xs border mb-3">
                                    <div>
                                        @foreach(($centre['langue_totals'] ?? []) as $langue => $total)
                                            <div class="langue-line">
                                                <span>{{ $langue }} : <strong>{{ $total }}</strong> cand.</span>
                                                <span class="ml-4">Salles : <strong>{{ $centre['langue_salles'][$langue] ?? 0 }}</strong></span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="right font-bold">
                                        Total : {{ $centre['total_candidats'] }} cand. | {{ $centre['total_salles'] }} Salles
                                    </div>
                                </div>
                            @endif

                            {{-- Tableaux de répartition --}}
                            @foreach($centre['tables'] as $table)
                                <div class="pdf-centre-table mb-4">
                                    <table class="pdf-table w-full text-[10px] text-center">
                                        <thead>
                                            <tr class="bg-slate-100">
                                                <th class="border p-1" rowspan="2">Centre</th>
                                                <th class="border p-1" rowspan="2">Détails</th>
                                                @foreach($table['salles'] as $salle)
                                                    <th class="border p-1">S{{ $salle }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $firstRow = true; @endphp
                                            @foreach($table['rows'] as $row)
                                                <tr>
                                                    @if($firstRow)
                                                        <td class="border p-1 font-bold" rowspan="{{ count($table['rows']) }}">
                                                            <div class="vertical-text">{{ $centre['centre_ecrit'] }}</div>
                                                        </td>
                                                        @php $firstRow = false; @endphp
                                                    @endif
                                                    <td class="border p-1 text-left font-medium bg-slate-50">{{ $row['label'] }}</td>
                                                    @foreach($table['salles'] as $salle)
                                                        <td class="border p-1">{{ $row['values'][$salle] ?? 0 }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </article>
                    @endforeach

                    <div class="page-footer">
                        Page {{ $pageNumber }} / {{ $allPages }} | RAMAROSON Andry Michael
                    </div>
                </section>
            @endforeach
        @endforeach

    </main>
</div>

</body>
</html>