@extends('layouts.app')

@section('title', 'Personnel')
@section('subtitle', 'Agents, fonctions et affectations')

@section('content')
<div class="space-y-8 rounded-3xl bg-gradient-to-br from-slate-50 via-white to-blue-50/40 p-6 shadow-lg shadow-slate-200/50">

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-5 py-4 text-sm font-semibold text-emerald-800 shadow-sm backdrop-blur-sm transition-all hover:shadow-md">
            <span class="text-xl">✅</span>
            {{ session('success') }}
        </div>
    @endif

    @if(($errors ?? null)?->any())
        <div class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50/80 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm backdrop-blur-sm transition-all hover:shadow-md">
            <span class="text-xl">⚠️</span>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Ajout d'un agent --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg backdrop-blur-sm transition-all hover:shadow-xl">
        <h2 class="flex items-center gap-2 text-lg font-black text-slate-800">
            <span class="rounded-full bg-emerald-100 p-1.5 text-emerald-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </span>
            Ajouter un agent
        </h2>

        <form method="POST" action="{{ route('hr.agents.store') }}" class="mt-4 grid gap-4 md:grid-cols-3 xl:grid-cols-5">
            @csrf

            <input name="matricule" placeholder="Matricule" class="rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:bg-white focus:outline-none">
            <input name="nom" placeholder="Nom" required class="rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:bg-white focus:outline-none">
            <input name="prenoms" placeholder="Prénoms" class="rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:bg-white focus:outline-none">
            <input name="fonction" placeholder="Fonction" class="rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:bg-white focus:outline-none">
            <input name="service" placeholder="Service" class="rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:bg-white focus:outline-none">
            <input name="statut" placeholder="Statut" class="rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:bg-white focus:outline-none">
            <input name="telephone" placeholder="Téléphone" class="rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:bg-white focus:outline-none">
            <input name="email" type="email" placeholder="Email" class="rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:bg-white focus:outline-none">

            <button class="rounded-xl bg-gradient-to-r from-slate-800 to-slate-700 px-6 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:scale-105 hover:shadow-lg active:scale-95">
                Ajouter
            </button>
        </form>
    </section>

    {{-- Liste des agents --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg backdrop-blur-sm transition-all hover:shadow-xl">

        {{-- Barre de recherche --}}
        <form method="GET" class="mb-5 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input
                    name="q"
                    value="{{ $search }}"
                    placeholder="Nom, matricule, fonction, service…"
                    class="w-full rounded-xl border-slate-300 bg-slate-50/50 pl-9 pr-4 py-2.5 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:bg-white focus:outline-none"
                >
            </div>
            <button class="rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:scale-105 hover:shadow-lg active:scale-95">
                Rechercher
            </button>
        </form>

        {{-- Tableau --}}
        <div class="overflow-hidden rounded-xl border border-slate-200/60">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100/70 text-left text-xs font-black uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="px-5 py-4">Matricule</th>
                            <th class="px-5 py-4">Agent</th>
                            <th class="px-5 py-4">Fonction</th>
                            <th class="px-5 py-4">Statut</th>
                            <th class="px-5 py-4">Direction / service</th>
                            <th class="px-5 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/80">
                        @forelse($agents as $agent)
                        <tr class="transition-colors duration-200 hover:bg-slate-50/80 group">
                            <td class="px-5 py-4 font-mono text-xs text-slate-600">
                                {{ $agent->matricule ?: '—' }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800">
                                {{ $agent->full_name }}
                            </td>
                            <td class="px-5 py-4 text-slate-700">
                                {{ $agent->fonction ?: '—' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-block rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                    {{ $agent->statut ?: '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-700">
                                {{ $agent->direction ?: '—' }} / {{ $agent->service ?: '—' }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <details class="group [&_summary::-webkit-details-marker]:hidden">
                                    <summary class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl bg-blue-50 px-4 py-2 text-xs font-bold text-blue-700 transition-colors hover:bg-blue-100 hover:text-blue-900">
                                        <span>Modifier / affecter / situation</span>
                                        <svg class="h-4 w-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </summary>

                                    <div class="mt-4 space-y-4 rounded-xl border border-slate-200 bg-slate-50/70 p-4 text-left">

                                        {{-- Formulaire de mise à jour --}}
                                        <form method="POST" action="{{ route('hr.agents.update', $agent) }}" class="grid gap-3 md:grid-cols-3">
                                            @csrf @method('PUT')
                                            <input name="matricule" value="{{ $agent->matricule }}" placeholder="Matricule" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="nom" value="{{ $agent->nom }}" required placeholder="Nom" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="prenoms" value="{{ $agent->prenoms }}" placeholder="Prénoms" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="fonction" value="{{ $agent->fonction }}" placeholder="Fonction" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="service" value="{{ $agent->service }}" placeholder="Service" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="statut" value="{{ $agent->statut }}" placeholder="Statut" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <button class="col-span-3 rounded-xl bg-slate-800 px-4 py-2 text-sm font-bold text-white shadow-md transition-all hover:bg-slate-700 hover:shadow-lg active:scale-95">
                                                Enregistrer
                                            </button>
                                        </form>

                                        {{-- Formulaire d'affectation --}}
                                        <form method="POST" action="{{ route('hr.agents.assignments.store', $agent) }}" class="grid gap-3 md:grid-cols-4">
                                            @csrf
                                            <input name="direction" placeholder="Direction accueil" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="service" placeholder="Service accueil" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="fonction" placeholder="Fonction" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="date_debut" type="date" required class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="date_fin" type="date" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="motif" placeholder="Motif" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="reference" placeholder="Référence" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <button class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-bold text-white shadow-md transition-all hover:bg-amber-700 hover:shadow-lg active:scale-95">
                                                Ajouter affectation
                                            </button>
                                        </form>

                                        {{-- Formulaire d'événement --}}
                                        <form method="POST" action="{{ route('hr.agents.events.store', $agent) }}" class="grid gap-3 md:grid-cols-4">
                                            @csrf
                                            <select name="type" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                                @foreach(\App\Models\HrEvent::TYPES as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <select name="status" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                                <option value="valide">Validé</option>
                                                <option value="demande">Demandé</option>
                                                <option value="brouillon">Brouillon</option>
                                            </select>
                                            <input name="title" placeholder="Intitulé / destination / organisme" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="date_debut" type="date" required class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="date_fin" type="date" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="motif" placeholder="Motif" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="reference" placeholder="Référence" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <input name="autorite" placeholder="Autorité" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-md transition-all hover:bg-blue-700 hover:shadow-lg active:scale-95">
                                                Enregistrer la situation
                                            </button>
                                        </form>

                                        {{-- Documents associés --}}
                                        @if($agent->events->isNotEmpty())
                                            <div class="mt-4 rounded-xl border border-slate-200 bg-white/80 p-4">
                                                <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                                                    📄 Documents
                                                </p>
                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <a href="{{ route('hr.documents.non-leave', $agent) }}" class="rounded-xl bg-slate-800 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-slate-700 hover:shadow-md active:scale-95">
                                                        Non‑jouissance
                                                    </a>
                                                    @foreach($agent->events as $event)
                                                        @if($event->type === 'conge')
                                                            <a href="{{ route('hr.documents.leave', [$agent->id, $event->id]) }}" class="rounded-xl bg-blue-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-md active:scale-95">
                                                                Fiche congé
                                                            </a>
                                                        @elseif($event->type === 'autorisation_absence')
                                                            <a href="{{ route('hr.documents.absence', [$agent->id, $event->id]) }}" class="rounded-xl bg-orange-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-orange-700 hover:shadow-md active:scale-95">
                                                                Autorisation absence
                                                            </a>
                                                        @elseif($event->type === 'mission')
                                                            <a href="{{ route('hr.documents.mission', [$agent->id, $event->id]) }}" class="rounded-xl bg-amber-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-amber-700 hover:shadow-md active:scale-95">
                                                                Ordre mission
                                                            </a>
                                                        @elseif($event->type === 'formation')
                                                            <a href="{{ route('hr.documents.training', [$agent->id, $event->id]) }}" class="rounded-xl bg-violet-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-violet-700 hover:shadow-md active:scale-95">
                                                                Autorisation formation
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </details>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center font-semibold text-slate-400">
                                <span class="text-2xl block mb-2">🔍</span>
                                Aucun agent enregistré.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-5 flex justify-center">
            {{ $agents->links() }}
        </div>

    </section>
</div>
@endsection