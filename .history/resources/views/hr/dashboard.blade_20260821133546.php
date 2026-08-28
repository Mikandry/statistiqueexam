@extends('layouts.app')

@section('title', $isAdmin ? 'Gestion du personnel' : 'Mon espace personnel')
@section('subtitle', $isAdmin
    ? 'Situation du personnel et affectations'
    : 'Votre situation administrative')

@section('content')

<div class="space-y-6 rounded-2xl bg-gradient-to-br from-slate-50 via-white to-teal-50/60 p-1">

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(($errors ?? null)?->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
            {{ $errors->first() }}
        </div>
    @endif


    {{-- STATISTIQUES ADMIN --}}
    @if($isAdmin)

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">

            @foreach([
                ['Agents actifs', $stats['total'], 'bg-slate-900 text-white'],
                ['Présents', $stats['present'], 'bg-emerald-600 text-white'],
                ['Congés', $stats['conge'], 'bg-blue-600 text-white'],
                ['Missions', $stats['mission'], 'bg-amber-500 text-white'],
                ['Formations', $stats['formation'], 'bg-violet-600 text-white'],
                ['Autorisations', $stats['autorisation_absence'], 'bg-orange-500 text-white'],
                ['Affectation temporaire', $stats['affectation_temporaire'], 'bg-cyan-600 text-white'],
                ['Sans affectation', $stats['sans_affectation'], 'bg-white text-slate-900'],
            ] as [$label, $value, $class])

                <article class="rounded-xl border border-white/70 p-4 shadow-sm transition-shadow hover:shadow-md {{ $class }}">
                    <p class="text-xs font-bold uppercase opacity-70">
                        {{ $label }}
                    </p>

                    <p class="mt-2 text-2xl font-black">
                        {{ $value }}
                    </p>
                </article>

            @endforeach

        </section>

        <section class="flex flex-wrap items-end justify-between gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">

            <form method="GET" class="flex flex-wrap gap-2">

                <input
                    type="date"
                    name="date"
                    value="{{ $selectedDate }}"
                    class="rounded-lg border-slate-300 text-sm"
                >

                <input
                    name="q"
                    value="{{ $search }}"
                    placeholder="Nom ou matricule"
                    class="rounded-lg border-slate-300 text-sm"
                >

                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">
                    Filtrer
                </button>

            </form>

            <a
                href="{{ route('hr.agents.index') }}"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold"
            >
                Personnel
            </a>

        @else

            <a
                href="{{ route('hr.my-profile') }}"
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white"
            >
                Mon dossier et mes demandes
            </a>

        </section>

    @endif


    {{-- SITUATION --}}
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 p-4">

            <h2 class="font-black">
                {{ $isAdmin
                    ? 'Situation du personnel'
                    : 'Ma situation administrative'
                }}
            </h2>

            <p class="text-sm text-slate-500">
                Le {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-slate-50 text-left text-xs font-black uppercase text-slate-500">

                    <tr>
                        @if($isAdmin)
                            <th class="px-3 py-3">Agent</th>
                        @endif

                        <th class="px-3 py-3">Fonction</th>
                        <th class="px-3 py-3">Service</th>
                        <th class="px-3 py-3">Situation</th>
                        <th class="px-3 py-3">Début</th>
                        <th class="px-3 py-3">Fin</th>
                        <th class="px-3 py-3">Disponibilité</th>
                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                @forelse($situations as $s)

                    <tr>

                        @if($isAdmin)

                            <td class="px-3 py-3 font-semibold">

                                <a
                                    href="{{ route('hr.agents.show', $s['agent']->id) }}"
                                    class="text-blue-700 hover:underline"
                                >
                                    {{ $s['agent']->full_name }}
                                </a>

                                <br>

                                <span class="text-xs text-slate-500">
                                    {{ $s['agent']->matricule ?: 'Sans matricule' }}
                                </span>

                            </td>

                        @endif


                        <td class="px-3 py-3">
                            {{ $s['agent']->fonction ?: '—' }}
                        </td>


                        <td class="px-3 py-3">
                            {{ $s['agent']->service ?: '—' }}
                        </td>


                        <td class="px-3 py-3">

                            <span class="rounded-full border px-2.5 py-1 text-xs font-bold
                                {{ $s['code'] === 'present'
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                    : ($s['code'] === 'conge'
                                        ? 'border-blue-200 bg-blue-50 text-blue-700'
                                        : 'border-amber-200 bg-amber-50 text-amber-700')
                                }}">

                                {{ $s['label'] }}

                            </span>

                            @if(!empty($s['time_start']))

                                <div class="mt-1 text-xs text-slate-500">
                                    {{ substr($s['time_start'], 0, 5) }}
                                    –
                                    {{ substr($s['time_end'], 0, 5) }}
                                </div>

                            @endif

                        </td>


                        <td class="px-3 py-3">
                            {{ $s['start']?->format('d/m/Y') ?: '—' }}
                        </td>


                        <td class="px-3 py-3">
                            {{ $s['end']?->format('d/m/Y') ?: '—' }}
                        </td>


                        <td class="px-3 py-3">
                            {{ $s['availability'] }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td
                            colspan="{{ $isAdmin ? 7 : 6 }}"
                            class="px-4 py-10 text-center font-semibold text-slate-500"
                        >
                            Aucun agent trouvé.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </section>


    {{-- DERNIERS EVENEMENTS --}}
    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">

        <div class="flex items-center justify-between">

            <h2 class="font-black">
                {{ $isAdmin
                    ? 'Dernières situations enregistrées'
                    : 'Mes dernières situations'
                }}
            </h2>

            @if($isAdmin)

                <a
                    href="{{ route('hr.agents.index') }}"
                    class="text-sm font-bold text-blue-700"
                >
                    Gérer les agents
                </a>

            @endif

        </div>


        <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-3">

            @forelse($recentEvents as $event)

                <div class="rounded border border-slate-200 p-3">

                    @if($isAdmin)
                        <p class="font-bold">
                            {{ $event->agent?->full_name }}
                        </p>
                    @endif

                    <p class="text-sm text-slate-700">
                        {{ \App\Models\HrEvent::TYPES[$event->type] ?? $event->type }}

                        @if($event->title)
                            · {{ $event->title }}
                        @endif
                    </p>

                    <p class="text-xs text-slate-500">

                        {{ $event->date_debut?->format('d/m/Y') }}

                        @if($event->date_fin)
                            au {{ $event->date_fin->format('d/m/Y') }}
                        @endif

                        · {{ ucfirst($event->status) }}

                    </p>

                </div>

            @empty

                <p class="text-sm text-slate-500">
                    Aucune situation enregistrée.
                </p>

            @endforelse

        </div>

    </section>

</div>

@endsection