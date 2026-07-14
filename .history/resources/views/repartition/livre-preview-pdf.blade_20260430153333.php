<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Livre de Répartition PDF</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 6mm 12mm 6mm;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #0f172a;
            margin: 0;
        }
        .page {
            position: relative;
            page-break-after: auto;
            min-height: 0;
            padding-bottom: 0;
        }
        .forced-break {
            page-break-after: always;
        }
        .section-title {
            background: #0f172a;
            color: #fff;
            font-weight: bold;
            padding: 6px 8px;
            margin: 0 0 8px 0;
            page-break-after: avoid;
        }
        .card {
            border: 1px solid #475569;
            padding: 8px;
            margin-bottom: 8px;
            page-break-inside: auto;
        }
        .centre-table-block,
        .summary-table {
            page-break-inside: avoid;
        }
        .meta-table,
        .pdf-table,
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 4px;
            vertical-align: top;
        }
        .summary-table td {
            width: 50%;
            vertical-align: top;
            border: 1px solid #475569;
            padding: 6px 8px;
        }
        .summary-line {
            margin-bottom: 2px;
        }
        .summary-compact {
            border: 1px solid #475569;
            background: #f8fafc;
            padding: 3px 5px;
            margin-bottom: 4px;
            font-size: 8.7px;
            line-height: 1.2;
            white-space: nowrap;
        }
        .summary-compact strong {
            font-weight: bold;
        }
        .summary-compact .summary-total {
            float: right;
            margin-left: 8px;
        }
        .pdf-table {
            margin-top: 6px;
        }
        .pdf-table th,
        .pdf-table td {
            border: 1px solid #0f172a;
            padding: 4px 5px;
        }
        .pdf-table th {
            background: #e2e8f0;
            font-weight: bold;
            text-align: left;
        }
        .right {
            text-align: right;
        }
        .center {
            text-align: center;
        }
        .muted {
            color: #475569;
        }
        .centre-name {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .small-title {
            font-size: 9px;
            font-weight: bold;
            color: #475569;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }
        .cisco-title {
            border: 1px solid #0f172a;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 11px;
            font-weight: bold;
            padding: 5px 8px;
            margin: 0 0 8px 0;
            page-break-after: avoid;
        }
        .page-footer {
            display: none;
        }
        .fixed-footer {
            position: fixed;
            right: 0;
            bottom: -8mm;
            left: 0;
            font-size: 9px;
            color: #334155;
            text-align: right;
            border-top: 1px solid #cbd5e1;
            padding-top: 2mm;
        }
        .fixed-footer .page-number:after {
            content: "Page " counter(page) " / " counter(pages);
        }
    </style>
</head>
<body>
<div class="fixed-footer">
    <span class="page-number"></span>
    <span> | produced: RAMAROSON Andry Michael V1.0</span>
</div>
@php
    $isBepcLanguesVariant = ($bookVariant ?? 'default') === 'langues' && (($filters['type_examen'] ?? '') === 'BEPC');
    $langueLabels = [
        'Anglais' => 'ANG',
        'Allemand' => 'ALL',
        'Esp' => 'ESP',
        'Option B' => 'B',
    ];
    $contentPages = collect($centrePagesByDren ?? [])->sum(fn($item) => count($item['pages'] ?? []));
    $recapPages = count($recapSheets ?? []);
    $specialPageCount = collect($specialCandidates ?? [])->isNotEmpty() ? 1 : 0;
    $allPages = $contentPages + $recapPages + $specialPageCount;
    $pageNumber = 0;
@endphp

@forelse(($recapSheets ?? []) as $recap)
    @php
        $pageNumber++;
        $hasPagesAfterRecap = $contentPages > 0 || $specialPageCount > 0 || ! $loop->last;
    @endphp
    <div class="page {{ $hasPagesAfterRecap ? 'forced-break' : '' }}">
        <div class="section-title">Récap DREN {{ $recap['dren'] }}</div>
        <div class="card">
            <table class="meta-table">
                <tr>
                    <td><strong>DREN {{ $recap['dren'] }}</strong></td>
                    <td class="right">
                        Candidats: <strong>{{ number_format($recap['total_candidats'], 0, ',', ' ') }}</strong> |
                        Salle: <strong>{{ number_format($recap['total_salles'], 0, ',', ' ') }}</strong> |
                        GE: <strong>{{ number_format($recap['total_ge'], 0, ',', ' ') }}</strong>
                    </td>
                </tr>
            </table>

            @if(($filters['type_examen'] ?? '') === 'BEPC')
                <div style="margin:6px 0;">
                    C correction: <strong>{{ number_format($recap['total_correction'], 0, ',', ' ') }}</strong>
                    |
                    Centre d'ecrit: <strong>{{ number_format($recap['total_ecrit'], 0, ',', ' ') }}</strong>
                    |
                    Total GE/Matiere: <strong>{{ number_format($recap['total_ge'], 0, ',', ' ') }}</strong>
                </div>
            @endif

            <table class="pdf-table">
                <thead>
                <tr>
                    <th>CISCO</th>
                    <th>Centre correction</th>
                    <th>Centre écrit</th>
                    <th class="right">Candidats</th>
                    <th class="right">Salles</th>
                    <th class="right">GE total</th>
                    <th>Répartition GE</th>
                </tr>
                </thead>
                <tbody>
                @foreach($recap['rows'] as $row)
                    <tr>
                        <td>{{ $row['cisco'] }}</td>
                        <td>{{ $row['centre_correction'] }}</td>
                        <td>{{ $row['centre_ecrit'] }}</td>
                        <td class="right">{{ number_format($row['candidats'], 0, ',', ' ') }}</td>
                        <td class="right">{{ number_format($row['salles'], 0, ',', ' ') }}</td>
                        <td class="right">{{ number_format($row['ge_total'], 0, ',', ' ') }}</td>
                        <td>{{ $row['ge_repartition'] !== '' ? $row['ge_repartition'] : '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="page-footer">
            <div>Page {{ $pageNumber }} / {{ $allPages }}</div>
            <div>produced by RAMAROSON Andry Michael V1.0</div>
        </div>
    </div>
@empty
@endforelse

@forelse(($centrePagesByDren ?? []) as $drenPages)
    @foreach(($drenPages['pages'] ?? []) as $pageIndex => $centres)
        @php
            $pageNumber++;
            $hasSpecialPage = collect($specialCandidates ?? [])->isNotEmpty();
            $isLastContentPage = $loop->last && $loop->parent->last && ! $hasSpecialPage;
            $pageCisco = (string) ($centres[0]['cisco'] ?? '');
            $previousPageCisco = $pageIndex > 0 ? (string) ($drenPages['pages'][$pageIndex - 1][0]['cisco'] ?? '') : '';
            $startsCisco = $pageIndex === 0 || $pageCisco !== $previousPageCisco;
        @endphp
        <div class="page {{ $isLastContentPage ? '' : 'forced-break' }}">
            <div class="section-title">Répartition Page {{ $pageIndex + 1 }} - DREN {{ $drenPages['dren'] }}</div>
            @if($startsCisco)
                <div class="cisco-title">CISCO {{ $pageCisco !== '' ? $pageCisco : '-' }}</div>
            @endif

            @foreach($centres as $centre)
                <div class="card centre-card">
                    @if(($filters['type_examen'] ?? '') !== 'BEPC')
                        <div class="centre-name">{{ $centre['centre_ecrit'] }} <span class="muted">({{ $centre['type_examen'] }})</span></div>
                        <div class="muted">{{ $centre['dren'] }} | {{ $centre['annee'] }}</div>
                    @endif

                    @foreach($centre['tables'] as $table)
                        <div class="centre-table-block">
                            @if(($filters['type_examen'] ?? '') === 'BEPC' && $loop->first)
                                <div class="summary-compact">
                                    <span class="summary-total">
                                        Candidats: <strong>{{ number_format($centre['total_candidats'], 0, ',', ' ') }}</strong>
                                        |
                                        Salle: <strong>{{ number_format($centre['total_salles'], 0, ',', ' ') }}</strong>
                                        |
                                        GE/matiere: <strong>{{ number_format($centre['ge_count'], 0, ',', ' ') }}</strong>
                                    </span>
                                    <strong>Langues:</strong>
                                    @forelse(($centre['langue_totals'] ?? []) as $langue => $total)
                                        <span>
                                            <strong>{{ $langueLabels[$langue] ?? $langue }}:</strong>
                                            {{ number_format($total, 0, ',', ' ') }}
                                            | Salle: {{ number_format((int) ($centre['langue_salles'][$langue] ?? 0), 0, ',', ' ') }}
                                        </span>
                                        @unless($loop->last)<span> ; </span>@endunless
                                    @empty
                                        <span class="muted">Aucune langue.</span>
                                    @endforelse
                                </div>
                            @endif
                            <div class="small-title" style="margin-top: 6px;">Tableau {{ $table['index'] }} / {{ count($centre['tables']) }}</div>
                            <table class="pdf-table">
                                @if(($filters['type_examen'] ?? '') === 'BEPC')
                                    @php $rowspan = count($table['rows']) + 2; @endphp
                                    <tbody>
                                    <tr>
                                        <td rowspan="{{ $rowspan }}"><strong>{{ $centre['centre_ecrit'] }}</strong></td>
                                        <td><strong>Salles</strong></td>
                                        @foreach($table['salles'] as $salle)
                                            <td class="center"><strong>S{{ $salle }}</strong></td>
                                        @endforeach
                                    </tr>
                                    @foreach($table['rows'] as $row)
                                        <tr>
                                            <td><strong>{{ $row['label'] }}</strong></td>
                                            @foreach($table['salles'] as $salle)
                                                <td class="center">{{ number_format((int) ($row['values'][$salle] ?? 0), 0, ',', ' ') }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    @unless($isBepcLanguesVariant)
                                        <tr>
                                            <td><strong>Total</strong></td>
                                            @foreach($table['salles'] as $salle)
                                                <td class="center"><strong>{{ number_format((int) ($table['totaux_salles'][$salle] ?? 0), 0, ',', ' ') }}</strong></td>
                                            @endforeach
                                        </tr>
                                    @endunless
                                    </tbody>
                                @else
                                    <thead>
                                    <tr>
                                        <th>Langue / Option</th>
                                        @foreach($table['salles'] as $salle)
                                            <th class="center">S{{ $salle }}</th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($table['rows'] as $row)
                                        <tr>
                                            <td><strong>{{ $row['label'] }}</strong></td>
                                            @foreach($table['salles'] as $salle)
                                                <td class="center">{{ number_format((int) ($row['values'][$salle] ?? 0), 0, ',', ' ') }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td><strong>TOTAL SALLE</strong></td>
                                        @foreach($table['salles'] as $salle)
                                            <td class="center"><strong>{{ number_format((int) ($table['totaux_salles'][$salle] ?? 0), 0, ',', ' ') }}</strong></td>
                                        @endforeach
                                    </tr>
                                    </tbody>
                                @endif
                            </table>
                        </div>
                    @endforeach
                </div>
            @endforeach
            <div class="page-footer">
                <div>Page {{ $pageNumber }} / {{ $allPages }}</div>
                <div>produced by RAMAROSON Andry Michael V1.0</div>
            </div>
        </div>
    @endforeach
        @empty
        @endforelse

        @if(collect($specialCandidates ?? [])->isNotEmpty())
            @php $pageNumber++; @endphp
            <div class="page">
                <div class="section-title">Candidats à besoins spécifiques</div>
                <div class="card">
                    <table class="pdf-table">
                        <thead>
                            <tr>
                                <th>Année</th>
                                <th>Type</th>
                                <th>DREN</th>
                                <th>CISCO</th>
                                <th>Centre correction</th>
                                <th>Centre écrit</th>
                                <th class="right">Salle</th>
                                <th>Type handicap</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(collect($specialCandidates) as $candidate)
                                <tr>
                                    <td>{{ $candidate->annee }}</td>
                                    <td>{{ $candidate->type_examen }}</td>
                                    <td>{{ $candidate->dren }}</td>
                                    <td>{{ $candidate->cisco }}</td>
                                    <td>{{ $candidate->centre_correction }}</td>
                                    <td>{{ $candidate->centre_ecrit }}</td>
                                    <td class="right">{{ number_format($candidate->numero_salle, 0, ',', ' ') }}</td>
                                    <td>{{ $candidate->type_handicap }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="page-footer">
                    <div>Page {{ $pageNumber }} / {{ $allPages }}</div>
                    <div>produced by RAMAROSON Andry Michael V1.0</div>
                </div>
            </div>
        @endif
</body>
</html>
