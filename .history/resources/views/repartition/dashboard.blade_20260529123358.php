<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>SOE · Tableau de bord des examens | Pilotage hiérarchique</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    
    @include('partials.head-assets')

    <style>
        :root {
            --dashboard-bg: radial-gradient(circle at top right, rgba(99, 102, 241, 0.12), transparent 30%),
                radial-gradient(circle at left center, rgba(16, 185, 129, 0.06), transparent 25%),
                linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            --primary-indigo: #6366f1;
        }

        body {
            font-family: var(--app-font-sans);
            background: var(--dashboard-bg);
            background-attachment: fixed;
        }

        .glass-card { 
            background: rgba(255, 255, 255, 0.8); 
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .table-row-hover { transition: transform 0.2s ease, background-color 0.2s; }
        .table-row-hover:hover { 
            background-color: rgba(99, 102, 241, 0.03); 
            transform: translateX(4px);
            box-shadow: inset 3px 0 0 var(--primary-indigo); 
        }
        
        .metric-card { transition: all 0.3s ease; border: 1px solid rgba(226, 232, 240, 0.8); min-height: 170px; }
        .metric-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05); }

        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 20px; }
        
        canvas { max-width: 100%; height: auto !important; }
        .chart-card { position: relative; overflow: hidden; }
        .chart-card::after {
            content: '';
            position: absolute;
            inset: auto -30px -50px auto;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.08), transparent 70%);
            pointer-events: none;
        }
        .chart-surface {
            position: relative;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.95), rgba(255, 255, 255, 0.98));
        }
        .chart-meta-pill {
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.9);
        }
        .special-table th {
            white-space: nowrap;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .special-input {
            min-height: 40px;
            width: 100%;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: .6rem .75rem;
            font-size: .86rem;
            font-weight: 750;
            color: #0f172a;
            outline: none;
        }
        .special-input:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.14);
        }
    </style>
</head>

<body class="h-full antialiased text-slate-900">
    <div class="flex min-h-screen">
        @include('partials.sidebar')

        <main class="flex-1 min-w-0 overflow-auto custom-scrollbar p-4 lg:p-8">
            <div class="max-w-[1600px] mx-auto space-y-8">
                
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">
                            <span>Examens</span>
                            <i class="fas fa-chevron-right text-[8px]"></i>
                            <span class="text-indigo-600">Tableau de bord</span>
                        </nav>
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 lg:text-4xl">Statistiques Globales</h1>
                        <p class="mt-1 text-slate-500 font-medium italic">Pilotage hiérarchique et suivi de la répartition.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-lg transition-all hover:bg-slate-800 hover:-translate-y-0.5" href="{{ route('bepc.repartition.create') }}">
                            <i class="fas fa-plus-circle text-slate-400"></i> Saisie
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50" href="{{ route('repartition.export.excel', request()->query()) }}">
                            <i class="fas fa-file-excel text-emerald-600"></i> Excel
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl bg-rose-50 px-4 py-2.5 text-xs font-bold text-rose-700 hover:bg-rose-100" href="{{ route('repartition.livre.pdf', request()->query()) }}">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/90 p-6 backdrop-blur-md shadow-sm">
                    <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-5 items-end">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400">Année</label>
                            <select name="annee" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:bg-white outline-none">
                                @foreach($annees as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400">Examen</label>
                            <select name="type_examen" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:bg-white outline-none">
                                <option value="ALL">Tous</option>
                                <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400">DREN</label>
                            <select name="dren" id="drenFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:bg-white outline-none">
                                <option value="">Toutes</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400">CISCO</label>
                            <select name="cisco" id="ciscoFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:bg-white outline-none">
                                <option value="">Tous</option>
                            </select>
                        </div>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-3.5 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700">
                            Appliquer
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-5">
                    @php 
                        $cards = [
                            ['Candidats', $globalStats['total_candidats'], 'fa-users', 'indigo'],
                            ['Salles', $globalStats['total_salles'], 'fa-door-open', 'violet'],
                            ['Centres correction', $globalStats['total_centres_correction'], 'fa-building', 'amber'],
                            ['Centres écrit', $globalStats['total_centres_ecrit'], 'fa-map-marker-alt', 'emerald'],
                        ];
                    @endphp
                    @foreach($cards as $card)
                    <div class="metric-card group rounded-3xl bg-white p-5 shadow-sm">
                        <div class="rounded-2xl bg-{{ $card[2] ?? 'slate' }}-50 p-3 w-fit text-{{ $card[3] }}-600 mb-3 group-hover:bg-{{ $card[3] }}-600 group-hover:text-white transition-colors">
                            <i class="fas {{ $card[2] }} text-xl"></i>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.18em]">{{ $card[0] }}</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-2">{{ number_format($card[1], 0, ',', ' ') }}</h3>
                    </div>
                    @endforeach

                    <div class="rounded-3xl border border-slate-200 bg-slate-950 p-5 shadow-xl text-white">
                        @php
                            $bepcSaisis = (int) ($centresSaisieStats['by_type']['BEPC']['saisis'] ?? 0);
                            $bepcTotal = (int) ($centresSaisieStats['by_type']['BEPC']['total'] ?? 0);
                            $cepeSaisis = (int) ($centresSaisieStats['by_type']['CEPE']['saisis'] ?? 0);
                            $cepeTotal = (int) ($centresSaisieStats['by_type']['CEPE']['total'] ?? 0);
                            $bepcPercent = $bepcTotal > 0 ? round(($bepcSaisis / $bepcTotal) * 100, 1) : 0;
                            $cepePercent = $cepeTotal > 0 ? round(($cepeSaisis / $cepeTotal) * 100, 1) : 0;
                        @endphp
                        <p class="text-[10px] font-black uppercase text-slate-500 mb-4">Progression Saisie</p>
                        <div class="flex items-baseline gap-2 mb-4">
                            <span class="text-3xl font-black text-indigo-400">{{ $centresSaisieStats['saisis'] }}</span>
                            <span class="text-slate-500 font-bold text-sm">/ {{ $centresSaisieStats['total'] }}</span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-800 rounded-full">
                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ ($centresSaisieStats['saisis'] / max($centresSaisieStats['total'], 1)) * 100 }}%"></div>
                        </div>
                        <div class="mt-5 space-y-2 text-xs font-bold">
                            <div class="flex items-center justify-between rounded-2xl bg-slate-800/80 px-3 py-2">
                                <span class="text-indigo-300">BEPC</span>
                                <span class="text-slate-200">{{ number_format($bepcPercent, 1, ',', ' ') }} %</span>
                                <span class="text-slate-400">Reste: {{ max($bepcTotal - $bepcSaisis, 0) }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl bg-slate-800/80 px-3 py-2">
                                <span class="text-emerald-300">CEPE</span>
                                <span class="text-slate-200">{{ number_format($cepePercent, 1, ',', ' ') }} %</span>
                                <span class="text-slate-400">Reste: {{ max($cepeTotal - $cepeSaisis, 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-amber-200 bg-white shadow-sm mb-8 overflow-hidden">
                    <div class="border-b border-amber-100 bg-amber-50/90 p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-amber-700 shadow-sm">
                                        <i class="fas fa-wheelchair"></i>
                                    </span>
                                    <div>
                                        <h2 class="text-lg font-black text-slate-900">Candidats à besoins spécifiques</h2>
                                        <p class="text-sm font-medium text-slate-500">Vue statistique avec modification rapide de la salle et du type de handicap.</p>
                                    </div>
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-4 py-2 text-xs font-black uppercase tracking-widest text-amber-800">
                                {{ collect($specialCandidates)->count() }} entrées
                            </span>
                        </div>
                    </div>
                    <div class="p-4 md:p-6">
                        @if(collect($specialCandidates ?? [])->isNotEmpty())
                        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                            <table class="special-table w-full min-w-[980px] text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-slate-100 text-slate-500">
                                        <th class="px-4 py-3 font-black uppercase tracking-wide">Année</th>
                                        <th class="px-4 py-3 font-black uppercase tracking-wide">Type</th>
                                        <th class="px-4 py-3 font-black uppercase tracking-wide">DREN</th>
                                        <th class="px-4 py-3 font-black uppercase tracking-wide">CISCO</th>
                                        <th class="px-4 py-3 font-black uppercase tracking-wide">Centre écrit</th>
                                        <th class="px-4 py-3 font-black uppercase tracking-wide">Salle</th>
                                        <th class="px-4 py-3 font-black uppercase tracking-wide">Handicap</th>
                                        <th class="px-4 py-3 font-black uppercase tracking-wide">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach(collect($specialCandidates) as $candidate)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-3">{{ $candidate->annee }}</td>
                                            <td class="px-4 py-3">{{ $candidate->type_examen }}</td>
                                            <td class="px-4 py-3">{{ $candidate->dren }}</td>
                                            <td class="px-4 py-3">{{ $candidate->cisco }}</td>
                                            <td class="px-4 py-3">{{ $candidate->centre_ecrit }}</td>
                                            <td class="px-4 py-3">
                                                <form id="special-candidate-dashboard-{{ $candidate->id }}" method="POST" action="{{ route('repartition.special-candidates.update', $candidate->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input class="special-input max-w-[100px]" type="number" min="1" name="numero_salle" value="{{ $candidate->numero_salle }}" aria-label="Salle">
                                                </form>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input class="special-input min-w-[180px]" form="special-candidate-dashboard-{{ $candidate->id }}" name="type_handicap" value="{{ $candidate->type_handicap }}" aria-label="Type handicap">
                                            </td>
                                            <td class="px-4 py-3">
                                                <button form="special-candidate-dashboard-{{ $candidate->id }}" type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-3 py-2 text-xs font-black text-white shadow-sm hover:bg-slate-800">
                                                    <i class="fas fa-save text-slate-400"></i>
                                                    Modifier
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-amber-200 bg-amber-50/50 px-5 py-8 text-center text-sm font-bold text-slate-500">
                                Aucun candidat à besoins spécifiques pour les filtres actuels.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 flex items-center gap-3 text-sm font-black uppercase text-slate-400">
                            <span class="h-4 w-1 bg-indigo-600 rounded-full"></span> Langues & Options
                        </h2>
                        <div class="space-y-3 max-h-[350px] overflow-y-auto custom-scrollbar pr-2">
                            @foreach($totalsByLangue as $label => $value)
                            <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4 transition-all hover:bg-slate-100">
                                <span class="font-bold text-slate-700 text-sm">{{ $label }}</span>
                                <span class="font-black text-slate-900">{{ number_format($value, 0, ',', ' ') }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="chart-card rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-[10px] font-black uppercase text-slate-400">Comparatif Langues (BEPC)</h2>
                                    <p class="mt-2 text-sm font-semibold text-slate-600">Volume par langue vivante pour repérer rapidement les écarts.</p>
                                </div>
                                <div class="chart-meta-pill rounded-2xl px-3 py-2 text-right">
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Entrées</div>
                                    <div class="text-sm font-black text-slate-900">{{ is_array($languesComparisonChart ?? null) ? count($languesComparisonChart) : 0 }}</div>
                                </div>
                            </div>
                            <div class="chart-surface h-[260px] rounded-2xl p-4 flex items-center justify-center">
                                @if($showLangueComparison)
                                    <canvas id="languesComparisonChart"></canvas>
                                @else
                                    <p class="text-xs font-bold text-slate-400 italic">Uniquement disponible pour BEPC</p>
                                @endif
                            </div>
                        </div>
                        <div class="chart-card rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-[10px] font-black uppercase text-slate-400">Options A vs B</h2>
                                    <p class="mt-2 text-sm font-semibold text-slate-600">Lecture visuelle des effectifs par option étrangère.</p>
                                </div>
                                <div class="chart-meta-pill rounded-2xl px-3 py-2 text-right">
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Comparatif</div>
                                    <div class="text-sm font-black text-slate-900">A / B</div>
                                </div>
                            </div>
                            <div class="chart-surface h-[260px] rounded-2xl p-4 flex items-center justify-center">
                                @if($showLangueComparison)
                                    <canvas id="optionsComparisonChart"></canvas>
                                @else
                                    <p class="text-xs font-bold text-slate-400 italic">Uniquement disponible pour BEPC</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-xl overflow-hidden">
                    <div class="bg-slate-50/50 px-8 py-6 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
                        <h2 class="text-lg font-black text-slate-900 uppercase">Récapitulatif Hiérarchique</h2>
                        <div class="flex items-center gap-3">
                            <select id="tableDrenFilter" onchange="filterTable()" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 outline-none">
                                <option value="">Toutes les DREN</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}">{{ $dren }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="resetFilters()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 shadow-sm transition-colors hover:bg-slate-50 hover:text-indigo-600">
                                <i class="fas fa-undo text-[11px]"></i>
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400">DREN</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400">CISCO</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400">Correction</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400">Centre Écrit</th>
                                    <th class="px-8 py-5 text-right text-[10px] font-black uppercase text-slate-400">Candidats</th>
                                    <th class="px-8 py-5 text-right text-[10px] font-black uppercase text-slate-400">Salles</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="divide-y divide-slate-100">
                                @foreach($recapByDren as $dren)
                                    @foreach($dren['ciscos'] as $cisco)
                                        @foreach($cisco['centres_correction'] as $cc)
                                            @foreach($cc['centres_ecrit'] as $ce)
                                                <tr class="table-row-hover" data-dren="{{ $dren['nom'] }}">
                                                    <td class="px-8 py-4 font-black text-slate-900">{{ $dren['nom'] }}</td>
                                                    <td class="px-8 py-4 font-bold text-slate-600">{{ $cisco['nom'] }}</td>
                                                    <td class="px-8 py-4 text-xs font-medium text-slate-500">{{ $cc['nom'] }}</td>
                                                    <td class="px-8 py-4">
                                                        <span class="inline-flex rounded-lg bg-indigo-50 px-2 py-1 text-[10px] font-black text-indigo-700 border border-indigo-100 uppercase">
                                                            {{ $ce['nom'] }}
                                                        </span>
                                                    </td>
                                                    <td class="px-8 py-4 text-right font-black">{{ number_format($ce['total_candidats'], 0, ',', ' ') }}</td>
                                                    <td class="px-8 py-4 text-right">
                                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ $ce['total_salles'] }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-slate-50/50 px-8 py-4 border-t border-slate-200 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-[10px] font-black text-slate-400 uppercase">
                            Affichage de <span id="rowCount" class="text-slate-900">0</span> entrées
                        </div>
                        <div class="flex items-center gap-3">
                            <select id="rowsPerPage" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 outline-none">
                                <option value="10">10 lignes</option>
                                <option value="15" selected>15 lignes</option>
                                <option value="25">25 lignes</option>
                                <option value="50">50 lignes</option>
                            </select>
                            <button onclick="changePage('prev')" class="p-2 rounded-lg hover:bg-slate-200 text-slate-400 transition-colors"><i class="fas fa-chevron-left"></i></button>
                            <span class="text-xs font-black text-slate-900">Page <span id="currentPage">1</span> / <span id="totalPages">1</span></span>
                            <button onclick="changePage('next')" class="p-2 rounded-lg hover:bg-slate-200 text-slate-400 transition-colors"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Data passed from Laravel
        const ciscosByDren = @json($ciscosByDren ?? []);
        const languesChartData = @json($languesComparisonChart ?? []);
        const optionsChartData = @json($optionsComparisonChart ?? []);

        // Table Logic
        let currentPage = 1;
        let rowsPerPage = 15;
        let filteredRows = [];

        function filterTable() {
            const drenVal = document.getElementById('tableDrenFilter').value.toLowerCase();
            const allRows = Array.from(document.querySelectorAll('#tableBody tr'));
            
            filteredRows = allRows.filter(row => {
                const rowDren = row.getAttribute('data-dren').toLowerCase();
                return !drenVal || rowDren === drenVal;
            });

            allRows.forEach(row => row.style.display = 'none');
            currentPage = 1;
            updatePagination();
        }

        function updatePagination() {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage) || 1;
            document.getElementById('totalPages').textContent = totalPages;
            document.getElementById('currentPage').textContent = currentPage;
            document.getElementById('rowCount').textContent = filteredRows.length;

            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            filteredRows.slice(start, end).forEach(row => row.style.display = '');
        }

        function changePage(dir) {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage) || 1;
            if (dir === 'prev' && currentPage > 1) currentPage--;
            if (dir === 'next' && currentPage < totalPages) currentPage++;
            updatePagination();
        }

        function resetFilters() {
            const drenFilter = document.getElementById('tableDrenFilter');
            if (drenFilter) {
                drenFilter.value = '';
            }
            currentPage = 1;
            filterTable();
        }

        // Chart Drawing Logic
        function normalizeChartItems(data) {
            if (Array.isArray(data)) {
                return data.map((item) => ({
                    label: String(item.label ?? ''),
                    value: Number(item.value ?? 0),
                }));
            }

            if (data && typeof data === 'object') {
                return Object.entries(data).map(([label, value]) => ({
                    label: String(label),
                    value: Number(value ?? 0),
                }));
            }

            return [];
        }

        function drawLanguageLinesChart(canvasId, data, colors) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const items = normalizeChartItems(data);
            if (!ctx || items.length === 0) return;

            const parentWidth = canvas.parentElement?.clientWidth || 420;
            const rowHeight = 42;
            const canvasHeight = Math.max(220, (items.length * rowHeight) + 26);
            canvas.width = parentWidth;
            canvas.height = canvasHeight;

            const width = canvas.width;
            const height = canvas.height;
            const left = 14;
            const labelWidth = 76;
            const valueWidth = 74;
            const right = 14;
            const top = 16;
            const barStart = left + labelWidth;
            const barEnd = width - right - valueWidth;
            const barWidth = Math.max(40, barEnd - barStart);
            const maxVal = Math.max(...items.map((item) => item.value), 1);

            ctx.clearRect(0, 0, width, height);

            items.forEach((item, index) => {
                const rowTop = top + (index * rowHeight);
                const centerY = rowTop + 15;
                const progressWidth = (item.value / maxVal) * barWidth;

                ctx.fillStyle = '#0f172a';
                ctx.font = '800 12px system-ui, sans-serif';
                ctx.textAlign = 'left';
                ctx.fillText(item.label, left, centerY + 4);

                ctx.fillStyle = '#e2e8f0';
                ctx.beginPath();
                ctx.roundRect(barStart, centerY - 7, barWidth, 14, [999, 999, 999, 999]);
                ctx.fill();

                const gradient = ctx.createLinearGradient(barStart, 0, barStart + progressWidth, 0);
                gradient.addColorStop(0, colors[0]);
                gradient.addColorStop(1, colors[1] || colors[0]);
                ctx.fillStyle = gradient;
                ctx.beginPath();
                ctx.roundRect(barStart, centerY - 7, Math.max(progressWidth, 6), 14, [999, 999, 999, 999]);
                ctx.fill();

                ctx.fillStyle = '#475569';
                ctx.font = '700 11px system-ui, sans-serif';
                ctx.textAlign = 'right';
                ctx.fillText(item.value.toLocaleString(), width - right, centerY + 4);

                ctx.strokeStyle = '#f1f5f9';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(left, rowTop + 30);
                ctx.lineTo(width - right, rowTop + 30);
                ctx.stroke();
            });
        }

        function drawOptionTotalsChart(canvasId, data, colors) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const items = normalizeChartItems(data);
            if (!ctx || items.length === 0) return;

            const parentWidth = canvas.parentElement?.clientWidth || 420;
            const parentHeight = canvas.parentElement?.clientHeight || 260;
            canvas.width = parentWidth;
            canvas.height = parentHeight;

            const width = canvas.width;
            const height = canvas.height;
            const left = 46;
            const right = 14;
            const top = 18;
            const bottom = 58;
            const plotWidth = width - left - right;
            const plotHeight = height - top - bottom;
            const barGap = Math.max(12, plotWidth * 0.06);
            const barWidth = Math.max(52, (plotWidth - (barGap * (items.length - 1))) / Math.max(items.length, 1));
            const lineCount = 4;
            const maxVal = Math.max(...items.map((item) => item.value), 1);

            ctx.clearRect(0, 0, width, height);

            for (let i = 0; i <= lineCount; i++) {
                const y = top + (plotHeight / lineCount) * i;
                ctx.strokeStyle = '#e2e8f0';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(left, y);
                ctx.lineTo(width - right, y);
                ctx.stroke();

                const tickValue = Math.round(maxVal - ((maxVal / lineCount) * i));
                ctx.fillStyle = '#94a3b8';
                ctx.font = '700 10px system-ui, sans-serif';
                ctx.textAlign = 'right';
                ctx.fillText(tickValue.toLocaleString(), left - 8, y + 3);
            }

            items.forEach((item, i) => {
                const value = item.value;
                const x = left + i * (barWidth + barGap);
                const barHeight = maxVal > 0 ? (value / maxVal) * plotHeight : 0;
                const y = top + plotHeight - barHeight;

                const gradient = ctx.createLinearGradient(0, y, 0, top + plotHeight);
                gradient.addColorStop(0, colors[i % colors.length]);
                gradient.addColorStop(1, colors[(i + 1) % colors.length] || colors[i % colors.length]);

                ctx.fillStyle = gradient;
                ctx.beginPath();
                ctx.roundRect(x, y, barWidth, barHeight, [12, 12, 0, 0]);
                ctx.fill();

                ctx.fillStyle = '#0f172a';
                ctx.font = '800 11px system-ui, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(value.toLocaleString(), x + (barWidth / 2), y - 8);

                ctx.save();
                ctx.translate(x + (barWidth / 2), height - 18);
                ctx.rotate(-0.18);
                ctx.fillStyle = '#64748b';
                ctx.font = '800 11px system-ui, sans-serif';
                ctx.fillText(String(item.label).substring(0, 18), 0, 0);
                ctx.restore();
            });
        }

        // Init
        document.addEventListener('DOMContentLoaded', () => {
            filterTable();
            drawLanguageLinesChart('languesComparisonChart', languesChartData, ['#6366f1', '#8b5cf6']);
            drawOptionTotalsChart('optionsComparisonChart', optionsChartData, ['#10b981', '#0ea5e9']);
            
            // Sync Cisco select
            const drenSelect = document.getElementById('drenFilter');
            const ciscoSelect = document.getElementById('ciscoFilter');
            const rowsPerPageSelect = document.getElementById('rowsPerPage');
            const selectedCisco = @json($filters['cisco'] ?? '');
            const allCiscos = [...new Set(Object.values(ciscosByDren).flat())].sort((a, b) => String(a).localeCompare(String(b)));

            function fillCiscoOptions(keepValue = '') {
                const selectedDren = drenSelect.value;
                const ciscos = selectedDren ? (ciscosByDren[selectedDren] || []) : allCiscos;

                ciscoSelect.innerHTML = '<option value="">Tous</option>';
                ciscos.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c;
                    opt.textContent = c;
                    opt.selected = keepValue !== '' && String(keepValue) === String(c);
                    ciscoSelect.appendChild(opt);
                });

                if (keepValue !== '' && !ciscos.includes(keepValue)) {
                    ciscoSelect.value = '';
                }
            }

            fillCiscoOptions(selectedCisco);

            drenSelect.addEventListener('change', () => {
                fillCiscoOptions('');
            });

            if (rowsPerPageSelect) {
                rowsPerPageSelect.addEventListener('change', (event) => {
                    rowsPerPage = parseInt(event.target.value, 10) || 15;
                    currentPage = 1;
                    updatePagination();
                });
            }
        });
        window.addEventListener('resize', () => {
            drawLanguageLinesChart('languesComparisonChart', languesChartData, ['#6366f1', '#8b5cf6']);
            drawOptionTotalsChart('optionsComparisonChart', optionsChartData, ['#10b981', '#0ea5e9']);
        });
    </script>
</body>
</html>
