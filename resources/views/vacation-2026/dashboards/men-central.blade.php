@extends('layouts.app')

@section('title', 'Tableau de bord MEN Central - Vacation 2026')
@section('content')

    @include('vacation-2026.dashboards._navigation')

    <div class="space-y-4">
        <!-- Header -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5 md:p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tableau de bord MEN Central</h1>
                        <p class="mt-1 text-sm text-slate-600">Vacation 2026 - Vue d'ensemble des activités au niveau central</p>
                    </div>
                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white">SOE</div>
                        <div class="text-sm">
                            <div class="font-semibold text-slate-900">SOE</div>
                            <div class="text-xs text-slate-500">Service de l'Organisation des Examens</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="border-b border-slate-200 p-5 md:p-6">
                <form method="GET" class="flex flex-wrap gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">Examen</label>
                        <select name="exam" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Tous les examens</option>
                            @foreach(['CEPE', 'BEPC'] as $exam)
                                <option value="{{ $exam }}" {{ $examFilter === $exam ? 'selected' : '' }}>{{ $exam }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[240px]">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">Activité</label>
                        <select name="activity_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Toutes les activités</option>
                            @foreach($filterActivities as $activity)
                                <option value="{{ $activity->id }}" @selected((string)($activityFilter ?? '') === (string)$activity->id)>{{ $activity->examen }} - {{ $activity->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">Phase</label>
                        <select name="phase" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Toutes les phases</option>
                            <option value="AVANT_SESSION" {{ $phaseFilter === 'AVANT_SESSION' ? 'selected' : '' }}>Avant session</option>
                            <option value="PENDANT_SESSION" {{ $phaseFilter === 'PENDANT_SESSION' ? 'selected' : '' }}>Pendant session</option>
                            <option value="APRES_SESSION" {{ $phaseFilter === 'APRES_SESSION' ? 'selected' : '' }}>Après session</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs uppercase tracking-wider text-blue-700">Activités CEPE</p>
                <p class="mt-2 text-2xl font-semibold text-blue-900">{{ $cepe['planned'] }}</p>
                <p class="mt-1 text-xs text-blue-600">Positions à pourvoir</p>
            </div>
            <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                <p class="text-xs uppercase tracking-wider text-green-700">Activités BEPC</p>
                <p class="mt-2 text-2xl font-semibold text-green-900">{{ $bepc['planned'] }}</p>
                <p class="mt-1 text-xs text-green-600">Positions à pourvoir</p>
            </div>
            <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                <p class="text-xs uppercase tracking-wider text-purple-700">Personnel prévu</p>
                <p class="mt-2 text-2xl font-semibold text-purple-900">{{ $total_planned }}</p>
                <p class="mt-1 text-xs text-purple-600">Total centralisé</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs uppercase tracking-wider text-emerald-700">Personnel affecté</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-900">{{ $total_assigned }}</p>
                <p class="mt-1 text-xs text-emerald-600">Agents assignés</p>
            </div>
            <div class="rounded-xl border border-orange-200 bg-orange-50 p-4">
                <p class="text-xs uppercase tracking-wider text-orange-700">Personnel restant</p>
                <p class="mt-2 text-2xl font-semibold text-orange-900">{{ $remaining }}</p>
                <p class="mt-1 text-xs text-orange-600">À pourvoir</p>
            </div>
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                <p class="text-xs uppercase tracking-wider text-indigo-700">Taux de réalisation</p>
                <p class="mt-2 text-2xl font-semibold text-indigo-900">{{ $completion_percentage }}%</p>
                <p class="mt-1 text-xs text-indigo-600">D'avancement</p>
            </div>
        </div>

        <!-- Detailed Statistics -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <!-- CEPE Activities -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Statistiques CEPE</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Positions à pourvoir</span>
                        <span class="font-semibold">{{ $cepe['planned'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Agents affectés</span>
                        <span class="font-semibold text-emerald-600">{{ $cepe['assigned'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Restant</span>
                        <span class="font-semibold text-orange-600">{{ $cepe['remaining'] }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $cepe['completion_percentage'] }}%"></div>
                    </div>
                    <div class="text-right text-xs text-slate-600">{{ $cepe['completion_percentage'] }}% complété</div>
                </div>
            </div>

            <!-- BEPC Activities -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Statistiques BEPC</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Positions à pourvoir</span>
                        <span class="font-semibold">{{ $bepc['planned'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Agents affectés</span>
                        <span class="font-semibold text-emerald-600">{{ $bepc['assigned'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Restant</span>
                        <span class="font-semibold text-orange-600">{{ $bepc['remaining'] }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $bepc['completion_percentage'] }}%"></div>
                    </div>
                    <div class="text-right text-xs text-slate-600">{{ $bepc['completion_percentage'] }}% complété</div>
                </div>
            </div>
        </div>

        <!-- Activities by Phase -->
        @if(!$activities_by_phase->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Activités par phase</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Phase</th>
                            <th class="px-4 py-2 text-center font-semibold text-slate-900">Activités</th>
                            <th class="px-4 py-2 text-center font-semibold text-slate-900">Prévu</th>
                            <th class="px-4 py-2 text-center font-semibold text-slate-900">Affecté</th>
                            <th class="px-4 py-2 text-center font-semibold text-slate-900">Restant</th>
                            <th class="px-4 py-2 text-center font-semibold text-slate-900">Taux</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities_by_phase as $phase => $stats)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-2 font-medium text-slate-900">{{ str_replace('_', ' ', $phase) }}</td>
                            <td class="px-4 py-2 text-center">{{ $stats['count'] }}</td>
                            <td class="px-4 py-2 text-center">{{ $stats['planned'] }}</td>
                            <td class="px-4 py-2 text-center text-emerald-600 font-semibold">{{ $stats['assigned'] }}</td>
                            <td class="px-4 py-2 text-center text-orange-600">{{ max(0, $stats['planned'] - $stats['assigned']) }}</td>
                            <td class="px-4 py-2 text-center">
                                {{ $stats['planned'] > 0 ? round(($stats['assigned'] / $stats['planned']) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(isset($central_groups) && $central_groups->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Ventilation centrale par groupe</h2>
            <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="border-b border-slate-200">
                <th class="px-3 py-2 text-left">Activité</th><th class="px-3 py-2">Groupe</th><th class="px-3 py-2">Personnel</th><th class="px-3 py-2">Jours</th><th class="px-3 py-2">Taux</th><th class="px-3 py-2 text-right">Montant</th><th></th>
            </tr></thead><tbody>
            @foreach($central_groups as $group)
                <tr class="border-b border-slate-100"><form method="POST" action="{{ route('vacation2026.activity-groups.update', $group['id']) }}">@csrf @method('PUT')
                    <td class="px-3 py-2">{{ $group['examen'] }} - {{ $group['activity'] }}</td><td class="px-3 py-2">{{ $group['group'] }}</td>
                    <td class="px-3 py-2"><input class="w-20 rounded border border-slate-300 px-2 py-1" type="number" min="0" name="personnel" value="{{ $group['personnel'] }}"></td>
                    <td class="px-3 py-2"><input class="w-20 rounded border border-slate-300 px-2 py-1" type="number" min="1" name="nb_jours" value="{{ $group['days'] }}"></td>
                    <td class="px-3 py-2"><input class="w-24 rounded border border-slate-300 px-2 py-1" type="number" step="0.01" min="0" name="taux" value="{{ $group['rate'] }}"></td>
                    <td class="px-3 py-2 text-right">{{ number_format($group['amount'], 0, ',', ' ') }}</td><td class="px-3 py-2"><button class="rounded bg-slate-900 px-2 py-1 text-xs text-white" type="submit">Enregistrer</button></td>
                </form></tr>
            @endforeach
            </tbody></table></div>
        </div>
        @endif

        <!-- Assignments by Status -->
        @if(!$assignments_by_status->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Statut des affectations</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach($assignments_by_status as $status => $count)
                <div class="rounded-lg border border-slate-200 p-4 text-center">
                    <p class="text-xs uppercase tracking-wider text-slate-600 mb-1">{{ $status }}</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $count }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
@endsection
