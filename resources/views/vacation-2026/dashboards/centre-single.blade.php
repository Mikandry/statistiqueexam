@extends('layouts.app')

@section('title', 'Tableau de bord Centre - Vacation 2026')
@section('content')

    @include('vacation-2026.dashboards._navigation')

    <div class="space-y-4">
        <!-- Header -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5 md:p-6">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tableau de bord Centre</h1>
                <p class="mt-1 text-sm text-slate-600">Sélectionnez un centre pour voir ses statistiques détaillées</p>
            </div>
        </div>

        <!-- Centre Selector -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @include('vacation-2026.dashboards._filters')
            <form method="GET" class="flex gap-3">
                <select name="centre_id" onchange="this.form.submit()" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Choisir un centre</option>
                    @foreach($allCentres as $centre)
                        <option value="{{ $centre['centre_id'] }}" {{ $selectedCentreId == $centre['centre_id'] ? 'selected' : '' }}>
                            {{ $centre['centre_name'] }} ({{ $centre['centre_type'] }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @include('vacation-2026.dashboards._phase-summary')

        @if($selectedCentreId)
        <!-- Centre Statistics -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs uppercase tracking-wider text-blue-700">Candidats</p>
                <p class="mt-2 text-2xl font-semibold text-blue-900">{{ number_format($total_candidates, 0, ',', ' ') }}</p>
            </div>
            <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                <p class="text-xs uppercase tracking-wider text-purple-700">Salles</p>
                <p class="mt-2 text-2xl font-semibold text-purple-900">{{ $total_salles }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs uppercase tracking-wider text-emerald-700">Personnel affecté</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-900">{{ $total_assigned }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wider text-slate-700">Taux</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $completion_percentage }}%</p>
            </div>
        </div>

        <!-- Centre Header -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900 mb-1">{{ $centre_name }}</h2>
            <p class="text-sm text-slate-600">Type: {{ $centre_type }} @if($is_eps_gym) • EPS/GYM @endif @if($has_special_needs) • Besoins spécifiques @endif</p>
            @if(($examFilter ?? '') !== '')
                <p class="mt-2 text-sm font-medium {{ $total_candidates > 0 ? 'text-emerald-700' : 'text-orange-700' }}">Examen sélectionné : {{ $examFilter }} — {{ $total_candidates > 0 ? 'candidats rattachés' : 'aucun candidat rattaché à cet examen' }}</p>
            @endif
        </div>

        <!-- Personnel Requirements -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-4">Surveillants de Salle</h3>
                <p class="text-3xl font-bold text-slate-900 mb-2">{{ $surveillants_required }}</p>
                <p class="text-xs text-slate-600">2 par salle @if($has_special_needs) + 2 (besoins spécifiques) @endif</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-4">Surveillants de Cour</h3>
                <p class="text-3xl font-bold text-slate-900 mb-2">{{ $yard_supervisors_required }}</p>
                <p class="text-xs text-slate-600">2 de base + 1 par tranche de 5 salles</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-4">Secrétaires</h3>
                <p class="text-3xl font-bold text-slate-900 mb-2">{{ $secretaries_required }}</p>
                <p class="text-xs text-slate-600">1 par tranche de 250 candidats</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-4">Sécurité</h3>
                <p class="text-3xl font-bold text-slate-900 mb-2">{{ $security_required }}</p>
                <p class="text-xs text-slate-600">Personnel de sécurité</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:col-span-2">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-4">Résumé Personnel</h3>
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-slate-600 mb-1">Total Prévu</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $total_planned }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-600 mb-1">Affecté</p>
                        <p class="text-2xl font-bold text-emerald-600">{{ $total_assigned }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-600 mb-1">Restant</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $remaining }}</p>
                    </div>
                </div>
                <div class="mt-3 w-full bg-slate-200 rounded-full h-3">
                    <div class="bg-slate-900 h-3 rounded-full" style="width: {{ $completion_percentage }}%"></div>
                </div>
            </div>
        </div>

        <!-- Detail: personnel by phase (avant / pendant / après session) -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-1 text-lg font-semibold text-slate-900">Détail du personnel par phase</h2>
            <p class="text-sm text-slate-600">Personnel mobilisé selon le décret, réparti par phase (avant, pendant et après session) et par type d'examen.</p>
        </div>

        @foreach($phase_details as $pd)
            @include('vacation-2026.dashboards._phase-detail', ['phase' => $pd])
        @endforeach

        <!-- Activities by Role (réelles) -->
        @if(!$activities_by_role->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Affectations réelles par rôle</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="px-4 py-2 text-left font-semibold text-slate-900">Rôle</th>
                            <th class="px-4 py-2 text-center font-semibold text-slate-900">Affectés</th>
                            <th class="px-4 py-2 text-center font-semibold text-slate-900">Statuts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities_by_role as $role => $data)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-2 font-medium text-slate-900">{{ $role }}</td>
                            <td class="px-4 py-2 text-center">{{ $data['count'] }}</td>
                            <td class="px-4 py-2 text-center text-xs">
                                @foreach($data['status_breakdown'] as $status => $count)
                                    <span class="inline-block mr-2 px-2 py-1 bg-slate-100 rounded">{{ $status }}: {{ $count }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @else
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8 text-center">
            <p class="text-slate-600">Veuillez sélectionner un centre pour voir ses statistiques</p>
        </div>
        @endif
    </div>
@endsection
