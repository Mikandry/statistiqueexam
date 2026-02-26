<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Statistiques</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; background: #f1f5f9; color: #0f172a; }
        .wrap { max-width: 1300px; margin: 20px auto; padding: 0 14px; }
        .card { background: #fff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #e2e8f0; padding: 7px; vertical-align: top; }
        input { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 7px; }
        .btn { border: 0; border-radius: 8px; padding: 8px 10px; color: #fff; cursor: pointer; font-size: 12px; }
        .blue { background: #1d4ed8; }
        .red { background: #b91c1c; }
        a { color: #1d4ed8; text-decoration: none; }
        .top { display:flex; gap:8px; align-items:center; justify-content:space-between; flex-wrap: wrap; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <h1>Administration - Statistiques de saisie</h1>
        <div>
            <a href="{{ route('bepc.repartition.create') }}">Saisie</a> |
            <a href="{{ route('admin.users.index') }}">Utilisateurs</a> |
            <a href="{{ route('repartition.vacations') }}">Vacations</a>
        </div>
    </div>

    @if(session('status'))
        <div class="card" style="border-color:#86efac;background:#f0fdf4">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="card" style="border-color:#fecaca;background:#fef2f2">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <p style="margin-top:0;color:#475569;font-size:12px;">La modification reste par salle. La suppression se fait par centre (toutes les salles/lignes du centre).</p>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Centre écrit</th>
                    <th>Année</th>
                    <th>Langue / Option</th>
                    <th>Salle</th>
                    <th>Effectif</th>
                    <th>Saisi par</th>
                    <th>Actions salle</th>
                    <th>Suppression centre</th>
                </tr>
            </thead>
            <tbody>
            @php($centresAlreadyRendered = [])
            @foreach($stats as $stat)
                <tr>
                    <td>{{ $stat->id }}</td>
                    <td>{{ $stat->centreEcrit->nom ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.statistics.update', $stat) }}">
                            @csrf
                            @method('PUT')
                            <input name="annee" value="{{ $stat->annee }}" required>
                    </td>
                    <td><input name="langue" value="{{ $stat->langue }}" required></td>
                    <td><input type="number" name="numero_salle" value="{{ $stat->numero_salle }}" min="1" required></td>
                    <td><input type="number" name="effectif" value="{{ $stat->effectif }}" min="0" required></td>
                    <td>{{ $stat->saisi_par }}</td>
                    <td style="display:flex;gap:6px;">
                        <button class="btn blue" type="submit">Modifier</button>
                        </form>
                    </td>
                    <td>
                        @if(!in_array($stat->centre_ecrit_id, $centresAlreadyRendered, true))
                            @php($centresAlreadyRendered[] = $stat->centre_ecrit_id)
                            <form method="POST" action="{{ route('admin.statistics.destroy-centre', $stat->centre_ecrit_id) }}" onsubmit="return confirm('Supprimer toutes les statistiques du centre {{ addslashes($stat->centreEcrit->nom ?? '') }} ?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn red" type="submit">Supprimer le centre</button>
                            </form>
                        @else
                            <span style="color:#64748b">deja affiche</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div style="margin-top:10px;">{{ $stats->links() }}</div>
    </div>
</div>
</body>
</html>
