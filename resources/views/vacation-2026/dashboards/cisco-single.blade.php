@extends('layouts.app')

@section('title', 'Tableau de bord CISCO - Vacation 2026')
@section('content')

    @include('vacation-2026.dashboards._navigation')

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
                <div class="w-full md:w-64">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Candidats EPS manuels</label>
                    <input type="number" min="0" name="manual_eps_candidates" value="{{ request()->query('manual_eps_candidates', $selectedCisco->manual_eps_candidates ?? '') }}" placeholder="Laisser vide = auto" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Appliquer</button>
            </form>
        </div>

        @include('vacation-2026.dashboards._phase-summary')
        @include('vacation-2026.dashboards._activity-details')

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
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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
            </div>
        </div>

        @if(!empty($centres))
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Centres de la CISCO</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Centre</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Type</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Candidats</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Salles</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Avant session</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Pendant session</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Après session</th>
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
                            <td class="px-4 py-3 text-right">{{ $centre['agents_avant_session'] }}</td>
                            <td class="px-4 py-3 text-right">{{ $centre['agents_pendant_session'] }}</td>
                            <td class="px-4 py-3 text-right">{{ $centre['agents_apres_session'] }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ $centre['agents_estimated'] }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ number_format($centre['montant_estimated'], 0, ',', ' ') }}</td>
                        </tr>
                        @if(!empty($centre['roles']) && $centre['roles']->isNotEmpty())
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <td colspan="9" class="px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Détail du personnel</p>
                                <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                                    @foreach($centre['roles'] as $role)
                                    <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-3 py-2">
                                        <span class="text-sm text-slate-700">{{ $role['role'] }}</span>
                                        <span class="text-sm font-bold text-slate-900">{{ $role['count'] }} <span class="text-xs text-slate-500">({{ $role['days'] }}j × {{ number_format($role['rate'], 2, ',', ' ') }})</span></span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endif
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
