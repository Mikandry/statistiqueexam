@php
    $user = auth()->user();
    
    // Core Tailwind styling state classes
    $baseItem = 'group relative flex items-center gap-3.5 rounded-xl px-3.5 py-3 text-sm font-semibold transition-all duration-200 outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50';
    
    $activeItem = $baseItem . ' bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25 ring-1 ring-white/20';
    $idleItem   = $baseItem . ' text-slate-400 hover:text-slate-100 hover:bg-slate-800/60 hover:shadow-sm hover:translate-x-0.5';
@endphp

<div class="app-sidebar-shell" id="appSidebarShell" data-sidebar-state="collapsed">
    <aside class="app-sidebar-panel">
        
        {{-- Header & Brand --}}
        <div class="app-sidebar-header">
            <div class="app-sidebar-brand">
                <div class="app-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="app-sidebar-copy">
                    <span class="app-sidebar-kicker">SOE</span> <p>. yar
                    <h2 class="app-sidebar-title">Service de l'organisation <br> des Examens</h2>
                </div>
            </div>

            <button type="button" class="app-sidebar-toggle" id="appSidebarToggle" aria-label="Toggle sidebar" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" class="app-sidebar-toggle-icon h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>

        {{-- Hint Banner --}}
        <div class="app-sidebar-hint">
            <span class="app-sidebar-hint-dot"></span>
            <span class="app-sidebar-label">Navigation Rapide</span>
        </div>

        {{-- Main Navigation Links --}}
        <nav class="app-sidebar-nav">
            
            {{-- Dashboard --}}
            <a class="{{ request()->routeIs('repartition.dashboard') ? $activeItem : $idleItem }}" href="{{ route('repartition.dashboard') }}">
                <span class="app-sidebar-icon-wrap bg-blue-500/10 text-blue-400 group-hover:scale-110 group-hover:bg-blue-500/20">
                    <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Dashboard</span>
                    <span class="app-sidebar-link-meta">Vue d'ensemble</span>
                </span>
            </a>

            @if(! $user?->isLogistique())
                {{-- HR --}}
                <a class="{{ request()->routeIs('hr.*') ? $activeItem : $idleItem }}" href="{{ route('hr.dashboard') }}">
                    <span class="app-sidebar-icon-wrap bg-cyan-500/10 text-cyan-400 group-hover:scale-110 group-hover:bg-cyan-500/20">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m6-6a4 4 0 100-8 4 4 0 000 8zm8-4h6m-3-3v6"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Ressources humaines</span>
                        <span class="app-sidebar-link-meta">Personnel & disponibilité</span>
                    </span>
                </a>
            @endif

            {{-- Stats Report --}}
            <a class="{{ request()->routeIs('repartition.stats.report*') ? $activeItem : $idleItem }}" href="{{ route('repartition.stats.report') }}">
                <span class="app-sidebar-icon-wrap bg-indigo-500/10 text-indigo-400 group-hover:scale-110 group-hover:bg-indigo-500/20">
                    <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-5 4h6m2 0a2 2 0 002-2V7a2 2 0 00-2-2h-3m-4 0H7a2 2 0 00-2 2v12a2 2 0 002 2h2"/></svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Rapport statistique</span>
                    <span class="app-sidebar-link-meta">Comparatif N / N-1</span>
                </span>
            </a>

            @if(! $user?->isLogistique())
                {{-- Exam Results --}}
                <a class="{{ request()->routeIs('exam-results.*') ? $activeItem : $idleItem }}" href="{{ route('exam-results.index') }}">
                    <span class="app-sidebar-icon-wrap bg-emerald-500/10 text-emerald-400 group-hover:scale-110 group-hover:bg-emerald-500/20">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Résultats examens</span>
                        <span class="app-sidebar-link-meta">Publication officielle</span>
                    </span>
                </a>
            @endif

            {{-- Stats by Language --}}
            <a class="{{ request()->routeIs('repartition.options.langues.stats') ? $activeItem : $idleItem }}" href="{{ route('repartition.options.langues.stats') }}">
                <span class="app-sidebar-icon-wrap bg-fuchsia-500/10 text-fuchsia-400 group-hover:scale-110 group-hover:bg-fuchsia-500/20">
                    <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747S17.5 6.253 12 6.253z"/></svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Stats par langue</span>
                    <span class="app-sidebar-link-meta">PE/GE par option</span>
                </span>
            </a>

            {{-- Simulation Soubique --}}
            <a class="{{ request()->routeIs('repartition.simulation.soubique') ? $activeItem : $idleItem }}" href="{{ route('repartition.simulation.soubique') }}">
                <span class="app-sidebar-icon-wrap bg-amber-500/10 text-amber-400 group-hover:scale-110 group-hover:bg-amber-500/20">
                    <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-8 5h10m-7 5h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Simulation soubique</span>
                    <span class="app-sidebar-link-meta">Sujets par centre</span>
                </span>
            </a>

            {{-- Dispatching --}}
            <a class="{{ request()->routeIs('repartition.export.dispatching.preview') ? $activeItem : $idleItem }}" href="{{ route('repartition.export.dispatching.preview') }}">
                <span class="app-sidebar-icon-wrap bg-amber-500/10 text-amber-400 group-hover:scale-110 group-hover:bg-amber-500/20">
                    <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Dispatching</span>
                    <span class="app-sidebar-link-meta">Dispatching par axe</span>
                </span>
            </a>

            {{-- Tirage --}}
            <a class="{{ request()->routeIs('repartition.tirage') ? $activeItem : $idleItem }}" href="{{ route('repartition.tirage') }}">
                <span class="app-sidebar-icon-wrap bg-teal-500/10 text-teal-400 group-hover:scale-110 group-hover:bg-teal-500/20">
                    <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2m12 7V2M4 13h16M6 22h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z"/></svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Tirage</span>
                    <span class="app-sidebar-link-meta">Simulation par matière</span>
                </span>
            </a>

            @if($user?->canAccessLogistics())
                {{-- Logistics Section --}}
                <div class="app-sidebar-section-label app-sidebar-label">Logistique</div>

                <a class="{{ request()->routeIs('inventory.*') ? $activeItem : $idleItem }}" href="{{ route('inventory.index') }}">
                    <span class="app-sidebar-icon-wrap bg-emerald-500/10 text-emerald-400 group-hover:scale-110 group-hover:bg-emerald-500/20">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Comptabilité matières</span>
                        <span class="app-sidebar-link-meta">Stock et approvisionnement</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('repartition.logistique.bepc-copies*') ? $activeItem : $idleItem }}" href="{{ route('repartition.logistique.bepc-copies') }}">
                    <span class="app-sidebar-icon-wrap bg-sky-500/10 text-sky-400 group-hover:scale-110 group-hover:bg-sky-500/20">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-8-6h16M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Feuilles BEPC</span>
                        <span class="app-sidebar-link-meta">Logistique CISCO</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('repartition.livraison.cepe*') ? $activeItem : $idleItem }}" href="{{ route('repartition.livraison.cepe') }}">
                    <span class="app-sidebar-icon-wrap bg-cyan-500/10 text-cyan-400 group-hover:scale-110 group-hover:bg-cyan-500/20">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V7a2 2 0 00-2-2h-3V3H9v2H6a2 2 0 00-2 2v6m16 0H4m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Livraison CEPE</span>
                        <span class="app-sidebar-link-meta">Bordereaux et suivi</span>
                    </span>
                </a>
            @endif

            @if(! $user?->isLogistique())
                {{-- General Entry Section --}}
                <a class="{{ request()->routeIs('bepc.repartition.create') ? $activeItem : $idleItem }}" href="{{ route('bepc.repartition.create') }}">
                    <span class="app-sidebar-icon-wrap bg-emerald-500/10 text-emerald-400 group-hover:scale-110 group-hover:bg-emerald-500/20">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Saisie</span>
                        <span class="app-sidebar-link-meta">Création et répartition</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('repartition.saisie.recap') ? $activeItem : $idleItem }}" href="{{ route('repartition.saisie.recap') }}">
                    <span class="app-sidebar-icon-wrap bg-blue-500/10 text-blue-400 group-hover:scale-110 group-hover:bg-blue-500/20">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m4 2v-6m4 6V7M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Récap saisie</span>
                        <span class="app-sidebar-link-meta">DREN complètes</span>
                    </span>
                </a>
            @endif

            @if(! $user?->isLogistique())
                <a class="{{ request()->routeIs('cap-cae-results.*') ? $activeItem : $idleItem }}" href="{{ route('cap-cae-results.index') }}">
                    <span class="app-sidebar-icon-wrap bg-rose-500/10 text-rose-400 group-hover:scale-110 group-hover:bg-rose-500/20">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-9 4h12M6 13h12M6 17h8M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Résultats CAP / CAE</span>
                        <span class="app-sidebar-link-meta">Admis et diplômes</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('repartition.livre.*') ? $activeItem : $idleItem }}" href="{{ route('repartition.livre.preview') }}">
                    <span class="app-sidebar-icon-wrap bg-amber-500/10 text-amber-400 group-hover:scale-110 group-hover:bg-amber-500/20">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Livre</span>
                        <span class="app-sidebar-link-meta">Aperçu et export</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('imports.*') ? $activeItem : $idleItem }}" href="{{ route('imports.index') }}">
                    <span class="app-sidebar-icon-wrap bg-purple-500/10 text-purple-400 group-hover:scale-110 group-hover:bg-purple-500/20">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Imports</span>
                        <span class="app-sidebar-link-meta">Référentiels CSV</span>
                    </span>
                </a>
            @endif

            <a class="{{ request()->routeIs('decision.centre') ? $activeItem : $idleItem }}" href="{{ route('decision.centre') }}">
                <span class="app-sidebar-icon-wrap bg-sky-500/10 text-sky-400 group-hover:scale-110 group-hover:bg-sky-500/20">
                    <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21V3m0 0L5 7m4-4l4 4"/></svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Décision de centre</span>
                    <span class="app-sidebar-link-meta">Édition centralisée</span>
                </span>
            </a>

            <a class="{{ request()->routeIs('repartition.groupes') ? $activeItem : $idleItem }}" href="{{ route('repartition.groupes') }}">
                <span class="app-sidebar-icon-wrap bg-orange-500/10 text-orange-400 group-hover:scale-110 group-hover:bg-orange-500/20">
                    <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5V4H2v16h5m10 0v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4m10 0H7m10-12h.01M7 8h8m-8 4h8"/></svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Répartition groupes</span>
                    <span class="app-sidebar-link-meta">Équilibrage DREN / CISCO</span>
                </span>
            </a>

            @if($user?->isAdmin())
                {{-- Administration Section --}}
                <div class="app-sidebar-section-label app-sidebar-label">Administration</div>

                <a class="{{ request()->routeIs('admin.hr.*') ? $activeItem : $idleItem }}" href="{{ route('admin.hr.settings') }}">
                    <span class="app-sidebar-icon-wrap bg-slate-800 text-slate-300 group-hover:scale-110">
                        <span class="text-xs font-black">RH</span>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Paramètres RH</span>
                        <span class="app-sidebar-link-meta">En-têtes et lettres</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('repartition.vacations') ? $activeItem : $idleItem }}" href="{{ route('repartition.vacations') }}">
                    <span class="app-sidebar-icon-wrap bg-violet-500/10 text-violet-400 group-hover:scale-110 group-hover:bg-violet-500/20">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Vacations</span>
                        <span class="app-sidebar-link-meta">Estimations et planning</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('vacation2026.*') ? $activeItem : $idleItem }}" href="{{ route('vacation2026.index') }}">
                    <span class="app-sidebar-icon-wrap bg-fuchsia-500/10 text-fuchsia-400 group-hover:scale-110 group-hover:bg-fuchsia-500/20">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Vacation 2026</span>
                        <span class="app-sidebar-link-meta">Traitement central</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('admin.statistics.*') ? $activeItem : $idleItem }}" href="{{ route('admin.statistics.index') }}">
                    <span class="app-sidebar-icon-wrap bg-slate-800 text-slate-300 group-hover:scale-110">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Statistiques</span>
                        <span class="app-sidebar-link-meta">Indicateurs système</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('admin.users.*') ? $activeItem : $idleItem }}" href="{{ route('admin.users.index') }}">
                    <span class="app-sidebar-icon-wrap bg-slate-800 text-slate-300 group-hover:scale-110">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Utilisateurs</span>
                        <span class="app-sidebar-link-meta">Accès et rôles</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('admin.references.*') ? $activeItem : $idleItem }}" href="{{ route('admin.references.index') }}">
                    <span class="app-sidebar-icon-wrap bg-slate-800 text-slate-300 group-hover:scale-110">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Référentiels</span>
                        <span class="app-sidebar-link-meta">Tables de base</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('admin.audit-logs.*') ? $activeItem : $idleItem }}" href="{{ route('admin.audit-logs.index') }}">
                    <span class="app-sidebar-icon-wrap bg-slate-800 text-slate-300 group-hover:scale-110">
                        <svg class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Historique IP</span>
                        <span class="app-sidebar-link-meta">Journal d'accès</span>
                    </span>
                </a>
            @endif
        </nav>

        {{-- Footer User Controls --}}
        <div class="app-sidebar-footer">
            <div class="app-sidebar-user">
                <div class="app-sidebar-avatar">{{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}</div>
                <div class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title text-slate-200">{{ $user?->name ?? 'Utilisateur' }}</span>
                    <span class="app-sidebar-link-meta text-emerald-400 font-medium inline-flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Session active
                    </span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-sidebar-logout">
                    <span class="app-sidebar-icon-wrap bg-rose-500/10 text-rose-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1" />
                        </svg>
                    </span>
                    <span class="app-sidebar-label">Se déconnecter</span>
                </button>
            </form>
        </div>
    </aside>
</div>

<style>
    .app-sidebar-shell {
        width: 100%;
        flex-shrink: 0;
        max-height: 100vh;
    }

    .app-sidebar-panel {
        position: relative;
        display: flex;
        min-height: 0;
        height: 100%;
        overflow: hidden;
        flex-direction: column;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 24px;
        background: 
            radial-gradient(circle at top right, rgba(37, 99, 235, 0.15), transparent 45%),
            linear-gradient(180deg, #0f172a 0%, #090d16 100%);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(20px);
    }

    .app-sidebar-header,
    .app-sidebar-footer,
    .app-sidebar-hint,
    .app-sidebar-nav {
        position: relative;
        z-index: 1;
    }

    .app-sidebar-header {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1.25rem 1rem 0.85rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .app-sidebar-brand,
    .app-sidebar-user,
    .app-sidebar-logout {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .app-sidebar-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 14px;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #4f46e5);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
        flex-shrink: 0;
    }

    .app-sidebar-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        font-weight: 800;
        border-radius: 12px;
        color: #38bdf8;
        background: rgba(56, 189, 248, 0.1);
        border: 1px solid rgba(56, 189, 248, 0.2);
        flex-shrink: 0;
    }

    .app-sidebar-icon-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 10px;
        flex-shrink: 0;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .app-sidebar-copy {
        min-width: 0;
    }

    .app-sidebar-kicker {
        display: block;
        margin-bottom: 0.1rem;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #38bdf8;
    }

    .app-sidebar-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #f8fafc;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .app-sidebar-subtitle,
    .app-sidebar-link-meta {
        margin: 0.15rem 0 0;
        font-size: 0.73rem;
        color: #64748b;
        line-height: 1.2;
    }

    .app-sidebar-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.03);
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .app-sidebar-toggle:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .app-sidebar-hint {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        gap: 0.5rem;
        margin: 0.85rem 0.85rem 0.4rem;
        padding: 0.5rem 0.75rem;
        border: 1px solid rgba(56, 189, 248, 0.15);
        border-radius: 10px;
        background: rgba(56, 189, 248, 0.05);
        font-size: 0.7rem;
        font-weight: 700;
        color: #38bdf8;
    }

    .app-sidebar-hint-dot {
        width: 0.45rem;
        height: 0.45rem;
        border-radius: 999px;
        background: #38bdf8;
        box-shadow: 0 0 10px #38bdf8;
        flex-shrink: 0;
    }

    .app-sidebar-nav {
        display: flex;
        min-height: 0;
        flex: 1;
        flex-direction: column;
        gap: 0.4rem;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 0.5rem 0.75rem 1rem;
        scrollbar-color: #334155 transparent;
        scrollbar-width: thin;
    }

    .app-sidebar-nav::-webkit-scrollbar {
        width: 5px;
    }

    .app-sidebar-nav::-webkit-scrollbar-track {
        background: transparent;
    }

    .app-sidebar-nav::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #334155;
    }

    .app-sidebar-copy-block {
        display: flex;
        min-width: 0;
        flex: 1;
        flex-direction: column;
        transition: opacity 0.2s ease, width 0.2s ease;
    }

    .app-sidebar-link-title {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1.25;
    }

    .app-sidebar-section-label {
        margin: 1.2rem 0 0.3rem;
        padding: 0 0.5rem;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #475569;
    }

    .app-sidebar-footer {
        flex-shrink: 0;
        padding: 0.85rem;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        background: rgba(15, 23, 42, 0.4);
    }

    .app-sidebar-user {
        margin-bottom: 0.5rem;
        padding: 0.6rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.02);
    }

    .app-sidebar-logout {
        width: 100%;
        justify-content: flex-start;
        padding: 0.65rem 0.75rem;
        border: 1px solid rgba(244, 63, 94, 0.15);
        border-radius: 12px;
        background: rgba(244, 63, 94, 0.05);
        font-size: 0.85rem;
        font-weight: 600;
        color: #fb7185;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .app-sidebar-logout:hover {
        border-color: rgba(244, 63, 94, 0.3);
        background: rgba(244, 63, 94, 0.12);
        color: #fff;
    }

    /* Desktop Collapsible Sidebar Animations */
    @media (min-width: 1024px) {
        .app-sidebar-shell {
            position: sticky;
            top: 1rem;
            height: calc(100vh - 2rem);
            width: var(--sidebar-width, 18.5rem);
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            align-self: flex-start;
            z-index: 30;
        }

        .app-sidebar-shell[data-sidebar-state="collapsed"] {
            --sidebar-width: 5.5rem;
        }

        .app-sidebar-shell[data-sidebar-state="expanded"],
        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within {
            --sidebar-width: 18.5rem;
        }

        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-kicker,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-title,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-subtitle,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-label,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-copy-block,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-section-label {
            opacity: 0;
            width: 0;
            max-width: 0;
            overflow: hidden;
            pointer-events: none;
            white-space: nowrap;
        }

        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover .app-sidebar-kicker,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover .app-sidebar-title,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover .app-sidebar-subtitle,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover .app-sidebar-label,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover .app-sidebar-copy-block,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover .app-sidebar-section-label,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within .app-sidebar-kicker,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within .app-sidebar-title,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within .app-sidebar-subtitle,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within .app-sidebar-label,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within .app-sidebar-copy-block,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within .app-sidebar-section-label {
            opacity: 1;
            width: auto;
            max-width: 100%;
            pointer-events: auto;
        }

        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-toggle-icon {
            transform: rotate(180deg);
        }

        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover .app-sidebar-toggle-icon,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within .app-sidebar-toggle-icon {
            transform: rotate(0deg);
        }
    }
</style>