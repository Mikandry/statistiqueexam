<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Utilisateurs</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; background: #f1f5f9; color: #0f172a; }
        .wrap { max-width: 1200px; margin: 20px auto; padding: 0 14px; }
        .card { background: #fff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; vertical-align: top; }
        input, select { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 7px; }
        .btn { border: 0; border-radius: 8px; padding: 8px 10px; color: #fff; cursor: pointer; font-size: 12px; }
        .blue { background: #1d4ed8; }
        .green { background: #047857; }
        .red { background: #b91c1c; }
        .muted { color: #475569; font-size: 13px; }
        .top { display:flex; gap:8px; align-items:center; justify-content:space-between; flex-wrap: wrap; }
        a { color: #1d4ed8; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <h1>Administration - Utilisateurs</h1>
        <div>
            <a href="{{ route('bepc.repartition.create') }}">Saisie</a> |
            <a href="{{ route('admin.statistics.index') }}">Statistiques</a>
        </div>
    </div>

    @if(session('status'))
        <div class="card" style="border-color:#86efac;background:#f0fdf4">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="card" style="border-color:#fecaca;background:#fef2f2">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <h2>Ajouter un utilisateur</h2>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;">
                <input name="name" placeholder="Nom complet" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="password" placeholder="Mot de passe (min 8)" required>
                <select name="role" required>
                    <option value="user">Saisisseur</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="margin-top:10px;"><button class="btn green" type="submit">Ajouter</button></div>
        </form>
    </div>

    <div class="card">
        <h2>Liste utilisateurs</h2>
        <p class="muted">L'admin peut modifier rôle/email/nom, réinitialiser mot de passe et supprimer.</p>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Nouveau mot de passe</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>
                        <form id="update-user-{{ $user->id }}" method="POST" action="{{ route('admin.users.update', $user) }}">
                            @csrf
                            @method('PUT')
                            <input name="name" value="{{ $user->name }}" required>
                    </td>
                    <td><input type="email" name="email" value="{{ $user->email }}" required></td>
                    <td>
                        <select name="role" required>
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Saisisseur</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </td>
                    <td><input name="password" placeholder="laisser vide pour garder"></td>
                    <td style="display:flex;gap:6px;">
                        <button class="btn blue" type="submit">Modifier</button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn red" type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div style="margin-top:10px;">{{ $users->links() }}</div>
    </div>
</div>
</body>
</html>
