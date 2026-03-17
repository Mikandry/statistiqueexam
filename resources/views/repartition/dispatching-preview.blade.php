<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prévisualisation Dispatching</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
    @endif
</head>
<body class="bg-slate-100 text-slate-900">
<div class="mx-auto max-w-[1700px] p-4 md:p-6 lg:p-8">
    <div class="flex flex-col gap-5 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-lg">
                <div class="border-b border-slate-200 px-6 py-5 md:px-8 md:py-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">Prévisualisation export dispatching</h1>
                            <p class="mt-1 text-sm text-slate-500">Regroupement par DREN, axe et point de largage.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700" href="{{ route('repartition.dashboard', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Dashboard</a>
                            <a class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white" href="{{ route('repartition.export.dispatching', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Exporter XML Excel</a>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <form method="GET" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="annee">Année scolaire</label>
                            <select id="annee" name="annee" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm">
                                <option value="">Toutes</option>
                                @foreach($annees as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="type_examen">Type d'examen</label>
                            <select id="type_examen" name="type_examen" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm">
                                <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                                <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="dren">DREN</label>
                            <select id="dren" name="dren" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm">
                                <option value="">Toutes</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white" type="submit">Filtrer</button>
                        </div>
                    </form>

                    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-5">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">Centres: <strong>{{ number_format($globalStats['total_centres'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">Candidats: <strong>{{ number_format($globalStats['total_candidats'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">Salles: <strong>{{ number_format($globalStats['total_salles'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">Axes: <strong>{{ number_format($globalStats['total_axes'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">Points: <strong>{{ number_format($globalStats['total_points'], 0, ',', ' ') }}</strong></div>
                    </div>

                    @forelse($dispatchingByAxe as $axe => $axeRows)
                        <section class="mb-6 rounded-xl border border-slate-200 bg-white p-4">
                            <h2 class="mb-3 text-lg font-bold">AXE: {{ $axe }}</h2>
                            @foreach($axeRows->groupBy('point_largage') as $point => $pointRows)
                                <div class="mb-3 rounded-md border border-slate-200 bg-white p-3">
                                    <div class="mb-2 text-sm font-semibold text-slate-700">Point de largage: {{ $point }}</div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full border-collapse text-xs">
                                            <thead>
                                            <tr class="bg-slate-100">
                                                <th class="border border-slate-200 px-2 py-2 text-left">DREN</th>
                                                <th class="border border-slate-200 px-2 py-2 text-left">CISCO</th>
                                                <th class="border border-slate-200 px-2 py-2 text-left">Centre</th>
                                                <th class="border border-slate-200 px-2 py-2 text-left">Code</th>
                                                <th class="border border-slate-200 px-2 py-2 text-right">ESP</th>
                                                <th class="border border-slate-200 px-2 py-2 text-right">ALL</th>
                                                <th class="border border-slate-200 px-2 py-2 text-right">ANG</th>
                                                <th class="border border-slate-200 px-2 py-2 text-right">B</th>
                                                <th class="border border-slate-200 px-2 py-2 text-right">S</th>
                                                <th class="border border-slate-200 px-2 py-2 text-right">Total</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($pointRows as $row)
                                                <tr>
                                                    <td class="border border-slate-200 px-2 py-2">{{ $row['dren'] }}</td>
                                                    <td class="border border-slate-200 px-2 py-2">{{ $row['cisco'] }}</td>
                                                    <td class="border border-slate-200 px-2 py-2">{{ $row['centre_ecrit'] }}</td>
                                                    <td class="border border-slate-200 px-2 py-2">{{ $row['code_centre'] }}</td>
                                                    <td class="border border-slate-200 px-2 py-2 text-right">{{ number_format($row['esp'], 0, ',', ' ') }}</td>
                                                    <td class="border border-slate-200 px-2 py-2 text-right">{{ number_format($row['allemand'], 0, ',', ' ') }}</td>
                                                    <td class="border border-slate-200 px-2 py-2 text-right">{{ number_format($row['anglais'], 0, ',', ' ') }}</td>
                                                    <td class="border border-slate-200 px-2 py-2 text-right">{{ number_format($row['option_b'], 0, ',', ' ') }}</td>
                                                    <td class="border border-slate-200 px-2 py-2 text-right">{{ number_format($row['salles'], 0, ',', ' ') }}</td>
                                                    <td class="border border-slate-200 px-2 py-2 text-right font-semibold">{{ number_format($row['candidats'], 0, ',', ' ') }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </section>
                    @empty
                        <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Aucune donnée.</div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
