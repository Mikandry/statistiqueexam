@extends('layouts.app')
@section('title', 'Paramètres - Vacation 2026')
@section('content')
@php $warnings = []; $types = json_decode($params['transcription_types'], true) ?: []; @endphp
<div class="space-y-4">
    <h1 class="text-3xl font-bold text-slate-900">Paramètres du calcul</h1>
    @include('vacation-2026.besoins._nav')
    <form method="POST" action="{{ route('vacation2026.besoins.parametres.update') }}" class="space-y-4">
        @csrf @method('PUT')
        <div class="grid gap-4 rounded-xl border border-slate-200 bg-white p-4 md:grid-cols-2">
            <label class="text-sm">Année des répartitions<input name="annee" value="{{ old('annee', $params['annee']) }}" class="mt-1 block w-full rounded border px-2 py-1"></label>
            <label class="text-sm">Nombre de provinces (0 = 6, valeur nationale)<input type="number" name="nb_provinces" value="{{ old('nb_provinces', $params['nb_provinces']) }}" class="mt-1 block w-full rounded border px-2 py-1"></label>
            <label class="text-sm">Art. 12 : matières pour les enveloppes CEPE (agents = matières × tranches de 500)<input type="number" name="cepe_enveloppes_matieres" value="{{ old('cepe_enveloppes_matieres', $params['cepe_enveloppes_matieres']) }}" class="mt-1 block w-full rounded border px-2 py-1"></label>
            <label class="text-sm">BEPC écrit : jours par défaut (3 option B, 4 jumelées)<select name="bepc_jours_ecrit_defaut" class="mt-1 block w-full rounded border px-2 py-1"><option value="3" @selected($params['bepc_jours_ecrit_defaut']==='3')>3</option><option value="4" @selected($params['bepc_jours_ecrit_defaut']==='4')>4</option></select></label>
            <label class="text-sm">BEPC : % de candidats en anglais (si langues non détaillées)<input type="number" name="bepc_anglais_pct" value="{{ old('bepc_anglais_pct', $params['bepc_anglais_pct']) }}" class="mt-1 block w-full rounded border px-2 py-1"></label>
            <label class="text-sm">Centres EPS estimés par CISCO (si aucun configuré)<input type="number" name="eps_centres_par_cisco" value="{{ old('eps_centres_par_cisco', $params['eps_centres_par_cisco']) }}" class="mt-1 block w-full rounded border px-2 py-1"></label>
            <div class="text-sm"><p class="mb-1">Centres assurant la transcription</p>
                @foreach($transcriptionChoices as $t)<label class="block"><input type="checkbox" name="transcription_types[]" value="{{ $t }}" @checked(in_array($t, $types, true))> {{ $t }}</label>@endforeach</div>
            <div class="space-y-1 text-sm">
                <label class="block"><input type="checkbox" name="cepe_enveloppes_actif" value="1" @checked($params['cepe_enveloppes_actif']==='1')> Appliquer l'Art. 12 (enveloppes CEPE 2026 en CISCO)</label>
                <label class="block"><input type="checkbox" name="inclure_speciale" value="1" @checked($params['inclure_speciale']==='1')> Inclure l'indemnité spéciale (Art. 7)</label>
                <label class="block"><input type="checkbox" name="entites_sans_centre" value="1" @checked($params['entites_sans_centre']==='1')> Compter DREN/CISCO sans centre saisi</label></div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-1 font-semibold">DREN : province et chef-lieu</h2>
            <p class="mb-2 text-xs text-slate-500">Les 12 agents (et 12 enseignants) des travaux provinciaux du CEPE sont comptés une fois par province, à la DREN chef-lieu.</p>
            <label class="mb-2 block text-sm"><input type="checkbox" name="deviner_provinces" value="1"> Redéduire province et chef-lieu depuis le nom des DREN (écrase la saisie ci-dessous)</label>
            <table class="w-full text-sm"><tbody>
            @foreach($drens as $d)
                <tr class="border-t"><td class="p-1">{{ $d->nom }}</td>
                    <td class="p-1"><select name="drens[{{ $d->id }}][province]" class="rounded border px-1 py-1"><option value="">—</option>@foreach($provinces as $p)<option @selected($d->province === $p)>{{ $p }}</option>@endforeach</select></td>
                    <td class="p-1"><label><input type="checkbox" name="drens[{{ $d->id }}][chef_lieu]" value="1" @checked($d->is_chef_lieu_province)> chef-lieu</label></td></tr>
            @endforeach</tbody></table>
        </div>
        <button class="rounded-lg bg-emerald-600 px-5 py-2 font-medium text-white">Enregistrer</button>
    </form>
</div>
@endsection
