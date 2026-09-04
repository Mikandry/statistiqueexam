@extends('layouts.app')

@section('title', 'Tableau de bord DREN - Vacation 2026')
@section('content')

    @include('vacation-2026.dashboards._navigation')

    <div class="space-y-4">
        <!-- Header -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5 md:p-6">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tableau de bord DREN</h1>
                <p class="mt-1 text-sm text-slate-600">Sélectionnez une DREN pour voir ses statistiques détaillées</p>
            </div>
        </div>

        <!-- DREN Selector -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @include('vacation-2026.dashboards._filters')
            <form method="GET" class="flex gap-3">
                <select name="dren_id" onchange="this.form.submit()" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Choisir une DREN</option>
                    @foreach($allDrens as $dren)
                        <option value="{{ $dren->id }}" {{ $selectedDrenId == $dren->id ? 'selected' : '' }}>
                            {{ $dren->nom }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @include('vacation-2026.dashboards._phase-summary')
        @include('vacation-2026.dashboards._activity-details')

        @if($selectedDrenId)
        <!-- Decree calculation context -->
        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-1">Contexte du calcul selon le décret</h2>
            <p class="text-sm text-slate-700">
                Cette DREN compte <span class="font-semibold text-blue-900">{{ number_format($centre_count, 0, ',', ' ') }} centre(s) d'examen</span>
                sous sa protection, répartis dans <span class="font-semibold text-blue-900">{{ $cisco_count }} CISCO</span>,
                soit <span class="font-semibold text-blue-900">{{ number_format($salle_count ?? 0, 0, ',', ' ') }} salle(s)</span>
                et <span class="font-semibold text-blue-900">{{ number_format($candidate_count ?? 0, 0, ',', ' ') }} candidat(s)</span>.
                Le nombre d'agents est ensuite calculé par activité de la façon suivante :
            </p>
        </div>

        <!-- Decree calculation breakdown per activity / role -->
        @if(isset($activity_breakdown) && $activity_breakdown->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-1 text-lg font-semibold text-slate-900">Détail du personnel selon le décret</h2>
            <p class="mb-4 text-sm text-slate-600">Pour chaque activité DREN : le rôle de chacun et le calcul expliqué du nombre de personnel requis.</p>

            @foreach(['AVANT_SESSION', 'PENDANT_SESSION', 'APRES_SESSION'] as $ph)
                @php($phaseActs = $activity_breakdown->where('phase', $ph)->values())
                @if($phaseActs->isNotEmpty())
                <div class="mb-5">
                    <h3 class="mb-3 inline-flex items-center rounded-lg bg-slate-900 px-3 py-1 text-sm font-semibold text-white">
                        {{ $ph === 'AVANT_SESSION' ? 'Avant session' : ($ph === 'PENDANT_SESSION' ? 'Pendant session' : 'Après session') }}
                    </h3>
                    <div class="space-y-3">
                    @foreach($phaseActs as $act)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $act['examen'] }} — {{ $act['libelle'] }}</p>
                                    <p class="text-xs text-slate-500">
                                        Estimé : <span class="font-semibold">{{ $act['required'] }}</span> personnel •
                                        Affecté : <span class="font-semibold text-emerald-700">{{ $act['assigned'] }}</span> •
                                        Restant : <span class="font-semibold text-orange-700">{{ $act['remaining'] }}</span> •
                                        {{ $act['days'] }} jour(s) •
                                        Taux : {{ number_format($act['rate'], 2, ',', ' ') }} •
                                        Montant : {{ number_format($act['amount'], 0, ',', ' ') }} Ar
                                    </p>
                                </div>
                            </div>
                            @if($act['roles']->isNotEmpty())
                            <table class="mt-3 w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-slate-600">
                                        <th class="px-2 py-1.5 text-left">Rôle</th>
                                        <th class="px-2 py-1.5 text-center">Nombre</th>
                                        <th class="px-2 py-1.5 text-left">Calcul / explication</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($act['roles'] as $role)
                                    <tr class="border-b border-slate-100">
                                        <td class="px-2 py-1.5 font-medium text-slate-800">{{ $role['role'] }}</td>
                                        <td class="px-2 py-1.5 text-center text-lg font-bold text-slate-900">{{ $role['count'] }}</td>
                                        <td class="px-2 py-1.5 text-slate-600">{{ $role['note'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @endif
                        </div>
                    @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        </div>
        @endif

        <!-- DREN Statistics -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs uppercase tracking-wider text-blue-700">DREN</p>
                <p class="mt-2 text-lg font-semibold text-blue-900">{{ $dren_name }}</p>
            </div>
            <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                <p class="text-xs uppercase tracking-wider text-purple-700">CISCO Count</p>
                <p class="mt-2 text-2xl font-semibold text-purple-900">{{ $cisco_count }}</p>
            </div>
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                <p class="text-xs uppercase tracking-wider text-indigo-700">Centres</p>
                <p class="mt-2 text-2xl font-semibold text-indigo-900">{{ $centre_count }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs uppercase tracking-wider text-emerald-700">Personnel affecté</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-900">{{ $total_assigned }}</p>
            </div>
            <div class="rounded-xl border border-orange-200 bg-orange-50 p-4">
                <p class="text-xs uppercase tracking-wider text-orange-700">Personnel restant</p>
                <p class="mt-2 text-2xl font-semibold text-orange-900">{{ $remaining }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wider text-slate-700">Taux de réalisation</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $completion_percentage }}%</p>
            </div>
        </div>

        <!-- CEPE & BEPC Stats -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Statistiques CEPE</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Positions à pourvoir</span>
                        <span class="font-semibold">{{ $cepe['planned'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Affectés</span>
                        <span class="font-semibold text-emerald-600">{{ $cepe['assigned'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Restant</span>
                        <span class="font-semibold text-orange-600">{{ $cepe['remaining'] }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $cepe['completion_percentage'] }}%"></div>
                    </div>
                    <div class="text-right text-xs text-slate-600">{{ $cepe['completion_percentage'] }}%</div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Statistiques BEPC</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Positions à pourvoir</span>
                        <span class="font-semibold">{{ $bepc['planned'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Affectés</span>
                        <span class="font-semibold text-emerald-600">{{ $bepc['assigned'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Restant</span>
                        <span class="font-semibold text-orange-600">{{ $bepc['remaining'] }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $bepc['completion_percentage'] }}%"></div>
                    </div>
                    <div class="text-right text-xs text-slate-600">{{ $bepc['completion_percentage'] }}%</div>
                </div>
            </div>
        </div>

        <!-- Assignment Status -->
        @if(!$assignments_by_status->isEmpty())
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
            <p class="text-slate-600">Veuillez sélectionner une DREN pour voir ses statistiques</p>
        </div>
        @endif
    </div>
@endsection
