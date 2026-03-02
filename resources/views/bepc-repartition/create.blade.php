<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service de l'Organisation des Examens - Saisie</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
    @endif
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 text-slate-900">
<div class="mx-auto max-w-[1700px] p-4 md:p-6 lg:p-8">
    <div class="flex flex-col gap-5 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-lg backdrop-blur-sm transition-all duration-200 hover:shadow-xl">
                <div class="border-b border-slate-200/80 bg-gradient-to-r from-white to-slate-50/50 px-6 py-5 md:px-8 md:py-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Saisie Répartition <span class="rounded-lg bg-blue-100 px-3 py-1 text-blue-700">{{ old('type_examen', $typeExamen) }}</span></h1>
                            <p class="text-sm font-medium text-slate-500">Saisie par salle avec choix BEPC / CEPE</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('repartition.dashboard') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Dashboard
                            </a>
                            @if(auth()->user()?->isAdmin())
                                <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('repartition.vacations') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Vacations
                                </a>
                            @endif
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('repartition.livre.preview') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Livre (aperçu)
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    @if(session('status'))
                        <div class="mb-6 animate-slideDown rounded-lg border border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-white px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ session('status') }}
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 animate-slideDown rounded-lg border border-rose-200/80 bg-gradient-to-r from-rose-50 to-white px-4 py-3 text-sm font-medium text-rose-700 shadow-sm">
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    @foreach($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('bepc.repartition.create') }}" class="mb-6 grid grid-cols-1 gap-4 rounded-xl border border-slate-200/80 bg-slate-50/50 p-5 shadow-inner md:grid-cols-3">
                        <div class="space-y-1.5">
                            <label for="type_examen" class="block text-sm font-semibold text-slate-700">Type d'examen</label>
                            <select id="type_examen" name="type_examen" required class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                <option value="BEPC" {{ old('type_examen', $typeExamen) === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ old('type_examen', $typeExamen) === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label for="nombre_salles" class="block text-sm font-semibold text-slate-700">Nombre de salles</label>
                            <input id="nombre_salles" type="number" name="nombre_salles" min="1" max="50" value="{{ old('nombre_salles', $nombreSalles) }}" required class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>
                        <div class="flex items-end">
                            <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Générer le canevas
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('bepc.repartition.store') }}">
                        @csrf
                        <input type="hidden" name="nombre_salles" value="{{ $nombreSalles }}">
                        <input type="hidden" name="type_examen" value="{{ old('type_examen', $typeExamen) }}">

                        <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div class="space-y-1.5">
                                <label for="dren_id" class="block text-sm font-semibold text-slate-700">DREN</label>
                                <select id="dren_id" name="dren_id" required class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    <option value="">Sélectionner</option>
                                    @foreach($drens as $dren)
                                        <option value="{{ $dren->id }}" {{ (string) old('dren_id') === (string) $dren->id ? 'selected' : '' }}>{{ $dren->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label for="cisco_id" class="block text-sm font-semibold text-slate-700">CISCO</label>
                                <select id="cisco_id" name="cisco_id" required class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    <option value="">Sélectionner</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label for="centre_correction_id" class="block text-sm font-semibold text-slate-700">Centre de correction</label>
                                <select id="centre_correction_id" name="centre_correction_id" required class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    <option value="">Sélectionner</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label for="centre_ecrit_id" class="block text-sm font-semibold text-slate-700">Centre d'écrit</label>
                                <select id="centre_ecrit_id" name="centre_ecrit_id" required class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    <option value="">Sélectionner</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-slate-700">Nom du saisisseur connecté</label>
                                <div class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm">
                                    {{ auth()->user()->name }}
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label for="axe_dispatching" class="block text-sm font-semibold text-slate-700">Axe de dispatching</label>
                                <input
                                    id="axe_dispatching"
                                    type="text"
                                    name="axe_dispatching"
                                    value="{{ old('axe_dispatching') }}"
                                    list="axeSuggestions"
                                    required
                                    class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                >
                                <datalist id="axeSuggestions">
                                    @foreach($axesSuggestions as $axe)
                                        <option value="{{ $axe }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="space-y-1.5">
                                <label for="point_largage" class="block text-sm font-semibold text-slate-700">Point de largage (lieu)</label>
                                <input
                                    id="point_largage"
                                    type="text"
                                    name="point_largage"
                                    value="{{ old('point_largage') }}"
                                    list="pointSuggestions"
                                    required
                                    class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                >
                                <datalist id="pointSuggestions"></datalist>
                            </div>
                            <div class="space-y-1.5">
                                <label for="annee" class="block text-sm font-semibold text-slate-700">Année scolaire</label>
                                <input id="annee" type="text" name="annee" placeholder="2025-2026" value="{{ old('annee') }}" required class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            </div>
                            <div class="space-y-1.5">
                                <label for="salles_inutilisables" class="block text-sm font-semibold text-slate-700">Salles inutilisables (optionnel)</label>
                                <input id="salles_inutilisables" type="text" name="salles_inutilisables" placeholder="Ex: 4, 9, 12" value="{{ old('salles_inutilisables') }}" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                <p class="text-xs text-slate-500">La numérotation reste identique (ex: 1 à 6), mais les salles indiquées seront ignorées à la saisie et à l'enregistrement.</p>
                            </div>
                        </div>

                        @if(old('type_examen', $typeExamen) === 'BEPC')
                            <div class="mb-5 rounded-xl border border-slate-200/80 bg-slate-50/60 p-4">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <h3 class="text-sm font-semibold text-slate-800">Exception candidats étrangers (BEPC)</h3>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                        <input id="has_foreign_candidates" type="checkbox" name="has_foreign_candidates" value="1" {{ old('has_foreign_candidates') ? 'checked' : '' }}>
                                        Activer cette exception
                                    </label>
                                </div>

                                <div id="foreign-config" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div class="space-y-1.5">
                                        <label for="foreign_option_a_lv" class="block text-sm font-semibold text-slate-700">Option A - Langue vivante</label>
                                        <select id="foreign_option_a_lv" name="foreign_option_a_lv" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                            <option value="ALL" {{ old('foreign_option_a_lv', 'ALL') === 'ALL' ? 'selected' : '' }}>ALL (Allemand)</option>
                                            <option value="Esp" {{ old('foreign_option_a_lv') === 'Esp' ? 'selected' : '' }}>Esp</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label for="foreign_option_a_replace_malagasy" class="block text-sm font-semibold text-slate-700">Option A - Remplacement Malagasy</label>
                                        <input id="foreign_option_a_replace_malagasy" type="text" name="foreign_option_a_replace_malagasy" value="{{ old('foreign_option_a_replace_malagasy') }}" placeholder="Ex: Francais" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label for="foreign_option_b_replace_malagasy" class="block text-sm font-semibold text-slate-700">Option B - Remplacement Malagasy</label>
                                        <input id="foreign_option_b_replace_malagasy" type="text" name="foreign_option_b_replace_malagasy" value="{{ old('foreign_option_b_replace_malagasy') }}" placeholder="Ex: Francais" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-5 overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-md">
                            <div class="overflow-x-auto">
                                <table class="min-w-full border-collapse text-sm">
                                    <thead>
                                    <tr class="bg-gradient-to-r from-slate-100 to-slate-50">
                                        <th class="sticky left-0 border border-slate-200 bg-slate-100 px-4 py-3 text-left font-semibold text-slate-700">{{ old('type_examen', $typeExamen) === 'BEPC' ? 'Langue' : 'Total' }}</th>
                                        @for($salle = 1; $salle <= $nombreSalles; $salle++)
                                            <th data-room-head="{{ $salle }}" class="whitespace-nowrap border border-slate-200 px-4 py-3 font-semibold text-slate-600">Salle {{ $salle }}</th>
                                        @endfor
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(old('type_examen', $typeExamen) === 'BEPC')
                                        @foreach($langues as $index => $langue)
                                            <tr class="transition-colors duration-150 hover:bg-slate-50/80 {{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50/30' }}">
                                                <td class="sticky left-0 border border-slate-200 bg-inherit px-4 py-3 font-semibold text-slate-700">{{ $langue }}</td>
                                                @for($salle = 1; $salle <= $nombreSalles; $salle++)
                                                    <td class="border border-slate-200 px-3 py-2.5">
                                                        <input
                                                            type="number"
                                                            min="0"
                                                            name="effectifs[{{ $langue }}][{{ $salle }}]"
                                                            value="{{ old("effectifs.$langue.$salle", 0) }}"
                                                            required
                                                            data-salle="{{ $salle }}"
                                                            data-category="{{ $langue }}"
                                                            class="effectif-input w-20 rounded-lg border border-slate-200 px-3 py-2 text-center text-sm shadow-sm transition-all duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                                        >
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="bg-white transition-colors duration-150 hover:bg-slate-50/80">
                                            <td class="sticky left-0 border border-slate-200 bg-inherit px-4 py-3 font-semibold text-slate-700">Total CEPE</td>
                                            @for($salle = 1; $salle <= $nombreSalles; $salle++)
                                                <td class="border border-slate-200 px-3 py-2.5">
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        name="effectifs_total[{{ $salle }}]"
                                                        value="{{ old("effectifs_total.$salle", 0) }}"
                                                        required
                                                        data-salle="{{ $salle }}"
                                                        data-category="Total CEPE"
                                                        class="effectif-input w-20 rounded-lg border border-slate-200 px-3 py-2 text-center text-sm shadow-sm transition-all duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                                    >
                                                </td>
                                            @endfor
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if(old('type_examen', $typeExamen) === 'BEPC')
                            <div id="foreign-effectifs" class="mb-5 overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-md">
                                <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">Effectifs candidats étrangers (si exception activée)</div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full border-collapse text-sm">
                                        <thead>
                                        <tr class="bg-gradient-to-r from-slate-100 to-slate-50">
                                            <th class="sticky left-0 border border-slate-200 bg-slate-100 px-4 py-3 text-left font-semibold text-slate-700">Option</th>
                                            @for($salle = 1; $salle <= $nombreSalles; $salle++)
                                                <th data-room-head="{{ $salle }}" class="whitespace-nowrap border border-slate-200 px-4 py-3 font-semibold text-slate-600">Salle {{ $salle }}</th>
                                            @endfor
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr class="bg-white">
                                            <td class="sticky left-0 border border-slate-200 bg-white px-4 py-3 font-semibold text-slate-700">Etranger Option A</td>
                                            @for($salle = 1; $salle <= $nombreSalles; $salle++)
                                                <td class="border border-slate-200 px-3 py-2.5">
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        name="foreign_effectifs[option_a][{{ $salle }}]"
                                                        value="{{ old("foreign_effectifs.option_a.$salle", 0) }}"
                                                        data-salle="{{ $salle }}"
                                                        data-category="Etranger Option A"
                                                        class="effectif-input foreign-input w-20 rounded-lg border border-slate-200 px-3 py-2 text-center text-sm shadow-sm transition-all duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                                    >
                                                </td>
                                            @endfor
                                        </tr>
                                        <tr class="bg-slate-50/30">
                                            <td class="sticky left-0 border border-slate-200 bg-inherit px-4 py-3 font-semibold text-slate-700">Etranger Option B</td>
                                            @for($salle = 1; $salle <= $nombreSalles; $salle++)
                                                <td class="border border-slate-200 px-3 py-2.5">
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        name="foreign_effectifs[option_b][{{ $salle }}]"
                                                        value="{{ old("foreign_effectifs.option_b.$salle", 0) }}"
                                                        data-salle="{{ $salle }}"
                                                        data-category="Etranger Option B"
                                                        class="effectif-input foreign-input w-20 rounded-lg border border-slate-200 px-3 py-2 text-center text-sm shadow-sm transition-all duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                                    >
                                                </td>
                                            @endfor
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="flex justify-end">
                            <button class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:from-blue-700 hover:to-blue-800 hover:shadow-lg focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Enregistrer la saisie
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-slideDown {
        animation: slideDown 0.3s ease-out;
    }
</style>

<script>
    (function () {
        const ciscos = @json($ciscos);
        const centresCorrection = @json($centresCorrection);
        const centresEcrit = @json($centresEcrit);
        const pointSuggestionsByAxe = @json($pointSuggestionsByAxe);

        const drenSelect = document.getElementById('dren_id');
        const ciscoSelect = document.getElementById('cisco_id');
        const ccSelect = document.getElementById('centre_correction_id');
        const ceSelect = document.getElementById('centre_ecrit_id');
        const axeInput = document.getElementById('axe_dispatching');
        const pointSuggestions = document.getElementById('pointSuggestions');
        const unusableRoomsInput = document.getElementById('salles_inutilisables');
        const roomHeaders = Array.from(document.querySelectorAll('[data-room-head]'));
        const effectifInputs = Array.from(document.querySelectorAll('.effectif-input'));
        const foreignModeCheckbox = document.getElementById('has_foreign_candidates');
        const foreignConfig = document.getElementById('foreign-config');
        const foreignEffectifsBlock = document.getElementById('foreign-effectifs');
        const foreignConfigInputs = Array.from(document.querySelectorAll('#foreign-config select, #foreign-config input'));
        const foreignEffectifInputs = Array.from(document.querySelectorAll('.foreign-input'));
        const form = document.querySelector('form[action="{{ route('bepc.repartition.store') }}"]');
        const optionAReplaceInput = document.getElementById('foreign_option_a_replace_malagasy');
        const optionBReplaceInput = document.getElementById('foreign_option_b_replace_malagasy');

        const oldCisco = "{{ old('cisco_id') }}";
        const oldCc = "{{ old('centre_correction_id') }}";
        const oldCe = "{{ old('centre_ecrit_id') }}";

        function fillSelect(selectEl, items, placeholder, selectedValue) {
            selectEl.innerHTML = '';
            const first = document.createElement('option');
            first.value = '';
            first.textContent = placeholder;
            selectEl.appendChild(first);

            items.forEach((item) => {
                const option = document.createElement('option');
                option.value = String(item.id);
                option.textContent = item.nom;
                if (String(selectedValue || '') === String(item.id)) {
                    option.selected = true;
                }
                selectEl.appendChild(option);
            });
        }

        function refreshCisco(selectedCisco = '') {
            const drenId = drenSelect.value;
            const filtered = ciscos.filter((item) => String(item.dren_id) === String(drenId));
            fillSelect(ciscoSelect, filtered, 'Sélectionner', selectedCisco);
            if (!filtered.some((item) => String(item.id) === String(ciscoSelect.value))) {
                ciscoSelect.value = '';
            }
            refreshCentreCorrection('');
        }

        function refreshCentreCorrection(selectedCc = '') {
            const ciscoId = ciscoSelect.value;
            const filtered = centresCorrection.filter((item) => String(item.cisco_id) === String(ciscoId));
            fillSelect(ccSelect, filtered, 'Sélectionner', selectedCc);
            if (!filtered.some((item) => String(item.id) === String(ccSelect.value))) {
                ccSelect.value = '';
            }
            refreshCentreEcrit('');
        }

        function refreshCentreEcrit(selectedCe = '') {
            const ccId = ccSelect.value;
            const ciscoId = ciscoSelect.value;
            const filtered = centresEcrit.filter((item) =>
                String(item.cisco_id) === String(ciscoId) &&
                (String(ccId) === '' || String(item.centre_correction_id) === String(ccId))
            );
            fillSelect(ceSelect, filtered, 'Sélectionner', selectedCe);
            if (!filtered.some((item) => String(item.id) === String(ceSelect.value))) {
                ceSelect.value = '';
            }
        }

        function refreshPointSuggestions() {
            const axe = String(axeInput.value || '').trim().toLowerCase();
            const entries = Object.entries(pointSuggestionsByAxe);
            const match = entries.find(([label]) => String(label).trim().toLowerCase() === axe);
            const values = match ? match[1] : [];

            pointSuggestions.innerHTML = '';
            values.forEach((value) => {
                const option = document.createElement('option');
                option.value = value;
                pointSuggestions.appendChild(option);
            });
        }

        function parseUnavailableRooms() {
            if (!unusableRoomsInput) {
                return new Set();
            }

            const raw = String(unusableRoomsInput.value || '');
            const values = raw.split(',')
                .map((v) => Number.parseInt(v.trim(), 10))
                .filter((v) => Number.isInteger(v) && v > 0);

            return new Set(values);
        }

        function applyRoomAvailability() {
            const unavailable = parseUnavailableRooms();

            roomHeaders.forEach((header) => {
                const room = Number.parseInt(header.dataset.roomHead || '0', 10);
                const disabled = unavailable.has(room);
                header.style.textDecoration = disabled ? 'line-through' : '';
                header.style.opacity = disabled ? '0.55' : '1';
            });

            effectifInputs.forEach((input) => {
                const room = Number.parseInt(input.dataset.salle || '0', 10);
                const disabled = unavailable.has(room);
                const foreignInput = input.classList.contains('foreign-input');
                const shouldDisableByForeign = foreignInput && foreignModeCheckbox && !foreignModeCheckbox.checked;
                input.disabled = disabled || shouldDisableByForeign;
                input.required = !foreignInput && !disabled;
                input.closest('td').style.opacity = (disabled || shouldDisableByForeign) ? '0.45' : '1';
            });
        }

        function applyForeignModeState() {
            if (!foreignModeCheckbox) {
                return;
            }

            const enabled = foreignModeCheckbox.checked;
            foreignConfigInputs.forEach((input) => {
                input.disabled = !enabled;
                input.required = enabled;
            });

            if (foreignConfig) {
                foreignConfig.style.opacity = enabled ? '1' : '0.5';
            }
            if (foreignEffectifsBlock) {
                foreignEffectifsBlock.style.opacity = enabled ? '1' : '0.5';
            }

            applyRoomAvailability();
        }

        function enforceForeignPerRoomExclusivity(changedInput) {
            if (!changedInput || !changedInput.name) {
                return;
            }

            const room = changedInput.dataset.salle;
            if (!room) {
                return;
            }

            const isOptionA = changedInput.name.includes('foreign_effectifs[option_a]');
            const oppositeName = isOptionA
                ? `foreign_effectifs[option_b][${room}]`
                : `foreign_effectifs[option_a][${room}]`;
            const oppositeInput = document.querySelector(`input[name="${oppositeName}"]`);

            if (!oppositeInput) {
                return;
            }

            const currentValue = Number.parseInt(changedInput.value || '0', 10) || 0;
            if (currentValue > 0) {
                oppositeInput.value = '0';
                oppositeInput.disabled = true;
            } else {
                oppositeInput.disabled = false;
                applyRoomAvailability();
            }
        }

        function syncForeignReplacementRequirements() {
            const optionAValues = Array.from(document.querySelectorAll('input[name^="foreign_effectifs[option_a]"]'));
            const optionBValues = Array.from(document.querySelectorAll('input[name^="foreign_effectifs[option_b]"]'));
            const hasOptionA = optionAValues.some((input) => (Number.parseInt(input.value || '0', 10) || 0) > 0);
            const hasOptionB = optionBValues.some((input) => (Number.parseInt(input.value || '0', 10) || 0) > 0);

            if (optionAReplaceInput) {
                optionAReplaceInput.required = !!(foreignModeCheckbox && foreignModeCheckbox.checked && hasOptionA);
            }
            if (optionBReplaceInput) {
                optionBReplaceInput.required = !!(foreignModeCheckbox && foreignModeCheckbox.checked && hasOptionB);
            }
        }

        drenSelect.addEventListener('change', () => refreshCisco(''));
        ciscoSelect.addEventListener('change', () => refreshCentreCorrection(''));
        ccSelect.addEventListener('change', () => refreshCentreEcrit(''));
        axeInput.addEventListener('input', refreshPointSuggestions);
        if (unusableRoomsInput) {
            unusableRoomsInput.addEventListener('input', applyRoomAvailability);
        }
        if (foreignModeCheckbox) {
            foreignModeCheckbox.addEventListener('change', applyForeignModeState);
        }
        foreignEffectifInputs.forEach((input) => {
            input.addEventListener('input', () => {
                enforceForeignPerRoomExclusivity(input);
                syncForeignReplacementRequirements();
            });
        });
        if (form) {
            form.addEventListener('submit', (event) => {
                const conflicts = [];
                for (let salle = 1; salle <= {{ $nombreSalles }}; salle++) {
                    const a = Number.parseInt((document.querySelector(`input[name="foreign_effectifs[option_a][${salle}]"]`)?.value || '0'), 10) || 0;
                    const b = Number.parseInt((document.querySelector(`input[name="foreign_effectifs[option_b][${salle}]"]`)?.value || '0'), 10) || 0;
                    if (a > 0 && b > 0) {
                        conflicts.push(salle);
                    }
                }
                if (conflicts.length > 0) {
                    event.preventDefault();
                    alert(`Conflit étrangers Option A/B sur salle(s): ${conflicts.join(', ')}`);
                }
            });
        }

        refreshCisco(oldCisco);
        if (oldCisco) {
            ciscoSelect.value = oldCisco;
            refreshCentreCorrection(oldCc);
        }
        if (oldCc) {
            ccSelect.value = oldCc;
            refreshCentreEcrit(oldCe);
        }
        if (oldCe) {
            ceSelect.value = oldCe;
        }
        refreshPointSuggestions();
        applyForeignModeState();
        applyRoomAvailability();
        syncForeignReplacementRequirements();
    })();
</script>
</body>
</html>
