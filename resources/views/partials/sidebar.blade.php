<aside class="w-full shrink-0 overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-lg backdrop-blur-sm transition-all duration-200 hover:shadow-xl md:w-72">
    <div class="border-b border-slate-200/80 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Menu Principal</div>
                <div class="text-sm font-medium text-slate-700">Service de l'Organisation des Examens</div>
            </div>
        </div>
    </div>
    
    <nav class="space-y-1 p-4">
        <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-blue-200 hover:bg-blue-50/50 hover:text-blue-700 hover:shadow-md" href="{{ route('repartition.dashboard') }}">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-blue-100 group-hover:text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </span>
            <span class="flex-1">Dashboard</span>
            <span class="text-xs text-slate-400">●</span>
        </a>

        <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-indigo-200 hover:bg-indigo-50/50 hover:text-indigo-700 hover:shadow-md" href="{{ route('repartition.stats.report') }}">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-indigo-100 group-hover:text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-5 4h6m2 0a2 2 0 002-2V7a2 2 0 00-2-2h-3m-4 0H7a2 2 0 00-2 2v12a2 2 0 002 2h2" />
                </svg>
            </span>
            <span class="flex-1">Rapport Statistique</span>
            <span class="text-xs text-slate-400">N/N-1</span>
        </a>
        
        <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-emerald-200 hover:bg-emerald-50/50 hover:text-emerald-700 hover:shadow-md" href="{{ route('bepc.repartition.create') }}">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-emerald-100 group-hover:text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </span>
            <span class="flex-1">Saisie</span>
            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Nouveau</span>
        </a>
        
        @if(auth()->user()?->isAdmin())
            <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-purple-200 hover:bg-purple-50/50 hover:text-purple-700 hover:shadow-md" href="{{ route('repartition.vacations') }}">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-purple-100 group-hover:text-purple-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
                <span class="flex-1">Vacations</span>
                <span class="text-xs text-slate-400">Planning</span>
            </a>
            <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-fuchsia-200 hover:bg-fuchsia-50/50 hover:text-fuchsia-700 hover:shadow-md" href="{{ route('vacation2026.index') }}">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-fuchsia-100 group-hover:text-fuchsia-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
                <span class="flex-1">Traitement vacation 2026</span>
                <span class="text-xs text-slate-400">Central</span>
            </a>
        @endif
        
        <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-amber-200 hover:bg-amber-50/50 hover:text-amber-700 hover:shadow-md" href="{{ route('repartition.livre.preview') }}">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-amber-100 group-hover:text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </span>
            <span class="flex-1">Livre</span>
            <span class="text-xs text-slate-400">PDF</span>
        </a>

        <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-teal-200 hover:bg-teal-50/50 hover:text-teal-700 hover:shadow-md" href="{{ route('repartition.livraison.cepe') }}">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-teal-100 group-hover:text-teal-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V7a2 2 0 00-2-2h-3V3H9v2H6a2 2 0 00-2 2v6m16 0H4m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4" />
                </svg>
            </span>
            <span class="flex-1">Livraison CEPE</span>
            <span class="text-xs text-slate-400">CISCO</span>
        </a>
        
        <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-rose-200 hover:bg-rose-50/50 hover:text-rose-700 hover:shadow-md" href="{{ route('imports.index') }}">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-rose-100 group-hover:text-rose-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                </svg>
            </span>
            <span class="flex-1">Imports</span>
            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-700">CSV</span>
        </a>

        <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-indigo-200 hover:bg-indigo-50/50 hover:text-indigo-700 hover:shadow-md" href="{{ route('decision.centre') }}">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-indigo-100 group-hover:text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21V3m0 0L5 7m4-4l4 4" />
                </svg>
            </span>
            <span class="flex-1">Decision de Centre</span>
        </a>

        @if(auth()->user()?->isAdmin())
            <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-blue-200 hover:bg-blue-50/50 hover:text-blue-700 hover:shadow-md" href="{{ route('admin.statistics.index') }}">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-blue-100 group-hover:text-blue-600">#</span>
                <span class="flex-1">Admin Statistiques</span>
            </a>
            <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-blue-200 hover:bg-blue-50/50 hover:text-blue-700 hover:shadow-md" href="{{ route('admin.users.index') }}">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-blue-100 group-hover:text-blue-600">@</span>
                <span class="flex-1">Admin Utilisateurs</span>
            </a>
            <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-blue-200 hover:bg-blue-50/50 hover:text-blue-700 hover:shadow-md" href="{{ route('admin.references.index') }}">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-blue-100 group-hover:text-blue-600">+</span>
                <span class="flex-1">Admin Référentiels</span>
            </a>
            <a class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-blue-200 hover:bg-blue-50/50 hover:text-blue-700 hover:shadow-md" href="{{ route('admin.audit-logs.index') }}">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors group-hover:bg-blue-100 group-hover:text-blue-600">#</span>
                <span class="flex-1">Historique IP</span>
            </a>
        @endif
    </nav>

    <div class="border-t border-slate-200/80 px-4 py-3">
        <div class="mb-2 text-xs text-slate-500">Connecté: <strong>{{ auth()->user()->name }}</strong></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Se déconnecter
            </button>
        </form>
    </div>
    
    <div class="border-t border-slate-200/80 bg-gradient-to-r from-slate-50 to-white px-4 py-3">
        <div class="mb-3 rounded-lg border border-blue-200/80 bg-gradient-to-r from-blue-50 to-indigo-50 px-3 py-2">
            <div class="text-xs font-semibold text-blue-700">Service de l'Organisation des Examens</div>
            <div class="mt-1 text-xs text-slate-600">Continuez, chaque saisie rapproche de l'objectif final.</div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-blue-100">
                <span class="sidebar-boost block h-full w-1/2 rounded-full bg-blue-500"></span>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Version 1.0 • Examens 2026</span>
        </div>
    </div>
</aside>
<style>
    @keyframes sidebarBoost {
        0% { transform: translateX(-80%); }
        50% { transform: translateX(80%); }
        100% { transform: translateX(-80%); }
    }
    .sidebar-boost {
        animation: sidebarBoost 3s ease-in-out infinite;
    }
</style>
