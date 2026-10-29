<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Procès-Verbal de Traçabilité Automatique</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    <style>
        @page WordSectionPortrait {
            size: 595.3pt 841.9pt;
            mso-page-orientation: portrait;
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px; 
            color: #111827;
            line-height: 1.4;
        }
        h1 { 
            font-size: 16px; 
            font-weight: bold;
            margin: 0 0 12px 0;
            text-align: center;
        }
        h2 { 
            font-size: 13px; 
            font-weight: bold;
            margin: 12px 0 8px 0;
            border-bottom: 2px solid #111827;
            padding-bottom: 4px;
        }
        h3 {
            font-size: 11px;
            font-weight: bold;
            margin: 8px 0 4px 0;
        }
        .meta { 
            margin-bottom: 8px;
            text-align: center;
            font-size: 10px;
        }
        .box { 
            border: 1px solid #cbd5e1; 
            padding: 8px; 
            margin-bottom: 12px;
            background: #f8fafc;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 12px;
        }
        th, td { 
            border: 1px solid #cbd5e1; 
            padding: 5px; 
            vertical-align: top;
            text-align: left;
        }
        th { 
            background: #e2e8f0; 
            font-weight: bold;
        }
        .right { 
            text-align: right; 
        }
        .center {
            text-align: center;
        }
        .group-section {
            page-break-inside: avoid;
            margin-bottom: 16px;
            border: 1px solid #475569;
            padding: 8px;
            background: #ffffff;
        }
        .group-title {
            background: #0f172a;
            color: white;
            padding: 6px 8px;
            font-weight: bold;
            margin: -8px -8px 8px -8px;
        }
        .compteur-block {
            margin-bottom: 10px;
            padding: 6px;
            border-left: 3px solid #0f172a;
            background: #f8fafc;
        }
        .compteur-label {
            font-weight: bold;
            margin-bottom: 4px;
        }
        .centre-line {
            margin-bottom: 3px;
            font-size: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .portrait-section { 
            page: WordSectionPortrait; 
        }
        .small { 
            font-size: 9px; 
        }
    </style>
</head>
<body>
<div class="portrait-section">
    <h1>PROCÈS-VERBAL DE TRAÇABILITÉ AUTOMATIQUE DES COMPTEURS</h1>
    
    <div class="meta">
        <strong>Année:</strong> {{ $filters['annee'] !== '' ? $filters['annee'] : 'Toutes' }} | 
        <strong>Examen:</strong> {{ $filters['type_examen'] }} | 
        <strong>DREN:</strong> {{ $filters['dren'] !== '' ? $filters['dren'] : 'Toutes' }}<br>
        <strong>Date:</strong> {{ now()->format('d/m/Y') }}
    </div>

    <div class="box">
        <strong>Résumé Général:</strong><br>
        Centres de correction: {{ number_format($stats['total_centres'], 0, ',', ' ') }} | 
        Candidats: {{ number_format($stats['total_candidats'], 0, ',', ' ') }} | 
        Salles d'examen: {{ number_format($stats['total_salles'], 0, ',', ' ') }} | 
        PE: {{ number_format($stats['total_pe'], 0, ',', ' ') }} | 
        GE: {{ number_format($stats['total_ge'], 0, ',', ' ') }}
    </div>

    <h2>Répartition par DREN</h2>
    <table>
        <thead>
            <tr>
                <th>DREN</th>
                <th class="right">Candidats</th>
                <th class="right">Salles</th>
                <th class="right">PE</th>
                <th class="right">GE</th>
                <th class="right">Centres</th>
            </tr>
        </thead>
        <tbody>
            @forelse($drenRows as $dren)
                <tr>
                    <td><strong>{{ $dren['dren'] }}</strong></td>
                    <td class="right">{{ number_format($dren['total_candidats'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($dren['total_salles'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($dren['total_pe'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($dren['total_ge'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($dren['total_centres'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Aucune donnée disponible.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Distribution Automatique des Compteurs par Centre</h2>
    
    @php
        // Grouper les centres par DREN, puis par CISCO
        $centresByDrenCisco = $centreRows->groupBy(function($centre) {
            return $centre['dren'] . '|' . $centre['cisco'];
        })->map(function($group) {
            $first = $group->first();
            return [
                'dren' => $first['dren'],
                'cisco' => $first['cisco'],
                'centres' => $group->sortBy('centre_ecrit')->values()
            ];
        })->sortBy(function($item) {
            return $item['dren'] . '|' . $item['cisco'];
        })->values();

        $getCompteurNames = function(int $groupNum) use ($compteurNames) {
            $raw = $compteurNames[(string) $groupNum] ?? $compteurNames[$groupNum] ?? '';
            $names = array_filter(array_map('trim', explode("\n", (string) $raw)));
            return !empty($names) ? array_values($names) : ["Compteur {$groupNum}"];
        };

        $getGroupName = function(int $groupNum) use ($groupNames) {
            return trim($groupNames[(string) $groupNum] ?? $groupNames[$groupNum] ?? "Groupe {$groupNum}");
        };

        $getGroupId = function(string $dren) use ($drenAssignments) {
            $groupId = isset($drenAssignments[$dren]) ? (int) $drenAssignments[$dren] : 1;
            return $groupId > 0 ? $groupId : 1;
        };

        $peRange = function(int $start, int $count) {
            if ($count <= 0) {
                return '-';
            }
            $end = $start + $count - 1;
            return $start === $end ? "PE{$start}" : "PE{$start}-PE{$end}";
        };

        $splitCentrePe = function(array $centre, array $counters) use ($peRange) {
            $totalPe = (int) ($centre['pe'] ?? 0);
            $totalCounters = count($counters);
            if ($totalPe <= 0 || $totalCounters === 0) {
                return [];
            }

            $base = intdiv($totalPe, $totalCounters);
            $extra = $totalPe % $totalCounters;
            $cursor = 1;
            $lines = [];

            foreach ($counters as $counter) {
                $count = $base + ($extra > 0 ? 1 : 0);
                if ($extra > 0) {
                    $extra--;
                }

                if ($count <= 0) {
                    continue;
                }

                $lines[] = [
                    'counter' => $counter,
                    'count' => $count,
                    'range' => $peRange($cursor, $count),
                ];
                $cursor += $count;
            }

            return $lines;
        };
    @endphp

    @forelse($centresByDrenCisco as $ciscoGroup)
        <div class="group-section">
            <div class="group-title">
                {{ $ciscoGroup['dren'] }} - {{ $ciscoGroup['cisco'] }}
            </div>

            @foreach($ciscoGroup['centres'] as $centre)
                @php
                    $groupId = $getGroupId($centre['dren']);
                    $counters = $getCompteurNames($groupId);
                    $counterLines = $splitCentrePe($centre, $counters);
                    $groupLabel = $getGroupName($groupId);
                @endphp

                <div class="compteur-block">
                    <div class="compteur-label">
                        Centre écrit: {{ $centre['centre_ecrit'] }}
                    </div>
                    <div class="centre-line">
                        <strong>Groupe:</strong> {{ $groupLabel }}<br>
                        @foreach($counterLines as $line)
                            <strong>{{ $line['counter'] }}</strong>: {{ $line['range'] }}<br>
                        @endforeach
                        <strong style="margin-top: 4px; display: block;">
                            Total centre: {{ number_format($centre['total_candidats'], 0, ',', ' ') }} candidats | {{ number_format($centre['pe'], 0, ',', ' ') }} PE
                        </strong>
                    </div>
                </div>
            @endforeach

            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #cbd5e1;">
                <strong>Résumé CISCO {{ $ciscoGroup['cisco'] }}:</strong> {{ count($ciscoGroup['centres']) }} centre(s)
            </div>
        </div>
    @empty
        <div class="box">
            Aucun centre disponible pour les filtres sélectionnés.
        </div>
    @endforelse

    <div class="page-break"></div>

    <h2>Récapitulatif Complet par Centre</h2>
    
    @php
        $fullRecap = [];
        foreach ($centresByDrenCisco as $ciscoGroup) {
            foreach ($ciscoGroup['centres'] as $centre) {
                $groupId = $getGroupId($centre['dren']);
                $counters = $getCompteurNames($groupId);
                $counterLines = $splitCentrePe($centre, $counters);
                foreach ($counterLines as $line) {
                    $fullRecap[] = [
                        'dren' => $ciscoGroup['dren'],
                        'cisco' => $ciscoGroup['cisco'],
                        'centre' => $centre['centre_ecrit'],
                        'counter' => $line['counter'],
                        'range' => $line['range'],
                        'centre_candidats' => $centre['total_candidats'],
                        'counter_pe' => $line['count'],
                    ];
                }
            }
        }
    @endphp

    <table>
        <thead>
            <tr>
                <th>DREN</th>
                <th>CISCO</th>
                <th>Centre</th>
                <th>Compteur</th>
                <th>Plage PE</th>
                <th class="right">Candidats</th>
                <th class="right">PE</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fullRecap as $recap)
                <tr>
                    <td>{{ $recap['dren'] }}</td>
                    <td>{{ $recap['cisco'] }}</td>
                    <td>{{ $recap['centre'] }}</td>
                    <td><strong>{{ $recap['counter'] }}</strong></td>
                    <td>{{ $recap['range'] }}</td>
                    <td class="right">{{ number_format($recap['centre_candidats'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($recap['counter_pe'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Aucun centre disponible.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="box" style="margin-top: 16px;">
        <strong>Notes Importantes:</strong>
        <ul style="margin: 4px 0; padding-left: 20px;">
            <li>Ce document constitue la traçabilité automatique des compteurs pour l'année {{ $filters['annee'] !== '' ? $filters['annee'] : 'en cours' }}</li>
            <li>Chaque compteur (A, B, C...) est assigné à un ensemble de centres d'examen</li>
            <li>Les PE (Petits Examinateurs) sont numérotés séquentiellement par centre</li>
            <li>La distribution des compteurs est équilibrée selon le nombre de centres par CISCO</li>
        </ul>
    </div>

    <div style="margin-top: 20px; text-align: center; font-size: 9px; color: #475569;">
        <p>Document généré automatiquement - {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</div>
</body>
</html>
