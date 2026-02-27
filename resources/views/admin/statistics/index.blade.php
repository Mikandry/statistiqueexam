<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Statistiques</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
    @endif
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
                            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Administration Statistiques</h1>
                            <p class="text-sm font-medium text-slate-500">Edition fine des lignes saisies et suppression par centre</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('bepc.repartition.create') }}">Saisie</a>
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('admin.users.index') }}">Utilisateurs</a>
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

                    <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                        La modification reste par salle. La suppression se fait par centre (toutes les salles/lignes du centre).
                    </div>

                    <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-slate-100/80">
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">ID</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Centre écrit</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Année</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Langue / Option</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Salle</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Effectif</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Saisi par</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Actions salle</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Suppression centre</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php($centresAlreadyRendered = [])
                                @foreach($stats as $stat)
                                    <tr class="transition-colors duration-150 hover:bg-slate-50/80">
                                        <td class="border border-slate-200 px-3 py-2">{{ $stat->id }}</td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $stat->centreEcrit->nom ?? '-' }}</td>
                                        <td class="border border-slate-200 px-3 py-2">
                                            <form method="POST" action="{{ route('admin.statistics.update', $stat) }}">
                                                @csrf
                                                @method('PUT')
                                                <input class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="annee" value="{{ $stat->annee }}" required>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2"><input class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="langue" value="{{ $stat->langue }}" required></td>
                                        <td class="border border-slate-200 px-3 py-2"><input class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" type="number" name="numero_salle" value="{{ $stat->numero_salle }}" min="1" required></td>
                                        <td class="border border-slate-200 px-3 py-2"><input class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" type="number" name="effectif" value="{{ $stat->effectif }}" min="0" required></td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $stat->saisi_par }}</td>
                                        <td class="border border-slate-200 px-3 py-2">
                                            <button class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-slate-700" type="submit">Modifier</button>
                                            </form>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2">
                                            @if(!in_array($stat->centre_ecrit_id, $centresAlreadyRendered, true))
                                                @php($centresAlreadyRendered[] = $stat->centre_ecrit_id)
                                                <form method="POST" action="{{ route('admin.statistics.destroy-centre', $stat->centre_ecrit_id) }}" onsubmit="return confirm('Supprimer toutes les statistiques du centre {{ addslashes($stat->centreEcrit->nom ?? '') }} ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50" type="submit">Supprimer le centre</button>
                                                </form>
                                            @else
                                                <span class="text-xs text-slate-500">deja affiche</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $stats->links() }}</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
