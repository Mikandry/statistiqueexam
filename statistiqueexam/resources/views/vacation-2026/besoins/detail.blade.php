@extends('layouts.app')
@section('title', $title.' · Vacation 2026')
@section('content')
@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); $warnings = []; @endphp
<div class="ui-page">
    <div class="ui-head"><div>
        <div class="ui-crumb"><a href="{{ $back }}">&larr; Retour à la liste</a></div>
        <h1 class="ui-title">{{ $title }}</h1>@if($subtitle)<p class="ui-sub">{{ $subtitle }}</p>@endif</div></div>
    @include('vacation-2026.besoins._kpis')
    @if($assign)@include('vacation-2026.besoins._assign')@endif

    <div class="ui-split">
        <div class="ui-card"><div class="ui-card-h"><h2>Par examen</h2></div><div class="ui-card-b pad">
            @foreach($byExam as $e => $t)<div class="ui-kv"><span>{{ $e }}</span><b>{{ $n($t['persons']) }} pers. · {{ $n($t['amount']) }} Ar</b></div>@endforeach</div></div>
        <div class="ui-card"><div class="ui-card-h"><h2>Par catégorie</h2></div><div class="ui-card-b pad">
            @foreach($byCategory as $c)<div class="ui-kv"><span>{{ $c['category'] }}</span><b>{{ $n($c['persons']) }} pers. · {{ $n($c['amount']) }} Ar</b></div>@endforeach</div></div>
    </div>

    @foreach($phaseBlocks as $b)
    <div class="ui-card">
        <div class="ui-card-h"><h2>{{ $b['label'] }}</h2><small>{{ $n($b['totals']['persons']) }} personnes · {{ $n($b['totals']['amount']) }} Ar</small></div>
        <div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Examen</th><th>Niveau</th><th>Activité</th><th class="num">Personnes</th><th class="num">Jours</th><th class="num">Montant Ar</th></tr></thead><tbody>
        @foreach($b['activities'] as $a)
            <tr><td>{{ $a['exam'] }}</td><td>{{ $levels[$a['level']] ?? $a['level'] }}</td>
                <td><details class="ui-details"><summary>{{ $a['activity'] }}</summary><span class="sub">{{ $a['rule'] }}</span>
                    <ul>@foreach($a['roles'] as $r)<li>{{ $r['role'] }} : <b>{{ $n($r['persons']) }}</b></li>@endforeach</ul></details></td>
                <td class="num strong">{{ $n($a['persons']) }}</td><td class="num">{{ $a['days_min'] === $a['days_max'] ? $a['days_min'] : $a['days_min'].'–'.$a['days_max'] }}</td><td class="num">{{ $n($a['amount']) }}</td></tr>
        @endforeach</tbody></table></div>
    </div>
    @endforeach

    @if($ciscos->isNotEmpty())
    <div class="ui-card"><div class="ui-card-h"><h2>CISCO</h2></div><div class="ui-table-wrap"><table class="ui-table"><tbody>
        @foreach($ciscos as $c)<tr><td><a href="{{ route('vacation2026.besoins.cisco.show', $c['id']) }}">{{ $c['nom'] }}</a></td><td class="num">{{ $n($c['persons']) }} pers.</td><td class="num">{{ $n($c['amount']) }} Ar</td></tr>@endforeach</tbody></table></div></div>
    @endif
    @if($centres->isNotEmpty())
    <div class="ui-card"><div class="ui-card-h"><h2>Centres</h2></div><div class="ui-table-wrap"><table class="ui-table"><tbody>
        @foreach($centres as $c)<tr><td><a href="{{ route('vacation2026.besoins.centres.show', $c['key']) }}">{{ $c['nom'] }}</a></td><td><span class="ui-badge">{{ $c['type'] }}</span></td><td class="num">{{ $n($c['persons']) }} pers.</td><td class="num">{{ $n($c['amount']) }} Ar</td></tr>@endforeach</tbody></table></div></div>
    @endif
</div>
@endsection
