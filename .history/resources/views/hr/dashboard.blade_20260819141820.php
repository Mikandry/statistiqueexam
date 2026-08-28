@extends('layouts.app')

@section('title', 'Gestion du personnel')
@section('subtitle', 'Situation du personnel et affectations')

@section('content')
<div class="space-y-6">
    @if(session('success'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ $errors->first() }}</div>@endif
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([['Agents actifs', $stats['total'], 'bg-slate-900 text-white'], ['Présents', $stats['present'], 'bg-emerald-600 text-white'], ['Affectation temporaire', $stats['affectation_temporaire'], 'bg-amber-500 text-white'], ['Sans affectation active', $stats['sans_affectation'], 'bg-white text-slate-900']] as [$label, $value, $class])
            <article class="rounded-lg border border-slate-200 p-4 shadow-sm {{ $class }}"><p class="text-xs font-bold uppercase opacity-70">{{ $label }}</p><p class="mt-2 text-2xl font-black">{{ $value }}</p></article>
        @endforeach
    </section>
    <section class="flex flex-wrap items-end justify-between gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-2"><input type="date" name="date" value="{{ $selectedDate }}" class="rounded-lg border-slate-300 text-sm"><input name="q" value="{{ $search }}" placeholder="Nom ou matricule" class="rounded-lg border-slate-300 text-sm"><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filtrer</button></form>
        <a href="{{ route('hr.agents.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold">Personnel</a>
    </section>
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-200 p-4"><h2 class="font-black">Situation du personnel le {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</h2></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left text-xs font-black uppercase text-slate-500"><tr><th class="px-3 py-3">Agent</th><th class="px-3 py-3">Fonction</th><th class="px-3 py-3">Service</th><th class="px-3 py-3">Situation actuelle</th><th class="px-3 py-3">Début</th><th class="px-3 py-3">Fin</th><th class="px-3 py-3">Disponibilité</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($situations as $s)<tr><td class="px-3 py-3 font-semibold">{{ $s['agent']->full_name }}<br><span class="text-xs text-slate-500">{{ $s['agent']->matricule ?: 'Sans matricule' }}</span></td><td class="px-3 py-3">{{ $s['agent']->fonction ?: '—' }}</td><td class="px-3 py-3">{{ $s['agent']->service ?: '—' }}</td><td class="px-3 py-3"><span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $s['code'] === 'present' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">{{ $s['label'] }}</span></td><td class="px-3 py-3">{{ $s['start']?->format('d/m/Y') ?: '—' }}</td><td class="px-3 py-3">{{ $s['end']?->format('d/m/Y') ?: '—' }}</td><td class="px-3 py-3">{{ $s['availability'] }}</td></tr>@empty<tr><td colspan="7" class="px-4 py-10 text-center font-semibold text-slate-500">Aucun agent trouvé.</td></tr>@endforelse</tbody></table></div></section>
</div>
@endsection
