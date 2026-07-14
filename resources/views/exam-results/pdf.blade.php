<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px; }
        th { background: #f3f4f6; text-align: left; }
        .stats { margin-bottom: 12px; }
    </style>
</head>
<body>
    <h1>Résultats des Examens</h1>
    <p class="stats">CISCO: {{ $stats['cisco_total'] }} · Publiés: {{ $stats['published'] }} · Réussite nationale: {{ $stats['national_success_rate'] }}% · Abandon: {{ $stats['national_abandonment_rate'] }}%</p>
    <table>
        <thead><tr><th>Région</th><th>CISCO</th><th>Candidats</th><th>Absents</th><th>Présents</th><th>Admis</th><th>Seuil</th><th>Réussite</th><th>Abandon</th><th>Statut</th></tr></thead>
        <tbody>
            @foreach($results as $result)
                <tr>
                    <td>{{ $result->dren?->nom }}</td><td>{{ $result->cisco?->nom }}</td><td>{{ $result->total_candidates }}</td><td>{{ $result->absent_candidates }}</td><td>{{ $result->present_candidates }}</td><td>{{ $result->admitted_candidates }}</td><td>{{ $result->admission_threshold }}</td><td>{{ $result->success_rate }}%</td><td>{{ $result->abandonment_rate }}%</td><td>{{ $result->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
