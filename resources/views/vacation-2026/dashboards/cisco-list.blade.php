@extends('layouts.app')

@section('title', 'Tableau de bord CISCO (Liste) - Vacation 2026')
@section('content')

    @include('vacation-2026.dashboards._navigation')
    @include('vacation-2026.dashboards._filters')

    <div class="space-y-4">
        <!-- Header -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5 md:p-6">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tableau de bord CISCO</h1>
                <p class="mt-1 text-sm text-slate-600">Statistiques par CISCO - Vacation 2026</p>
            </div>
        </div>

        <!-- CISCO List -->
        <div class="grid grid-cols-1 gap-4">
            @forelse($dashboards as $dashboard)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-slate-900">{{ $dashboard['cisco_name'] }}</h3>
                        <p class="text-sm text-slate-600 mt-1">
                            {{ $dashboard['dren_name'] }} • 
                            {{ $dashboard['centre_count'] }} Centres • 
                            {{ $dashboard['total_candidates'] }} Candidats
                        </p>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <p class="text-xs text-slate-600 font-semibold">AFFECTÉ</p>
                            <p class="text-lg font-bold text-emerald-600">{{ $dashboard['total_assigned'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-600 font-semibold">PRÉVU</p>
                            <p class="text-lg font-bold text-slate-900">{{ $dashboard['total_planned'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-600 font-semibold">TAUX</p>
                            <p class="text-lg font-bold text-slate-900">{{ $dashboard['completion_percentage'] }}%</p>
                        </div>
                    </div>
                    <a href="{{ route('vacation2026.cisco', ['cisco_id' => $dashboard['cisco_id']]) }}" 
                       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Détails →
                    </a>
                </div>
            </div>
            @empty
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8 text-center">
                <p class="text-slate-600">Aucune CISCO disponible</p>
            </div>
            @endforelse
        </div>
    </div>
@endsection
