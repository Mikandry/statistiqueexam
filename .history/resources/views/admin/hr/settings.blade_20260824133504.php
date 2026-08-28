@extends('layouts.app')

@section('title', 'Paramètres RH')
@section('subtitle', 'En-têtes, numérotation et champs des documents administratifs')

@section('content')
<div class="space-y-6 rounded-2xl bg-gradient-to-br from-slate-50 via-white to-teal-50/50 p-1">

    @if(session('status'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-5 py-4 text-sm font-semibold text-emerald-800 shadow-sm backdrop-blur-sm">
            <span class="text-xl">✅</span>
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50/80 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm backdrop-blur-sm">
            <span class="text-xl">⚠️</span>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ============================================================= --}}
    {{-- EN-TÊTE OFFICIEL & NUMÉROTATION                            --}}
    {{-- ============================================================= --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white/95 shadow-md transition-all hover:shadow-lg">
        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-teal-50/40 px-6 py-5">
            <h2 class="text-lg font-black text-slate-900">🏛️ En-tête officiel</h2>
            <p class="mt-1 text-sm text-slate-500">Lignes affichées dans l'ordre : ministère, SG, DG, direction puis service.</p>
        </div>

        <form method="POST" action="{{ route('admin.hr.settings.update') }}" class="grid gap-4 p-6 md:grid-cols-2">
            @csrf @method('PUT')

            @foreach([
                'ministere' => 'Ministère',
                'secretariat_general' => 'Secrétariat général',
                'direction_generale' => 'Direction générale',
                'direction' => 'Direction',
                'service' => 'Service',
                'signataire' => 'Nom du signataire',
                'signataire_qualite' => 'Qualité du signataire (Directeur, Chef de service...)',
                'ville' => 'Ville',
            ] as $field => $label)
                <label class="text-sm font-semibold text-slate-700">
                    {{ $label }}
                    <input name="{{ $field }}" value="{{ old($field, $settings?->{$field}) }}" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2 text-sm shadow-sm transition-all focus:border-teal-400 focus:ring-2 focus:ring-teal-200 focus:outline-none">
                </label>
            @endforeach

            <div class="md:col-span-2 mt-4 border-t border-slate-100 pt-5">
                <h3 class="text-base font-black text-slate-800">🔢 Numérotation des lettres</h3>
                <p class="mt-0.5 text-xs text-slate-400">Référence : numéro / année + préfixe .</p>
            </div>

            

            <label class="text-sm font-semibold text-slate-700">
                Prochain numéro
                <input type="number" min="1" name="next_reference_number" value="{{ old('next_reference_number', $settings?->next_reference_number ?: 1) }}" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2 text-sm shadow-sm transition-all focus:border-teal-400 focus:ring-2 focus:ring-teal-200 focus:outline-none">
            </label>
            <label class="text-sm font-semibold text-slate-700">
                Préfixe
                <input name="reference_prefix" value="{{ old('reference_prefix', $settings?->reference_prefix ?: 'N°') }}" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2 text-sm shadow-sm transition-all focus:border-teal-400 focus:ring-2 focus:ring-teal-200 focus:outline-none">
            </label>

            

            <div class="md:col-span-2 flex items-end justify-end">
                <button class="rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:scale-105 hover:shadow-lg active:scale-95">
                    💾 Enregistrer les paramètres
                </button>
            </div>
        </form>
    </section>


    {{-- ============================================================= --}}
    {{-- CONFIGURATION DES CHAMPS PAR DOCUMENT  --}}
    {{-- ============================================================= --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white/95 shadow-md transition-all hover:shadow-lg">
        <div class="border-b border-slate-100 bg-gradient-to-r from-teal-50/70 to-emerald-50/40 px-6 py-5">
            <h2 class="text-lg font-black text-slate-900">⚙️ Champs visibles par document</h2>
            <p class="mt-1 text-sm text-slate-500">
                Cochez les informations à afficher dans chaque attestation.
                Les champs <strong>Nom</strong>, <strong>IM</strong> et <strong>Corps / Grade</strong> sont recommandés.
            </p>
        </div>

        @php
            $availableFields = \App\Models\HrDocumentSetting::availableFields();
            $documents = [
                'non-interruption' => ['🔄', 'Non-interruption de service'],
                'non-jouissance' => ['📄', 'Non-jouissance de congé'],
                'prise-service' => ['🚀', 'Prise de service'],
                'conge' => ['🌴', 'Fiche de congé'],
                'absence' => ['📝', 'Autorisation d’absence'],
                'mission' => ['📋', 'Ordre de mission'],
                'formation' => ['📚', 'Demande de formation'],
                'autre' => ['🗂️', 'Autre demande'],
                'fiche-administrative' => ['🗃️', 'Fiche administrative'],
            ];
            $currentConfig = $settings?->fields_config ?? [];
        @endphp

        <form method="POST" action="{{ route('admin.hr.settings.update') }}" class="p-6">
            @csrf @method('PUT')

            <div class="grid gap-5 xl:grid-cols-2">

                @foreach($documents as $slug => [$icon, $label])

                    <article class="rounded-xl border border-slate-200/70 bg-slate-50/50 p-5 transition-colors hover:border-teal-200 hover:bg-teal-50/20">

                        <div class="mb-3 flex items-center gap-2">
                            <span class="text-2xl">{{ $icon }}</span>
                            <h3 class="font-black text-slate-800">{{ $label }}</h3>
                        </div>

                        @php($selected = $currentConfig[$slug] ?? $settings?->fieldsFor($slug) ?? ['nom', 'im', 'corps_grade'])

                        <div class="grid gap-2 sm:grid-cols-2">

                            @foreach($availableFields as $fieldKey => $fieldLabel)

                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-all hover:border-teal-300 hover:bg-teal-50/40 has-[:checked]:border-teal-400 has-[:checked]:bg-teal-50/60 has-[:checked]:ring-1 has-[:checked]:ring-teal-200">

                                    <input
                                        type="checkbox"
                                        name="fields_config[{{ $slug }}][]"
                                        value="{{ $fieldKey }}"
                                        @checked(in_array($fieldKey, $selected, true))
                                        class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-400"
                                    >
                                    <span class="text-xs font-semibold">{{ $fieldLabel }}</span>

                                </label>

                            @endforeach

                        </div>

                    </article>

                @endforeach

            </div>

            <div class="mt-6 flex items-center justify-end border-t border-slate-100 pt-5">
                <button class="rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-teal-200/50 transition-all hover:scale-105 hover:bg-teal-700 hover:shadow-lg active:scale-95">
                    💾 Enregistrer la configuration des champs
                </button>
            </div>

        </form>
    </section>

</div>
@endsection