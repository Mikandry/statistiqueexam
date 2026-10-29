@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp
<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4"><p class="text-xs uppercase text-emerald-700">Montant total</p><p class="mt-1 text-2xl font-semibold text-emerald-900">{{ $n($totals['amount']) }} Ar</p></div>
    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4"><p class="text-xs uppercase text-blue-700">Personnes mobilisées (postes)</p><p class="mt-1 text-2xl font-semibold text-blue-900">{{ $n($totals['persons']) }}</p></div>
    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4"><p class="text-xs uppercase text-indigo-700">Jours-agents</p><p class="mt-1 text-2xl font-semibold text-indigo-900">{{ $n($totals['person_days']) }}</p></div>
    <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs uppercase text-slate-600">Activités</p><p class="mt-1 text-2xl font-semibold text-slate-900">{{ $totals['activities'] }}</p></div>
</div>
@if(($totals['missing_rates'] ?? 0) > 0)
    <p class="mb-4 text-xs text-amber-700">{{ $totals['missing_rates'] }} code(s) de taux sans taux saisi : montants partiels. <a class="underline" href="{{ route('vacation2026.besoins.baremes') }}">Saisir les barèmes</a></p>
@endif
