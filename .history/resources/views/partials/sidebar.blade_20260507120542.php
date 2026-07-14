@php
    $user = auth()->user();
    $activeItem = 'app-sidebar-item-active group relative flex items-center gap-3 overflow-hidden rounded-2xl border border-slate-900 bg-slate-900/95 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition-all duration-200';
    $idleItem = 'app-sidebar-item group relative flex items-center gap-3 overflow-hidden rounded-2xl border border-slate-200/80 bg-white/88 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:bg-white hover:shadow-md';
@endphp

<div class="app-sidebar-shell" id="appSidebarShell" data-sidebar-state="collapsed">
    <aside class="app-sidebar-panel">
        <div class="app-sidebar-header">
            <div class="app-sidebar-brand">
                <div class="app-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="app-sidebar-copy">
                    <p class="app-sidebar-kicker">SOE 2026</p>
                    <h2 class="app-sidebar-title">Service des Examens</h2>
                    <p class="app-sidebar-subtitle">Navigation rapide et outils de production.</p>
                </div>
            </div>

            <button type="button" class="app-sidebar-toggle" id="appSidebarToggle" aria-label="Afficher ou réduire le menu latéral" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" class="app-sidebar-toggle-icon h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <div class="app-sidebar-hint">
            <span class="app-sidebar-hint-dot"></span>
            <span class="app-sidebar-label">Survolez ou utilisez le bouton pour ouvrir</span>
        </div>

        <nav class="app-sidebar-nav">
            <a class="{{ request()->routeIs('repartition.dashboard') ? $activeItem : $idleItem }}" href="{{ route('repartition.dashboard') }}">
                <span class="app-sidebar-icon-wrap bg-blue-100 text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Dashboard</span>
                    <span class="app-sidebar-link-meta">Vue d'ensemble</span>
                </span>
            </a>

            <a class="{{ request()->routeIs('repartition.stats.report*') ? $activeItem : $idleItem }}" href="{{ route('repartition.stats.report') }}">
                <span class="app-sidebar-icon-wrap bg-indigo-100 text-indigo-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-5 4h6m2 0a2 2 0 002-2V7a2 2 0 00-2-2h-3m-4 0H7a2 2 0 00-2 2v12a2 2 0 002 2h2" />
                    </svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Rapport statistique</span>
                    <span class="app-sidebar-link-meta">Comparatif N / N-1</span>
                </span>
            </a>

            <a class="{{ request()->routeIs('repartition.simulation.soubique') ? $activeItem : $idleItem }}" href="{{ route('repartition.simulation.soubique') }}">
                <span class="app-sidebar-icon-wrap bg-amber-100 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-8 5h10m-7 5h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    </svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Simulation soubique</span>
                    <span class="app-sidebar-link-meta">Sujets par centre</span>
                </span>
            </a>
            <a class="{{ request()->routeIs('repartition.export.dispatching.preview') ? $activeItem : $idleItem }}" href="{{ route('repartition.export.dispatching.preview') }}">
                <span class="app-sidebar-icon-wrap bg-amber-100 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 17h12M5 11h14M7 7h10M5 14h14M8 20h8M8 4h8M4 8h16M4 16h16M4 12h16M4 6h16M4 18h16M4 10h16M4 14h16" />
            </svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Dispatching</span>
                    <span class="app-sidebar-link-meta">Dispatching par axe</span>
                </span>
            </a>

            <a class="{{ request()->routeIs('repartition.tirage') ? $activeItem : $idleItem }}" href="{{ route('repartition.tirage') }}">
                <span class="app-sidebar-icon-wrap bg-lime-100 text-lime-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2m12 7V2M4 13h16M6 22h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                    </svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Tirage</span>
                    <span class="app-sidebar-link-meta">Simulation par matière</span>
                </span>
            </a>

            @if($user?->canAccessLogistics())
                <a class="{{ request()->routeIs('repartition.logistique.bepc-copies*') ? $activeItem : $idleItem }}" href="{{ route('repartition.logistique.bepc-copies') }}">
                    <span class="app-sidebar-icon-wrap bg-cyan-100 text-cyan-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-8-6h16M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                        </svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Feuilles BEPC</span>
                        <span class="app-sidebar-link-meta">Logistique CISCO</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('repartition.livraison.cepe*') ? $activeItem : $idleItem }}" href="{{ route('repartition.livraison.cepe') }}">
                    <span class="app-sidebar-icon-wrap bg-teal-100 text-teal-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V7a2 2 0 00-2-2h-3V3H9v2H6a2 2 0 00-2 2v6m16 0H4m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4" />
                        </svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Livraison CEPE</span>
                        <span class="app-sidebar-link-meta">Bordereaux et suivi</span>
                    </span>
                </a>
            @endif

            @if(! $user?->isLogistique())
                <a class="{{ request()->routeIs('bepc.repartition.create') ? $activeItem : $idleItem }}" href="{{ route('bepc.repartition.create') }}">
                    <span class="app-sidebar-icon-wrap bg-emerald-100 text-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Saisie</span>
                        <span class="app-sidebar-link-meta">Création et répartition</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('repartition.saisie.recap') ? $activeItem : $idleItem }}" href="{{ route('repartition.saisie.recap') }}">
                    <span class="app-sidebar-icon-wrap bg-sky-100 text-sky-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m4 2v-6m4 6V7M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Récap saisie</span>
                        <span class="app-sidebar-link-meta">DREN complètes</span>
                    </span>
                </a>
            @endif

            @if($user?->isAdmin())
                <a class="{{ request()->routeIs('repartition.vacations') ? $activeItem : $idleItem }}" href="{{ route('repartition.vacations') }}">
                    <span class="app-sidebar-icon-wrap bg-violet-100 text-violet-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Vacations</span>
                        <span class="app-sidebar-link-meta">Estimations et planning</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('vacation2026.*') ? $activeItem : $idleItem }}" href="{{ route('vacation2026.index') }}">
                    <span class="app-sidebar-icon-wrap bg-fuchsia-100 text-fuchsia-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Vacation 2026</span>
                        <span class="app-sidebar-link-meta">Traitement central</span>
                    </span>
                </a>
            @endif

            @if(! $user?->isLogistique())
                <a class="{{ request()->routeIs('repartition.livre.*') ? $activeItem : $idleItem }}" href="{{ route('repartition.livre.preview') }}">
                    <span class="app-sidebar-icon-wrap bg-amber-100 text-amber-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Livre</span>
                        <span class="app-sidebar-link-meta">Aperçu et export</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('imports.*') ? $activeItem : $idleItem }}" href="{{ route('imports.index') }}">
                    <span class="app-sidebar-icon-wrap bg-rose-100 text-rose-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                    </span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Imports</span>
                        <span class="app-sidebar-link-meta">Référentiels CSV</span>
                    </span>
                </a>
            @endif

            <a class="{{ request()->routeIs('decision.centre') ? $activeItem : $idleItem }}" href="{{ route('decision.centre') }}">
                <span class="app-sidebar-icon-wrap bg-sky-100 text-sky-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21V3m0 0L5 7m4-4l4 4" />
                    </svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Décision de centre</span>
                    <span class="app-sidebar-link-meta">Edition centralisée</span>
                </span>
            </a>

            <a class="{{ request()->routeIs('repartition.groupes') ? $activeItem : $idleItem }}" href="{{ route('repartition.groupes') }}">
                <span class="app-sidebar-icon-wrap bg-orange-100 text-orange-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5V4H2v16h5m10 0v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4m10 0H7m10-12h.01M7 8h8m-8 4h8" />
                    </svg>
                </span>
                <span class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">Répartition groupes</span>
                    <span class="app-sidebar-link-meta">Equilibrage DREN / CISCO</span>
                </span>
            </a>

            @if($user?->isAdmin())
                <div class="app-sidebar-section-label app-sidebar-label">Administration</div>

                <a class="{{ request()->routeIs('admin.statistics.*') ? $activeItem : $idleItem }}" href="{{ route('admin.statistics.index') }}">
                    <span class="app-sidebar-icon-wrap bg-slate-200 text-slate-700">#</span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Statistiques</span>
                        <span class="app-sidebar-link-meta">Indicateurs système</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('admin.users.*') ? $activeItem : $idleItem }}" href="{{ route('admin.users.index') }}">
                    <span class="app-sidebar-icon-wrap bg-slate-200 text-slate-700">@</span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Utilisateurs</span>
                        <span class="app-sidebar-link-meta">Accès et rôles</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('admin.references.*') ? $activeItem : $idleItem }}" href="{{ route('admin.references.index') }}">
                    <span class="app-sidebar-icon-wrap bg-slate-200 text-slate-700">+</span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Référentiels</span>
                        <span class="app-sidebar-link-meta">Tables de base</span>
                    </span>
                </a>

                <a class="{{ request()->routeIs('admin.audit-logs.*') ? $activeItem : $idleItem }}" href="{{ route('admin.audit-logs.index') }}">
                    <span class="app-sidebar-icon-wrap bg-slate-200 text-slate-700">#</span>
                    <span class="app-sidebar-copy-block">
                        <span class="app-sidebar-link-title">Historique IP</span>
                        <span class="app-sidebar-link-meta">Journal d'accès</span>
                    </span>
                </a>
            @endif
        </nav>

        <div class="app-sidebar-footer">
            <div class="app-sidebar-user">
                <div class="app-sidebar-avatar">{{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}</div>
                <div class="app-sidebar-copy-block">
                    <span class="app-sidebar-link-title">{{ $user?->name }}</span>
                    <span class="app-sidebar-link-meta">Session active</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-sidebar-logout">
                    <span class="app-sidebar-icon-wrap bg-slate-200 text-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
    }

    .app-sidebar-panel {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 30%),
            linear-gradient(180deg, rgba(255, 255, 255, 0.97), rgba(248, 250, 252, 0.96));
        box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
        backdrop-filter: blur(18px);
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
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1.25rem 1rem 0.75rem;
    }

    .app-sidebar-brand,
    .app-sidebar-user,
    .app-sidebar-logout {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .app-sidebar-logo,
    .app-sidebar-avatar,
    .app-sidebar-icon-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        flex-shrink: 0;
    }

    .app-sidebar-logo {
        width: 3rem;
        height: 3rem;
        color: #fff;
        background: linear-gradient(135deg, #0f172a, #2563eb);
        box-shadow: 0 12px 28px rgba(37, 99, 235, 0.28);
    }

    .app-sidebar-avatar,
    .app-sidebar-icon-wrap {
        width: 2.65rem;
        height: 2.65rem;
        font-weight: 800;
    }

    .app-sidebar-copy {
        min-width: 0;
    }

    .app-sidebar-kicker {
        margin: 0 0 0.2rem;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #2563eb;
    }

    .app-sidebar-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }

    .app-sidebar-subtitle,
    .app-sidebar-link-meta {
        margin: 0.2rem 0 0;
        font-size: 0.78rem;
        color: #64748b;
    }

    .app-sidebar-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border: 1px solid rgba(203, 213, 225, 0.95);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.95);
        color: #334155;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .app-sidebar-toggle:hover {
        background: #0f172a;
        border-color: #0f172a;
        color: #fff;
    }

    .app-sidebar-hint {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0 1rem 0.75rem;
        padding: 0.8rem 0.95rem;
        border: 1px solid rgba(191, 219, 254, 0.95);
        border-radius: 18px;
        background: rgba(239, 246, 255, 0.9);
        font-size: 0.74rem;
        font-weight: 700;
        color: #1d4ed8;
    }

    .app-sidebar-hint-dot {
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 999px;
        background: #2563eb;
        box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.12);
        flex-shrink: 0;
    }

    .app-sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        padding: 0 1rem 1rem;
    }

    .app-sidebar-copy-block {
        display: flex;
        min-width: 0;
        flex: 1;
        flex-direction: column;
    }

    .app-sidebar-link-title {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1.2;
    }

    .app-sidebar-section-label {
        margin: 0.8rem 0 0.1rem;
        padding: 0 0.35rem;
        font-size: 0.66rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .app-sidebar-item-active .app-sidebar-icon-wrap {
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
    }

    .app-sidebar-footer {
        padding: 1rem;
        border-top: 1px solid rgba(226, 232, 240, 0.95);
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.72), rgba(255, 255, 255, 0.94));
    }

    .app-sidebar-user {
        margin-bottom: 0.85rem;
        padding: 0.8rem;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.82);
    }

    .app-sidebar-avatar {
        color: #0f172a;
        background: linear-gradient(135deg, #dbeafe, #f8fafc);
        border: 1px solid rgba(191, 219, 254, 0.95);
    }

    .app-sidebar-logout {
        width: 100%;
        justify-content: flex-start;
        padding: 0.85rem;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 18px;
        background: #fff;
        font-size: 0.92rem;
        font-weight: 700;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .app-sidebar-logout:hover {
        border-color: rgba(148, 163, 184, 0.95);
        background: #f8fafc;
    }

    @media (min-width: 1024px) {
        .app-sidebar-shell {
            position: sticky;
            top: 1.5rem;
            width: var(--sidebar-width, 20rem);
            transition: width 0.24s ease;
            align-self: flex-start;
            z-index: 30;
        }

        .app-sidebar-shell[data-sidebar-state="collapsed"] {
            --sidebar-width: 5.9rem;
        }

        .app-sidebar-shell[data-sidebar-state="expanded"],
        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within {
            --sidebar-width: 20rem;
        }

        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-title,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-subtitle,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-label,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-copy-block,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-section-label {
            opacity: 0;
            width: 0;
            max-width: 0;
            overflow: hidden;
            transform: translateX(-6px);
            pointer-events: none;
        }

        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover .app-sidebar-title,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover .app-sidebar-subtitle,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover .app-sidebar-label,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover .app-sidebar-copy-block,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:hover .app-sidebar-section-label,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within .app-sidebar-title,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within .app-sidebar-subtitle,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within .app-sidebar-label,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within .app-sidebar-copy-block,
        .app-sidebar-shell[data-sidebar-state="collapsed"]:focus-within .app-sidebar-section-label {
            opacity: 1;
            width: auto;
            max-width: 100%;
            transform: translateX(0);
            pointer-events: auto;
        }

        .app-sidebar-title,
        .app-sidebar-subtitle,
        .app-sidebar-label,
        .app-sidebar-copy-block,
        .app-sidebar-section-label {
            transition: opacity 0.18s ease, transform 0.18s ease, width 0.18s ease, max-width 0.18s ease;
        }

        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-header,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-hint,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-footer {
            padding-left: 0.9rem;
            padding-right: 0.9rem;
        }

        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-brand,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-user,
        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-logout {
            justify-content: center;
        }

        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-nav > a {
            justify-content: center;
            padding-left: 0.85rem;
            padding-right: 0.85rem;
        }

        .app-sidebar-shell[data-sidebar-state="collapsed"] .app-sidebar-toggle-icon {
            transform: rotate(180deg);
        }

        .app-sidebar-shell[data-sidebar-state="expanded"] .app-sidebar-toggle-icon {
            transform: rotate(0deg);
        }
    }
</style>

<script>
    (function () {
        const shell = document.getElementById('appSidebarShell');
        const toggle = document.getElementById('appSidebarToggle');
        const storageKey = 'soe.sidebar.state';

        if (!shell || !toggle) {
            return;
        }

        const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

        const setState = (state, persist = true) => {
            const normalized = state === 'expanded' ? 'expanded' : 'collapsed';
            shell.setAttribute('data-sidebar-state', isDesktop() ? normalized : 'expanded');
            toggle.setAttribute('aria-expanded', String(shell.getAttribute('data-sidebar-state') === 'expanded'));

            if (persist && isDesktop()) {
                window.localStorage.setItem(storageKey, normalized);
            }
        };

        const restoreState = () => {
            const stored = window.localStorage.getItem(storageKey);
            setState(stored || 'collapsed', false);
        };

        restoreState();

        toggle.addEventListener('click', function () {
            const nextState = shell.getAttribute('data-sidebar-state') === 'expanded' ? 'collapsed' : 'expanded';
            setState(nextState, true);
        });

        window.addEventListener('resize', function () {
            restoreState();
        });
    })();
</script>
