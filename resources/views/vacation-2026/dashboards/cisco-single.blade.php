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
            <form method="GET" class="flex gap-3">
                <select name="cisco_id" onchange="this.form.submit()" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Choisir une CISCO</option>
                    @foreach($allCiscos as $cisco)
                        <option value="{{ $cisco->id }}" {{ $selectedCiscoId == $cisco->id ? 'selected' : '' }}>
                            {{ $cisco->nom }} ({{ $cisco->dren->nom ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @include('vacation-2026.dashboards._phase-summary')
        @include('vacation-2026.dashboards._activity-details')

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

        <!-- Status -->
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
            <p class="text-slate-600">Veuillez sélectionner une CISCO pour voir ses statistiques</p>
        </div>
        @endif
    </div>
@endsection
