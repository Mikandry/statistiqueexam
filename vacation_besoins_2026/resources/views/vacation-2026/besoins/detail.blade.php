@extends('layouts.app')
@section('title', $title.' - Vacation 2026')
@section('content')
@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); $warnings = []; @endphp
<div class="space-y-4">
    <a href="{{ $back }}" class="text-sm text-blue-700 underline">&larr; Retour à la liste</a>
    <div><h1 class="text-3xl font-bold text-slate-900">{{ $title }}</h1>@if($subtitle)<p class="text-sm text-slate-600">{{ $subtitle }}</p>@endif</div>
    @include('vacation-2026.besoins._kpis')
    <div class="grid gap-3 md:grid-cols-2">
        <div class="rounded-xl border bg-white p-4"><h2 class="mb-2 font-semibold">Par examen</h2>
            @foreach($byExam as $e => $t)<p class="text-sm">{{ $e }} : <b>{{ $n($t['persons']) }}</b> personnes · {{ $n($t['amount']) }} Ar</p>@endforeach</div>
        <div class="rounded-xl border bg-white p-4"><h2 class="mb-2 font-semibold">Par niveau</h2>
            @foreach($byLevel as $l => $t)<p class="text-sm">{{ $levels[$l] ?? $l }} : <b>{{ $n($t['persons']) }}</b> personnes · {{ $n($t['amount']) }} Ar</p>@endforeach</div>
    </div>
    <div class="rounded-xl border bg-white p-4"><h2 class="mb-2 font-semibold">Activités</h2>
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-100 text-left text-xs uppercase text-slate-600"><tr><th class="p-2">Examen</th><th class="p-2">Niveau</th><th class="p-2">Phase</th><th class="p-2">Activité</th><th class="p-2 text-right">Personnes</th><th class="p-2 text-right">Jours</th><th class="p-2 text-right">Montant Ar</th></tr></thead><tbody>
        @foreach($activities as $a)
            <tr class="border-t align-top"><td class="p-2">{{ $a['exam'] }}</td><td class="p-2">{{ $levels[$a['level']] ?? $a['level'] }}</td><td class="p-2">{{ $phases[$a['phase']] ?? $a['phase'] }}</td>
                <td class="p-2"><details><summary class="cursor-pointer font-medium">{{ $a['activity'] }}</summary><p class="text-xs text-slate-500">{{ $a['rule'] }}</p>
                    <ul class="text-xs">@foreach($a['roles'] as $r)<li>{{ $r['role'] }} : <b>{{ $n($r['persons']) }}</b></li>@endforeach</ul></details></td>
                <td class="p-2 text-right font-semibold">{{ $n($a['persons']) }}</td><td class="p-2 text-right">{{ $a['days_min'] === $a['days_max'] ? $a['days_min'] : $a['days_min'].'–'.$a['days_max'] }}</td><td class="p-2 text-right">{{ $n($a['amount']) }}</td></tr>
        @endforeach</tbody></table></div></div>
    @if($ciscos->isNotEmpty())
    <div class="rounded-xl border bg-white p-4"><h2 class="mb-2 font-semibold">CISCO</h2><table class="w-full text-sm"><tbody>
        @foreach($ciscos as $c)<tr class="border-t"><td class="p-2"><a class="text-blue-700 underline" href="{{ route('vacation2026.besoins.cisco.show', $c['id']) }}">{{ $c['nom'] }}</a></td><td class="p-2 text-right">{{ $n($c['persons']) }} pers.</td><td class="p-2 text-right">{{ $n($c['amount']) }} Ar</td></tr>@endforeach</tbody></table></div>
    @endif
    @if($centres->isNotEmpty())
    <div class="rounded-xl border bg-white p-4"><h2 class="mb-2 font-semibold">Centres</h2><table class="w-full text-sm"><tbody>
        @foreach($centres as $c)<tr class="border-t"><td class="p-2"><a class="text-blue-700 underline" href="{{ route('vacation2026.besoins.centres.show', $c['key']) }}">{{ $c['nom'] }}</a></td><td class="p-2 text-xs">{{ $c['type'] }}</td><td class="p-2 text-right">{{ $n($c['persons']) }} pers.</td><td class="p-2 text-right">{{ $n($c['amount']) }} Ar</td></tr>@endforeach</tbody></table></div>
    @endif
</div>
@endsection
