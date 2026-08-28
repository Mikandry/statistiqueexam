@extends('layouts.app')

@section('title', 'Personnel')
@section('subtitle', 'Gestion administrative des agents')

@section('content')

<div class="space-y-6 rounded-2xl bg-gradient-to-br from-slate-50 via-white to-cyan-50/50 p-1">

    <div class="flex flex-wrap items-center justify-between gap-3">

        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">
                Personnel
            </h1>

            <p class="text-sm text-slate-500">
                Liste des agents de la direction
            </p>
        </div>

        <a
            href="{{ route('hr.dashboard') }}"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold"
        >
            ← Tableau de bord
        </a>

    </div>


        <form method="GET"
            class="flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-white/90 p-4 shadow-sm backdrop-blur">

        <input
            type="text"
            name="q"
            value="{{ $search }}"
            placeholder="Nom, matricule, service ou fonction"
            class="min-w-[280px] rounded-lg border-slate-300 text-sm"
        >

        <button
            class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-bold text-white"
        >
            Rechercher
        </button>

    </form>

    <section class="rounded-lg border bg-white p-5 shadow-sm">

        <h2 class="mb-4 font-black">
            Ajouter un personnel
        </h2>

        <form method="POST" action="{{ route('hr.agents.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @csrf

            <input name="matricule" value="{{ old('matricule') }}" placeholder="Matricule" class="rounded-lg border-slate-300 text-sm">
            <input name="nom" value="{{ old('nom') }}" placeholder="Nom" class="rounded-lg border-slate-300 text-sm" required>
            <input name="prenoms" value="{{ old('prenoms') }}" placeholder="Prénoms" class="rounded-lg border-slate-300 text-sm">
            <select name="sexe" class="rounded-lg border-slate-300 text-sm">
                <option value="">Sexe</option>
                <option value="M" @selected(old('sexe') === 'M')>Masculin</option>
                <option value="F" @selected(old('sexe') === 'F')>Féminin</option>
            </select>
            <input name="date_naissance" value="{{ old('date_naissance') }}" type="date" placeholder="Date de naissance" class="rounded-lg border-slate-300 text-sm">
            <input name="cin" value="{{ old('cin') }}" placeholder="CIN" class="rounded-lg border-slate-300 text-sm">
            <input name="telephone" value="{{ old('telephone') }}" placeholder="Téléphone" class="rounded-lg border-slate-300 text-sm">
            <input name="email" value="{{ old('email') }}" type="email" placeholder="Email" class="rounded-lg border-slate-300 text-sm">
            <input name="adresse" value="{{ old('adresse') }}" placeholder="Adresse" class="rounded-lg border-slate-300 text-sm">

            <input name="statut" value="{{ old('statut') }}" placeholder="Statut" class="rounded-lg border-slate-300 text-sm">
            <input name="corps" value="{{ old('corps') }}" placeholder="Corps" class="rounded-lg border-slate-300 text-sm">
            <input name="grade" value="{{ old('grade') }}" placeholder="Grade" class="rounded-lg border-slate-300 text-sm">
            <input name="indice" value="{{ old('indice') }}" placeholder="Indice" class="rounded-lg border-slate-300 text-sm">
            <input name="categorie" value="{{ old('categorie') }}" placeholder="Catégorie" class="rounded-lg border-slate-300 text-sm">
            <input name="echelon" value="{{ old('echelon') }}" placeholder="Échelon" class="rounded-lg border-slate-300 text-sm">

            <input name="fonction" value="{{ old('fonction') }}" placeholder="Fonction" class="rounded-lg border-slate-300 text-sm">
            <input name="direction" value="{{ old('direction') }}" placeholder="Direction" class="rounded-lg border-slate-300 text-sm">
            <input name="service" value="{{ old('service') }}" placeholder="Service" class="rounded-lg border-slate-300 text-sm">
            <input name="bureau" value="{{ old('bureau') }}" placeholder="Bureau" class="rounded-lg border-slate-300 text-sm">
            <input name="superieur_hierarchique" value="{{ old('superieur_hierarchique') }}" placeholder="Supérieur hiérarchique" class="rounded-lg border-slate-300 text-sm">

            <input name="budget" value="{{ old('budget') }}" placeholder="Budget" class="rounded-lg border-slate-300 text-sm">
            <input name="chapitre" value="{{ old('chapitre') }}" placeholder="Chapitre" class="rounded-lg border-slate-300 text-sm">
            <input name="date_recrutement" value="{{ old('date_recrutement') }}" type="date" placeholder="Date de recrutement" class="rounded-lg border-slate-300 text-sm">
            <input name="date_prise_service" value="{{ old('date_prise_service') }}" type="date" placeholder="Date de prise de service" class="rounded-lg border-slate-300 text-sm">
            <input name="situation_administrative" value="{{ old('situation_administrative') }}" placeholder="Situation administrative" class="rounded-lg border-slate-300 text-sm">

            <label class="flex items-center gap-2 text-sm font-semibold">
                <input name="actif" value="1" type="checkbox" @checked(old('actif', true)) class="rounded border-slate-300">
                Personnel actif
            </label>

            <div class="xl:col-span-4">
                <button class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-bold text-white">
                    Ajouter le personnel
                </button>
            </div>
        </form>

    </section>


    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-slate-50 text-xs font-black uppercase text-slate-500">

                    <tr>
                        <th class="px-4 py-3 text-left">Agent</th>
                        <th class="px-4 py-3 text-left">Matricule</th>
                        <th class="px-4 py-3 text-left">Fonction</th>
                        <th class="px-4 py-3 text-left">Service</th>
                        <th class="px-4 py-3 text-left">Compte</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                @forelse($agents as $agent)

                    <tr>

                        <td class="px-4 py-3 font-bold">

                            <a
                                href="{{ route('hr.agents.show', $agent->id) }}"
                                class="text-blue-700 hover:underline"
                            >
                                {{ $agent->full_name }}
                            </a>

                        </td>


                        <td class="px-4 py-3">
                            {{ $agent->matricule ?: '—' }}
                        </td>


                        <td class="px-4 py-3">
                            {{ $agent->fonction ?: '—' }}
                        </td>


                        <td class="px-4 py-3">
                            {{ $agent->service ?: '—' }}
                        </td>


                        <td class="px-4 py-3">

                            @if($agent->user)

                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">
                                    Compte associé
                                </span>

                            @else

                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">
                                    Aucun compte
                                </span>

                            @endif

                        </td>


                        <td class="px-4 py-3">

                            <a
                                href="{{ route('hr.agents.show', $agent->id) }}"
                                class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white"
                            >
                                Ouvrir
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="px-4 py-10 text-center text-slate-500"
                        >
                            Aucun agent trouvé.
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{ $agents->links() }}

</div>

@endsection