@php
    $typeLabels = [
        'ecrit' => "Centre d'écrit seulement",
        'correction' => 'Centre de correction seulement',
        'jumele' => 'Centre d’écrit et de correction jumelé',
    ];
@endphp

<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">{{ $dashboard['centre_name'] }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ $typeLabels[$type] ?? $dashboard['centre_type'] }}</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                {{ number_format($dashboard['total_candidates'], 0, ',', ' ') }} candidats
            </span>
        </div>

        <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
            <div class="rounded-lg bg-blue-50 p-3">
                <p class="text-xs text-blue-700">Salles</p>
                <p class="mt-1 text-xl font-bold text-blue-900">{{ $dashboard['total_salles'] }}</p>
            </div>
            <div class="rounded-lg bg-emerald-50 p-3">
                <p class="text-xs text-emerald-700">Personnel estimé</p>
                <p class="mt-1 text-xl font-bold text-emerald-900">{{ $dashboard['total_planned'] }}</p>
            </div>
            <div class="rounded-lg bg-amber-50 p-3">
                <p class="text-xs text-amber-700">Affecté</p>
                <p class="mt-1 text-xl font-bold text-amber-900">{{ $dashboard['total_assigned'] }}</p>
            </div>
            <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs text-slate-600">Montant estimé</p>
                <p class="mt-1 text-lg font-bold text-slate-900">{{ number_format($dashboard['estimated_indemnity'], 0, ',', ' ') }}</p>
            </div>
        </div>

        @if($dashboard['activities']->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-slate-200 text-slate-500">
                        <tr>
                            <th class="px-2 py-2">Activité</th>
                            <th class="px-2 py-2 text-right">Estimé</th>
                            <th class="px-2 py-2 text-right">Affecté</th>
                            <th class="px-2 py-2 text-right">Restant</th>
                            <th class="px-2 py-2 text-right">Jours</th>
                            <th class="px-2 py-2 text-right">Taux</th>
                            <th class="px-2 py-2 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dashboard['activities'] as $activity)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="px-2 py-2 text-slate-700">{{ $activity['examen'] }} · {{ $activity['libelle'] }}</td>
                                <td class="px-2 py-2 text-right font-semibold text-slate-900">{{ $activity['required'] }}</td>
                                <td class="px-2 py-2 text-right text-emerald-700">{{ $activity['assigned'] }}</td>
                                <td class="px-2 py-2 text-right text-orange-700">{{ max(0, $activity['required'] - $activity['assigned']) }}</td>
                                <td class="px-2 py-2 text-right text-slate-600">{{ $activity['days'] }}</td>
                                <td class="px-2 py-2 text-right text-slate-600">{{ number_format($activity['rate'], 2, ',', ' ') }}</td>
                                <td class="px-2 py-2 text-right font-semibold text-slate-900">{{ number_format($activity['amount'], 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($dashboard['centre_id'])
            <a href="{{ route('vacation2026.centre', ['centre_id' => $dashboard['centre_id']]) }}" class="self-start rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Voir le détail
            </a>
        @endif
    </div>
</article>
