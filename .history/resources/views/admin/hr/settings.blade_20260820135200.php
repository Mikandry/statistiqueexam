@extends('layouts.app')

@section('title', 'Paramètres RH')
@section('subtitle', 'En-têtes et numérotation des documents administratifs')

@section('content')
<div class="space-y-6">
    @if(session('status'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ $errors->first() }}</div>@endif
    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5"><h2 class="font-black">En-tête officiel</h2><p class="mt-1 text-sm text-slate-500">Les lignes sont affichées dans l’ordre ministère, SG, DG, direction puis service.</p></div>
        <form method="POST" action="{{ route('admin.hr.settings.update') }}" class="grid gap-4 md:grid-cols-2">
            @csrf @method('PUT')
            @foreach(['ministere' => 'Ministère', 'secretariat_general' => 'Secrétariat général', 'direction_generale' => 'Direction générale', 'direction' => 'Direction', 'service' => 'Service', 'signataire' => 'Nom ou fonction du signataire'] as $field => $label)
                <label class="text-sm font-semibold text-slate-700">{{ $label }}<input name="{{ $field }}" value="{{ old($field, $settings?->{$field}) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
            @endforeach
            <div class="md:col-span-2 mt-2 border-t border-slate-100 pt-5"><h3 class="font-black">Numérotation des lettres</h3></div>
            <label class="text-sm font-semibold text-slate-700">Préfixe<input name="reference_prefix" value="{{ old('reference_prefix', $settings?->reference_prefix ?: 'N°') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
            <label class="text-sm font-semibold text-slate-700">Prochain numéro<input type="number" min="1" name="next_reference_number" value="{{ old('next_reference_number', $settings?->next_reference_number ?: 1) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
            <label class="text-sm font-semibold text-slate-700">Année de référence<input type="number" min="2000" max="2200" name="reference_year" value="{{ old('reference_year', $settings?->reference_year ?: now()->year) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
            <div class="flex items-end justify-end md:col-span-2"><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Enregistrer les paramètres</button></div>
        </form>
    </section>
</div>
@endsection