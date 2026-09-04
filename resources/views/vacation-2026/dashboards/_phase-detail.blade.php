@if(isset($phase))
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <!-- Section header -->
    <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900">{{ $phase['label'] }}</h2>
            <p class="text-xs text-slate-500">{{ $phase['exams']->count() }} examen(s) • {{ $phase['activity_count'] }} activité(s)</p>
        </div>
        <div class="flex flex-wrap gap-5 text-sm">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500">Selon le décret</p>
                <p class="text-xl font-bold text-slate-900">{{ $phase['planned'] }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-emerald-600">Affecté</p>
                <p class="text-xl font-bold text-emerald-600">{{ $phase['assigned'] }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-orange-600">Restant</p>
                <p class="text-xl font-bold text-orange-600">{{ $phase['remaining'] }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500">Montant estimé</p>
                <p class="text-xl font-bold text-slate-900">{{ number_format($phase['amount'], 0, ',', ' ') }}</p>
            </div>
        </div>
    </div>

    @if($phase['exams']->isNotEmpty())
    <!-- Per exam -->
    <div class="divide-y divide-slate-100">
        @foreach($phase['exams'] as $exam)
        <div class="p-5">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-1 text-sm font-semibold text-white">{{ $exam['examen'] }}</span>
                <span class="text-xs text-slate-600">{{ $exam['planned'] }} pers. selon le décret • {{ $exam['assigned'] }} affecté(s) • {{ $exam['days'] }} jour(s)</span>
                <span class="text-xs font-semibold text-orange-600">{{ $exam['remaining'] }} restant(s)</span>
            </div>

            @if($exam['personnel_by_role']->isNotEmpty())
            <div class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                @foreach($exam['personnel_by_role'] as $role => $count)
                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <span class="text-sm text-slate-700">{{ $role }}</span>
                    <span class="text-lg font-bold text-slate-900">{{ $count }}</span>
                </div>
                @endforeach
            </div>
            @endif

            @if($exam['activities']->isNotEmpty())
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="px-3 py-2 text-left">Activité</th>
                            <th class="px-3 py-2 text-center">Estimé</th>
                            <th class="px-3 py-2 text-center">Affecté</th>
                            <th class="px-3 py-2 text-center">Restant</th>
                            <th class="px-3 py-2 text-center">Jours</th>
                            <th class="px-3 py-2 text-right">Taux</th>
                            <th class="px-3 py-2 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($exam['activities'] as $activity)
                        <tr class="border-b border-slate-100">
                            <td class="px-3 py-2">{{ $activity['libelle'] }}</td>
                            <td class="px-3 py-2 text-center font-semibold">{{ $activity['required'] }}</td>
                            <td class="px-3 py-2 text-center text-emerald-700">{{ $activity['assigned'] }}</td>
                            <td class="px-3 py-2 text-center text-orange-700">{{ max(0, $activity['required'] - $activity['assigned']) }}</td>
                            <td class="px-3 py-2 text-center">{{ $activity['days'] }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($activity['rate'], 2, ',', ' ') }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ number_format($activity['amount'], 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @else
    <div class="p-5 text-sm text-slate-500">Aucune activité de personnel selon le décret pour cette phase.</div>
    @endif
</div>
@endif