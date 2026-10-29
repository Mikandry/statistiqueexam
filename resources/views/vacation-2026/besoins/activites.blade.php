@extends('layouts.app')
@section('title', 'Personnes par activité · Vacation 2026')
@section('content')
@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp
<div class="ui-page">
    <div class="ui-head"><div><h1 class="ui-title">Personnes mobilisées par activité</h1>
        <p class="ui-sub">Pour chaque activité du décret : entités concernées, personnes mobilisées sur tout Madagascar, durée et montant. Cliquez une activité pour voir le détail par rôle.</p></div></div>
    @include('vacation-2026.besoins._nav')
    @include('vacation-2026.besoins._filters')
    @include('vacation-2026.besoins._kpis')
    @foreach($levels as $lk => $ll)
        @php $rows = $activities->where('level', $lk); @endphp
        @if($rows->isNotEmpty())
        <div class="ui-card">
            <div class="ui-card-h"><h2>{{ $ll }}</h2><small>{{ $n($rows->sum('persons')) }} personnes · {{ $n($rows->sum('amount')) }} Ar</small></div>
            <div class="ui-table-wrap"><table class="ui-table">
                <thead><tr><th>Examen</th><th>Phase</th><th>Activité</th><th class="num">Entités</th><th class="num">Personnes</th><th class="num">Moy./entité</th><th class="num">Jours</th><th class="num">Jours-agents</th><th class="num">Montant Ar</th></tr></thead>
                <tbody>
                @foreach($rows as $a)
                    <tr><td>{{ $a['exam'] }}</td><td>{{ $phases[$a['phase']] ?? $a['phase'] }}</td>
                        <td><details class="ui-details"><summary>{{ $a['activity'] }}</summary>
                            <span class="sub">{{ $a['rule'] }} — {{ $a['ref'] }}</span>
                            <ul>@foreach($a['roles'] as $r)<li>{{ $r['role'] }} : <b>{{ $n($r['persons']) }}</b> pers. · {{ $n($r['amount']) }} Ar</li>@endforeach</ul></details></td>
                        <td class="num">{{ $n($a['units']) }}</td><td class="num strong">{{ $n($a['persons']) }}</td><td class="num">{{ $a['persons_per_unit'] }}</td>
                        <td class="num">{{ $a['days_min'] === $a['days_max'] ? $a['days_min'] : $a['days_min'].'–'.$a['days_max'] }}</td>
                        <td class="num">{{ $n($a['person_days']) }}</td><td class="num">{{ $n($a['amount']) }}</td></tr>
                @endforeach</tbody></table></div>
        </div>
        @endif
    @endforeach
</div>
@endsection
