<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de Traçabilité et de Contrôle de Traçabilité</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 8px 0; }
        h2 { font-size: 14px; margin: 16px 0 6px 0; }
        .meta { margin-bottom: 8px; }
        .box { border: 1px solid #cbd5e1; padding: 6px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px; vertical-align: top; }
        th { background: #f1f5f9; text-align: left; }
        .right { text-align: right; }
    </style>
</head>
<body>
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

    <h2>Répartition PE par Compteur (à compléter)</h2>
    @php
        $peByDren = collect($peRows ?? [])->groupBy('dren')->map->count()->sortKeys();
    @endphp
    <table>
        <thead>
            <tr>
                <th>Groupe (DREN)</th>
                <th class="right">Total PE</th>
                <th class="right">Nb compteurs (à saisir)</th>
                <th class="right">PE / compteur</th>
                <th>Répartition plages PE</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peByDren as $dren => $countPe)
                <tr>
                    <td>{{ $dren }}</td>
                    <td class="right">{{ number_format($countPe, 0, ',', ' ') }}</td>
                    <td class="right">..........</td>
                    <td class="right">..........</td>
                    <td>................................................................</td>
                </tr>
            @empty
                <tr><td colspan="5">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Fiche Contrôle PE</h2>
    <table>
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
                    <td>....................</td>
                    <td>....................</td>
                    <td>....................</td>
                    <td>....................</td>
                    <td>....................</td>
                </tr>
            @empty
                <tr><td colspan="11">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Fiche Contrôle GE</h2>
    <table>
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
                    <td>....................</td>
                    <td>....................</td>
                    <td>....................</td>
                    <td>....................</td>
                </tr>
            @empty
                <tr><td colspan="11">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
