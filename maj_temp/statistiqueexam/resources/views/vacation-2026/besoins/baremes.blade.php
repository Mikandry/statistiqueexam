@extends('layouts.app')
@section('title', 'Barèmes · Vacation 2026')
@section('content')
@php $warnings = []; @endphp
<div class="ui-page">
    <div class="ui-head"><div><h1 class="ui-title">Barèmes des indemnités</h1>
        <p class="ui-sub">Le décret renvoie les taux à un arrêté (Art. 5 et 7). Montant = taux × personnes × jours (mode « Jour ») ou taux × personnes (mode « Forfait »). Case vide = taux par défaut ; l'indemnité spéciale n'a jamais de taux par défaut.</p></div></div>
    @include('vacation-2026.besoins._nav')
    <form method="POST" action="{{ route('vacation2026.besoins.baremes.update') }}" class="ui-page">
        @csrf @method('PUT')
        <div class="ui-card"><div class="ui-card-h"><h2>Taux par défaut (rôles sans taux propre)</h2></div>
            <div class="ui-filter">
                @foreach($exams as $e)<label class="ui-field"><span>{{ $e }} (Ar)</span><input type="number" step="0.01" min="0" name="default[{{ $e }}]" value="{{ isset($defaults[$e]) ? (float) $defaults[$e]['montant'] : '' }}" class="ui-input"></label>@endforeach
                <label class="ui-field"><span>Mode</span><select name="default_mode" class="ui-select"><option value="JOUR">Par jour</option><option value="FORFAIT" @selected(($defaults['CEPE']['mode'] ?? 'JOUR') === 'FORFAIT')>Forfait</option></select></label>
            </div></div>
        @foreach($groups as $group => $items)
        <div class="ui-card"><div class="ui-card-h"><h2>{{ $group }}</h2></div>
            <div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Rôle</th><th>Mode</th>@foreach($exams as $e)<th>{{ $e }} (Ar)</th>@endforeach</tr></thead><tbody>
            @foreach($items as $it)
                @php $mode = $table[$it['code']]['CEPE']['mode'] ?? $table[$it['code']]['BEPC']['mode'] ?? $it['mode']; $k = str_replace('.', '__', $it['code']); @endphp
                <tr><td>{{ $it['label'] }}</td>
                    <td><select name="modes[{{ $k }}]" class="ui-select sm"><option value="JOUR" @selected($mode === 'JOUR')>Jour</option><option value="FORFAIT" @selected($mode === 'FORFAIT')>Forfait</option></select></td>
                    @foreach($exams as $e)<td><input type="number" step="0.01" min="0" name="rates[{{ $k }}][{{ $e }}]" value="{{ isset($table[$it['code']][$e]) ? (float) $table[$it['code']][$e]['montant'] : '' }}" class="ui-input sm"></td>@endforeach</tr>
            @endforeach</tbody></table></div>
        </div>
        @endforeach
        <div><button class="ui-btn accent">Enregistrer les barèmes</button></div>
    </form>
</div>
@endsection
