@if(isset($activity_details) && $activity_details->isNotEmpty())
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="mb-4 text-lg font-semibold text-slate-900">Activités détaillées</h2>
    <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="border-b border-slate-200">
        <th class="px-3 py-2 text-left">Activité</th><th class="px-3 py-2 text-left">Phase</th><th class="px-3 py-2 text-center">Personnel</th><th class="px-3 py-2 text-center">Affecté</th><th class="px-3 py-2 text-center">Jours</th><th class="px-3 py-2 text-right">Montant</th>
    </tr></thead><tbody>
    @foreach($activity_details as $activity)
        <tr class="border-b border-slate-100"><td class="px-3 py-2">{{ $activity['examen'] }} - {{ $activity['libelle'] }}</td><td class="px-3 py-2">{{ str_replace('_', ' ', $activity['phase'] ?: 'AVANT_SESSION') }}</td><td class="px-3 py-2 text-center font-semibold">{{ $activity['required'] }}</td><td class="px-3 py-2 text-center">{{ $activity['assigned'] }}</td><td class="px-3 py-2 text-center">{{ $activity['days'] }}</td><td class="px-3 py-2 text-right font-semibold">{{ number_format($activity['amount'], 0, ',', ' ') }}</td></tr>
    @endforeach
    </tbody></table></div>
</div>
@endif
