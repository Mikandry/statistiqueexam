@extends('layouts.app')

@section('title', $agent->full_name)
@section('subtitle', 'Dossier administratif du personnel')

@section('content')

<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">

        <div>

            <h1 class="text-2xl font-black">
                {{ $agent->full_name }}
            </h1>

            <p class="text-sm text-slate-500">
                Matricule :
                {{ $agent->matricule ?: 'Non renseigné' }}
            </p>

        </div>

        <a
            href="{{ route('hr.agents.index') }}"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold"
        >
            ← Personnel
        </a>

    </div>


    {{-- IDENTITE --}}
    <section class="rounded-lg border bg-white p-5 shadow-sm">

        <h2 class="mb-4 font-black">
            Informations administratives
        </h2>

        <div class="grid gap-4 md:grid-cols-3">

            <div>
                <p class="text-xs font-bold uppercase text-slate-500">
                    Nom et prénoms
                </p>
                <p class="font-semibold">{{ $agent->full_name }}</p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-500">
                    Matricule
                </p>
                <p>{{ $agent->matricule ?: '—' }}</p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-500">
                    Statut
                </p>
                <p>{{ $agent->statut ?: '—' }}</p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-500">
                    Corps
                </p>
                <p>{{ $agent->corps ?: '—' }}</p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-500">
                    Grade
                </p>
                <p>{{ $agent->grade ?: '—' }}</p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-500">
                    Indice
                </p>
                <p>{{ $agent->indice ?: '—' }}</p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-500">
                    Catégorie
                </p>
                <p>{{ $agent->categorie ?: '—' }}</p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-500">
                    Échelon
                </p>
                <p>{{ $agent->echelon ?: '—' }}</p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-500">
                    Fonction
                </p>
                <p>{{ $agent->fonction ?: '—' }}</p>
            </div>

        </div>

    </section>


    {{-- AFFECTATION --}}
    <section class="rounded-lg border bg-white p-5 shadow-sm">

        <h2 class="mb-4 font-black">
            Affectation actuelle
        </h2>

        @if($agent->currentAssignment)

            <div class="grid gap-4 md:grid-cols-4">

                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">
                        Direction
                    </p>
                    <p>{{ $agent->currentAssignment->direction ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">
                        Service
                    </p>
                    <p>{{ $agent->currentAssignment->service ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">
                        Bureau
                    </p>
                    <p>{{ $agent->currentAssignment->bureau ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">
                        Fonction
                    </p>
                    <p>{{ $agent->currentAssignment->fonction ?: '—' }}</p>
                </div>

            </div>

        @else

            <p class="text-sm text-red-600">
                Aucune affectation active enregistrée.
            </p>

        @endif

    </section>


    {{-- SITUATIONS --}}
    <section class="rounded-lg border bg-white shadow-sm">

        <div class="border-b p-5">

            <h2 class="font-black">
                Historique des situations
            </h2>

        </div>


        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-slate-50 text-xs font-black uppercase">

                    <tr>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Période</th>
                        <th class="px-4 py-3 text-left">Statut</th>
                        <th class="px-4 py-3 text-left">Motif</th>
                        <th class="px-4 py-3 text-left">Documents</th>
                    </tr>

                </thead>


                <tbody class="divide-y">

                @forelse($agent->events as $event)

                    <tr>

                        <td class="px-4 py-3 font-semibold">
                            {{ \App\Models\HrEvent::TYPES[$event->type] ?? $event->type }}
                        </td>


                        <td class="px-4 py-3">

                            {{ $event->date_debut->format('d/m/Y') }}

                            @if($event->date_fin)
                                au {{ $event->date_fin->format('d/m/Y') }}
                            @endif

                            @if($event->heure_debut)
                                <br>
                                <span class="text-xs text-slate-500">
                                    {{ substr($event->heure_debut,0,5) }}
                                    -
                                    {{ substr($event->heure_fin,0,5) }}
                                </span>
                            @endif

                        </td>


                        <td class="px-4 py-3">
                            {{ ucfirst($event->status) }}
                        </td>


                        <td class="px-4 py-3">
                            {{ $event->motif ?: '—' }}
                        </td>


                        <td class="px-4 py-3">

                            @if($event->type === 'conge')

                                <a
                                    href="{{ route('hr.documents.leave', [$agent->id, $event->id]) }}"
                                    class="text-blue-700 hover:underline"
                                >
                                    Fiche
                                </a>

                            @elseif($event->type === 'autorisation_absence')

                                <a
                                    href="{{ route('hr.documents.absence', [$agent->id, $event->id]) }}"
                                    class="text-blue-700 hover:underline"
                                >
                                    Autorisation
                                </a>

                            @elseif($event->type === 'mission')

                                <a
                                    href="{{ route('hr.documents.mission', [$agent->id, $event->id]) }}"
                                    class="text-blue-700 hover:underline"
                                >
                                    Ordre
                                </a>

                            @elseif($event->type === 'formation')

                                <a
                                    href="{{ route('hr.documents.training', [$agent->id, $event->id]) }}"
                                    class="text-blue-700 hover:underline"
                                >
                                    Formation
                                </a>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5"
                            class="px-4 py-8 text-center text-slate-500">
                            Aucune situation enregistrée.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </section>


    {{-- DOCUMENTS GENERAUX --}}
    <section class="rounded-lg border bg-white p-5 shadow-sm">

        <h2 class="mb-4 font-black">
            Attestations
        </h2>

        <div class="flex flex-wrap gap-2">

            <a
                href="{{ route('hr.documents.non-leave', $agent->id) }}"
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white"
            >
                Attestation de non-jouissance de congé
            </a>

        </div>

    </section>

</div>

@endsection