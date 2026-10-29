@extends('layouts.app')

@section('title', 'Tableau de bord CISCO - Vacation 2026')
@section('content')

    @include('vacation-2026.dashboards._navigation', [
        'navBackLabel' => 'Tableau de bord CISCO',
        'navBackRoute' => route('vacation2026.cisco', array_filter([
            'exam' => $examFilter,
            'phase' => $phaseFilter,
            'activity_id' => $activityFilter,
        ], fn ($value) => $value !== null && $value !== '')),
    ])

    <div class="space-y-4">
        <!-- Header -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5 md:p-6">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tableau de bord CISCO</h1>
                <p class="mt-1 text-sm text-slate-600">Sélectionnez une CISCO pour voir ses statistiques détaillées</p>
            </div>
        </div>

        <!-- CISCO Selector -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @include('vacation-2026.dashboards._filters')
            <form method="GET" class="flex flex-col gap-3 md:flex-row md:items-end">
                <div class="flex-1">
                    <label class="mb-1 block text-sm font-medium text-slate-700">CISCO</label>
                    <select name="cisco_id" onchange="this.form.submit()" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Choisir une CISCO</option>
                        @foreach($allCiscos as $cisco)
                            <option value="{{ $cisco->id }}" {{ $selectedCiscoId == $cisco->id ? 'selected' : '' }}>
                                {{ $cisco->nom }} ({{ $cisco->dren->nom ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

        </div>

        @include('vacation-2026.dashboards._phase-summary')
        @include('vacation-2026.dashboards._activity-details')

        @if($selectedCiscoId && isset($activity_role_breakdown) && $activity_role_breakdown->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Composition du personnel par activité</h2>
            <p class="mb-4 text-sm text-slate-600">Rôles requis pour les activités de la CISCO sélectionnée.</p>
            <div class="space-y-3">@foreach($activity_role_breakdown as $activity)<details class="rounded-xl border border-slate-200"><summary class="cursor-pointer list-none px-4 py-3 font-medium text-slate-900">{{ $activity['libelle'] }} <span class="ml-2 text-sm font-normal text-slate-500">— {{ $activity['required'] }} requis · {{ $activity['assigned'] }} affecté(s)</span></summary><div class="border-t border-slate-200 px-4 py-3"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500"><th class="py-1">Fonction</th><th class="py-1 text-center">Nombre</th><th class="py-1">Référence</th></tr></thead><tbody>@foreach($activity['roles'] as $role)<tr class="border-t border-slate-100"><td class="py-2">{{ $role['role'] }}</td><td class="py-2 text-center font-semibold">{{ $role['count'] }}</td><td class="py-2 text-slate-600">{{ $role['note'] }}</td></tr>@endforeach</tbody></table></div></details>@endforeach</div>
        </div>
        @endif

        @if($selectedCiscoId && isset($activity_details) && $activity_details->isNotEmpty())
        @php
            $epsActivityDetails = $activity_details->filter(fn ($activity) => str_contains((string) ($activity['phase'] ?? ''), 'EPS') || str_contains((string) ($activity['libelle'] ?? ''), 'EPS'));
        @endphp
        @if($epsActivityDetails->isNotEmpty())
        <div class="rounded-2xl border border-purple-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Activités EPS détaillées</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="px-3 py-2 text-left">Activité</th>
                            <th class="px-3 py-2 text-left">Phase</th>
                            <th class="px-3 py-2 text-center">Personnel</th>
                            <th class="px-3 py-2 text-center">Affecté</th>
                            <th class="px-3 py-2 text-center">Jours</th>
                            <th class="px-3 py-2 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($epsActivityDetails as $activity)
                        <tr class="border-b border-slate-100">
                            <td class="px-3 py-2">{{ $activity['examen'] }} - {{ $activity['libelle'] }}</td>
                            <td class="px-3 py-2">{{ str_replace('_', ' ', $activity['phase'] ?: 'AVANT_SESSION') }}</td>
                            <td class="px-3 py-2 text-center font-semibold">{{ $activity['required'] }}</td>
                            <td class="px-3 py-2 text-center">{{ $activity['assigned'] }}</td>
                            <td class="px-3 py-2 text-center">{{ $activity['days'] }}</td>
                            <td class="px-3 py-2 text-right font-semibold text-purple-700">{{ number_format($activity['amount'], 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @endif

        @if($selectedCiscoId && !empty($eps_summary) && in_array($examFilter, ['', 'BEPC'], true))
        <div class="rounded-2xl border border-purple-200 bg-white p-5 shadow-sm">
            <h2 class="mb-1 text-lg font-semibold text-slate-900">Récapitulatif EPS de la CISCO</h2>
            <p class="mb-4 text-sm text-slate-600">Le calcul est effectué séparément pour chaque centre EPS, puis totalisé pour cette CISCO.</p>
            <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="border-b border-slate-200">
                <th class="px-3 py-2 text-left">Centre EPS</th><th class="px-3 py-2 text-center">Candidats</th><th class="px-3 py-2 text-center">Chefs</th><th class="px-3 py-2 text-center">Surveillants</th><th class="px-3 py-2 text-center">Interrogateurs</th><th class="px-3 py-2 text-center">Secrétariat</th><th class="px-3 py-2 text-center">Médecins</th><th class="px-3 py-2 text-center">Stade</th><th class="px-3 py-2 text-center">Total</th>
            </tr></thead><tbody>@forelse($eps_summary['centres'] as $epsCentre)<tr class="border-b border-slate-100">
                <td class="px-3 py-3 font-medium">{{ $epsCentre['centre_name'] }}</td><td class="px-3 py-3 text-center">{{ number_format($epsCentre['candidates'], 0, ',', ' ') }}</td><td class="px-3 py-3 text-center">{{ $epsCentre['chef_centre_required'] }}</td><td class="px-3 py-3 text-center">{{ $epsCentre['surveillants_required'] }}</td><td class="px-3 py-3 text-center">{{ $epsCentre['interrogators_required'] }}</td><td class="px-3 py-3 text-center">{{ $epsCentre['secretariat_required'] }}</td><td class="px-3 py-3 text-center">{{ $epsCentre['medical_required'] }}</td><td class="px-3 py-3 text-center">{{ $epsCentre['stadium_agents_required'] }}</td><td class="px-3 py-3 text-center font-semibold">{{ $epsCentre['chef_centre_required'] + $epsCentre['surveillants_required'] + $epsCentre['interrogators_required'] + $epsCentre['secretariat_required'] + $epsCentre['medical_required'] + $epsCentre['stadium_agents_required'] }}</td>
            </tr>@empty<tr><td colspan="9" class="px-3 py-4 text-center text-slate-500">Aucun centre EPS/GYM rattaché à cette CISCO.</td></tr>@endforelse<tr class="bg-purple-50 font-semibold"><td class="px-3 py-3">Total CISCO</td><td class="px-3 py-3 text-center">{{ number_format($eps_summary['total_candidates'], 0, ',', ' ') }}</td><td colspan="6"></td><td class="px-3 py-3 text-center text-purple-900">{{ $eps_summary['total_planned'] }}</td></tr></tbody></table></div>
        </div>
        @endif

        @if($selectedCiscoId)
        <!-- CISCO Statistics -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs uppercase tracking-wider text-blue-700">CISCO</p>
                <p class="mt-2 text-lg font-semibold text-blue-900">{{ $cisco_name }}</p>
                <p class="text-xs text-blue-600 mt-1">{{ $dren_name }}</p>
            </div>
            <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                <p class="text-xs uppercase tracking-wider text-purple-700">Candidats</p>
                <p class="mt-2 text-2xl font-semibold text-purple-900">{{ number_format($total_candidates, 0, ',', ' ') }}</p>
            </div>
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                <p class="text-xs uppercase tracking-wider text-indigo-700">Centres</p>
                <p class="mt-2 text-2xl font-semibold text-indigo-900">{{ $centre_count }}</p>
            </div>
            <div class="rounded-xl border border-cyan-200 bg-cyan-50 p-4">
                <p class="text-xs uppercase tracking-wider text-cyan-700">Salles</p>
                <p class="mt-2 text-2xl font-semibold text-cyan-900">{{ $salle_count }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs uppercase tracking-wider text-emerald-700">Affecté</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-900">{{ $total_assigned }}</p>
            </div>
            <div class="rounded-xl border border-orange-200 bg-orange-50 p-4">
                <p class="text-xs uppercase tracking-wider text-orange-700">Taux</p>
                <p class="mt-2 text-2xl font-semibold text-orange-900">{{ $completion_percentage }}%</p>
            </div>
        </div>

        <!-- Exam Statistics -->
        <div class="grid grid-cols-1 gap-4 {{ $examFilter === 'CEPE' ? '' : 'lg:grid-cols-3' }}">
            @if($examFilter !== 'BEPC')<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">CEPE</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Prévu</span>
                        <span class="font-semibold">{{ $cepe['planned'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Affecté</span>
                        <span class="font-semibold text-emerald-600">{{ $cepe['assigned'] }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $cepe['completion_percentage'] }}%"></div>
                    </div>
                </div>
            </div>@endif

            @if($examFilter !== 'CEPE')<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">BEPC</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Prévu</span>
                        <span class="font-semibold">{{ $bepc['planned'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Affecté</span>
                        <span class="font-semibold text-emerald-600">{{ $bepc['assigned'] }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $bepc['completion_percentage'] }}%"></div>
                    </div>
                </div>
            </div>@endif

            @if($examFilter !== 'CEPE')<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">EPS</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Prévu</span>
                        <span class="font-semibold">{{ $eps['planned'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Affecté</span>
                        <span class="font-semibold text-emerald-600">{{ $eps['assigned'] }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $eps['completion_percentage'] }}%"></div>
                    </div>
                </div>
            </div>@endif
        </div>

        @if(!empty($centres))
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Synthèse des centres d’examen</h2>
            <p class="mb-4 text-sm text-slate-600">Vue rapide des besoins par centre. Consultez le tableau de bord Centre pour le détail des activités.</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Centre</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Type</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Candidats</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Salles</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Agents estimés</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Montant estimé</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($centres as $centre)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $centre['centre_name'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $centre['type_label'] }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($centre['candidates'], 0, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right">{{ $centre['salles'] }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ $centre['agents_estimated'] }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ number_format($centre['montant_estimated'], 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Status -->
        @if(!empty($assignments_by_status))
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Statut des Affectations</h3>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach($assignments_by_status as $status => $count)
                <div class="rounded-lg border border-slate-200 p-4 text-center">
                    <p class="text-xs uppercase tracking-wider text-slate-600 mb-2">{{ $status }}</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $count }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @else
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8 text-center">
            <p class="text-slate-600">Veuillez sélectionner une CISCO pour voir ses statistiques</p>
        </div>
        @endif
    </div>
@endsection
