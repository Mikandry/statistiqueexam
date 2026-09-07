@extends('layouts.app')

@section('title', 'Paramètres RH')
@section('subtitle', 'En-têtes, numérotation et champs des documents administratifs')

@section('content')

<div class="space-y-6 rounded-2xl bg-gradient-to-br from-slate-50 via-white to-teal-50/50 p-1">

    {{-- ============================================================= --}}
    {{-- MESSAGES --}}
    {{-- ============================================================= --}}

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
    {{-- EN-TÊTE OFFICIEL & NUMÉROTATION --}}
    {{-- ============================================================= --}}

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white/95 shadow-md transition-all hover:shadow-lg">

        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-teal-50/40 px-6 py-5">

            <h2 class="text-lg font-black text-slate-900">
                🏛️ En-tête officiel
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Lignes affichées dans l'ordre :
                ministère, SG, DG, direction puis service.
            </p>

        </div>


        <form
            method="POST"
            action="{{ route('admin.hr.settings.update') }}"
            class="grid gap-4 p-6"
        >

            @csrf
            @method('PUT')


            {{-- ----------------------------------------------------- --}}
            {{-- INFORMATIONS OFFICIELLES --}}
            {{-- ----------------------------------------------------- --}}

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

                    <input
                        name="{{ $field }}"
                        value="{{ old($field, $settings?->{$field}) }}"
                        class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2 text-sm shadow-sm transition-all focus:border-teal-400 focus:ring-2 focus:ring-teal-200 focus:outline-none"
                    >

                </label>

            @endforeach


            {{-- ----------------------------------------------------- --}}
            {{-- NUMÉROTATION --}}
            {{-- ----------------------------------------------------- --}}

            <div class="mt-4 border-t border-slate-100 pt-5 md:col-span-2">

                <h3 class="text-base font-black text-slate-800">
                    🔢 Numérotation des lettres
                </h3>

                <p class="mt-0.5 text-xs text-slate-400">
                    Exemple :
                    <strong>N°2026/1 MEN.SG.DGES.DEXAMC/SOE</strong>
                </p>

            </div>


            {{-- PROCHAIN NUMÉRO --}}

            <label class="text-sm font-semibold text-slate-700">

                Prochain numéro

                <input
                    type="number"
                    min="1"
                    name="next_reference_number"
                    value="{{ old('next_reference_number', $settings?->next_reference_number ?: 1) }}"
                    class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2 text-sm shadow-sm transition-all focus:border-teal-400 focus:ring-2 focus:ring-teal-200 focus:outline-none"
                >

            </label>


            {{-- ANNÉE --}}

            <label class="text-sm font-semibold text-slate-700">

                Année de référence

                <input
                    type="number"
                    min="2000"
                    max="2200"
                    name="reference_year"
                    value="{{ old('reference_year', $settings?->reference_year ?: now()->year) }}"
                    class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2 text-sm shadow-sm transition-all focus:border-teal-400 focus:ring-2 focus:ring-teal-200 focus:outline-none"
                >

            </label>


            {{-- PRÉFIXE --}}

            <label class="text-sm font-semibold text-slate-700">

                Préfixe

                <input
                    name="reference_prefix"
                    value="{{ old('reference_prefix', $settings?->reference_prefix) }}"
                    placeholder="MEN.SG.DGES.DEXAMC/SOE"
                    class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2 text-sm shadow-sm transition-all focus:border-teal-400 focus:ring-2 focus:ring-teal-200 focus:outline-none"
                >

            </label>


            {{-- BOUTON --}}

            <div class="mt-2 flex items-end justify-end md:col-span-2">

                <button
                    type="submit"
                    class="rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:scale-105 hover:shadow-lg active:scale-95"
                >
                    💾 Enregistrer les paramètres
                </button>

            </div>

        </form>

    </section>



    {{-- ============================================================= --}}
    {{-- CONFIGURATION DES CHAMPS PAR DOCUMENT --}}
    {{-- ============================================================= --}}

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white/95 shadow-md transition-all hover:shadow-lg">


        {{-- --------------------------------------------------------- --}}
        {{-- TITRE --}}
        {{-- --------------------------------------------------------- --}}

        <div class="border-b border-slate-100 bg-gradient-to-r from-teal-50/70 to-emerald-50/40 px-6 py-5">

            <h2 class="text-lg font-black text-slate-900">
                ⚙️ Champs visibles par document
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Cochez les informations à afficher puis utilisez
                <strong>↑</strong> et <strong>↓</strong>
                pour définir leur ordre d'apparition dans le document.
            </p>

        </div>


        {{-- --------------------------------------------------------- --}}
        {{-- DONNÉES --}}
        {{-- --------------------------------------------------------- --}}

        @php

            $availableFields =
                \App\Models\HrDocumentSetting::availableFields();

            $documents = [

                'non-interruption' => [
                    '🔄',
                    'Non-interruption de service'
                ],

                'non-jouissance' => [
                    '📄',
                    'Non-jouissance de congé'
                ],

                'prise-service' => [
                    '🚀',
                    'Prise de service'
                ],

                'conge' => [
                    '🌴',
                    'Fiche de congé'
                ],

                'absence' => [
                    '📝',
                    'Autorisation d’absence'
                ],

                'mission' => [
                    '📋',
                    'Ordre de mission'
                ],

                'formation' => [
                    '📚',
                    'Demande de formation'
                ],

                'autre' => [
                    '🗂️',
                    'Autre demande'
                ],

                'fiche-administrative' => [
                    '🗃️',
                    'Fiche administrative'
                ],

            ];

            $currentConfig =
                $settings?->fields_config ?? [];

        @endphp



        {{-- --------------------------------------------------------- --}}
        {{-- FORMULAIRE --}}
        {{-- --------------------------------------------------------- --}}

        <form
            method="POST"
            action="{{ route('admin.hr.settings.fields.update') }}"
            class="p-6"
        >

            @csrf
            @method('PUT')


            <div class="grid gap-5 xl:grid-cols-2">


                {{-- ================================================= --}}
                {{-- CHAQUE DOCUMENT --}}
                {{-- ================================================= --}}

                @foreach($documents as $slug => [$icon, $label])


                    <article
                        class="rounded-xl border border-slate-200/70 bg-slate-50/50 p-5 transition-colors hover:border-teal-200 hover:bg-teal-50/20"
                    >


                        {{-- ------------------------------------------------ --}}
                        {{-- TITRE DU DOCUMENT --}}
                        {{-- ------------------------------------------------ --}}

                        <div class="mb-4 flex items-center justify-between gap-3">

                            <div class="flex items-center gap-2">

                                <span class="text-2xl">
                                    {{ $icon }}
                                </span>

                                <div>

                                    <h3 class="font-black text-slate-800">
                                        {{ $label }}
                                    </h3>

                                    <p class="text-[11px] text-slate-400">
                                        Champs sélectionnés :
                                        <span
                                            class="selected-count font-bold text-teal-600"
                                        >
                                            0
                                        </span>
                                    </p>

                                </div>

                            </div>

                        </div>



                        {{-- ================================================= --}}
                        {{-- ORDRE DES CHAMPS --}}
                        {{-- ================================================= --}}

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | Champs actuellement sélectionnés
                            |--------------------------------------------------------------------------
                            */

                            $selected =
                                $currentConfig[$slug]
                                ?? $settings?->fieldsFor($slug)
                                ?? [
                                    'nom',
                                    'im',
                                    'corps_grade'
                                ];


                            /*
                            |--------------------------------------------------------------------------
                            | Construire la liste dans l'ordre sauvegardé
                            |--------------------------------------------------------------------------
                            */

                            $orderedFields = [];


                            /*
                            |--------------------------------------------------------------------------
                            | 1. Champs sélectionnés
                            |--------------------------------------------------------------------------
                            */

                            foreach ($selected as $fieldKey) {

                                if (isset($availableFields[$fieldKey])) {

                                    $orderedFields[$fieldKey] =
                                        $availableFields[$fieldKey];

                                }

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | 2. Champs non sélectionnés
                            |--------------------------------------------------------------------------
                            */

                            foreach ($availableFields as $fieldKey => $fieldLabel) {

                                if (!isset($orderedFields[$fieldKey])) {

                                    $orderedFields[$fieldKey] =
                                        $fieldLabel;

                                }

                            }

                        @endphp



                        <div
                            class="field-sortable-list space-y-2"
                            data-document="{{ $slug }}"
                        >


                            @foreach($orderedFields as $fieldKey => $fieldLabel)

                                @php

                                    $isSelected =
                                        in_array(
                                            $fieldKey,
                                            $selected,
                                            true
                                        );

                                @endphp


                                <div
                                    class="field-item flex items-center gap-2 rounded-xl border px-3 py-2 transition-all duration-200
                                    {{ $isSelected
                                        ? 'border-teal-300 bg-teal-50/50'
                                        : 'border-slate-200 bg-white opacity-70'
                                    }}"
                                    data-field="{{ $fieldKey }}"
                                >


                                    {{-- ================================= --}}
                                    {{-- CASE À COCHER --}}
                                    {{-- ================================= --}}

                                    <input
                                        type="checkbox"
                                        name="fields_config[{{ $slug }}][]"
                                        value="{{ $fieldKey }}"
                                        @checked($isSelected)
                                        class="field-checkbox h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-400"
                                    >



                                    {{-- ================================= --}}
                                    {{-- NUMÉRO --}}
                                    {{-- ================================= --}}

                                    <span
                                        class="field-number flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-[11px] font-black text-slate-400 shadow-sm ring-1 ring-slate-200"
                                    >
                                        —
                                    </span>



                                    {{-- ================================= --}}
                                    {{-- NOM DU CHAMP --}}
                                    {{-- ================================= --}}

                                    <span
                                        class="flex-1 text-xs font-semibold text-slate-700"
                                    >
                                        {{ $fieldLabel }}
                                    </span>



                                    {{-- ================================= --}}
                                    {{-- BOUTON MONTER --}}
                                    {{-- ================================= --}}

                                    <button
                                        type="button"
                                        class="move-up flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-sm font-black text-slate-500 shadow-sm transition hover:border-teal-300 hover:bg-teal-50 hover:text-teal-700 active:scale-95"
                                        title="Monter"
                                    >
                                        ↑
                                    </button>



                                    {{-- ================================= --}}
                                    {{-- BOUTON DESCENDRE --}}
                                    {{-- ================================= --}}

                                    <button
                                        type="button"
                                        class="move-down flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-sm font-black text-slate-500 shadow-sm transition hover:border-teal-300 hover:bg-teal-50 hover:text-teal-700 active:scale-95"
                                        title="Descendre"
                                    >
                                        ↓
                                    </button>


                                </div>

                            @endforeach


                        </div>


                    </article>

                @endforeach


            </div>



            {{-- ===================================================== --}}
            {{-- BOUTON SAUVEGARDE --}}
            {{-- ===================================================== --}}

            <div
                class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5"
            >

                <p class="text-xs text-slate-400">

                    💡
                    Cochez un champ puis utilisez
                    <strong>↑</strong> ou <strong>↓</strong>
                    pour modifier son ordre.

                </p>


                <button
                    type="submit"
                    class="rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-teal-200/50 transition-all hover:scale-105 hover:bg-teal-700 hover:shadow-lg active:scale-95"
                >

                    💾 Enregistrer la configuration des champs

                </button>

            </div>


        </form>

    </section>

</div>

@endsection



{{-- ================================================================ --}}
{{-- JAVASCRIPT --}}
{{-- ================================================================ --}}

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {


    /*
    |--------------------------------------------------------------------------
    | Mise à jour des numéros
    |--------------------------------------------------------------------------
    */

    function updateFieldNumbers(list) {

        let position = 1;

        const items =
            list.querySelectorAll('.field-item');


        items.forEach(function (item) {

            const checkbox =
                item.querySelector('.field-checkbox');

            const number =
                item.querySelector('.field-number');


            if (checkbox.checked) {

                number.textContent = position;

                number.classList.remove(
                    'text-slate-400'
                );

                number.classList.add(
                    'text-teal-700'
                );

                position++;

            } else {

                number.textContent = '—';

                number.classList.remove(
                    'text-teal-700'
                );

                number.classList.add(
                    'text-slate-400'
                );

            }

        });


        /*
        |--------------------------------------------------------------------------
        | Nombre de champs sélectionnés
        |--------------------------------------------------------------------------
        */

        const countElement =
            list.parentElement.querySelector(
                '.selected-count'
            );


        if (countElement) {

            countElement.textContent =
                position - 1;

        }

    }



    /*
    |--------------------------------------------------------------------------
    | Apparence d'un champ
    |--------------------------------------------------------------------------
    */

    function updateFieldAppearance(item) {

        const checkbox =
            item.querySelector('.field-checkbox');


        if (checkbox.checked) {

            item.classList.remove(
                'border-slate-200',
                'bg-white',
                'opacity-70'
            );

            item.classList.add(
                'border-teal-300',
                'bg-teal-50/50'
            );

        } else {

            item.classList.remove(
                'border-teal-300',
                'bg-teal-50/50'
            );

            item.classList.add(
                'border-slate-200',
                'bg-white',
                'opacity-70'
            );

        }

    }



    /*
    |--------------------------------------------------------------------------
    | Bouton MONTER
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.move-up')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const item =
                    this.closest('.field-item');

                const list =
                    item.closest(
                        '.field-sortable-list'
                    );

                const previous =
                    item.previousElementSibling;


                if (!previous) {
                    return;
                }


                /*
                | Déplacement
                */

                list.insertBefore(
                    item,
                    previous
                );


                updateFieldNumbers(list);

            });

        });



    /*
    |--------------------------------------------------------------------------
    | Bouton DESCENDRE
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.move-down')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const item =
                    this.closest('.field-item');

                const list =
                    item.closest(
                        '.field-sortable-list'
                    );

                const next =
                    item.nextElementSibling;


                if (!next) {
                    return;
                }


                /*
                | Déplacement
                */

                list.insertBefore(
                    next,
                    item
                );


                updateFieldNumbers(list);

            });

        });



    /*
    |--------------------------------------------------------------------------
    | Checkbox
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.field-checkbox')
        .forEach(function (checkbox) {

            checkbox.addEventListener(
                'change',
                function () {

                    const item =
                        this.closest('.field-item');

                    const list =
                        item.closest(
                            '.field-sortable-list'
                        );


                    updateFieldAppearance(item);

                    updateFieldNumbers(list);

                }
            );

        });



    /*
    |--------------------------------------------------------------------------
    | Initialisation
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.field-sortable-list')
        .forEach(function (list) {

            list.querySelectorAll('.field-item')
                .forEach(function (item) {

                    updateFieldAppearance(item);

                });

            updateFieldNumbers(list);

        });

});

</script>

@endpush