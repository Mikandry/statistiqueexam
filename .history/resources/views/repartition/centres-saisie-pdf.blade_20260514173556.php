<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Centres saisis et non saisis</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 6mm 12mm 26mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 6px;
        }
        h2 {
            font-size: 15px;
            margin: 20px 0 8px;
        }
        .meta {
            margin-bottom: 16px;
            color: #4b5563;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f3f4f6;
        }
        .empty {
            padding: 10px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
    </style>
</head>
<body>
    <h1>Centres Saisis et Non Saisis</h1>
    <div class="meta">
        Type: <strong>{{ $selectedType !== '' ? $selectedType : 'Tous' }}</strong> |
        Région: <strong>{{ $selectedRegion !== '' ? $selectedRegion : 'Toutes' }}</strong> |
        Total: <strong>{{ $totalCentres }}</strong> |
        Saisis: <strong>{{ $totalSaisis }}</strong> |
        Non saisis: <strong>{{ $totalNonSaisis }}</strong>
    </div>

    <h2>Centres Saisis</h2>
    @if($centresSaisis->isEmpty())
        <div class="empty">Aucun centre saisi pour ces filtres.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nom du centre</th>
                    <th>Région</th>
                </tr>
            </thead>
            <tbody>
                @foreach($centresSaisis as $centre)
                    <tr>
                        <td>{{ $centre->nom }}</td>
                        <td>{{ $centre->region }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Centres Non Saisis</h2>
    @if($centresNonSaisis->isEmpty())
        <div class="empty">Aucun centre non saisi pour ces filtres.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nom du centre</th>
                    <th>Région</th>
                </tr>
            </thead>
            <tbody>
                @foreach($centresNonSaisis as $centre)
                    <tr>
                        <td>{{ $centre->nom }}</td>
                        <td>{{ $centre->region }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
