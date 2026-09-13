@extends('layouts.app')

@section('title', 'Centres d’examen — Vacation 2026')

@section('content')
@include('vacation-2026.dashboards._navigation', [
        'navBackLabel' => 'Dashboard global',
        'navBackRoute' => route('vacation2026.dashboard.global'),
    ])
<div class="space-y-5">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h1 class="text-2xl font-bold text-slate-900">Centres d’examen</h1><p class="mt-1 text-sm text-slate-600">Liste opérationnelle des besoins et coûts estimés.</p></div>
    @include('vacation-2026.dashboards._filters')
    <form method="GET" class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="exam" value="{{ $examFilter }}"><input type="hidden" name="phase" value="{{ $phaseFilter }}"><input type="hidden" name="activity_id" value="{{ $activityFilter }}">
        <input name="search" value="{{ $search }}" placeholder="Rechercher un centre" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm"><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Rechercher</button>
    </form>
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><h2 class="font-semibold text-slate-900">{{ number_format($totalCentres, 0, ',', ' ') }} centre(s)</h2><span class="text-sm text-slate-500">30 centres par page</span></div><div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-3">Centre</th><th class="px-4 py-3">Type</th><th class="px-4 py-3 text-right">Candidats</th><th class="px-4 py-3 text-right">Salles</th><th class="px-4 py-3 text-right">Personnel</th><th class="px-4 py-3 text-right">Montant</th><th class="px-4 py-3"></th></tr></thead><tbody>@forelse($dashboards as $centre)<tr class="border-t border-slate-100 hover:bg-slate-50"><td class="px-4 py-3 font-medium text-slate-900">{{ $centre['centre_name'] }}</td><td class="px-4 py-3 text-slate-600">{{ $centre['centre_type'] }}</td><td class="px-4 py-3 text-right">{{ number_format($centre['total_candidates'], 0, ',', ' ') }}</td><td class="px-4 py-3 text-right">{{ $centre['total_salles'] }}</td><td class="px-4 py-3 text-right font-semibold">{{ $centre['total_planned'] }}</td><td class="px-4 py-3 text-right">{{ number_format($centre['estimated_indemnity'], 0, ',', ' ') }} Ar</td><td class="px-4 py-3 text-right"><a class="font-medium text-blue-700 hover:underline" href="{{ route('vacation2026.centre', array_filter(['centre_id' => $centre['centre_ecrit_id'] ? null : $centre['centre_id'], 'centre_ecrit_id' => $centre['centre_ecrit_id'], 'exam' => $examFilter], fn ($value) => $value !== null && $value !== '')) }}">Détail</a></td></tr>@empty<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Aucun centre ne correspond aux critères.</td></tr>@endforelse</tbody></table></div>@if($dashboards->hasPages())<div class="border-t border-slate-200 px-5 py-3">{{ $dashboards->links() }}</div>@endif</div>
</div>
@endsection
