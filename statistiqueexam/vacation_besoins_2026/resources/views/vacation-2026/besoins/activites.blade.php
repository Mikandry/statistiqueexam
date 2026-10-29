@extends('layouts.app')
@section('title', 'Personnes par activité - Vacation 2026')
@section('content')
@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp
<div class="space-y-4">
    <div><h1 class="text-3xl font-bold text-slate-900">Personnes mobilisées par activité</h1>
        <p class="text-sm text-slate-600">Pour chaque activité du décret : nombre d'entités concernées, personnes mobilisées sur tout Madagascar, durée et montant. Cliquez une ligne pour le détail par rôle.</p></div>
    @include('vacation-2026.besoins._nav')
    @include('vacation-2026.besoins._filters')
    @include('vacation-2026.besoins._kpis')
    @foreach($levels as $lk => $ll)
        @php $rows = $activities->where('level', $lk); @endphp
        @if($rows->isNotEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-2 font-semibold">{{ $ll }} <span class="text-sm font-normal text-slate-500">— {{ $n($rows->sum('persons')) }} personnes · {{ $n($rows->sum('amount')) }} Ar</span></h2>
            <div class="overflow-x-auto"><table class="w-full text-sm">
                <thead class="bg-slate-100 text-left text-xs uppercase text-slate-600"><tr><th class="p-2">Examen</th><th class="p-2">Phase</th><th class="p-2">Activité</th><th class="p-2 text-right">Entités</th><th class="p-2 text-right">Personnes</th><th class="p-2 text-right">Moy./entité</th><th class="p-2 text-right">Jours</th><th class="p-2 text-right">Jours-agents</th><th class="p-2 text-right">Montant Ar</th></tr></thead>
                <tbody>
                @foreach($rows as $a)
                    <tr class="border-t align-top"><td class="p-2">{{ $a['exam'] }}</td><td class="p-2">{{ $phases[$a['phase']] ?? $a['phase'] }}</td>
                        <td class="p-2"><details><summary class="cursor-pointer font-medium">{{ $a['activity'] }}</summary>
                            <p class="mt-1 text-xs text-slate-500">{{ $a['rule'] }} — <em>{{ $a['ref'] }}</em></p>
                            <ul class="mt-1 text-xs text-slate-700">@foreach($a['roles'] as $r)<li>{{ $r['role'] }} : <b>{{ $n($r['persons']) }}</b> pers. · {{ $n($r['amount']) }} Ar</li>@endforeach</ul></details></td>
                        <td class="p-2 text-right">{{ $n($a['units']) }}</td><td class="p-2 text-right font-semibold">{{ $n($a['persons']) }}</td><td class="p-2 text-right">{{ $a['persons_per_unit'] }}</td>
                        <td class="p-2 text-right">{{ $a['days_min'] === $a['days_max'] ? $a['days_min'] : $a['days_min'].'–'.$a['days_max'] }}</td>
                        <td class="p-2 text-right">{{ $n($a['person_days']) }}</td><td class="p-2 text-right">{{ $n($a['amount']) }}</td></tr>
                @endforeach</tbody></table></div>
        </div>
        @endif
    @endforeach
</div>
@endsection
