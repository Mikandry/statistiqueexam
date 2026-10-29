@extends('layouts.app')
@section('title', $title.' - Vacation 2026')
@section('content')
@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp
<div class="space-y-4">
    <h1 class="text-3xl font-bold text-slate-900">{{ $title }}</h1>
    @include('vacation-2026.besoins._nav')
    @include('vacation-2026.besoins._filters')
    @include('vacation-2026.besoins._kpis')
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white"><table class="w-full text-sm">
        <thead class="bg-slate-100 text-left text-xs uppercase text-slate-600"><tr>
        @if($kind === 'CENTRE')
            <th class="p-2">Centre</th><th class="p-2">Type</th><th class="p-2">CISCO</th><th class="p-2">DREN</th><th class="p-2">Examen</th><th class="p-2 text-right">Candidats</th><th class="p-2 text-right">Personnes</th><th class="p-2 text-right">Montant Ar</th>
        @else
            <th class="p-2">{{ $kind === 'CISCO' ? 'CISCO' : 'DREN' }}</th>@if($kind === 'CISCO')<th class="p-2">DREN</th>@endif
            <th class="p-2 text-right">Personnes</th><th class="p-2 text-right">DREN/CISCO</th><th class="p-2 text-right">Écrit</th><th class="p-2 text-right">Correction</th><th class="p-2 text-right">Transcr.</th><th class="p-2 text-right">EPS</th><th class="p-2 text-right">CEPE Ar</th><th class="p-2 text-right">BEPC Ar</th><th class="p-2 text-right">Total Ar</th>
        @endif
        </tr></thead><tbody>
        @foreach($rows as $r)
            <tr class="border-t">
            @if($kind === 'CENTRE')
                <td class="p-2"><a class="text-blue-700 underline" href="{{ route('vacation2026.besoins.centres.show', $r['key']) }}">{{ $r['nom'] }}</a>@if($r['parent_nom'])<div class="text-xs text-slate-500">sous-centre de {{ $r['parent_nom'] }}</div>@endif</td>
                <td class="p-2 text-xs">{{ $r['type'] }}</td><td class="p-2">{{ $r['cisco_nom'] }}</td><td class="p-2">{{ $r['dren_nom'] }}</td><td class="p-2">{{ implode('+', $r['exams']) }}</td>
                <td class="p-2 text-right">{{ $n($r['candidates']) }}</td><td class="p-2 text-right">{{ $n($r['persons']) }}</td><td class="p-2 text-right font-semibold">{{ $n($r['amount']) }}</td>
            @else
                @php $bl = $r['by_level']; $lv = fn ($k) => $bl[$k]['amount'] ?? 0; @endphp
                <td class="p-2"><a class="text-blue-700 underline" href="{{ route($kind === 'CISCO' ? 'vacation2026.besoins.cisco.show' : 'vacation2026.besoins.dren.show', $r['id']) }}">{{ $r['nom'] }}</a></td>
                @if($kind === 'CISCO')<td class="p-2">{{ $r['dren_nom'] }}</td>@endif
                <td class="p-2 text-right">{{ $n($r['persons']) }}</td><td class="p-2 text-right">{{ $n($lv('DREN') + $lv('CISCO')) }}</td><td class="p-2 text-right">{{ $n($lv('CENTRE_ECRIT')) }}</td><td class="p-2 text-right">{{ $n($lv('CENTRE_CORRECTION')) }}</td><td class="p-2 text-right">{{ $n($lv('TRANSCRIPTION')) }}</td><td class="p-2 text-right">{{ $n($lv('EPS')) }}</td>
                <td class="p-2 text-right">{{ $n($r['by_exam']['CEPE']['amount'] ?? 0) }}</td><td class="p-2 text-right">{{ $n($r['by_exam']['BEPC']['amount'] ?? 0) }}</td><td class="p-2 text-right font-semibold">{{ $n($r['amount']) }}</td>
            @endif
            </tr>
        @endforeach</tbody></table></div>
</div>
@endsection
