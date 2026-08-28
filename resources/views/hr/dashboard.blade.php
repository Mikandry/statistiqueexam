@extends('layouts.app')

@section('title', $isAdmin ? 'Gestion du personnel' : 'Mon espace personnel')
@section('subtitle', $isAdmin
    ? 'Situation du personnel et affectations'
    : 'Votre situation administrative')

@section('content')

{{-- Conteneur principal avec un dégradé plus doux et une ombre subtile --}}
<div class="space-y-8 rounded-3xl bg-gradient-to-br from-slate-50 via-white to-teal-50/40 p-6 shadow-lg shadow-slate-200/50">

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


    {{-- STATISTIQUES ADMIN --}}
    @if($isAdmin)

        {{-- Grille de cartes statistiques avec animations et icônes --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            @foreach([
                ['Agents actifs', $stats['total'], 'bg-slate-800 text-white', '👥'],
                ['Présents', $stats['present'], 'bg-emerald-600 text-white', '✅'],
                ['Congés', $stats['conge'], 'bg-blue-600 text-white', '🌴'],
                ['Missions', $stats['mission'], 'bg-amber-500 text-white', '📋'],
                ['Formations', $stats['formation'], 'bg-violet-600 text-white', '📚'],
                ['Autorisations', $stats['autorisation_absence'], 'bg-orange-500 text-white', '📝'],
                ['Affectation temporaire', $stats['affectation_temporaire'], 'bg-cyan-600 text-white', '🔄'],
                ['Sans affectation', $stats['sans_affectation'], 'bg-white text-slate-800 border-2 border-slate-200', '🚫'],
            ] as [$label, $value, $class, $icon])

                <article class="group relative overflow-hidden rounded-2xl p-5 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ $class }}">
                    {{-- Décoration de fond --}}
                    <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-white/10 blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider opacity-80">
                                {{ $label }}
                            </p>
                            <p class="mt-2 text-3xl font-black">
                                {{ $value }}
                            </p>
                        </div>
                        <span class="text-3xl opacity-70 group-hover:scale-110 transition-transform duration-300">
                            {{ $icon }}
                        </span>
                    </div>
                </article>

            @endforeach

        </section>

        @if($isAdmin)
        <section class="overflow-x-auto rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
            <h2 class="text-xl font-black text-slate-800">Suivi des congés et autorisations</h2>
            <table class="mt-4 min-w-full text-sm">
                <thead class="border-b border-slate-200 text-left text-xs font-black uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-3">Agent</th>
                        <th class="px-3 py-3">Congés</th>
                        <th class="px-3 py-3">Jours de congé</th>
                        <th class="px-3 py-3">Autorisations</th>
                        <th class="px-3 py-3">Jours d’absence</th>
                        <th class="px-3 py-3">Total jours</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($eventSummary as $summary)
                        <tr>
                            <td class="px-3 py-3 font-semibold">{{ $summary['agent']->full_name }}</td>
                            <td class="px-3 py-3">{{ $summary['leave_count'] }}</td>
                            <td class="px-3 py-3">{{ $summary['leave_days'] }}</td>
                            <td class="px-3 py-3">{{ $summary['absence_count'] }}</td>
                            <td class="px-3 py-3">{{ $summary['absence_days'] }}</td>
                            <td class="px-3 py-3 font-bold">{{ $summary['total_days'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-5 text-center text-slate-400">Aucune demande enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        @endif

        {{-- Barre de filtres et actions --}}
        <section class="flex flex-wrap items-end justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white/80 p-5 shadow-md backdrop-blur-sm transition-all hover:shadow-lg">

            <form method="GET" class="flex flex-wrap items-end gap-3">

                <div>
                    <label for="date" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Date</label>
                    <input
                        type="date"
                        name="date"
                        id="date"
                        value="{{ $selectedDate }}"
                        class="mt-1 rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2 text-sm shadow-sm transition-all focus:border-teal-400 focus:ring-2 focus:ring-teal-200 focus:outline-none"
                    >
                </div>

                <div>
                    <label for="q" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Recherche</label>
                    <input
                        name="q"
                        id="q"
                        value="{{ $search }}"
                        placeholder="Nom ou matricule"
                        class="mt-1 rounded-xl border-slate-300 bg-slate-50/50 px-4 py-2 text-sm shadow-sm transition-all focus:border-teal-400 focus:ring-2 focus:ring-teal-200 focus:outline-none"
                    >
                </div>

                <button class="rounded-xl bg-gradient-to-r from-slate-800 to-slate-700 px-6 py-2 text-sm font-bold text-white shadow-md transition-all hover:scale-105 hover:shadow-lg active:scale-95">
                    Filtrer
                </button>

            </form>

            <a
                href="{{ route('hr.agents.index') }}"
                class="rounded-xl border border-slate-300 bg-white px-6 py-2 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:shadow-md active:scale-95"
            >
                👤 Personnel
            </a>

        @else

            <a
                href="{{ route('hr.my-profile') }}"
                class="rounded-xl bg-gradient-to-r from-slate-800 to-slate-700 px-6 py-3 text-sm font-bold text-white shadow-md transition-all hover:scale-105 hover:shadow-lg active:scale-95"
            >
                📂 Mon dossier et mes demandes
            </a>

        </section>

    @endif

        @unless($isAdmin)
            @php($mySummary = $eventSummary->first())
            <section class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase text-slate-500">Jours de congé</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">{{ $mySummary['leave_days'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $mySummary['leave_count'] ?? 0 }} demande(s)</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase text-slate-500">Jours d’absence</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">{{ $mySummary['absence_days'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $mySummary['absence_count'] ?? 0 }} demande(s)</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase text-slate-500">Total de jours</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">{{ $mySummary['total_days'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-slate-500">Toutes les demandes enregistrées</p>
                </div>
            </section>
        @endunless


    {{-- SITUATION --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-lg backdrop-blur-sm transition-all">

        {{-- En-tête du tableau --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/70 bg-gradient-to-r from-slate-50/80 to-teal-50/40 p-5">
            <div>
                <h2 class="text-xl font-black text-slate-800">
                    {{ $isAdmin
                        ? '📊 Situation du personnel'
                        : '📋 Ma situation administrative'
                    }}
                </h2>
                <p class="mt-0.5 text-sm text-slate-500">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                    </span>
                </p>
            </div>
            @if($isAdmin)
                <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-700">
                    {{ count($situations) }} agent(s)
                </span>
            @endif
        </div>

        {{-- Tableau --}}
        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-slate-100/70 text-left text-xs font-black uppercase tracking-wider text-slate-600">

                    <tr>
                        @if($isAdmin)
                            <th class="px-4 py-4">Agent</th>
                        @endif

                        <th class="px-4 py-4">Fonction</th>
                        <th class="px-4 py-4">Service</th>
                        <th class="px-4 py-4">Situation</th>
                        <th class="px-4 py-4">Début</th>
                        <th class="px-4 py-4">Fin</th>
                        <th class="px-4 py-4">Disponibilité</th>
                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100/80">

                @forelse($situations as $s)

                    <tr class="transition-colors duration-200 hover:bg-slate-50/80 group">

                        @if($isAdmin)

                            <td class="px-4 py-4 font-semibold">

                                <a
                                    href="{{ route('hr.agents.show', $s['agent']->id) }}"
                                    class="text-blue-700 transition-colors hover:text-blue-900 hover:underline flex items-center gap-1.5"
                                >
                                    <span class="group-hover:translate-x-0.5 transition-transform">▶</span>
                                    {{ $s['agent']->full_name }}
                                </a>

                                <br>

                                <span class="text-xs text-slate-400">
                                    {{ $s['agent']->matricule ?: 'Sans matricule' }}
                                </span>

                            </td>

                        @endif


                        <td class="px-4 py-4 text-slate-700">
                            {{ $s['agent']->fonction ?: '—' }}
                        </td>


                        <td class="px-4 py-4 text-slate-700">
                            {{ $s['agent']->service ?: '—' }}
                        </td>


                        <td class="px-4 py-4">

                            <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold
                                {{ $s['code'] === 'present'
                                    ? 'border-emerald-300 bg-emerald-50 text-emerald-700'
                                    : ($s['code'] === 'conge'
                                        ? 'border-blue-300 bg-blue-50 text-blue-700'
                                        : 'border-amber-300 bg-amber-50 text-amber-700')
                                }}">

                                @if($s['code'] === 'present')
                                    ✅
                                @elseif($s['code'] === 'conge')
                                    🌴
                                @else
                                    📌
                                @endif

                                {{ $s['label'] }}

                            </span>

                            @if(!empty($s['time_start']))

                                <div class="mt-1.5 text-xs font-medium text-slate-400">
                                    ⏱ {{ substr($s['time_start'], 0, 5) }}
                                    –
                                    {{ substr($s['time_end'], 0, 5) }}
                                </div>

                            @endif

                        </td>


                        <td class="px-4 py-4 text-slate-700">
                            {{ $s['start']?->format('d/m/Y') ?: '—' }}
                        </td>


                        <td class="px-4 py-4 text-slate-700">
                            {{ $s['end']?->format('d/m/Y') ?: '—' }}
                        </td>


                        <td class="px-4 py-4">
                            <span class="inline-block rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                                {{ $s['availability'] }}
                            </span>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td
                            colspan="{{ $isAdmin ? 7 : 6 }}"
                            class="px-4 py-12 text-center font-semibold text-slate-400"
                        >
                            <span class="text-2xl block mb-2">🔍</span>
                            Aucun agent trouvé.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </section>


    {{-- DERNIERS EVENEMENTS --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg backdrop-blur-sm transition-all hover:shadow-xl">

        <div class="flex flex-wrap items-center justify-between gap-3">

            <h2 class="text-xl font-black text-slate-800">
                {{ $isAdmin
                    ? '🕒 Dernières situations enregistrées'
                    : '🕒 Mes dernières situations'
                }}
            </h2>

            @if($isAdmin)

                <a
                    href="{{ route('hr.agents.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-4 py-2 text-sm font-bold text-teal-700 transition-colors hover:bg-teal-100"
                >
                    Gérer les agents
                    <span class="text-lg">→</span>
                </a>

            @endif

        </div>


        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">

            @forelse($recentEvents as $event)

                <div class="group rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">

                    @if($isAdmin)
                        <p class="font-bold text-slate-800 flex items-center gap-2">
                            <span class="text-lg">👤</span>
                            {{ $event->agent?->full_name }}
                        </p>
                    @endif

                    <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-700">
                        <span class="text-base">
                            @switch($event->type)
                                @case('conge') 🌴 @break
                                @case('mission') 📋 @break
                                @case('formation') 📚 @break
                                @default 📌
                            @endswitch
                        </span>
                        {{ \App\Models\HrEvent::TYPES[$event->type] ?? $event->type }}

                        @if($event->title)
                            <span class="text-xs text-slate-400">·</span>
                            <span class="font-medium">{{ $event->title }}</span>
                        @endif
                    </p>

                    <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">

                        <span class="inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $event->date_debut?->format('d/m/Y') }}
                        </span>

                        @if($event->date_fin)
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $event->date_fin->format('d/m/Y') }}
                            </span>
                        @endif

                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 font-semibold text-slate-600">
                            {{ $event->date_fin?->lt(today()) ? 'Expiré' : ucfirst($event->status) }}
                        </span>

                    </p>

                </div>

            @empty

                <p class="col-span-full text-center text-sm text-slate-400 py-8">
                    <span class="text-2xl block mb-2">📭</span>
                    Aucune situation enregistrée.
                </p>

            @endforelse

        </div>

    </section>

</div>

@endsection