<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de Traçabilité et de Contrôle de Traçabilité</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    <style>
        @page WordSectionPortrait {
            size: 595.3pt 841.9pt;
            mso-page-orientation: portrait;
        }
        @page WordSectionLandscape {
            size: 841.9pt 595.3pt;
            mso-page-orientation: landscape;
        }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 8px 0; }
        h2 { font-size: 14px; margin: 16px 0 6px 0; }
        .meta { margin-bottom: 8px; }
        .box { border: 1px solid #cbd5e1; padding: 6px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px; vertical-align: top; }
        th { background: #f1f5f9; text-align: left; }
        .right { text-align: right; }
        .portrait-section { page: WordSectionPortrait; }
        .landscape-section { page: WordSectionLandscape; }
        .page-break { page-break-before: always; }
        .small { font-size: 10px; }
    </style>
</head>
<body>
<div class="portrait-section">
    <h1>Fiche de Traçabilité et de Contrôle de Traçabilité</h1>
    <div class="meta">
        <strong>Année:</strong> {{ $filters['annee'] !== '' ? $filters['annee'] : 'Toutes' }} |
        <strong>Examen:</strong> {{ $filters['type_examen'] }} |
        <strong>DREN:</strong> {{ $filters['dren'] !== '' ? $filters['dren'] : 'Toutes' }}
    </div>

    <div class="box">
        <strong>Totaux:</strong>
        Centres {{ number_format($stats['total_centres'], 0, ',', ' ') }},
        PE {{ number_format($stats['total_pe'], 0, ',', ' ') }},
        GE {{ number_format($stats['total_ge'], 0, ',', ' ') }}.
    </div>

    <h2>Répartition PE par Compteur - {{ $selectedCompteurModeLabel ?? 'Par DREN' }}</h2>
    <table>
        <thead>
            <tr>
                <th>Groupe</th>
                <th>Assigné à</th>
                <th class="right">Total PE</th>
                <th class="right">Nb compteurs</th>
                <th class="right">PE / compteur</th>
                <th>Répartition plages PE</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($selectedCompteurSummary ?? []) as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ $row['group_name'] ?? '' }}</td>
                    <td class="right">{{ number_format($row['total_pe'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($row['compteur_count'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($row['pe_par_compteur'], 0, ',', ' ') }}</td>
                    <td>{{ $row['repartition'] }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Tâches par groupe</h2>
    @forelse(($controleTaskGroups ?? []) as $group)
        <div class="box">
            <strong>{{ $group['name'] }}</strong>
            @if(($group['counters'] ?? collect())->isNotEmpty())
                @foreach($group['counters'] as $counter)
                    <div>
                        <strong>{{ $counter['name'] }}:</strong>
                        @if(($counter['tasks'] ?? collect())->isNotEmpty())
                            @foreach($counter['tasks'] as $task)
                                {{ $task['centre_ecrit'] }} {{ $task['range'] }}@unless($loop->last); @endunless
                            @endforeach
                        @else
                            Aucune tâche.
                        @endif
                    </div>
                @endforeach
            @else
                <div>Aucune tâche assignée.</div>
            @endif
        </div>
    @empty
        <div class="box">Aucune tâche de groupe.</div>
    @endforelse

    <h2>Répartition PE par Compteur - CISCO</h2>
    <table>
        <thead>
            <tr>
                <th>Groupe (DREN / CISCO)</th>
                <th class="right">Total PE</th>
                <th class="right">Nb compteurs</th>
                <th class="right">PE / compteur</th>
                <th>Répartition plages PE</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($compteurSummaryByCisco ?? []) as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="right">{{ number_format($row['total_pe'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($row['compteur_count'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($row['pe_par_compteur'], 0, ',', ' ') }}</td>
                    <td>{{ $row['repartition'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Répartition PE par Compteur - Centre écrit</h2>
    <table>
        <thead>
            <tr>
                <th>Groupe (DREN / CISCO / Centre écrit)</th>
                <th class="right">Total PE</th>
                <th class="right">Nb compteurs</th>
                <th class="right">PE / compteur</th>
                <th>Répartition plages PE</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($compteurSummaryByCentre ?? []) as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="right">{{ number_format($row['total_pe'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($row['compteur_count'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($row['pe_par_compteur'], 0, ',', ' ') }}</td>
                    <td>{{ $row['repartition'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

    <div class="landscape-section page-break">
    <h2>Fiche Contrôle PE</h2>
    <table class="small">
        <thead>
            <tr>
                <th>DREN</th>
                <th>CISCO</th>
                <th>Centre écrit</th>
                <th>Examen</th>
                <th class="right">PE</th>
                <th class="right">GE</th>
                <th>Compteur</th>
                <th>Matière/LV</th>
                <th>Date/Heure</th>
                <th>Agent</th>
                <th>Observation</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peRows as $row)
                <tr>
                    <td>{{ $row['dren'] }}</td>
                    <td>{{ $row['cisco'] }}</td>
                    <td>{{ $row['centre_ecrit'] }}</td>
                    <td>{{ $row['type_examen'] }}</td>
                    <td class="right">{{ $row['pe_no'] }}</td>
                    <td class="right">{{ $row['ge_no'] }}</td>
                    <td>{{ $row['compteur'] !== '' ? $row['compteur'] : '....................' }}</td>
                    <td>{{ $row['matiere'] !== '' ? $row['matiere'] : '....................' }}</td>
                    <td>{{ $row['datetime'] !== '' ? $row['datetime'] : '....................' }}</td>
                    <td>{{ $row['agent'] !== '' ? $row['agent'] : '....................' }}</td>
                    <td>{{ $row['obs'] !== '' ? $row['obs'] : '....................' }}</td>
                </tr>
            @empty
                <tr><td colspan="11">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Fiche Contrôle GE</h2>
    <table class="small">
        <thead>
            <tr>
                <th>DREN</th>
                <th>CISCO</th>
                <th>Centre écrit</th>
                <th>Examen</th>
                <th class="right">GE</th>
                <th>PE concernés</th>
                <th class="right">Nb PE</th>
                <th>Compteur GE</th>
                <th>Date/Heure</th>
                <th>Validé par</th>
                <th>Observation</th>
            </tr>
        </thead>
        <tbody>
            @forelse($geRows as $row)
                <tr>
                    <td>{{ $row['dren'] }}</td>
                    <td>{{ $row['cisco'] }}</td>
                    <td>{{ $row['centre_ecrit'] }}</td>
                    <td>{{ $row['type_examen'] }}</td>
                    <td class="right">{{ $row['ge_no'] }}</td>
                    <td>{{ $row['pe_range'] }}</td>
                    <td class="right">{{ $row['pe_count'] }}</td>
                    <td>{{ $row['compteur'] !== '' ? $row['compteur'] : '....................' }}</td>
                    <td>{{ $row['datetime'] !== '' ? $row['datetime'] : '....................' }}</td>
                    <td>{{ $row['agent'] !== '' ? $row['agent'] : '....................' }}</td>
                    <td>{{ $row['obs'] !== '' ? $row['obs'] : '....................' }}</td>
                </tr>
            @empty
                <tr><td colspan="11">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</body>
</html>
