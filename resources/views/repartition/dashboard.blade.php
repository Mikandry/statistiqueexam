<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service de l'Organisation des Examens</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(226, 232, 240, 0.8); }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        .table-row-hover:hover { background-color: rgba(248, 250, 252, 1); transform: scale(1.002); transition: all 0.2s ease; }
    </style>
</head>

<body class="h-full antialiased text-slate-900">
    <div class="flex min-h-screen">
        @include('partials.sidebar')

        <main class="flex-1 min-w-0 overflow-auto custom-scrollbar p-4 lg:p-8">
            <div class="max-w-[1600px] mx-auto space-y-8">
                
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <nav class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">
                            <span>Examens</span>
                            <i class="fas fa-chevron-right text-[10px]"></i>
                            <span class="text-indigo-600">Tableau de bord</span>
                        </nav>
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 lg:text-4xl">Statistiques Globales</h1>
                        <p class="mt-1 text-slate-500 font-medium">Pilotage hiérarchique et suivi de la répartition des examens.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-slate-200 transition-all hover:bg-slate-800 hover:-translate-y-0.5" href="{{ route('bepc.repartition.create') }}">
                            <i class="fas fa-plus-circle text-slate-400"></i> Saisie
                        </a>
                        <div class="h-10 w-[1px] bg-slate-200 mx-1 hidden sm:block"></div>
                        <a class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-300" href="{{ route('repartition.export.excel', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">
                            <i class="fas fa-file-excel text-emerald-600"></i> Excel
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50" href="{{ route('repartition.export.dispatching.preview', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">
                            <i class="fas fa-eye text-indigo-500"></i> Dispatching
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700 transition-all hover:bg-rose-100" href="{{ route('repartition.livre.pdf', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                        @if(auth()->user()?->isAdmin())
                            <a class="inline-flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-3 text-sm font-bold text-amber-700 transition-all hover:bg-amber-100" href="{{ route('repartition.vacations', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">
                                <i class="fas fa-clock"></i> Vacations
                            </a>
                        @endif
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-5 items-end">
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Année Académique</label>
                            <select name="annee" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                                <option value="">Toutes</option>
                                @foreach($annees as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Type d'Examen</label>
                            <select name="type_examen" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none">
                                <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous les examens</option>
                                <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">DREN / Région</label>
                            <select name="dren" id="drenFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none">
                                <option value="">Toutes les DREN</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">CISCO / District</label>
                            <select name="cisco" id="ciscoFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none">
                                <option value="">Tous les CISCO</option>
                                @foreach($ciscos ?? [] as $cisco)
                                    <option value="{{ $cisco }}" {{ request('cisco') == $cisco ? 'selected' : '' }}>{{ $cisco }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-100 transition-all hover:bg-indigo-700 active:scale-95">
                            <i class="fas fa-sync-alt"></i> Appliquer
                        </button>
                    </form>

                    <div class="mt-6 flex flex-wrap items-center gap-2 pt-4 border-t border-slate-100">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mr-2">Filtres Actifs:</span>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 border border-slate-200">
                                Année: {{ $filters['annee'] ?: 'Toutes' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 border border-indigo-100">
                                Examen: {{ $filters['type_examen'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center justify-between mb-4">
                            <div class="rounded-2xl bg-indigo-50 p-3 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <span class="text-[10px] font-black text-emerald-500 bg-emerald-50 px-2 py-1 rounded-lg">+ Global</span>
                        </div>
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Candidats</p>
                        <h3 class="text-4xl font-black text-slate-900 mt-1 tracking-tight">{{ number_format($globalStats['total_candidats'], 0, ',', ' ') }}</h3>
                    </div>

                    <div class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center justify-between mb-4">
                            <div class="rounded-2xl bg-violet-50 p-3 text-violet-600 group-hover:bg-violet-600 group-hover:text-white transition-colors">
                                <i class="fas fa-door-open text-xl"></i>
                            </div>
                        </div>
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Salles de classe</p>
                        <h3 class="text-4xl font-black text-slate-900 mt-1 tracking-tight">{{ number_format($globalStats['total_salles'], 0, ',', ' ') }}</h3>
                    </div>

                    <div class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center justify-between mb-4">
                            <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                <i class="fas fa-map-marker-alt text-xl"></i>
                            </div>
                        </div>
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Centres d'écrit</p>
                        <h3 class="text-4xl font-black text-slate-900 mt-1 tracking-tight">{{ number_format($globalStats['total_centres_ecrit'], 0, ',', ' ') }}</h3>
                        <div class="mt-4 flex gap-4 text-xs font-bold text-slate-400">
                            <span>{{ $globalStats['total_drens'] }} DREN</span>
                            <span>{{ $globalStats['total_ciscos'] }} CISCO</span>
                        </div>
                    </div>

                    <div class="relative overflow-hidden rounded-3xl border border-slate-900 bg-slate-900 p-6 shadow-xl text-white">
                        <div class="relative z-10">
                            <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4">Saisie des centres</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-black text-indigo-400">{{ number_format($centresSaisieStats['saisis'], 0, ',', ' ') }}</span>
                                <span class="text-slate-500 font-bold">/ {{ number_format($centresSaisieStats['total'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full bg-indigo-500 rounded-full" style="width: {{ ($centresSaisieStats['saisis'] / max($centresSaisieStats['total'], 1)) * 100 }}%"></div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2 text-[10px] font-black uppercase">
                                <div class="text-indigo-300">BEPC: {{ $centresSaisieStats['by_type']['BEPC']['saisis'] ?? 0 }}</div>
                                <div class="text-emerald-300">CEPE: {{ $centresSaisieStats['by_type']['CEPE']['saisis'] ?? 0 }}</div>
                            </div>
                        </div>
                        <i class="fas fa-check-circle absolute -bottom-4 -right-4 text-8xl text-slate-800 opacity-50"></i>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 flex items-center gap-3 text-lg font-extrabold text-slate-900">
                            <span class="h-8 w-1.5 rounded-full bg-indigo-600"></span>
                            Langues & Options
                        </h2>
                        <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                            @forelse($totalsByLangue as $label => $value)
                                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4 transition-all hover:bg-slate-100 group">
                                    <span class="font-bold text-slate-700 group-hover:text-indigo-600">{{ $label }}</span>
                                    <span class="rounded-xl bg-white px-3 py-1 text-sm font-black text-slate-900 shadow-sm border border-slate-200">
                                        {{ number_format($value, 0, ',', ' ') }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-center py-8 text-slate-400 font-medium italic">Aucune donnée</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="mb-6 text-sm font-black uppercase tracking-widest text-slate-400">Comparatif Langues (BEPC)</h2>
                            <div class="h-[250px] flex items-center justify-center">
                                @if($showLangueComparison)
                                    <canvas id="languesComparisonChart"></canvas>
                                @else
                                    <p class="text-xs font-bold text-slate-400 bg-slate-50 p-4 rounded-xl text-center">Disponible pour BEPC uniquement</p>
                                @endif
                            </div>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="mb-6 text-sm font-black uppercase tracking-widest text-slate-400">Options A vs B</h2>
                            <div class="h-[250px] flex items-center justify-center">
                                @if($showLangueComparison)
                                    <canvas id="optionsComparisonChart"></canvas>
                                @else
                                    <p class="text-xs font-bold text-slate-400 bg-slate-50 p-4 rounded-xl text-center">Disponible pour BEPC uniquement</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-xl overflow-hidden">
                    <div class="bg-slate-50 px-8 py-6 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Récapitulatif Hiérarchique</h2>
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <select id="tableDrenFilter" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-500/10">
                                <option value="">Toutes les DREN</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}">{{ $dren }}</option>
                                @endforeach
                            </select>
                            <button onclick="resetFilters()" class="rounded-xl bg-white border border-slate-200 px-4 py-2 text-xs font-bold text-slate-500 hover:text-indigo-600 hover:bg-slate-50 transition-colors">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table id="hierarchicalTable" class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200">DREN</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200">CISCO</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200">Correction</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200">Centre Écrit</th>
                                    <th class="px-8 py-5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200">Candidats</th>
                                    <th class="px-8 py-5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200">Salles</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="divide-y divide-slate-100">
                                @forelse($recapByDren as $dren)
                                    @foreach($dren['ciscos'] as $cisco)
                                        @foreach($cisco['centres_correction'] as $cc)
                                            @foreach($cc['centres_ecrit'] as $ce)
                                                <tr class="table-row-hover" data-dren="{{ $dren['nom'] }}" data-cisco="{{ $cisco['nom'] }}">
                                                    <td class="px-8 py-5">
                                                        <span class="font-extrabold text-slate-900">{{ $dren['nom'] }}</span>
                                                    </td>
                                                    <td class="px-8 py-5">
                                                        <span class="font-bold text-slate-600">{{ $cisco['nom'] }}</span>
                                                    </td>
                                                    <td class="px-8 py-5 text-sm text-slate-500">{{ $cc['nom'] }}</td>
                                                    <td class="px-8 py-5">
                                                        <div class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-2 py-1 text-xs font-bold text-indigo-700">
                                                            <i class="fas fa-school text-[10px] text-indigo-400"></i>
                                                            {{ $ce['nom'] }}
                                                        </div>
                                                    </td>
                                                    <td class="px-8 py-5 text-right">
                                                        <span class="font-black text-slate-900">{{ number_format($ce['total_candidats'], 0, ',', ' ') }}</span>
                                                    </td>
                                                    <td class="px-8 py-5 text-right">
                                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                                                            {{ number_format($ce['total_salles'], 0, ',', ' ') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-8 py-20 text-center">
                                            <div class="flex flex-col items-center">
                                                <i class="fas fa-folder-open text-5xl text-slate-200 mb-4"></i>
                                                <p class="text-slate-400 font-bold">Aucune donnée correspondante</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-slate-50/50 px-8 py-6 border-t border-slate-200">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                            <div class="text-xs font-bold text-slate-500">
                                Affichage de <span id="rowCount" class="text-slate-900">0</span> entrées filtrées
                            </div>
                            
                            <div class="flex items-center gap-1 bg-white border border-slate-200 p-1 rounded-2xl shadow-sm">
                                <button onclick="changePage('prev')" class="h-10 w-10 flex items-center justify-center rounded-xl hover:bg-slate-50 text-slate-600 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <div class="px-4 text-xs font-black text-slate-900">
                                    Page <span id="currentPage">1</span> sur <span id="totalPages">1</span>
                                </div>
                                <button onclick="changePage('next')" class="h-10 w-10 flex items-center justify-center rounded-xl hover:bg-slate-50 text-slate-600 transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>

                            <select id="rowsPerPage" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                                <option value="10">10 lignes</option>
                                <option value="25">25 lignes</option>
                                <option value="50">50 lignes</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let currentPage = 1;
        let rowsPerPage = 10;
        let filteredRows = [];
        const languesChartData = @json($languesComparisonChart ?? []);
        const optionsChartData = @json($optionsComparisonChart ?? []);
        const ciscosByDren = @json($ciscosByDren ?? []);

        function initPagination() {
            const rows = Array.from(document.querySelectorAll('#tableBody tr:not(.empty-row)'));
            filteredRows = rows;
            updatePagination();
        }

        function filterTable() {
            const drenFilter = document.getElementById('tableDrenFilter').value.toLowerCase();
            const rows = document.querySelectorAll('#tableBody tr');
            
            filteredRows = [];
            rows.forEach(row => {
                const dren = row.getAttribute('data-dren')?.toLowerCase() || '';
                const match = !drenFilter || dren.includes(drenFilter);
                
                if (match) {
                    filteredRows.push(row);
                } else {
                    row.style.display = 'none';
                }
            });
            
            currentPage = 1;
            updatePagination();
        }

        function updatePagination() {
            const total = filteredRows.length;
            const totalPages = Math.ceil(total / rowsPerPage);
            
            document.getElementById('rowCount').textContent = total;
            document.getElementById('totalPages').textContent = totalPages || 1;
            document.getElementById('currentPage').textContent = currentPage;

            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            document.querySelectorAll('#tableBody tr').forEach(row => row.style.display = 'none');
            filteredRows.slice(start, end).forEach(row => row.style.display = '');
        }

        function changePage(direction) {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            if (direction === 'prev' && currentPage > 1) currentPage--;
            if (direction === 'next' && currentPage < totalPages) currentPage++;
            updatePagination();
        }

        function resetFilters() {
            document.getElementById('tableDrenFilter').value = '';
            filterTable();
        }

        function syncCiscoFilterOptions() {
            const drenSelect = document.getElementById('drenFilter');
            const ciscoSelect = document.getElementById('ciscoFilter');
            if (!drenSelect || !ciscoSelect) return;

            const selectedDren = drenSelect.value || '';
            const selectedCisco = ciscoSelect.value || '';
            const ciscos = selectedDren ? (ciscosByDren[selectedDren] || []) : Object.values(ciscosByDren).flat();
            const uniqueCiscos = [...new Set(ciscos)].sort((a, b) => a.localeCompare(b, 'fr'));

            ciscoSelect.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Tous les CISCO';
            ciscoSelect.appendChild(defaultOption);

            uniqueCiscos.forEach((cisco) => {
                const option = document.createElement('option');
                option.value = cisco;
                option.textContent = cisco;
                if (cisco === selectedCisco) {
                    option.selected = true;
                }
                ciscoSelect.appendChild(option);
            });

            if (selectedCisco && !uniqueCiscos.includes(selectedCisco)) {
                ciscoSelect.value = '';
            }
        }

        function drawBarChart(canvasId, chartData, config = {}) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            const width = canvas.parentElement?.clientWidth || canvas.clientWidth || 420;
            const height = canvas.parentElement?.clientHeight || 250;
            canvas.width = width;
            canvas.height = height;
            ctx.clearRect(0, 0, width, height);

            if (!Array.isArray(chartData) || chartData.length === 0) {
                ctx.fillStyle = '#94a3b8';
                ctx.font = '600 12px sans-serif';
                ctx.fillText(config.emptyMessage || 'Aucune donnée', 16, 24);
                return;
            }

            const left = 24;
            const right = 12;
            const top = 20;
            const bottom = 56;
            const plotWidth = width - left - right;
            const plotHeight = height - top - bottom;
            const gap = 12;
            const barWidth = Math.max(20, (plotWidth - ((chartData.length - 1) * gap)) / chartData.length);
            const max = Math.max(...chartData.map((d) => Number(d.value) || 0), 1);
            const colors = config.colors || ['#4f46e5', '#f59e0b', '#10b981', '#0ea5e9', '#8b5cf6'];

            chartData.forEach((item, i) => {
                const value = Number(item.value) || 0;
                const x = left + i * (barWidth + gap);
                const h = (value / max) * plotHeight;
                const y = top + (plotHeight - h);

                ctx.fillStyle = colors[i % colors.length];
                ctx.fillRect(x, y, barWidth, h);

                ctx.fillStyle = '#0f172a';
                ctx.font = '700 11px sans-serif';
                ctx.fillText(String(value), x, y - 6);

                ctx.save();
                ctx.translate(x + (barWidth / 2), height - 14);
                ctx.rotate(-0.35);
                ctx.fillStyle = '#475569';
                ctx.font = '600 10px sans-serif';
                ctx.fillText(String(item.label || '').substring(0, 18), 0, 0);
                ctx.restore();
            });
        }

        function drawComparativeCharts() {
            drawBarChart('languesComparisonChart', languesChartData, {
                emptyMessage: 'Aucune langue à comparer',
                colors: ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b'],
            });
            drawBarChart('optionsComparisonChart', optionsChartData, {
                emptyMessage: 'Aucune option à comparer',
                colors: ['#2563eb', '#d97706'],
            });
        }

        document.getElementById('tableDrenFilter').addEventListener('change', filterTable);
        document.getElementById('drenFilter')?.addEventListener('change', syncCiscoFilterOptions);
        document.getElementById('rowsPerPage').addEventListener('change', (e) => {
            rowsPerPage = parseInt(e.target.value);
            currentPage = 1;
            updatePagination();
        });

        document.addEventListener('DOMContentLoaded', () => {
            syncCiscoFilterOptions();
            initPagination();
            drawComparativeCharts();
        });
        window.addEventListener('resize', drawComparativeCharts);
    </script>
</body>
</html>
