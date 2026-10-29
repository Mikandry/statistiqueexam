<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total PE / GE ajusté</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')
</head>
<body class="bg-slate-100 text-slate-900">
<div class="mx-auto max-w-[1800px] p-2 md:p-4">
    <div class="flex flex-col gap-4 md:flex-row md:items-start">
        <div>
            @include('partials.sidebar')
        </div>

        <main class="min-w-0 flex-1">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-5 md:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">Total PE / GE ajusté</h1>
                            <p class="mt-1 text-sm text-slate-500">
                                Une salle avec 50 candidats ou plus compte pour 2 PE. Le GE est recalculé sur ce total PE.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('repartition.livre.preview', request()->query()) }}">Retour livre</a>
                            <a class="rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100" href="{{ route('repartition.livre.controle', request()->except('niveau')) }}">Fiche contrôle</a>
                        </div>
                    </div>
                </div>

                <div class="border-b border-slate-200 p-5 md:p-6">
                    <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-6">
                        <div>
                            <label for="annee" class="mb-1 block text-sm font-medium text-slate-700">Année scolaire</label>
                            <select id="annee" name="annee" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Toutes</option>
                                @foreach($annees as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="type_examen" class="mb-1 block text-sm font-medium text-slate-700">Type d'examen</label>
                            <select id="type_examen" name="type_examen" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                                <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>
                        <div>
                            <label for="dren" class="mb-1 block text-sm font-medium text-slate-700">DREN</label>
                            <select id="dren" name="dren" onchange="document.getElementById('cisco').value = ''; this.form.submit();" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Toutes</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="cisco" class="mb-1 block text-sm font-medium text-slate-700">CISCO</label>
                            <select id="cisco" name="cisco" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Tous</option>
                                @foreach(($ciscos ?? []) as $cisco)
                                    <option value="{{ $cisco }}" {{ ($filters['cisco'] ?? '') === $cisco ? 'selected' : '' }}>{{ $cisco }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="centre_search" class="mb-1 block text-sm font-medium text-slate-700">Recherche centre</label>
                            <input id="centre_search" name="centre_search" type="text" value="{{ $filters['centre_search'] ?? '' }}" placeholder="Nom du centre" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label for="niveau" class="mb-1 block text-sm font-medium text-slate-700">Afficher par</label>
                            <select id="niveau" name="niveau" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="DREN" {{ $niveau === 'DREN' ? 'selected' : '' }}>DREN</option>
                                <option value="CISCO" {{ $niveau === 'CISCO' ? 'selected' : '' }}>CISCO</option>
                                <option value="CENTRE" {{ $niveau === 'CENTRE' ? 'selected' : '' }}>Centre</option>
                            </select>
                        </div>
                        <div class="md:col-span-6">
                            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Mettre à jour</button>
                        </div>
                    </form>
                </div>

                <div class="p-5 md:p-6">
                    <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-6">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="text-xs uppercase tracking-wide text-slate-500">Candidats</div>
                            <div class="mt-1 text-xl font-semibold">{{ number_format($stats['total_candidats'], 0, ',', ' ') }}</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="text-xs uppercase tracking-wide text-slate-500">Salles</div>
                            <div class="mt-1 text-xl font-semibold">{{ number_format($stats['total_salles'], 0, ',', ' ') }}</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="text-xs uppercase tracking-wide text-slate-500">Salles >= 50</div>
                            <div class="mt-1 text-xl font-semibold">{{ number_format($stats['total_salles_divisees'], 0, ',', ' ') }}</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="text-xs uppercase tracking-wide text-slate-500">PE normal / ajusté</div>
                            <div class="mt-1 text-xl font-semibold">{{ number_format($stats['total_pe_normal'], 0, ',', ' ') }} / {{ number_format($stats['total_pe'], 0, ',', ' ') }}</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="text-xs uppercase tracking-wide text-slate-500">GE normal / ajusté</div>
                            <div class="mt-1 text-xl font-semibold">{{ number_format($stats['total_ge_normal'], 0, ',', ' ') }} / {{ number_format($stats['total_ge'], 0, ',', ' ') }}</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="text-xs uppercase tracking-wide text-slate-500">Centres concernés</div>
                            <div class="mt-1 text-xl font-semibold">{{ number_format($stats['total_centres_divises'], 0, ',', ' ') }}</div>
                        </div>
                    </div>

                    <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold">Comparatif PE / GE</h2>
                            <p class="mt-1 text-xs text-slate-500">Seuls les groupes contenant au moins une salle avec 50 candidats ou plus sont affichés.</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700">
                            Ecart global PE: {{ number_format($stats['total_pe'] - $stats['total_pe_normal'], 0, ',', ' ') }} |
                            Ecart global GE: {{ number_format($stats['total_ge'] - $stats['total_ge_normal'], 0, ',', ' ') }}
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full border-collapse text-sm">
                            <thead class="bg-slate-100 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="border border-slate-200 px-3 py-3">DREN</th>
                                @if($niveau !== 'DREN')
                                    <th class="border border-slate-200 px-3 py-3">CISCO</th>
                                @endif
                                @if($niveau === 'CENTRE')
                                    <th class="border border-slate-200 px-3 py-3">Centre correction</th>
                                    <th class="border border-slate-200 px-3 py-3">Centre écrit</th>
                                    <th class="border border-slate-200 px-3 py-3">Type</th>
                                @endif
                                <th class="border border-slate-200 px-3 py-3 text-right">Centres</th>
                                <th class="border border-slate-200 px-3 py-3 text-right">Candidats</th>
                                <th class="border border-slate-200 px-3 py-3 text-right">Salles</th>
                                <th class="border border-slate-200 px-3 py-3 text-right">Salles >= 50</th>
                                <th class="border border-slate-200 px-3 py-3 text-right">% >= 50</th>
                                <th class="border border-slate-200 px-3 py-3 text-right">PE normal</th>
                                <th class="border border-slate-200 px-3 py-3 text-right">PE ajusté</th>
                                <th class="border border-slate-200 px-3 py-3 text-right">Ecart PE</th>
                                <th class="border border-slate-200 px-3 py-3 text-right">GE normal</th>
                                <th class="border border-slate-200 px-3 py-3 text-right">GE ajusté</th>
                                <th class="border border-slate-200 px-3 py-3 text-right">Ecart GE</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($summaryRows as $row)
                                <tr class="hover:bg-slate-50">
                                    <td class="border border-slate-200 px-3 py-2 font-semibold">{{ $row['dren'] }}</td>
                                    @if($niveau !== 'DREN')
                                        <td class="border border-slate-200 px-3 py-2">{{ $row['cisco'] }}</td>
                                    @endif
                                    @if($niveau === 'CENTRE')
                                        <td class="border border-slate-200 px-3 py-2">{{ $row['centre_correction'] }}</td>
                                        <td class="border border-slate-200 px-3 py-2 font-semibold">{{ $row['centre_ecrit'] }}</td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $row['type_examen'] }}</td>
                                    @endif
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['total_centres'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['total_candidats'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['total_salles'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right font-semibold text-amber-700">{{ number_format($row['total_salles_divisees'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right font-semibold text-amber-700">{{ number_format($row['salles_divisees_percent'], 1, ',', ' ') }}%</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['total_pe_normal'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right font-bold">{{ number_format($row['total_pe'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right font-bold text-emerald-700">+{{ number_format($row['ecart_pe'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['total_ge_normal'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right font-bold">{{ number_format($row['total_ge'], 0, ',', ' ') }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right font-bold {{ $row['ecart_ge'] > 0 ? 'text-emerald-700' : 'text-slate-500' }}">{{ $row['ecart_ge'] > 0 ? '+' : '' }}{{ number_format($row['ecart_ge'], 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $niveau === 'CENTRE' ? 16 : ($niveau === 'CISCO' ? 13 : 12) }}" class="px-3 py-6 text-center text-slate-500">Aucune salle avec 50 candidats ou plus pour les filtres actuels.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        <h2 class="mb-3 text-base font-bold">Détail des salles divisées</h2>
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="min-w-full border-collapse text-sm">
                                <thead class="bg-slate-100 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="border border-slate-200 px-3 py-3">DREN</th>
                                    <th class="border border-slate-200 px-3 py-3">CISCO</th>
                                    <th class="border border-slate-200 px-3 py-3">Centre écrit</th>
                                    <th class="border border-slate-200 px-3 py-3 text-right">Salles totales</th>
                                    <th class="border border-slate-200 px-3 py-3 text-right">Salles >= 50</th>
                                    <th class="border border-slate-200 px-3 py-3 text-right">% >= 50</th>
                                    <th class="border border-slate-200 px-3 py-3">Salles concernées</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($splitCentreRows as $centre)
                                    <tr class="align-top hover:bg-slate-50">
                                        <td class="border border-slate-200 px-3 py-2 font-semibold">{{ $centre['dren'] }}</td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $centre['cisco'] }}</td>
                                        <td class="border border-slate-200 px-3 py-2 font-semibold">{{ $centre['centre_ecrit'] }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($centre['total_salles'], 0, ',', ' ') }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-right font-bold text-amber-700">{{ number_format($centre['salles_divisees'], 0, ',', ' ') }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-right font-bold text-amber-700">{{ number_format($centre['salles_divisees_percent'], 1, ',', ' ') }}%</td>
                                        <td class="border border-slate-200 px-3 py-2">
                                            <div class="flex max-w-4xl flex-wrap gap-1.5">
                                                @foreach($centre['salles_divisees_labels'] as $label)
                                                    <span class="rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-900">{{ $label }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-6 text-center text-slate-500">Aucune salle à diviser.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
