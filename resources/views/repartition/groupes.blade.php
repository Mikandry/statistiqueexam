<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Répartition de Groupes</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')

    <style>
        body { font-family: var(--app-font-sans); }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        .scenario-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: minmax(0, 1fr);
        }
        @media (min-width: 1280px) {
            .scenario-grid {
                grid-template-columns: repeat(var(--group-count, 4), minmax(0, 1fr));
            }
        }
    </style>
</head>

<body class="h-full antialiased text-slate-900">
    <div class="flex min-h-screen">
        @include('partials.sidebar')

        <main class="custom-scrollbar flex-1 min-w-0 overflow-auto p-4 lg:p-8">
            <div class="mx-auto max-w-[1650px] space-y-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <nav class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400">
                            <span>Examens</span>
                            <i class="fas fa-chevron-right text-[10px]"></i>
                            <span class="text-orange-600">Répartition Groupes</span>
                        </nav>
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 lg:text-4xl">Répartition de Groupes DREN</h1>
                        <p class="mt-1 font-medium text-slate-500">Equilibrage automatique des DREN en {{ $groupCount }} groupes selon les candidats, les salles, et un scénario ajusté avec certaines CISCO.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50" href="{{ route('repartition.dashboard', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">
                            <i class="fas fa-chart-line text-indigo-500"></i> Dashboard
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-3 text-sm font-bold text-amber-700 transition-all hover:bg-amber-100" href="{{ route('repartition.vacations', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">
                            <i class="fas fa-layer-group"></i> Vacations
                        </a>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <form method="GET" action="{{ route('repartition.groupes') }}" class="grid grid-cols-1 items-end gap-4 md:grid-cols-5">
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Année académique</label>
                            <select name="annee" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition-all focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10">
                                <option value="">Toutes</option>
                                @foreach($annees as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Type d'examen</label>
                            <select name="type_examen" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition-all focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10">
                                <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous les examens</option>
                                <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Nombre de groupes</label>
                            <select name="groups" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition-all focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10">
                                <option value="4" {{ $groupCount === 4 ? 'selected' : '' }}>4 groupes</option>
                                <option value="5" {{ $groupCount === 5 ? 'selected' : '' }}>5 groupes</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">DREN / Région</label>
                            <select name="dren" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition-all focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10">
                                <option value="">Toutes les DREN</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="flex items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-orange-100 transition-all hover:bg-orange-700 active:scale-95">
                            <i class="fas fa-sliders-h"></i> Appliquer
                        </button>
                    </form>

                    <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                        <span class="mr-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Paramètres actifs:</span>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                Année: {{ $filters['annee'] ?: 'Toutes' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-orange-100 bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700">
                                Examen: {{ $filters['type_examen'] }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                                Groupes: {{ $groupCount }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <div class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="rounded-2xl bg-blue-50 p-3 text-blue-600 transition-colors group-hover:bg-blue-600 group-hover:text-white">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                        </div>
                        <p class="text-sm font-bold uppercase tracking-wider text-slate-500">Candidats</p>
                        <h3 class="mt-1 text-4xl font-black tracking-tight text-slate-900">{{ number_format($globalStats['total_candidats'], 0, ',', ' ') }}</h3>
                    </div>

                    <div class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-600 transition-colors group-hover:bg-emerald-600 group-hover:text-white">
                                <i class="fas fa-door-open text-xl"></i>
                            </div>
                        </div>
                        <p class="text-sm font-bold uppercase tracking-wider text-slate-500">Salles</p>
                        <h3 class="mt-1 text-4xl font-black tracking-tight text-slate-900">{{ number_format($globalStats['total_salles'], 0, ',', ' ') }}</h3>
                    </div>

                    <div class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="rounded-2xl bg-violet-50 p-3 text-violet-600 transition-colors group-hover:bg-violet-600 group-hover:text-white">
                                <i class="fas fa-map text-xl"></i>
                            </div>
                        </div>
                        <p class="text-sm font-bold uppercase tracking-wider text-slate-500">DREN</p>
                        <h3 class="mt-1 text-4xl font-black tracking-tight text-slate-900">{{ number_format($globalStats['total_drens'], 0, ',', ' ') }}</h3>
                    </div>

                    <div class="rounded-3xl border border-slate-900 bg-slate-900 p-6 text-white shadow-xl">
                        <p class="mb-4 text-xs font-black uppercase tracking-widest text-slate-400">Découpage</p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-black text-orange-400">{{ $groupCount }}</span>
                            <span class="font-bold text-slate-500">groupes</span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2 text-[10px] font-black uppercase">
                            <div class="text-orange-300">{{ number_format($globalStats['total_ciscos'], 0, ',', ' ') }} CISCO</div>
                            <div class="text-blue-300">3 scénarios</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-xl font-extrabold text-slate-900">Base DREN / CISCO</h2>
                            <p class="mt-1 text-sm text-slate-500">Données sources utilisées pour calculer les groupes.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="border border-slate-200 px-3 py-2 text-left">DREN</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left">CISCO</th>
                                    <th class="border border-slate-200 px-3 py-2 text-right">Candidats</th>
                                    <th class="border border-slate-200 px-3 py-2 text-right">Salles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($drenStats as $dren)
                                    @foreach($dren['ciscos'] as $index => $cisco)
                                        <tr>
                                            <td class="border border-slate-200 px-3 py-2 font-semibold">{{ $index === 0 ? $dren['dren'] : '' }}</td>
                                            <td class="border border-slate-200 px-3 py-2">{{ $cisco['cisco'] }}</td>
                                            <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($cisco['candidats'], 0, ',', ' ') }}</td>
                                            <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($cisco['salles'], 0, ',', ' ') }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-slate-50 font-bold">
                                        <td colspan="2" class="border border-slate-200 px-3 py-2">Total {{ $dren['dren'] }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($dren['candidats'], 0, ',', ' ') }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($dren['salles'], 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="border border-slate-200 px-3 py-8 text-center text-slate-500">Aucune donnée disponible pour ces filtres.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @php
                    $scenarios = [
                        [
                            'title' => 'Scénario 1 · Groupes par candidats',
                            'description' => 'Les DREN restent entières et sont réparties selon le volume de candidats.',
                            'scenario' => $scenarioCandidates,
                            'badge' => 'border-blue-200 bg-blue-50 text-blue-700',
                        ],
                        [
                            'title' => 'Scénario 2 · Ajustement avec certaines CISCO',
                            'description' => 'Le système déplace certaines CISCO vers un autre groupe pour réduire l’écart global.',
                            'scenario' => $scenarioCandidatesWithCisco,
                            'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
                        ],
                        [
                            'title' => 'Scénario 3 · Groupes par salles',
                            'description' => 'La charge est équilibrée en fonction du nombre de salles au lieu du nombre de candidats.',
                            'scenario' => $scenarioSalles,
                            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        ],
                    ];
                @endphp

                @foreach($scenarios as $scenarioBlock)
                    @php($scenario = $scenarioBlock['scenario'])
                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 class="text-xl font-extrabold text-slate-900">{{ $scenarioBlock['title'] }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ $scenarioBlock['description'] }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-bold {{ $scenarioBlock['badge'] }}">
                                    Cible moyenne: {{ number_format($scenario['target'], 0, ',', ' ') }} {{ $scenario['metric'] === 'salles' ? 'salles' : 'candidats' }}
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-bold text-slate-700">
                                    Ecart max: {{ number_format($scenario['spread'], 0, ',', ' ') }}
                                </span>
                            </div>
                        </div>

                        <div class="scenario-grid" style="--group-count: {{ $groupCount }};">
                            @foreach($scenario['groups'] as $group)
                                <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                    <div class="mb-4 flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Groupe {{ $group['group'] }}</p>
                                            <p class="mt-1 text-lg font-black text-slate-950">{{ count($group['dren_labels']) }} élément(s)</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs font-bold text-slate-500">{{ number_format($group['candidats'], 0, ',', ' ') }} candidats</p>
                                            <p class="text-xs font-bold text-slate-500">{{ number_format($group['salles'], 0, ',', ' ') }} salles</p>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        @forelse($group['items'] as $item)
                                            <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p class="font-black text-slate-900">{{ $item['label'] }}</p>
                                                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                            {{ $item['type'] === 'cisco' ? 'CISCO déplacée' : 'Bloc DREN' }}
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-xs font-bold text-slate-500">
                                                        <div>{{ number_format($item['candidats'], 0, ',', ' ') }} candidats</div>
                                                        <div>{{ number_format($item['salles'], 0, ',', ' ') }} salles</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-3 py-6 text-center text-sm text-slate-400">
                                                Aucun élément.
                                            </div>
                                        @endforelse
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        @if(!empty($scenario['adjustments']))
                            <div class="mt-6 rounded-3xl border border-amber-200 bg-amber-50 p-5">
                                <div class="mb-3">
                                    <h3 class="text-lg font-black text-amber-900">Ajustements CISCO appliqués</h3>
                                    <p class="text-sm text-amber-800">Ces déplacements affinent l’équilibre entre les groupes.</p>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full border-collapse text-sm">
                                        <thead>
                                            <tr class="bg-white/70">
                                                <th class="border border-amber-200 px-3 py-2 text-left">CISCO</th>
                                                <th class="border border-amber-200 px-3 py-2 text-center">Depuis</th>
                                                <th class="border border-amber-200 px-3 py-2 text-center">Vers</th>
                                                <th class="border border-amber-200 px-3 py-2 text-right">Candidats</th>
                                                <th class="border border-amber-200 px-3 py-2 text-right">Salles</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($scenario['adjustments'] as $move)
                                                <tr>
                                                    <td class="border border-amber-200 px-3 py-2 font-semibold">{{ $move['item'] }}</td>
                                                    <td class="border border-amber-200 px-3 py-2 text-center">Groupe {{ $move['from_group'] }}</td>
                                                    <td class="border border-amber-200 px-3 py-2 text-center">Groupe {{ $move['to_group'] }}</td>
                                                    <td class="border border-amber-200 px-3 py-2 text-right">{{ number_format($move['candidats'], 0, ',', ' ') }}</td>
                                                    <td class="border border-amber-200 px-3 py-2 text-right">{{ number_format($move['salles'], 0, ',', ' ') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>
        </main>
    </div>
</body>
</html>
