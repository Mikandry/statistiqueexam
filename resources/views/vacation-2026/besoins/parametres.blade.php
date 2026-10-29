@extends('layouts.app')
@section('title', 'Paramètres · Vacation 2026')
@section('content')
@php $warnings = []; $types = json_decode($params['transcription_types'], true) ?: []; @endphp
<div class="ui-page">
    <div class="ui-head"><div><h1 class="ui-title">Paramètres du calcul</h1>
        <p class="ui-sub">Options qui dépendent de l'interprétation du décret. Chaque réglage est expliqué ; rien n'est inventé par défaut.</p></div></div>
    @include('vacation-2026.besoins._nav')
    <form method="POST" action="{{ route('vacation2026.besoins.parametres.update') }}" class="ui-page">
        @csrf @method('PUT')
        <div class="ui-card"><div class="ui-card-b pad"><div class="ui-grid c2">
            <label class="ui-field"><span>Année des répartitions</span><input name="annee" value="{{ old('annee', $params['annee']) }}" class="ui-input"></label>
            <label class="ui-field"><span>Nombre de provinces (0 = 6)</span><input type="number" name="nb_provinces" value="{{ old('nb_provinces', $params['nb_provinces']) }}" class="ui-input"></label>
            <label class="ui-field"><span>Art. 12 : matières pour les enveloppes CEPE (agents = matières × tranches de 500)</span><input type="number" name="cepe_enveloppes_matieres" value="{{ old('cepe_enveloppes_matieres', $params['cepe_enveloppes_matieres']) }}" class="ui-input"></label>
            <label class="ui-field"><span>BEPC écrit : jours par défaut</span><select name="bepc_jours_ecrit_defaut" class="ui-select"><option value="3" @selected($params['bepc_jours_ecrit_defaut']==='3')>3 (option B)</option><option value="4" @selected($params['bepc_jours_ecrit_defaut']==='4')>4 (jumelés)</option></select></label>
            <label class="ui-field"><span>BEPC : % de candidats en anglais (si langues non détaillées)</span><input type="number" name="bepc_anglais_pct" value="{{ old('bepc_anglais_pct', $params['bepc_anglais_pct']) }}" class="ui-input"></label>
            <label class="ui-field"><span>Centres EPS estimés par CISCO (si aucun configuré)</span><input type="number" name="eps_centres_par_cisco" value="{{ old('eps_centres_par_cisco', $params['eps_centres_par_cisco']) }}" class="ui-input"></label>
            <div><div class="ui-label" style="margin-bottom:6px">Centres assurant la transcription</div>
                @foreach($transcriptionChoices as $t)<label class="ui-check"><input type="checkbox" name="transcription_types[]" value="{{ $t }}" @checked(in_array($t, $types, true))> {{ $t }}</label>@endforeach</div>
            <div style="display:grid;gap:8px;align-content:start">
                <label class="ui-check"><input type="checkbox" name="cepe_enveloppes_actif" value="1" @checked($params['cepe_enveloppes_actif']==='1')> Appliquer l'Art. 12 (enveloppes CEPE 2026 en CISCO)</label>
                <label class="ui-check"><input type="checkbox" name="inclure_speciale" value="1" @checked($params['inclure_speciale']==='1')> Inclure l'indemnité spéciale (Art. 7)</label>
                <label class="ui-check"><input type="checkbox" name="entites_sans_centre" value="1" @checked($params['entites_sans_centre']==='1')> Compter DREN/CISCO sans centre saisi</label></div>
        </div></div></div>
        <div class="ui-card">
            <div class="ui-card-h"><h2>DREN : province et chef-lieu</h2><small>Les travaux provinciaux du CEPE sont comptés une fois par province, à la DREN chef-lieu.</small></div>
            <div class="ui-card-b pad"><label class="ui-check"><input type="checkbox" name="deviner_provinces" value="1"> Redéduire province et chef-lieu depuis le nom des DREN (écrase la saisie ci-dessous)</label></div>
            <div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>DREN</th><th>Province</th><th>Chef-lieu</th></tr></thead><tbody>
            @foreach($drens as $d)
                <tr><td>{{ $d->nom }}</td>
                    <td><select name="drens[{{ $d->id }}][province_id]" class="ui-select sm"><option value="">—</option>@foreach($provinces as $p)<option value="{{ $p->id }}" @selected((int) $d->province_id === (int) $p->id)>{{ $p->nom }}</option>@endforeach</select></td>
                    <td><label class="ui-check"><input type="checkbox" name="drens[{{ $d->id }}][chef_lieu]" value="1" @checked($d->is_chef_lieu_province)> chef-lieu</label></td></tr>
            @endforeach</tbody></table></div>
        </div>
        <div><button class="ui-btn accent">Enregistrer</button></div>
    </form>
</div>
@endsection
