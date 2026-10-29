@extends('layouts.app')
@section('title', 'Bilan de '.$agent->full_name)
@section('content')
<div class="ui-page">
    <div class="ui-head"><div><div class="ui-crumb"><a href="{{ route('hr.activity.index', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}">&larr; Bilan du personnel</a></div>
        <h1 class="ui-title">{{ $agent->full_name }}</h1><p class="ui-sub">{{ $agent->matricule }} · du {{ $from->format('d/m/Y') }} au {{ $to->format('d/m/Y') }}</p></div></div>
    <form method="GET" class="ui-card"><div class="ui-filter">
        <label class="ui-field"><span>Du</span><input type="date" name="from" value="{{ $from->toDateString() }}" class="ui-input"></label>
        <label class="ui-field"><span>Au</span><input type="date" name="to" value="{{ $to->toDateString() }}" class="ui-input"></label>
        <button class="ui-btn accent">Filtrer</button></div></form>
    <div class="ui-card"><div class="ui-table-wrap"><table class="ui-table">
        <thead><tr><th>Type</th><th class="num">Jours bruts</th><th class="num">Jours attribués (sans doublon)</th></tr></thead><tbody>
        @foreach($types as $t => $label)
            @if(($row['raw'][$t] ?? 0) > 0)<tr><td>{{ $label }}</td><td class="num">{{ $row['raw'][$t] }}</td><td class="num">{{ $row['consolidated'][$t] ?? 0 }}</td></tr>@endif
        @endforeach</tbody>
        <tfoot><tr><td>Total</td><td class="num">{{ array_sum($row['raw']) }}</td><td class="num">{{ $row['union_days'] }}</td></tr></tfoot></table></div></div>
    <div class="ui-card"><div class="ui-card-h"><h2>Historique</h2></div><div class="ui-table-wrap"><table class="ui-table">
        <thead><tr><th>Type</th><th>Intitulé</th><th>Début</th><th>Fin</th><th>Statut</th></tr></thead><tbody>
        @forelse($events as $e)<tr><td>{{ $types[$e->type] ?? $e->type }}</td><td>{{ $e->title ?: $e->motif }}</td><td>{{ $e->date_debut?->format('d/m/Y') }}</td><td>{{ ($e->date_fin ?: $e->date_debut)?->format('d/m/Y') }}</td><td><span class="ui-badge green">{{ \App\Models\HrEvent::STATUSES[$e->status] ?? $e->status }}</span></td></tr>
        @empty<tr><td colspan="5" class="ui-empty">Aucun événement validé sur cette période.</td></tr>@endforelse</tbody></table></div></div>
</div>
@endsection
