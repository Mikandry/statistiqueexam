@extends('layouts.app')
@section('title', 'Bilan des activités du personnel')
@section('content')
@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp
<div class="ui-page">
    <div class="ui-head"><div><h1 class="ui-title">Bilan des activités du personnel</h1>
        <p class="ui-sub">Congés, absences, missions, formations et ateliers validés, sur la période choisie. Un jour partagé par deux événements n'est compté qu'une fois dans le total consolidé.</p></div>
        <div class="ui-actions"><a class="ui-btn ghost" href="{{ route('hr.dashboard') }}">Disponibilités du jour</a></div></div>

    <div class="ui-grid c4">
        <div class="ui-kpi blue"><div class="l">Agents</div><div class="v">{{ $n($overview['total']) }}</div><div class="n">{{ $n($overview['active']) }} actifs · {{ $n($overview['inactive']) }} inactifs</div></div>
        <div class="ui-kpi amber"><div class="l">Validations en attente</div><div class="v">{{ $n($overview['pending_validations']) }}</div><div class="n">événements au statut « demandé »</div></div>
        <div class="ui-kpi violet"><div class="l">Affectations vacation</div><div class="v">{{ $n($overview['vacation_assignments']) }}</div><div class="n">{{ $n($overview['vacation_agents']) }} agents de vacation, dont {{ $n($overview['vacation_agents_linked_to_hr']) }} rapprochés du personnel par matricule</div></div>
        <div class="ui-kpi accent"><div class="l">Période</div><div class="v" style="font-size:1.1rem">{{ $from->format('d/m/Y') }} → {{ $to->format('d/m/Y') }}</div></div>
    </div>

    <div class="ui-split">
        <div class="ui-card"><div class="ui-card-h"><h2>Répartition par unité</h2></div><div class="ui-card-b pad">
            @forelse($overview['by_unit'] as $label => $c)<div class="ui-kv"><span>{{ $label }}</span><b>{{ $c }}</b></div>@empty<p class="ui-muted">Aucun agent actif.</p>@endforelse</div></div>
        <div class="ui-card"><div class="ui-card-h"><h2>Répartition par fonction</h2></div><div class="ui-card-b pad">
            @forelse($overview['by_function'] as $label => $c)<div class="ui-kv"><span>{{ $label }}</span><b>{{ $c }}</b></div>@empty<p class="ui-muted">Aucun agent actif.</p>@endforelse</div></div>
    </div>

    <form method="GET" class="ui-card"><div class="ui-filter">
        <label class="ui-field"><span>Du</span><input type="date" name="from" value="{{ $from->toDateString() }}" class="ui-input"></label>
        <label class="ui-field"><span>Au</span><input type="date" name="to" value="{{ $to->toDateString() }}" class="ui-input"></label>
        <label class="ui-field"><span>Agent ou matricule</span><input name="q" value="{{ $search }}" class="ui-input"></label>
        <button class="ui-btn accent">Filtrer</button>
    </div></form>

    <div class="ui-card"><div class="ui-table-wrap"><table class="ui-table">
        <thead><tr><th>Agent</th>@foreach($priority as $t)<th class="num">{{ $types[$t] ?? $t }}</th>@endforeach<th class="num">Total consolidé</th><th class="num">Chevauchement</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr><td><a href="{{ route('hr.activity.show', ['agent' => $r['agent']->id, 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}">{{ $r['agent']->full_name }}</a><span class="sub">{{ $r['agent']->matricule }}</span></td>
                @foreach($priority as $t)<td class="num">{{ $r['consolidated'][$t] ?? 0 ?: '—' }}</td>@endforeach
                <td class="num strong">{{ $r['union_days'] }}</td>
                <td class="num">@if($r['overlap_days'] > 0)<span class="ui-badge amber">{{ $r['overlap_days'] }} j</span>@else —@endif</td></tr>
        @empty
            <tr><td colspan="12" class="ui-empty">Aucun agent.</td></tr>
        @endforelse</tbody></table></div></div>
    <p class="ui-muted ui-sm">Les colonnes donnent les jours attribués à chaque type après suppression des chevauchements. Le détail « jours bruts » est dans la fiche de chaque agent.</p>
</div>
@endsection
