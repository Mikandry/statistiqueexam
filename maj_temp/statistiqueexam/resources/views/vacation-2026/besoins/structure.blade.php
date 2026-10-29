@extends('layouts.app')
@section('title', 'Centres de transcription · Vacation 2026')
@section('content')
@php $warnings = []; @endphp
<div class="ui-page">
    <div class="ui-head"><div><h1 class="ui-title">Centres de transcription</h1>
        <p class="ui-sub">Entités distinctes des centres d'écrit et de correction. Une CISCO peut en avoir plusieurs (zones éloignées : 2 ou 3). Sans centre saisi, la CISCO compte comme un seul centre. {{ $withCentres }} CISCO en ont au moins un.</p></div></div>
    @include('vacation-2026.besoins._nav')
    <form method="GET" class="ui-card"><div class="ui-filter">
        <label class="ui-field"><span>DREN</span><select name="dren_id" class="ui-select" onchange="this.form.submit()"><option value="">Toutes</option>@foreach($drens as $d)<option value="{{ $d->id }}" @selected($drenId === $d->id)>{{ $d->nom }}</option>@endforeach</select></label>
    </div></form>

    <div class="ui-card"><div class="ui-card-h"><h2>Ajouter un centre</h2></div>
    <form method="POST" action="{{ route('vacation2026.besoins.transcription.store') }}" class="ui-filter">@csrf
        <label class="ui-field"><span>CISCO</span><select name="cisco_id" class="ui-select" required>@foreach($ciscos as $c)<option value="{{ $c->id }}">{{ $c->nom }}</option>@endforeach</select></label>
        <label class="ui-field"><span>Nom</span><input name="nom" class="ui-input" required></label>
        <label class="ui-field"><span>Localisation</span><input name="localisation" class="ui-input"></label>
        <label class="ui-field" style="min-width:90px"><span>Ordre</span><input type="number" name="ordre" min="0" value="0" class="ui-input"></label>
        <label class="ui-field" style="min-width:110px"><span>Candidats CEPE</span><input type="number" name="candidats_cepe" min="0" class="ui-input"></label>
        <label class="ui-field" style="min-width:110px"><span>Candidats BEPC</span><input type="number" name="candidats_bepc" min="0" class="ui-input"></label>
        <button class="ui-btn accent">Ajouter</button></form></div>

    <div class="ui-card"><div class="ui-table-wrap"><table class="ui-table">
        <thead><tr><th>CISCO</th><th>DREN · Province</th><th>Centres de transcription</th></tr></thead><tbody>
        @foreach($ciscos as $c)
            <tr><td class="strong">{{ $c->nom }}</td><td>{{ $c->dren?->nom }}<span class="sub">{{ $c->provinceRef?->nom ?? $c->dren?->province }}</span></td>
                <td>@forelse($c->transcriptionCentres as $t)
                    <form method="POST" action="{{ route('vacation2026.besoins.transcription.update', $t) }}" class="ui-row" style="margin-bottom:6px">@csrf @method('PUT')
                        <input type="hidden" name="cisco_id" value="{{ $c->id }}">
                        <input name="nom" value="{{ $t->nom }}" class="ui-input sm" style="width:170px"><input name="localisation" value="{{ $t->localisation }}" placeholder="Lieu" class="ui-input sm" style="width:140px">
                        <input type="number" name="ordre" value="{{ $t->ordre }}" class="ui-input sm" style="width:64px" title="Ordre"><input type="number" name="candidats_cepe" value="{{ $t->candidats_cepe }}" placeholder="CEPE" class="ui-input sm" style="width:80px"><input type="number" name="candidats_bepc" value="{{ $t->candidats_bepc }}" placeholder="BEPC" class="ui-input sm" style="width:80px">
                        <button class="ui-btn sm accent">Enregistrer</button>
                        <button class="ui-btn sm danger" form="del{{ $t->id }}" onclick="return confirm('Supprimer ce centre ?')">Supprimer</button></form>
                    <form id="del{{ $t->id }}" method="POST" action="{{ route('vacation2026.besoins.transcription.destroy', $t) }}">@csrf @method('DELETE')</form>
                @empty<span class="ui-muted ui-sm">Aucun : la CISCO compte comme un centre.</span>@endforelse</td></tr>
        @endforeach</tbody></table></div></div>
</div>
@endsection
