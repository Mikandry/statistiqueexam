@extends('layouts.app')
@section('title', 'Barèmes - Vacation 2026')
@section('content')
@php $warnings = []; @endphp
<div class="space-y-4">
    <h1 class="text-3xl font-bold text-slate-900">Barèmes des indemnités</h1>
    <p class="text-sm text-slate-600">Le décret renvoie les taux à un arrêté (Art. 5 et 7). Saisissez-les ici : montant = taux × personnes × jours (mode « Jour ») ou taux × personnes (mode « Forfait »). Case vide = taux par défaut ; les indemnités spéciales n'ont jamais de taux par défaut.</p>
    @include('vacation-2026.besoins._nav')
    <form method="POST" action="{{ route('vacation2026.besoins.baremes.update') }}" class="space-y-4">
        @csrf @method('PUT')
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-2 font-semibold">Taux par défaut (rôles sans taux propre)</h2>
            <div class="flex flex-wrap items-end gap-3">
                @foreach($exams as $e)<div><label class="block text-xs font-semibold">{{ $e }} (Ar)</label><input type="number" step="0.01" min="0" name="default[{{ $e }}]" value="{{ isset($defaults[$e]) ? (float) $defaults[$e]['montant'] : '' }}" class="w-36 rounded border px-2 py-1 text-sm"></div>@endforeach
                <div><label class="block text-xs font-semibold">Mode</label><select name="default_mode" class="rounded border px-2 py-1 text-sm"><option value="JOUR">Par jour</option><option value="FORFAIT" @selected(($defaults['CEPE']['mode'] ?? 'JOUR') === 'FORFAIT')>Forfait</option></select></div>
            </div>
        </div>
        @foreach($groups as $group => $items)
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-2 font-semibold">{{ $group }}</h2>
            <table class="w-full text-sm"><thead class="text-left text-xs uppercase text-slate-600"><tr><th class="p-1">Rôle</th><th class="p-1">Mode</th>@foreach($exams as $e)<th class="p-1">{{ $e }} (Ar)</th>@endforeach</tr></thead><tbody>
            @foreach($items as $it)
                @php $mode = $table[$it['code']]['CEPE']['mode'] ?? $table[$it['code']]['BEPC']['mode'] ?? $it['mode']; @endphp
                <tr class="border-t"><td class="p-1">{{ $it['label'] }}</td>
                    <td class="p-1"><select name="modes[{{ str_replace('.', '__', $it['code']) }}]" class="rounded border px-1 py-1 text-xs"><option value="JOUR" @selected($mode === 'JOUR')>Jour</option><option value="FORFAIT" @selected($mode === 'FORFAIT')>Forfait</option></select></td>
                    @foreach($exams as $e)<td class="p-1"><input type="number" step="0.01" min="0" name="rates[{{ str_replace('.', '__', $it['code']) }}][{{ $e }}]" value="{{ isset($table[$it['code']][$e]) ? (float) $table[$it['code']][$e]['montant'] : '' }}" class="w-32 rounded border px-2 py-1 text-sm"></td>@endforeach</tr>
            @endforeach</tbody></table>
        </div>
        @endforeach
        <button class="rounded-lg bg-emerald-600 px-5 py-2 font-medium text-white">Enregistrer les barèmes</button>
    </form>
</div>
@endsection
