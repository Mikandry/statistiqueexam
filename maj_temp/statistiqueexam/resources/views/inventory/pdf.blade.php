<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px; }
        th { background: #f3f4f6; text-align: left; }
    </style>
</head>
<body>
    <h1>Comptabilité des Matières</h1>
    <p>Total matériels: {{ $stats['total_materials'] }} · Disponibles: {{ $stats['available'] }} · Alertes: {{ $stats['alerts'] }} · Valeur: {{ number_format($stats['stock_value'], 0, ',', ' ') }} Ar</p>
    <table>
        <thead><tr><th>Code</th><th>Matériel</th><th>Catégorie</th><th>Unité</th><th>Initial</th><th>Disponible</th><th>Seuil</th><th>Prix</th><th>Valeur</th><th>Fournisseur</th><th>Etat</th></tr></thead>
        <tbody>
            @foreach($materials as $material)
                <tr>
                    <td>{{ $material->code }}</td><td>{{ $material->name }}</td><td>{{ $material->category }}</td><td>{{ $material->unit }}</td><td>{{ $material->initial_quantity }}</td><td>{{ $material->available_quantity }}</td><td>{{ $material->minimum_threshold }}</td><td>{{ $material->unit_price }}</td><td>{{ $material->total_value }}</td><td>{{ $material->supplier?->name }}</td><td>{{ $material->condition }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
