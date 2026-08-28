@extends('layouts.app')

@section('title', 'Personnel')
@section('subtitle', 'Gestion administrative des agents')

@section('content')

<div class="space-y-8 rounded-3xl bg-gradient-to-br from-slate-50 via-white to-cyan-50/40 p-6 shadow-lg shadow-slate-200/50">

    {{-- En-tête --}}
    <div class="flex flex-wrap items-center justify-between gap-4">

        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-800 flex items-center gap-3">
                <span class="bg-gradient-to-r from-slate-700 to-slate-900 bg-clip-text text-transparent">Personnel</span>
                <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-bold text-cyan-700">{{ $agents->total() }}</span>
            </h1>

            <p class="mt-0.5 text-sm text-slate-500 flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Liste des agents de la direction
            </p>
        </div>

        <a
            href="{{ route('hr.dashboard') }}"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:shadow-md active:scale-95"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Tableau de bord
        </a>

    </div>


    {{-- Barre de recherche --}}
    <form method="GET"
          class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white/80 p-4 shadow-md backdrop-blur-sm transition-all">

        <div class="relative flex-1 min-w-[240px]">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Nom, matricule, service ou fonction…"
                class="w-full rounded-xl border-slate-300 bg-slate-50/50 pl-9 pr-4 py-2.5 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none"
            >
        </div>

        <button
            class="rounded-xl bg-gradient-to-r from-slate-800 to-slate-700 px-6 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:scale-105 hover:shadow-lg active:scale-95"
        >
            Rechercher
        </button>

    </form>


    {{-- Formulaire d'ajout --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg backdrop-blur-sm transition-all hover:shadow-xl">

        <h2 class="mb-5 flex items-center gap-2 text-lg font-black text-slate-800">
            <span class="rounded-full bg-emerald-100 p-1.5 text-emerald-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </span>
            Ajouter un personnel
        </h2>

        <form method="POST" action="{{ route('hr.agents.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Matricule</label>
                <input name="matricule" value="{{ old('matricule') }}" placeholder="Matricule" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Nom <span class="text-red-500">*</span></label>
                <input name="nom" value="{{ old('nom') }}" placeholder="Nom" required class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Prénoms</label>
                <input name="prenoms" value="{{ old('prenoms') }}" placeholder="Prénoms" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Sexe</label>
                <select name="sexe" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
                    <option value="">Sexe</option>
                    <option value="M" @selected(old('sexe') === 'M')>Masculin</option>
                    <option value="F" @selected(old('sexe') === 'F')>Féminin</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Date de naissance</label>
                <input name="date_naissance" value="{{ old('date_naissance') }}" type="date" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">CIN</label>
                <input name="cin" value="{{ old('cin') }}" placeholder="CIN" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone</label>
                <input name="telephone" value="{{ old('telephone') }}" placeholder="Téléphone" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Email</label>
                <input name="email" value="{{ old('email') }}" type="email" placeholder="Email" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Adresse</label>
                <input name="adresse" value="{{ old('adresse') }}" placeholder="Adresse" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Statut</label>
                <input name="statut" value="{{ old('statut') }}" placeholder="Statut" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Corps</label>
                <input name="corps" value="{{ old('corps') }}" placeholder="Corps" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Grade</label>
                <input name="grade" value="{{ old('grade') }}" placeholder="Grade" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Indice</label>
                <input name="indice" value="{{ old('indice') }}" placeholder="Indice" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Catégorie</label>
                <input name="categorie" value="{{ old('categorie') }}" placeholder="Catégorie" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Échelon</label>
                <input name="echelon" value="{{ old('echelon') }}" placeholder="Échelon" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Fonction</label>
                <input name="fonction" value="{{ old('fonction') }}" placeholder="Fonction" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Direction</label>
                <input name="direction" value="{{ old('direction') }}" placeholder="Direction" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Service</label>
                <input name="service" value="{{ old('service') }}" placeholder="Service" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Bureau</label>
                <input name="bureau" value="{{ old('bureau') }}" placeholder="Bureau" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Supérieur hiérarchique</label>
                <input name="superieur_hierarchique" value="{{ old('superieur_hierarchique') }}" placeholder="Supérieur hiérarchique" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Budget</label>
                <input name="budget" value="{{ old('budget') }}" placeholder="Budget" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Chapitre</label>
                <input name="chapitre" value="{{ old('chapitre') }}" placeholder="Chapitre" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Date de recrutement</label>
                <input name="date_recrutement" value="{{ old('date_recrutement') }}" type="date" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Date de prise de service</label>
                <input name="date_prise_service" value="{{ old('date_prise_service') }}" type="date" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Situation administrative</label>
                <input name="situation_administrative" value="{{ old('situation_administrative') }}" placeholder="Situation administrative" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 focus:bg-white focus:outline-none">
            </div>

            <div class="flex items-center gap-2 self-end pb-1">
                <input name="actif" value="1" type="checkbox" @checked(old('actif', true)) class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-2 focus:ring-cyan-200">
                <label class="text-sm font-semibold text-slate-700">Personnel actif</label>
            </div>

            <div class="md:col-span-2 xl:col-span-3 2xl:col-span-4 mt-2">
                <button class="w-full rounded-xl bg-gradient-to-r from-slate-800 to-slate-700 px-6 py-3 text-sm font-bold text-gre shadow-md transition-all hover:scale-[1.02] hover:shadow-lg active:scale-95">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Ajouter le personnel
                    </span>
                </button>
            </div>
        </form>

    </section>


    {{-- Tableau des agents --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-lg backdrop-blur-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-slate-100/70 text-left text-xs font-black uppercase tracking-wider text-slate-600">

                    <tr>
                        <th class="px-5 py-4">Agent</th>
                        <th class="px-5 py-4">Matricule</th>
                        <th class="px-5 py-4">Fonction</th>
                        <th class="px-5 py-4">Service</th>
                        <th class="px-5 py-4">Compte</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100/80">

                @forelse($agents as $agent)

                    <tr class="transition-colors duration-200 hover:bg-slate-50/80 group">

                        <td class="px-5 py-4 font-semibold">

                            <a
                                href="{{ route('hr.agents.show', $agent->id) }}"
                                class="text-blue-700 transition-colors hover:text-blue-900 hover:underline flex items-center gap-1.5"
                            >
                                <span class="group-hover:translate-x-0.5 transition-transform">▶</span>
                                {{ $agent->full_name }}
                            </a>

                        </td>


                        <td class="px-5 py-4 font-mono text-xs text-slate-600">
                            {{ $agent->matricule ?: '—' }}
                        </td>


                        <td class="px-5 py-4 text-slate-700">
                            {{ $agent->fonction ?: '—' }}
                        </td>


                        <td class="px-5 py-4 text-slate-700">
                            {{ $agent->service ?: '—' }}
                        </td>


                        <td class="px-5 py-4">

                            @if($agent->user)

                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Compte associé
                                </span>

                            @else

                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Aucun compte
                                </span>

                            @endif

                        </td>


                        <td class="px-5 py-4 text-right">

                            <a
                                href="{{ route('hr.agents.show', $agent->id) }}"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 px-4 py-2 text-xs font-bold text-white shadow-sm transition-all hover:bg-slate-700 hover:shadow-md active:scale-95"
                            >
                                <span>Ouvrir</span>
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="px-4 py-12 text-center text-slate-400"
                        >
                            <span class="text-2xl block mb-2">🔍</span>
                            Aucun agent trouvé.
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- Pagination --}}
    <div class="flex justify-center">
        {{ $agents->links() }}
    </div>

</div>

@endsection