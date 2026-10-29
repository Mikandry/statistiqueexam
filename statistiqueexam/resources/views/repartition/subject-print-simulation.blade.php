<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tirage sujets</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; -webkit-font-smoothing: antialiased; }
        
        /* Animation de l'accordéon */
        #configPanel {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
            opacity: 0;
        }
        #configPanel.open {
            max-height: 500px; /* Valeur haute pour permettre l'expansion */
            opacity: 1;
            margin-top: 1rem;
        }

        /* Rotation de l'icône du bouton */
        .chevron-icon { transition: transform 0.3s ease; }
        .rotate-180 { transform: rotate(180deg); }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        select { 
            background-position: right 0.5rem center; 
            background-repeat: no-repeat; 
            background-size: 1.5em 1.5em; 
            padding-right: 2.5rem; 
            -webkit-appearance: none; 
            appearance: none; 
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); 
        }
    </style>

        @include('partials.head-assets')
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen antialiased">
<div class="mx-auto max-w-[1700px] p-4 lg:p-8">
    <div class="flex flex-col gap-6 md:flex-row md:items-start">
        
        <aside class="md:shrink-0 md:sticky md:top-8 z-20">
            @include('partials.sidebar')
        </aside>

        <main class="min-w-0 flex-1 space-y-6">
            @php
                $selectedType = $filters['type_examen'] ?? 'ALL';
                $cepePages = [
                    ['label' => 'Français', 'value' => $settings->cepe_pages_francais],
                    ['label' => 'Connaissances usuelles', 'value' => $settings->cepe_pages_connaissances_usuelles],
                    ['label' => 'Géographie', 'value' => $settings->cepe_pages_geographie],
                    ['label' => 'Malagasy', 'value' => $settings->cepe_pages_malagasy],
                    ['label' => 'Opération', 'value' => $settings->cepe_pages_operation],
                    ['label' => 'Problème', 'value' => $settings->cepe_pages_probleme],
                    ['label' => 'TFFMOM', 'value' => $settings->cepe_pages_tffmom],
                ];
                $bepcPages = [
                    ['label' => 'Malagasy', 'value' => $settings->bepc_pages_malagasy],
                    ['label' => 'SVT', 'value' => $settings->bepc_pages_svt],
                    ['label' => 'Français', 'value' => $settings->bepc_pages_francais],
                    ['label' => 'Anglais', 'value' => $settings->bepc_pages_anglais],
                    ['label' => 'ESP', 'value' => $settings->bepc_pages_esp],
                    ['label' => 'PC', 'value' => $settings->bepc_pages_pc],
                    ['label' => 'Math', 'value' => $settings->bepc_pages_math],
                    ['label' => 'HG', 'value' => $settings->bepc_pages_hg],
                    ['label' => 'ALL', 'value' => $settings->bepc_pages_all],
                ];
                $visiblePageConfigs = match ($selectedType) {
                    'BEPC' => [['title' => 'Paramètres BEPC', 'items' => $bepcPages, 'color' => 'blue', 'id' => 'tab-bepc']],
                    'CEPE' => [['title' => 'Paramètres CEPE', 'items' => $cepePages, 'color' => 'amber', 'id' => 'tab-cepe']],
                    default => [
                        ['title' => 'Paramètres BEPC', 'items' => $bepcPages, 'color' => 'blue', 'id' => 'tab-bepc'],
                        ['title' => 'Paramètres CEPE', 'items' => $cepePages, 'color' => 'amber', 'id' => 'tab-cepe'],
                    ],
                };
            @endphp

            <section class="rounded-3xl border border-white bg-white/60 p-6 shadow-sm backdrop-blur-md lg:p-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="flex h-2 w-2 rounded-full bg-lime-500"></span>
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-lime-600">Production & Logistique</p>
                        </div>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Simulation de Tirage</h1>
                        <p class="mt-1 text-sm text-slate-500">Planification des consommables par matière.</p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="inline-flex items-center gap-3 rounded-2xl bg-white border border-slate-200 px-5 py-3 shadow-sm">
                            <div class="text-right">
                                <p class="text-[9px] font-bold uppercase text-slate-400 tracking-wider">Capacité SORD</p>
                                <p class="text-lg font-black text-slate-900 leading-none">{{ $settings->sord_sheet_page_capacity }} <span class="text-[10px] font-medium text-slate-400">pg/f</span></p>
                            </div>
                            <div class="h-8 w-[1px] bg-slate-100"></div>
                            <svg class="w-6 h-6 text-lime-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <form method="GET" id="tirageFiltersForm" class="grid grid-cols-2 gap-3 lg:grid-cols-8 lg:items-end">
                    <div class="space-y-1.5">
                        <label class="px-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Année</label>
                        <select name="annee" class="tirage-autosubmit block w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-lime-500/10 transition-all">
                            <option value="">Toutes</option>
                            @foreach($annees as $annee)
                                <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="px-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Examen</label>
                        <select name="type_examen" class="tirage-autosubmit block w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700">
                            <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                            <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                            <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="px-1 text-[10px] font-black uppercase tracking-widest text-slate-400">DREN</label>
                        <select name="dren" class="tirage-autosubmit block w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700">
                            <option value="">Toutes</option>
                            @foreach($drens as $dren)
                                <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="px-1 text-[10px] font-black uppercase tracking-widest text-slate-400">CISCO</label>
                        <select name="cisco" class="tirage-autosubmit block w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700">
                            <option value="">Tous</option>
                            @foreach($ciscos as $cisco)
                                <option value="{{ $cisco }}" {{ $filters['cisco'] === $cisco ? 'selected' : '' }}>{{ $cisco }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="px-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Impression</label>
                        <select name="print_mode" class="tirage-autosubmit block w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700">
                            <option value="sord" {{ $printMode === 'sord' ? 'selected' : '' }}>Machine SORD I</option>
                            <option value="a4" {{ $printMode === 'a4' ? 'selected' : '' }}>Imprimante A4 recto-verso</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="px-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Matière</label>
                        <select name="subject" class="tirage-autosubmit block w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700">
                            <option value="">Toutes</option>
                            @foreach($subjectOptions as $subjectKey => $subjectLabel)
                                <option value="{{ $subjectKey }}" {{ ($filters['subject'] ?? '') === $subjectKey ? 'selected' : '' }}>{{ $subjectLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="px-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Marge SORD / salle</label>
                        <input type="number" min="0" step="1" name="sord_margin_per_room" value="{{ $filters['sord_margin_per_room'] ?? 5 }}" class="tirage-autosubmit block w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="h-10 flex-1 rounded-xl bg-slate-900 text-xs font-black uppercase tracking-widest text-white hover:bg-slate-800 transition-all active:scale-95 shadow-lg shadow-slate-200">
                            Calculer
                        </button>
                        <a href="{{ route('repartition.tirage.excel', request()->query()) }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-[10px] font-black uppercase tracking-widest text-emerald-700 hover:bg-emerald-100">
                            XLSX
                        </a>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <button onclick="toggleConfig()" class="w-full flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors group">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-slate-100 p-2 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="text-left">
                            <h2 class="text-xs font-black uppercase tracking-widest text-slate-900">Configuration des pages</h2>
                            <p class="text-[10px] text-slate-500">SORD: exemplaires = candidats + (salles × marge). A4 recto-verso: feuilles = candidats × ceil(pages / 2).</p>
                        </div>
                    </div>
                    <svg id="chevron" class="chevron-icon w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div id="configPanel" class="px-6 pb-6">
                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach($visiblePageConfigs as $config)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4">
                                <h3 class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-{{ $config['color'] }}-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-{{ $config['color'] }}-500"></span>
                                    {{ $config['title'] }}
                                </h3>
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3">
                                    @foreach($config['items'] as $item)
                                        <div class="rounded-xl bg-white px-3 py-2 shadow-sm ring-1 ring-black/5">
                                            <p class="text-[9px] font-bold text-slate-400 uppercase truncate">{{ $item['label'] }}</p>
                                            <p class="text-xs font-black text-slate-900">{{ $item['value'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if(auth()->user()?->isAdmin())
                        <div class="mt-4 flex justify-end">
                            <a href="{{ route('admin.statistics.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                                Paramètres système
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    @endif
                </div>
            </section>

            <section class="grid grid-cols-2 gap-4 md:grid-cols-5">
                @php
                    $kpis = [
                        ['label' => 'Unités DREN', 'val' => $globalStats['total_drens'], 'color' => 'slate'],
                        ['label' => 'Total Salles', 'val' => $globalStats['total_salles'], 'color' => 'blue'],
                        ['label' => 'Marge Totale', 'val' => $globalStats['total_margin_surplus'], 'color' => 'amber'],
                        ['label' => 'Total Exemplaires', 'val' => $globalStats['total_exemplaires'], 'color' => 'slate'],
                        ['label' => 'Feuilles Support', 'val' => $globalStats['total_feuilles'], 'color' => 'lime'],
                        ['label' => 'Total Impressions', 'val' => $globalStats['total_impressions'], 'color' => 'slate'],
                    ];
                @endphp
                @foreach($kpis as $kpi)
                    <div class="rounded-2xl border border-{{ $kpi['color'] }}-200 bg-white p-5 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[0.15em] text-{{ $kpi['color'] }}-500">{{ $kpi['label'] }}</p>
                        <p class="mt-2 text-2xl font-black text-{{ $kpi['color'] }}-900">{{ number_format($kpi['val'], 0, ',', ' ') }}</p>
                    </div>
                @endforeach
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-slate-50/80 border-b border-slate-200 px-6 py-4">
                    <h2 class="text-xs font-black uppercase tracking-widest text-slate-500">Synthèse par matière</h2>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-tighter text-slate-400">
                        <tr>
                            <th class="px-6 py-4">Matière</th>
                            <th class="px-6 py-4 text-right">Pages</th>
                            <th class="px-6 py-4 text-right">Salles</th>
                            <th class="px-6 py-4 text-right">Candidats</th>
                            <th class="px-6 py-4 text-right">Marge</th>
                            <th class="px-6 py-4 text-right">Exemplaires tirage</th>
                            <th class="px-6 py-4 text-right">Feuilles</th>
                            <th class="px-6 py-4 text-right">Impressions</th>
                            <th class="px-6 py-4">Découpage tirage</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @forelse($subjectTotals as $subject)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $subject['label'] }}</td>
                                <td class="px-6 py-4 text-right font-mono text-xs">{{ number_format($subject['pages'], 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black text-blue-600">{{ number_format($subject['total_room_count'], 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black text-slate-700">{{ number_format($subject['total_candidates'], 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black text-amber-600">{{ number_format($subject['total_margin_surplus'], 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black text-slate-900">{{ number_format($subject['total_exemplaires'], 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black text-lime-600">{{ number_format($subject['total_feuilles'], 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black text-slate-900">{{ number_format($subject['total_impressions'], 0, ',', ' ') }}</td>
                                <td class="px-6 py-4">
                                    @if($printMode === 'sord')
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($subject['segments'] as $segment)
                                                <span class="inline-flex items-center rounded-lg bg-lime-50 px-2.5 py-1 text-[10px] font-bold text-lime-700 ring-1 ring-inset ring-lime-600/10">
                                                    {{ $segment['pages'] }}p : {{ number_format($segment['feuilles'], 0, ',', ' ') }}f
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-400 uppercase italic">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-6 py-16 text-center text-slate-400">Aucune donnée disponible.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-900 text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <tr>
                            <th class="px-6 py-5">DREN</th>
                            <th class="px-6 py-5 text-right">Centres</th>
                            <th class="px-6 py-5 text-right">Salles</th>
                            <th class="px-6 py-5 text-right">Marge</th>
                            <th class="px-6 py-5 text-right">Exemplaires</th>
                            <th class="px-6 py-5 text-right">Feuilles</th>
                            <th class="px-6 py-5 text-right">Impressions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @forelse($drenRows as $row)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-6 py-4 font-black text-slate-900 text-xs">{{ $row['dren'] }}</td>
                                <td class="px-6 py-4 text-right font-bold text-slate-600">{{ number_format($row['total_centres'], 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black text-blue-600">{{ number_format($row['total_salles'], 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black text-amber-600">{{ number_format($row['total_margin_surplus'], 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-bold text-slate-900">{{ number_format($row['total_exemplaires'], 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black text-lime-600">{{ number_format($row['total_feuilles'], 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-bold text-slate-900">{{ number_format($row['total_impressions'], 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-10 text-center text-slate-400 italic">Aucune synthèse régionale.</td></tr>
                        @endforelse
                        @if($drenRows->isNotEmpty())
                            <tr class="bg-slate-900 text-white">
                                <td class="px-6 py-4 font-black uppercase">TOTAL</td>
                                <td class="px-6 py-4 text-right font-black">{{ number_format($drenRows->sum('total_centres'), 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black">{{ number_format($drenRows->sum('total_salles'), 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black">{{ number_format($drenRows->sum('total_margin_surplus'), 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black">{{ number_format($drenRows->sum('total_exemplaires'), 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black">{{ number_format($drenRows->sum('total_feuilles'), 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-black">{{ number_format($drenRows->sum('total_impressions'), 0, ',', ' ') }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
    // Toggle pour le panneau de configuration
    function toggleConfig() {
        const panel = document.getElementById('configPanel');
        const chevron = document.getElementById('chevron');
        panel.classList.toggle('open');
        chevron.classList.toggle('rotate-180');
    }

    // Auto-submit du formulaire
    (function () {
        const form = document.getElementById('tirageFiltersForm');
        if (!form) return;
        form.querySelectorAll('.tirage-autosubmit').forEach((field) => {
            field.addEventListener('change', () => form.submit());
        });
    }());
</script>
</body>
</html>
