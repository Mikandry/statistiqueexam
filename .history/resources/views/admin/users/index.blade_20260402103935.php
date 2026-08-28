<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Utilisateurs</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')
    <style>
        nav[role="navigation"] svg { width: 16px; height: 16px; }
        nav[role="navigation"] a, nav[role="navigation"] span { font-size: 12px; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 text-slate-900">
<div class="mx-auto max-w-[1700px] p-4 md:p-6 lg:p-8">
    <div class="flex flex-col gap-5 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-lg backdrop-blur-sm transition-all duration-200 hover:shadow-xl">
                <div class="border-b border-slate-200/80 bg-gradient-to-r from-white to-slate-50/50 px-6 py-5 md:px-8 md:py-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Administration Utilisateurs</h1>
                            <p class="text-sm font-medium text-slate-500">Gestion des comptes, rôles et mots de passe</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('bepc.repartition.create') }}">Saisie</a>
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('admin.statistics.index') }}">Statistiques</a>
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('admin.references.index') }}">Référentiels</a>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    @if(session('status'))
                        <div class="mb-6 rounded-lg border border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-white px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">{{ session('status') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="mb-6 rounded-lg border border-rose-200/80 bg-gradient-to-r from-rose-50 to-white px-4 py-3 text-sm font-medium text-rose-700 shadow-sm">{{ $errors->first() }}</div>
                    @endif

                    <div class="mb-6 rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <h2 class="mb-3 text-lg font-semibold text-slate-800">Ajouter un utilisateur</h2>
                        <form method="POST" action="{{ route('admin.users.store') }}">
                            @csrf
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                <input class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" name="name" placeholder="Nom complet" required>
                                <input class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" type="email" name="email" placeholder="Email" required>
                                <input class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" type="text" name="password" placeholder="Mot de passe (min 8)" required>
                                <select class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" name="role" required>
                                    <option value="user">Saisisseur</option>
                                    <option value="logistique">Logistique</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="mt-3">
                                <button class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-slate-700 hover:shadow-md" type="submit">Ajouter</button>
                            </div>
                        </form>
                    </div>

                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <h2 class="mb-2 text-lg font-semibold text-slate-800">Liste utilisateurs</h2>
                        <p class="mb-4 text-sm text-slate-500">L'admin peut modifier rôle/email/nom, réinitialiser le mot de passe et supprimer.</p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-slate-100/80">
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">ID</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Nom</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Email</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Rôle</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Nouveau mot de passe</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($users as $user)
                                    <tr class="transition-colors duration-150 hover:bg-slate-50/80">
                                        <td class="border border-slate-200 px-3 py-2">{{ $user->id }}</td>
                                        <td class="border border-slate-200 px-3 py-2">
                                            <form id="update-user-{{ $user->id }}" method="POST" action="{{ route('admin.users.update', $user) }}">
                                                @csrf
                                                @method('PUT')
                                                <input class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="name" value="{{ $user->name }}" required>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2"><input class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" type="email" name="email" value="{{ $user->email }}" required></td>
                                        <td class="border border-slate-200 px-3 py-2">
                                            <select class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="role" required>
                                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Saisisseur</option>
                                                <option value="logistique" {{ $user->role === 'logistique' ? 'selected' : '' }}>Logistique</option>
                                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                            </select>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2"><input class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="password" placeholder="laisser vide pour garder"></td>
                                        <td class="border border-slate-200 px-3 py-2">
                                            <div class="flex flex-wrap gap-2">
                                                <button class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-slate-700" type="submit">Modifier</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50" type="submit">Supprimer</button>
                                            </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">{{ $users->links() }}</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
