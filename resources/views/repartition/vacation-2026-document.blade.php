<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ strtoupper($document) }} - Vacation 2026</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #0f172a; }
        h1, h2 { margin: 0; }
        .header { white-space: pre-line; font-size: 12px; line-height: 1.4; }
        .title { margin-top: 16px; font-size: 20px; font-weight: 700; }
        .subtitle { margin-top: 8px; margin-bottom: 16px; white-space: pre-line; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; }
        thead th { background: #dbeafe; text-align: left; }
        .right { text-align: right; }
        .footer { margin-top: 24px; font-size: 12px; }
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="header">{{ $setting?->entete }}</div>
    <div class="title">
        {{ $documentTitle ?? strtoupper($document) }}
        - VACATION 2026
    </div>
    <div class="subtitle">{{ $setting?->considerant }}</div>
    @if(in_array($document, ['note-service', 'decision'], true))
        <div class="subtitle">
            Activité: {{ $selectedActivity?->examen ? $selectedActivity->examen.' - '.$selectedActivity->libelle : 'Toutes activités' }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php($showDecisionReference = $document === 'decision' && !empty($decisionReference))
            @if($document === 'decision')
                @php($groups = $rows->groupBy(fn ($row) => $row['examen'].'|'.$row['activite']))
                @forelse($groups as $groupKey => $groupRows)
                    @php([$groupExamen, $groupActivite] = array_pad(explode('|', $groupKey, 2), 2, ''))
                    <tr>
                        <td colspan="{{ count($headers) }}" style="font-weight: 700; background: #f1f5f9;">
                            {{ trim($groupExamen.' - '.$groupActivite) }}
                        </td>
                    </tr>
                    @foreach($groupRows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['nom'] }}</td>
                            <td>{{ $row['im'] }}</td>
                            <td>{{ $row['localite'] }}</td>
                            @if($showDecisionReference)
                                <td>{{ $decisionReference }}</td>
                            @endif
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="{{ count($headers) }}" style="text-align: center;">Aucune donnée affectée.</td>
                    </tr>
                @endforelse
            @else
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['examen'] }}</td>
                        <td>{{ $row['activite'] }}</td>
                        <td>{{ $row['nom'] }}</td>
                        <td>{{ $row['im'] }}</td>
                        <td>{{ $row['localite'] }}</td>
                        @if($document === 'decompte')
                            <td class="right">{{ $row['jours'] }}</td>
                            <td class="right">{{ $row['taux'] !== null ? number_format((float) $row['taux'], 2, ',', ' ') : '' }}</td>
                            <td class="right">{{ $row['montant'] !== null ? number_format((float) $row['montant'], 2, ',', ' ') : '' }}</td>
                        @endif
                        @if($document === 'presence')
                            <td>{{ $row['cin'] }}</td>
                            <td></td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) }}" style="text-align: center;">Aucune donnée affectée.</td>
                    </tr>
                @endforelse
            @endif
        </tbody>
    </table>

    <div class="footer">
        Antananarivo, le {{ now()->format('d/m/Y') }}<br>
        {{ $setting?->signature }}
    </div>

    <script>
        if (window.location.search.includes('autoprint=1')) {
            window.print();
        }
    </script>
</body>
</html>
