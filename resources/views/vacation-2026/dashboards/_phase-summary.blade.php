@if(isset($activities_by_phase) && $activities_by_phase->isNotEmpty())
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="mb-4 text-lg font-semibold text-slate-900">Récapitulatif par phase</h2>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
        @foreach(['AVANT_SESSION' => 'Avant session', 'PENDANT_SESSION' => 'Pendant session', 'APRES_SESSION' => 'Après session'] as $phase => $label)
            @php($stats = $activities_by_phase->get($phase, ['count' => 0, 'planned' => 0, 'assigned' => 0, 'remaining' => 0, 'amount' => 0]))
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['planned'] }}</p>
                <p class="text-sm text-slate-600">{{ $stats['count'] }} activité(s), {{ $stats['assigned'] }} affecté(s)</p>
                <p class="mt-2 text-sm font-semibold text-emerald-700">{{ number_format($stats['amount'] ?? 0, 0, ',', ' ') }} montant estimé</p>
            </div>
        @endforeach
    </div>
</div>
@endif
