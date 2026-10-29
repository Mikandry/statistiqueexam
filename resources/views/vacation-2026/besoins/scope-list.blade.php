@extends('layouts.app')
@section('title', $title.' · Vacation 2026')
@section('content')
@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp
<div class="ui-page">
    <div class="ui-head"><div><h1 class="ui-title">{{ $title }}</h1></div></div>
    @include('vacation-2026.besoins._nav')
    @include('vacation-2026.besoins._filters')
    @include('vacation-2026.besoins._kpis')
    <div class="ui-card"><div class="ui-table-wrap"><table class="ui-table">
        <thead><tr>
        @if($kind === 'CENTRE')
            <th>Centre</th><th>Type</th><th>CISCO</th><th>DREN</th><th>Examen</th><th class="num">Candidats</th><th class="num">Besoin</th><th class="num">Affectés</th><th class="num">Validés</th><th class="num">Montant Ar</th>
        @elseif($kind === 'PROVINCE')
            <th>Province</th><th class="num">DREN</th><th class="num">CISCO</th><th class="num">Centres</th><th class="num">Candidats</th><th class="num">Personnes</th><th class="num">CEPE Ar</th><th class="num">BEPC Ar</th><th class="num">Total Ar</th>
        @else
            <th>{{ $kind }}</th>@if($kind === 'CISCO')<th>DREN</th>@endif
            <th class="num">Besoin</th><th class="num">Affectés</th><th class="num">Validés</th><th class="num">DREN/CISCO</th><th class="num">Écrit</th><th class="num">Correction</th><th class="num">Transcr.</th><th class="num">EPS</th><th class="num">CEPE Ar</th><th class="num">BEPC Ar</th><th class="num">Total Ar</th>
        @endif
        </tr></thead><tbody>
        @forelse($rows as $r)
            <tr>
            @if($kind === 'CENTRE')
                <td><a href="{{ route('vacation2026.besoins.centres.show', $r['key']) }}">{{ $r['nom'] }}</a>@if($r['parent_nom'])<span class="sub">{{ $r['type'] === 'TRANSCRIPTION' ? 'Lieu : ' : 'Sous-centre de ' }}{{ $r['parent_nom'] }}</span>@endif</td>
                <td><span class="ui-badge">{{ $r['type'] }}</span></td><td>{{ $r['cisco_nom'] }}</td><td>{{ $r['dren_nom'] }}</td><td>{{ implode(' + ', $r['exams']) }}</td>
                <td class="num">{{ $n($r['candidates']) }}</td><td class="num">{{ $n($r['persons']) }}</td><td class="num">{{ $n($r['assign']['assigned'] ?? 0) }}</td><td class="num">{{ $n($r['assign']['validated'] ?? 0) }}</td><td class="num strong">{{ $n($r['amount']) }}</td>
            @elseif($kind === 'PROVINCE')
                <td class="strong">{{ $r['nom'] }}</td><td class="num">{{ $r['drens'] }}</td><td class="num">{{ $r['ciscos'] }}</td><td class="num">{{ $r['centres'] }}</td><td class="num">{{ $n($r['candidates']) }}</td><td class="num">{{ $n($r['persons']) }}</td>
                <td class="num">{{ $n($r['by_exam']['CEPE']['amount'] ?? 0) }}</td><td class="num">{{ $n($r['by_exam']['BEPC']['amount'] ?? 0) }}</td><td class="num strong">{{ $n($r['amount']) }}</td>
            @else
                @php $bl = $r['by_level']; $lv = fn ($k) => $bl[$k]['amount'] ?? 0; @endphp
                <td><a href="{{ route($kind === 'CISCO' ? 'vacation2026.besoins.cisco.show' : 'vacation2026.besoins.dren.show', $r['id']) }}">{{ $r['nom'] }}</a></td>
                @if($kind === 'CISCO')<td>{{ $r['dren_nom'] ?? '' }}</td>@endif
                <td class="num">{{ $n($r['persons']) }}</td><td class="num">{{ $n($r['assign']['assigned'] ?? 0) }}</td><td class="num">{{ $n($r['assign']['validated'] ?? 0) }}</td>
                <td class="num">{{ $n($lv('DREN') + $lv('CISCO')) }}</td><td class="num">{{ $n($lv('CENTRE_ECRIT')) }}</td><td class="num">{{ $n($lv('CENTRE_CORRECTION')) }}</td><td class="num">{{ $n($lv('TRANSCRIPTION')) }}</td><td class="num">{{ $n($lv('EPS')) }}</td>
                <td class="num">{{ $n($r['by_exam']['CEPE']['amount'] ?? 0) }}</td><td class="num">{{ $n($r['by_exam']['BEPC']['amount'] ?? 0) }}</td><td class="num strong">{{ $n($r['amount']) }}</td>
            @endif
            </tr>
        @empty
            <tr><td colspan="12" class="ui-empty">Aucune ligne pour cette sélection.</td></tr>
        @endforelse</tbody></table></div></div>
</div>
@endsection
