@extends('layouts.app')

@section('title', $agent->full_name)
@section('subtitle', 'Dossier administratif du personnel')

@section('content')

<div class="space-y-6 rounded-2xl bg-gradient-to-br from-slate-50 via-white to-emerald-50/40 p-1">

    <div class="flex flex-wrap items-center justify-between gap-3">

        <div>

            <h1 class="text-3xl font-black tracking-tight text-slate-900">
                {{ $agent->full_name }}
            </h1>

            <p class="text-sm text-slate-500">
                Matricule :
                {{ $agent->matricule ?: 'Non renseigné' }}
            </p>

        </div>

        <a
            href="{{ ($isAdmin ?? false) ? route('hr.agents.index') : route('hr.dashboard') }}"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold"
        >
            ← Personnel
        </a>

    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ACTIONS ADMINISTRATIVES --}}
    @if($isAdmin ?? false)
    <section class="rounded-lg border bg-white p-5 shadow-sm">
        <details open>
            <summary class="cursor-pointer font-black text-slate-900">
                Modifier les informations du personnel
            </summary>

            <form method="POST" action="{{ route('hr.agents.update', $agent->id) }}" class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @csrf
                @method('PUT')

                <input name="matricule" value="{{ $agent->matricule }}" placeholder="Matricule" class="rounded-lg border-slate-300 text-sm">
                <input name="nom" value="{{ $agent->nom }}" placeholder="Nom" class="rounded-lg border-slate-300 text-sm" required>
                <input name="prenoms" value="{{ $agent->prenoms }}" placeholder="Prénoms" class="rounded-lg border-slate-300 text-sm">
                <select name="sexe" class="rounded-lg border-slate-300 text-sm">
                    <option value="">Sexe</option>
                    <option value="M" @selected($agent->sexe === 'M')>Masculin</option>
                    <option value="F" @selected($agent->sexe === 'F')>Féminin</option>
                </select>
                <input name="date_naissance" type="date" value="{{ $agent->date_naissance?->format('Y-m-d') }}" class="rounded-lg border-slate-300 text-sm">
                <input name="cin" value="{{ $agent->cin }}" placeholder="CIN" class="rounded-lg border-slate-300 text-sm">
                <input name="telephone" value="{{ $agent->telephone }}" placeholder="Téléphone" class="rounded-lg border-slate-300 text-sm">
                <input name="email" type="email" value="{{ $agent->email }}" placeholder="Email" class="rounded-lg border-slate-300 text-sm">
                <input name="adresse" value="{{ $agent->adresse }}" placeholder="Adresse" class="rounded-lg border-slate-300 text-sm">

                <input name="statut" value="{{ $agent->statut }}" placeholder="Statut" class="rounded-lg border-slate-300 text-sm">
                <input name="corps" value="{{ $agent->corps }}" placeholder="Corps" class="rounded-lg border-slate-300 text-sm">
                <input name="grade" value="{{ $agent->grade }}" placeholder="Grade" class="rounded-lg border-slate-300 text-sm">
                <input name="indice" value="{{ $agent->indice }}" placeholder="Indice" class="rounded-lg border-slate-300 text-sm">
                <input name="categorie" value="{{ $agent->categorie }}" placeholder="Catégorie" class="rounded-lg border-slate-300 text-sm">
                <input name="echelon" value="{{ $agent->echelon }}" placeholder="Échelon" class="rounded-lg border-slate-300 text-sm">

                <input name="fonction" value="{{ $agent->fonction }}" placeholder="Fonction" class="rounded-lg border-slate-300 text-sm">
                <input name="direction" value="{{ $agent->direction }}" placeholder="Direction" class="rounded-lg border-slate-300 text-sm">
                <input name="service" value="{{ $agent->service }}" placeholder="Service" class="rounded-lg border-slate-300 text-sm">
                <input name="bureau" value="{{ $agent->bureau }}" placeholder="Bureau" class="rounded-lg border-slate-300 text-sm">
                <input name="superieur_hierarchique" value="{{ $agent->superieur_hierarchique }}" placeholder="Supérieur hiérarchique" class="rounded-lg border-slate-300 text-sm">
                <input name="budget" value="{{ $agent->budget }}" placeholder="Budget" class="rounded-lg border-slate-300 text-sm">
                <input name="chapitre" value="{{ $agent->chapitre }}" placeholder="Chapitre" class="rounded-lg border-slate-300 text-sm">
                <input name="date_recrutement" type="date" value="{{ $agent->date_recrutement?->format('Y-m-d') }}" class="rounded-lg border-slate-300 text-sm">
                <input name="date_prise_service" type="date" value="{{ $agent->date_prise_service?->format('Y-m-d') }}" class="rounded-lg border-slate-300 text-sm">
                <input name="situation_administrative" value="{{ $agent->situation_administrative }}" placeholder="Situation administrative" class="rounded-lg border-slate-300 text-sm">

                <label class="flex items-center gap-2 text-sm font-semibold">
                    <input name="actif" value="1" type="checkbox" @checked($agent->actif) class="rounded border-slate-300">
                    Personnel actif
                </label>

                <div class="xl:col-span-4">
                    <button class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-bold text-white">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </details>
    </section>
    @endif

    <section class="grid gap-6 xl:grid-cols-2">
        @if($isAdmin ?? false)
        <div class="rounded-lg border bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-black">Ajouter une affectation</h2>
            <form method="POST" action="{{ route('hr.agents.assignments.store', $agent->id) }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                <input name="direction" placeholder="Direction" class="rounded-lg border-slate-300 text-sm">
                <input name="service" placeholder="Service" class="rounded-lg border-slate-300 text-sm">
                <input name="bureau" placeholder="Bureau" class="rounded-lg border-slate-300 text-sm">
                <input name="fonction" placeholder="Fonction" class="rounded-lg border-slate-300 text-sm">
                <label class="text-xs font-bold text-slate-500">Date de début<input name="date_debut" type="date" required class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                <label class="text-xs font-bold text-slate-500">Date de fin<input name="date_fin" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                <input name="motif" placeholder="Motif" class="rounded-lg border-slate-300 text-sm">
                <input name="reference" placeholder="Référence" class="rounded-lg border-slate-300 text-sm">
                <button class="rounded-lg bg-amber-600 px-5 py-2 text-sm font-bold text-white md:col-span-2">Enregistrer l'affectation</button>
            </form>
        </div>
        @endif

        <div class="rounded-lg border bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-black">Ajouter un congé ou une autorisation</h2>
            <form method="POST" action="{{ ($isAdmin ?? false) ? route('hr.agents.events.store', $agent->id) : route('hr.my-events.store') }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                <select name="type" class="rounded-lg border-slate-300 text-sm">
                    @foreach(\App\Models\HrEvent::TYPES as $value => $label)
                        @if(($isAdmin ?? false) || in_array($value, ['conge', 'autorisation_absence'], true))
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endif
                    @endforeach
                </select>
                @if($isAdmin ?? false)
                    <select name="status" class="rounded-lg border-slate-300 text-sm">@foreach(\App\Models\HrEvent::STATUSES as $value => $label)<option value="{{ $value }}" @selected($value === 'demande')>{{ $label }}</option>@endforeach</select>
                @else
                    <input type="hidden" name="status" value="demande">
                    <p class="rounded-lg bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800">La demande sera soumise à validation.</p>
                @endif
                <input name="title" placeholder="Intitulé" class="rounded-lg border-slate-300 text-sm md:col-span-2">
                <label class="text-xs font-bold text-slate-500">Date de début<input id="event-date-start" name="date_debut" type="date" required class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                <label class="text-xs font-bold text-slate-500">Date de fin<input id="event-date-end" name="date_fin" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                <label class="text-xs font-bold text-slate-500">Jours demandés<input id="event-days" type="text" value="0" readonly class="mt-1 w-full rounded-lg border-slate-300 bg-slate-50 text-sm"></label>
                <input name="duree_heures" type="number" min="0.25" max="24" step="0.25" placeholder="Durée en heures" class="rounded-lg border-slate-300 text-sm">
                <input name="heure_debut" type="time" class="rounded-lg border-slate-300 text-sm">
                <input name="heure_fin" type="time" class="rounded-lg border-slate-300 text-sm">
                <input name="lieu" placeholder="Lieu" class="rounded-lg border-slate-300 text-sm">
                <input name="organisme" placeholder="Organisme" class="rounded-lg border-slate-300 text-sm">
                <input name="destination" placeholder="Destination" class="rounded-lg border-slate-300 text-sm">
                <div class="md:col-span-2">
                    <p class="mb-2 text-xs font-bold text-slate-500">Jours de la semaine concernés</p>
                    <div class="flex flex-wrap gap-3 text-sm">
                        @foreach([1 => 'Lun', 2 => 'Mar', 3 => 'Mer', 4 => 'Jeu', 5 => 'Ven', 6 => 'Sam', 7 => 'Dim'] as $day => $label)
                            <label class="flex items-center gap-1"><input type="checkbox" name="jours_semaine[]" value="{{ $day }}" class="rounded border-slate-300"> {{ $label }}</label>
                        @endforeach
                    </div>
                </div>
                <input name="reference" placeholder="Référence" class="rounded-lg border-slate-300 text-sm">
                <input name="autorite" placeholder="Autorité" class="rounded-lg border-slate-300 text-sm">
                <textarea name="motif" placeholder="Motif" class="rounded-lg border-slate-300 text-sm md:col-span-2"></textarea>
                <textarea name="observation" placeholder="Observation" class="rounded-lg border-slate-300 text-sm md:col-span-2"></textarea>
                <button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-bold text-white md:col-span-2">Enregistrer la demande</button>
            </form>
        </div>
    </section>

    @php
        $daysFor = fn ($event) => ($event->date_fin ?: $event->date_debut)->diffInDays($event->date_debut) + 1;
        $leaveDays = $agent->events->where('type', 'conge')->sum($daysFor);
        $absenceDays = $agent->events->where('type', 'autorisation_absence')->sum($daysFor);
        $totalDays = $agent->events->sum($daysFor);
        $requestedCount = $agent->events->where('status', 'demande')->count();
    @endphp

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-500">Jours de congé</p><p class="text-2xl font-black">{{ $leaveDays }}</p></div>
        <div class="rounded-lg border bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-500">Jours d'autorisation</p><p class="text-2xl font-black">{{ $absenceDays }}</p></div>
        <div class="rounded-lg border bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-500">Total indisponibilité</p><p class="text-2xl font-black">{{ $totalDays }}</p></div>
        <div class="rounded-lg border bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-500">Demandes en attente</p><p class="text-2xl font-black">{{ $requestedCount }}</p></div>
    </section>


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

                            @if(($isAdmin ?? false) && $event->status === 'demande')
                                <form method="POST" action="{{ route('hr.agents.events.status', [$agent->id, $event->id]) }}" class="mt-2 flex flex-wrap gap-1">
                                    @csrf
                                    @method('PUT')
                                    <button name="status" value="valide" class="rounded bg-emerald-600 px-2 py-1 text-xs font-bold text-white">Accepter</button>
                                    <button name="status" value="refuse" class="rounded bg-red-600 px-2 py-1 text-xs font-bold text-white">Refuser</button>
                                </form>
                            @endif
                        </td>


                        <td class="px-4 py-3">
                            {{ $event->motif ?: '—' }}
                        </td>


                        <td class="px-4 py-3">

                            @php($documentType = null)
                            @if($event->type === 'conge')
                                @php($documentType = 'conge')

                            @elseif($event->type === 'autorisation_absence')
                                @php($documentType = 'absence')

                            @elseif($event->type === 'mission')
                                @php($documentType = 'mission')

                            @elseif($event->type === 'formation')
                                @php($documentType = 'formation')

                            @endif

                            @if($documentType && (($isAdmin ?? false) || $event->status === 'valide'))
                                <div class="flex flex-wrap gap-1">
                                    <a href="{{ route('hr.documents.preview', [$agent->id, $documentType, $event->id]) }}" target="_blank" class="rounded border border-slate-300 px-2 py-1 text-xs font-bold text-slate-700">Aperçu</a>
                                    <a href="{{ route('hr.documents.word', [$agent->id, $documentType, $event->id]) }}" class="rounded bg-blue-600 px-2 py-1 text-xs font-bold text-white">Word</a>
                                    <a href="{{ route('hr.documents.pdf', [$agent->id, $documentType, $event->id]) }}" class="rounded bg-rose-600 px-2 py-1 text-xs font-bold text-white">PDF</a>
                                </div>
                            @elseif($documentType)
                                <span class="text-xs text-amber-700">Après validation</span>
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
    <section class="overflow-hidden rounded-2xl border border-slate-200/70 bg-gradient-to-br from-white to-slate-50/80 shadow-md transition-all hover:shadow-lg">

        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 bg-gradient-to-r from-teal-50/70 to-emerald-50/40 p-5
            Attestations
        </h2>

        <div class="flex flex-wrap gap-2">

            <a
                href="{{ route('hr.documents.non-interruption', $agent->id) }}"
                class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-bold text-white"
            >
                Non-interruption de service
            </a>

            <a href="{{ route('hr.documents.preview', [$agent->id, 'non-interruption']) }}" target="_blank" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700">Aperçu interruption</a>
            <a href="{{ route('hr.documents.word', [$agent->id, 'non-interruption']) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white">Word interruption</a>
            <a href="{{ route('hr.documents.pdf', [$agent->id, 'non-interruption']) }}" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-bold text-white">PDF interruption</a>

            <a
                href="{{ route('hr.documents.service-start', $agent->id) }}"
                class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-bold text-white"
            >
                Fiche de prise de service
            </a>

            <a href="{{ route('hr.documents.preview', [$agent->id, 'prise-service']) }}" target="_blank" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700">Aperçu prise de service</a>
            <a href="{{ route('hr.documents.word', [$agent->id, 'prise-service']) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white">Word prise de service</a>
            <a href="{{ route('hr.documents.pdf', [$agent->id, 'prise-service']) }}" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-bold text-white">PDF prise de service</a>

            <a
                href="{{ route('hr.documents.non-leave', $agent->id) }}"
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white"
            >
                Attestation de non-jouissance de congé
            </a>

            <a href="{{ route('hr.documents.preview', [$agent->id, 'non-jouissance']) }}" target="_blank" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700">Aperçu non-jouissance</a>
            <a href="{{ route('hr.documents.word', [$agent->id, 'non-jouissance']) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white">Word non-jouissance</a>
            <a href="{{ route('hr.documents.pdf', [$agent->id, 'non-jouissance']) }}" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-bold text-white">PDF non-jouissance</a>

            <a href="{{ route('hr.documents.preview', [$agent->id, 'fiche-administrative']) }}" target="_blank" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700">Aperçu fiche administrative</a>
            <a href="{{ route('hr.documents.word', [$agent->id, 'fiche-administrative']) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white">Word fiche administrative</a>
            <a href="{{ route('hr.documents.pdf', [$agent->id, 'fiche-administrative']) }}" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-bold text-white">PDF fiche administrative</a>

        </div>

    </section>

</div>

<script>
    (() => {
        const start = document.getElementById('event-date-start');
        const end = document.getElementById('event-date-end');
        const days = document.getElementById('event-days');

        if (!start || !end || !days) return;

        const updateDays = () => {
            if (!start.value) {
                days.value = '0';
                return;
            }

            const startDate = new Date(`${start.value}T00:00:00`);
            const endDate = new Date(`${end.value || start.value}T00:00:00`);
            const difference = Math.floor((endDate - startDate) / 86400000) + 1;

            days.value = difference > 0 ? difference : '0';
        };

        start.addEventListener('change', updateDays);
        end.addEventListener('change', updateDays);
    })();
</script>

@endsection