@php
    // Bouton optionnel "retour au niveau hiérarchique supérieur".
    // La vue appelante le fournit via $navBackLabel et $navBackRoute.
    $navGlobalRoute = route('vacation2026.dashboard.global');
    $navHasBack = isset($navBackLabel) && isset($navBackRoute);
    // Si le retour pointe déjà vers le dashboard global, on masque le bouton
    // "Dashboard global" de droite pour éviter toute redondance.
    $navBackIsGlobal = $navHasBack && $navBackRoute === $navGlobalRoute;
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap items-center gap-2">
        @if($navHasBack)
            <a href="{{ $navBackRoute }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white">
                <span aria-hidden="true">&larr;</span>
                {{ $navBackLabel }}
            </a>
        @endif
        <a href="{{ route('vacation2026.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            <span aria-hidden="true">&larr;</span>
            Retour au vacation
        </a>
    </div>
    @if(!$navBackIsGlobal)
        <a href="{{ $navGlobalRoute }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Dashboard global
        </a>
    @endif
</div>
