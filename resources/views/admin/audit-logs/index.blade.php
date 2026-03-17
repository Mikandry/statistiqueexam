<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Historique IP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
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
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-lg backdrop-blur-sm">
                <div class="border-b border-slate-200/80 bg-gradient-to-r from-white to-slate-50/50 px-6 py-5 md:px-8 md:py-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Historique IP & Connexions</h1>
                            <p class="text-sm font-medium text-slate-500">Connexion, import et saisie avec IP, date et poste</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <form method="GET" class="mb-6 grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-6">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="user_id">Utilisateur</label>
                            <select id="user_id" name="user_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                <option value="">Tous</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ (int) $filters['user_id'] === (int) $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="action">Action</label>
                            <select id="action" name="action" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                <option value="">Toutes</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ $filters['action'] === $action ? 'selected' : '' }}>{{ $action }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="date_from">Du</label>
                            <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="date_to">Au</label>
                            <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                        </div>
                        <div class="flex items-end">
                            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">Filtrer</button>
                        </div>
                    </form>

                    <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-slate-100/80">
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Date</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Utilisateur</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Action</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">IP</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Poste</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">User-Agent</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Détails</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($logs as $log)
                                    <tr class="transition-colors duration-150 hover:bg-slate-50/80">
                                        <td class="border border-slate-200 px-3 py-2 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $log->user?->name ?? '-' }}</td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $log->action }}</td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $log->ip ?? '-' }}</td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $log->device_name ?? '-' }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-xs text-slate-500">{{ $log->user_agent ?? '-' }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-xs text-slate-500">{{ $log->meta ? json_encode($log->meta, JSON_UNESCAPED_UNICODE) : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="border border-slate-200 px-3 py-6 text-center text-slate-500">Aucun historique.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $logs->links() }}</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
