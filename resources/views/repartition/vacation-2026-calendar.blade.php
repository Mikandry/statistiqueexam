<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendrier de vacation</title>

    @include('partials.head-assets')
</head>

<body class="bg-slate-100 text-slate-900">

<div class="mx-auto max-w-[1800px] p-4 md:p-6">

    <div class="flex gap-4">

        @include('partials.sidebar')

        <main class="min-w-0 flex-1 space-y-4">

            {{-- ========================================================= --}}
            {{-- EN-TÊTE ET CRÉATION DE SESSION --}}
            {{-- ========================================================= --}}

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <h1 class="text-2xl font-bold">
                    📅 Calendrier de vacation – Niveau central
                </h1>

                <p class="mt-1 text-sm text-slate-600">
                    Planification exclusive des activités du MEN/DEXAMC.
                    Les DREN, CISCO et centres ne sont pas inclus.
                </p>


                {{-- Message de succès --}}
                @if (session('status'))
                    <div class="mt-3 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif


                {{-- Messages d'erreur --}}
                @if ($errors->any())
                    <div class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-800">
                        {{ $errors->first() }}
                    </div>
                @endif


                <div class="mt-4 grid gap-4 lg:grid-cols-2">

                    {{-- ================================================= --}}
                    {{-- FORMULAIRE DE CRÉATION DE SESSION --}}
                    {{-- ================================================= --}}

                    <form
                        method="POST"
                        action="{{ route('vacation2026.calendar.sessions.store') }}"
                        class="grid grid-cols-2 gap-2 rounded-xl bg-slate-50 p-4"
                    >

                        @csrf

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Examen
                            </label>

                            <input
                                name="examen"
                                required
                                placeholder="Examen (CEPE / BEPC)"
                                class="w-full rounded border border-slate-300 px-3 py-2"
                            >
                        </div>


                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Année
                            </label>

                            <input
                                name="annee"
                                type="number"
                                value="{{ date('Y') }}"
                                required
                                class="w-full rounded border border-slate-300 px-3 py-2"
                            >
                        </div>


                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Libellé
                            </label>

                            <input
                                name="libelle"
                                placeholder="Session BEPC {{ date('Y') }}"
                                class="w-full rounded border border-slate-300 px-3 py-2"
                            >
                        </div>


                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Début de session
                            </label>

                            <input
                                name="date_session"
                                type="date"
                                required
                                class="w-full rounded border border-slate-300 px-3 py-2"
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Fin de session <span class="font-normal text-slate-400">(optionnelle pour CEPE)</span>
                            </label>
                            <input name="date_fin_session" type="date" class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>


                        <button
                            type="submit"
                            class="col-span-2 rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
                        >
                            Créer / générer la session centrale
                        </button>

                    </form>


                    {{-- ================================================= --}}
                    {{-- SESSION AFFICHÉE --}}
                    {{-- ================================================= --}}

                    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">

                        <form method="GET">

                            <label class="text-sm font-medium">
                                Session affichée
                            </label>

                            <select
                                name="session_id"
                                onchange="this.form.submit()"
                                class="mt-2 w-full rounded border border-slate-300 px-3 py-2"
                            >

                                @foreach ($sessions as $item)

                                    <option
                                        value="{{ $item->id }}"
                                        @selected($session?->id === $item->id)
                                    >
                                        {{ $item->libelle }}
                                        —
                                        {{ $item->date_session->format('d/m/Y') }}
                                    </option>

                                @endforeach

                            </select>

                        </form>


                        @if ($session)

                            <p class="mt-3 text-sm">

                                <b>Examen :</b>
                                {{ $session->examen }}

                                ·

                                <b>Année :</b>
                                {{ $session->annee }}

                                ·

                                <b>Date pivot :</b>
                                {{ $session->date_session->format('d/m/Y') }}

                                ·

                                <b>Niveau :</b>
                                CENTRAL

                            </p>

                        @endif

                    </div>

                </div>

            </section>


            {{-- ========================================================= --}}
            {{-- CONTENU DE LA SESSION --}}
            {{-- ========================================================= --}}

            @if ($session)

                {{-- ===================================================== --}}
                {{-- PLAN PRÉVISIONNEL --}}
                {{-- ===================================================== --}}

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex flex-wrap justify-between gap-3">

                        <div>

                            <h2 class="text-lg font-semibold">
                                Plan prévisionnel
                            </h2>

                            <p class="text-sm text-slate-600">
                                Les dates manuelles sont conservées lors du
                                recalcul « conserver ».
                            </p>

                        </div>


                        {{-- Formulaire de recalcul --}}

                        <form
                            method="POST"
                            action="{{ route('vacation2026.calendar.recalculate', $session) }}"
                            class="flex gap-2"
                        >

                            @csrf

                            <select
                                name="mode"
                                class="rounded border border-slate-300 px-2 text-sm"
                            >

                                <option value="preserve">
                                    Conserver les modifications manuelles
                                </option>

                                <option value="all">
                                    Recalculer toutes les activités
                                </option>

                            </select>


                            <button
                                type="submit"
                                class="rounded bg-indigo-700 px-3 py-2 text-sm text-white hover:bg-indigo-600"
                            >
                                Recalculer le calendrier
                            </button>

                        </form>

                    </div>


                    {{-- ================================================= --}}
                    {{-- TABLEAU DES ACTIVITÉS --}}
                    {{-- ================================================= --}}

                    <div class="mt-4 overflow-x-auto">

                        <table class="min-w-full border-collapse text-sm">

                            <thead>

                                <tr class="bg-slate-100">

                                    <th class="border p-2">
                                        #
                                    </th>

                                    <th class="border p-2 text-left">
                                        Activité
                                    </th>

                                    <th class="border p-2">
                                        Début / fin
                                    </th>

                                    <th class="border p-2">
                                        Durée
                                    </th>

                                    <th class="border p-2">
                                        Précédente(s)
                                    </th>

                                    <th class="border p-2">
                                        Chevauchement
                                    </th>

                                    <th class="border p-2">
                                        Origine
                                    </th>

                                    <th class="border p-2">
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach ($plans as $plan)

                                    <tr>

                                        {{-- Ordre --}}

                                        <td class="border p-2">
                                            {{ $plan->ordre }}
                                        </td>


                                        {{-- Activité --}}

                                        <td class="border p-2">
                                            {{ $plan->activity?->libelle }}
                                        </td>


                                        {{-- Dates --}}

                                        <td class="border p-2">

                                            <form
                                                id="plan-{{ $plan->id }}"
                                                method="POST"
                                                action="{{ route('vacation2026.calendar.plans.update', $plan) }}"
                                            >

                                                @csrf

                                                @method('PUT')


                                                <input
                                                    name="ordre"
                                                    type="hidden"
                                                    value="{{ $plan->ordre }}"
                                                >


                                                <div class="flex flex-wrap gap-1">

                                                    <input
                                                        name="date_debut"
                                                        type="date"
                                                        value="{{ $plan->date_debut?->format('Y-m-d') }}"
                                                        class="rounded border border-slate-300 p-1"
                                                    >


                                                    <input
                                                        name="date_fin"
                                                        type="date"
                                                        value="{{ $plan->date_fin?->format('Y-m-d') }}"
                                                        class="rounded border border-slate-300 p-1"
                                                    >

                                                </div>

                                            </form>

                                        </td>


                                        {{-- Durée --}}

                                        <td class="border p-2">

                                            <input
                                                form="plan-{{ $plan->id }}"
                                                name="duree_jours"
                                                type="number"
                                                min="1"
                                                value="{{ $plan->duree_jours }}"
                                                class="w-16 rounded border border-slate-300 p-1"
                                            >

                                        </td>


                                        {{-- Dépendances --}}

                                        <td class="border p-2">

                                            <select
                                                form="plan-{{ $plan->id }}"
                                                name="dependency_ids[]"
                                                multiple
                                                class="max-w-40 rounded border border-slate-300 p-1"
                                            >

                                                @foreach (
                                                    $plans->where('id', '!=', $plan->id)
                                                    as $candidate
                                                )

                                                    <option
                                                        value="{{ $candidate->id }}"
                                                        @selected(
                                                            in_array(
                                                                $candidate->id,
                                                                $plan->dependency_ids ?? []
                                                            )
                                                        )
                                                    >
                                                        {{ $candidate->ordre }}.
                                                        {{ \Illuminate\Support\Str::limit($candidate->activity?->libelle, 24) }}
                                                    </option>

                                                @endforeach

                                            </select>

                                        </td>


                                        {{-- Chevauchement --}}

                                        <td class="border p-2 text-center">

                                            <input
                                                form="plan-{{ $plan->id }}"
                                                type="checkbox"
                                                name="chevauchement_autorise"
                                                value="1"
                                                @checked($plan->chevauchement_autorise)
                                            >

                                        </td>


                                        {{-- Origine --}}

                                        <td class="border p-2">

                                            @if ($plan->manuel)
                                                Manuelle
                                            @else
                                                Auto
                                            @endif

                                        </td>


                                        {{-- Bouton enregistrer --}}

                                        <td class="border p-2">

                                            <button
                                                type="submit"
                                                form="plan-{{ $plan->id }}"
                                                class="rounded bg-slate-800 px-2 py-1 text-xs text-white hover:bg-slate-700"
                                            >
                                                Enregistrer
                                            </button>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </section>


                {{-- ===================================================== --}}
                {{-- CALCUL DES DATES DU GANTT --}}
                {{-- ===================================================== --}}

                @php

                    $first = $plans->min('date_debut');

                    $last = $plans->max('date_fin');

                    if ($first && $last) {
                        $totalDays = max(
                            1,
                            $first->diffInDays($last) + 1
                        );
                    } else {
                        $totalDays = 1;
                    }

                @endphp


                {{-- ===================================================== --}}
                {{-- DIAGRAMME DE GANTT --}}
                {{-- ===================================================== --}}

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h2 class="text-lg font-semibold">
                        Diagramme de Gantt
                    </h2>


                    <div class="mt-4 overflow-x-auto">

                        <div class="min-w-[850px]">

                            <div class="relative border-l border-slate-300 pl-56">

                                {{-- ================================================= --}}
                                {{-- ACTIVITÉS DU GANTT --}}
                                {{-- ================================================= --}}

                                @foreach ($plans as $plan)

                                    @php

                                        $start = 0;

                                        $width = 0;

                                        if (
                                            $first &&
                                            $plan->date_debut &&
                                            $plan->date_fin
                                        ) {

                                            $start =
                                                (
                                                    $first->diffInDays(
                                                        $plan->date_debut
                                                    )
                                                    / $totalDays
                                                ) * 100;


                                            $duration =
                                                $plan->date_debut->diffInDays(
                                                    $plan->date_fin
                                                ) + 1;


                                            $width =
                                                (
                                                    max(1, $duration)
                                                    / $totalDays
                                                ) * 100;
                                        }

                                    @endphp


                                    <div class="relative h-11 border-b border-slate-100">

                                        {{-- Nom de l'activité --}}

                                        <span
                                            class="absolute -left-56 top-2 w-52 truncate text-xs"
                                        >
                                            {{ $plan->activity?->libelle }}
                                        </span>


                                        {{-- Barre du Gantt --}}

                                        @if (
                                            $plan->date_debut &&
                                            $plan->date_fin
                                        )

                                            <div
                                                class="absolute top-2 h-6 rounded bg-indigo-500 px-2 text-xs leading-6 text-white"
                                                style="left: {{ $start }}%; width: {{ $width }}%;"
                                                title="{{ $plan->date_debut->format('d/m/Y') }} → {{ $plan->date_fin->format('d/m/Y') }}"
                                            >
                                            </div>

                                        @endif

                                    </div>

                                @endforeach


                                {{-- ================================================= --}}
                                {{-- LIGNE DE LA SESSION --}}
                                {{-- ================================================= --}}

                                @if ($first && $session->date_session)

                                    @php

                                        $sessionPosition =
                                            (
                                                $first->diffInDays(
                                                    $session->date_session
                                                )
                                                / $totalDays
                                            ) * 100;

                                    @endphp


                                    <div
                                        class="pointer-events-none absolute inset-y-0 border-l-2 border-rose-500"
                                        style="left: {{ $sessionPosition }}%;"
                                    >

                                        <span
                                            class="absolute -top-5 -ml-8 whitespace-nowrap text-xs font-bold text-rose-600"
                                        >
                                            SESSION
                                        </span>

                                    </div>

                                @endif

                            </div>

                        </div>

                    </div>

                </section>

            @endif

        </main>

    </div>

</div>

</body>
</html>
