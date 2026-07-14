<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Statistiques</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')
    <style>
        nav[role="navigation"] svg { width: 16px; height: 16px; }
        nav[role="navigation"] a, nav[role="navigation"] span { font-size: 12px; }
    </style>
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
                            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Administration Statistiques</h1>
                            <p class="text-sm font-medium text-slate-500">Edition fine des lignes saisies et suppression par centre</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('bepc.repartition.create') }}">Saisie</a>
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('admin.users.index') }}">Utilisateurs</a>
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('admin.references.index') }}">Référentiels</a>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    @if(session('status'))
                        <div class="mb-6 rounded-lg border border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-white px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">{{ session('status') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="mb-6 rounded-lg border border-rose-200/80 bg-gradient-to-r from-rose-50 to-white px-4 py-3 text-sm font-medium text-rose-700 shadow-sm">{{ $errors->first() }}</div>
                    @endif

                    <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                        La salle est verrouillée en modification: seul l'effectif est éditable. La suppression se fait par centre (toutes les salles/lignes du centre).
                    </div>

                    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-slate-900">Paramètres généraux</h2>
                            <p class="text-sm text-slate-600">Réglages communs pour feuilles BEPC, soubiques sujets et tirage CEPE/BEPC.</p>
                        </div>
                        <form method="POST" action="{{ route('admin.statistics.settings.general') }}" class="space-y-5">
                            @csrf

                            <div class="grid gap-3 md:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700" for="bepc_copy_margin_percent">Marge BEPC feuilles (%)</label>
                                    <input id="bepc_copy_margin_percent" type="number" step="0.01" min="0" max="100" name="bepc_copy_margin_percent" value="{{ old('bepc_copy_margin_percent', $globalSetting?->bepc_copy_margin_percent ?? 5) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700" for="subject_soubique_ge_capacity">GE max / soubique sujets</label>
                                    <input id="subject_soubique_ge_capacity" type="number" min="1" max="100" name="subject_soubique_ge_capacity" value="{{ old('subject_soubique_ge_capacity', $globalSetting?->subject_soubique_ge_capacity ?? 6) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700" for="subject_soubique_subject_capacity">Matières max / soubique sujets</label>
                                    <input id="subject_soubique_subject_capacity" type="number" min="1" max="100" name="subject_soubique_subject_capacity" value="{{ old('subject_soubique_subject_capacity', $globalSetting?->subject_soubique_subject_capacity ?? 9) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                </div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700" for="sord_sheet_page_capacity">Capacité pages SORD / feuille</label>
                                    <input id="sord_sheet_page_capacity" type="number" min="1" max="200" name="sord_sheet_page_capacity" value="{{ old('sord_sheet_page_capacity', $globalSetting?->sord_sheet_page_capacity ?? 16) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Dispatching et largage</h3>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-sm font-semibold text-slate-700" for="dispatching_axes">Axes de dispatching</label>
                                        <textarea id="dispatching_axes" name="dispatching_axes" rows="5" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="Un axe par ligne">{{ old('dispatching_axes', $globalSetting?->dispatching_axes ?? '') }}</textarea>
                                        <p class="mt-1 text-xs text-slate-500">Ces valeurs alimentent la liste déroulante dans l'écran de saisie.</p>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-sm font-semibold text-slate-700" for="dispatching_drop_points">Points de largage BEPC</label>
                                        <textarea id="dispatching_drop_points" name="dispatching_drop_points" rows="5" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="Un point par ligne">{{ old('dispatching_drop_points', $globalSetting?->dispatching_drop_points ?? '') }}</textarea>
                                        <p class="mt-1 text-xs text-slate-500">Pour le CEPE, le point de largage sera automatiquement la CISCO sélectionnée.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Pages par matière CEPE</h3>
                                <div class="grid gap-3 md:grid-cols-4">
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="cepe_pages_francais">Français</label><input id="cepe_pages_francais" type="number" min="0" max="50" name="cepe_pages_francais" value="{{ old('cepe_pages_francais', $globalSetting?->cepe_pages_francais ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="cepe_pages_connaissances_usuelles">Connaissances usuelles</label><input id="cepe_pages_connaissances_usuelles" type="number" min="0" max="50" name="cepe_pages_connaissances_usuelles" value="{{ old('cepe_pages_connaissances_usuelles', $globalSetting?->cepe_pages_connaissances_usuelles ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="cepe_pages_geographie">Géographie</label><input id="cepe_pages_geographie" type="number" min="0" max="50" name="cepe_pages_geographie" value="{{ old('cepe_pages_geographie', $globalSetting?->cepe_pages_geographie ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="cepe_pages_malagasy">Malagasy</label><input id="cepe_pages_malagasy" type="number" min="0" max="50" name="cepe_pages_malagasy" value="{{ old('cepe_pages_malagasy', $globalSetting?->cepe_pages_malagasy ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="cepe_pages_operation">Opération</label><input id="cepe_pages_operation" type="number" min="0" max="50" name="cepe_pages_operation" value="{{ old('cepe_pages_operation', $globalSetting?->cepe_pages_operation ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="cepe_pages_probleme">Problème</label><input id="cepe_pages_probleme" type="number" min="0" max="50" name="cepe_pages_probleme" value="{{ old('cepe_pages_probleme', $globalSetting?->cepe_pages_probleme ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="cepe_pages_tffmom">TFFMOM</label><input id="cepe_pages_tffmom" type="number" min="0" max="50" name="cepe_pages_tffmom" value="{{ old('cepe_pages_tffmom', $globalSetting?->cepe_pages_tffmom ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Pages par matière BEPC</h3>
                                <div class="grid gap-3 md:grid-cols-5">
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="bepc_pages_malagasy">Malagasy</label><input id="bepc_pages_malagasy" type="number" min="0" max="50" name="bepc_pages_malagasy" value="{{ old('bepc_pages_malagasy', $globalSetting?->bepc_pages_malagasy ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="bepc_pages_svt">SVT</label><input id="bepc_pages_svt" type="number" min="0" max="50" name="bepc_pages_svt" value="{{ old('bepc_pages_svt', $globalSetting?->bepc_pages_svt ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="bepc_pages_francais">Français</label><input id="bepc_pages_francais" type="number" min="0" max="50" name="bepc_pages_francais" value="{{ old('bepc_pages_francais', $globalSetting?->bepc_pages_francais ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="bepc_pages_anglais">Anglais</label><input id="bepc_pages_anglais" type="number" min="0" max="50" name="bepc_pages_anglais" value="{{ old('bepc_pages_anglais', $globalSetting?->bepc_pages_anglais ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="bepc_pages_esp">Esp</label><input id="bepc_pages_esp" type="number" min="0" max="50" name="bepc_pages_esp" value="{{ old('bepc_pages_esp', $globalSetting?->bepc_pages_esp ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="bepc_pages_pc">PC</label><input id="bepc_pages_pc" type="number" min="0" max="50" name="bepc_pages_pc" value="{{ old('bepc_pages_pc', $globalSetting?->bepc_pages_pc ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="bepc_pages_math">Math</label><input id="bepc_pages_math" type="number" min="0" max="50" name="bepc_pages_math" value="{{ old('bepc_pages_math', $globalSetting?->bepc_pages_math ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="bepc_pages_hg">HG</label><input id="bepc_pages_hg" type="number" min="0" max="50" name="bepc_pages_hg" value="{{ old('bepc_pages_hg', $globalSetting?->bepc_pages_hg ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-sm font-semibold text-slate-700" for="bepc_pages_all">ALL</label><input id="bepc_pages_all" type="number" min="0" max="50" name="bepc_pages_all" value="{{ old('bepc_pages_all', $globalSetting?->bepc_pages_all ?? 1) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Ordre de tirage</h3>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-sm font-semibold text-slate-700" for="bepc_print_order">BEPC</label>
                                        <textarea id="bepc_print_order" name="bepc_print_order" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="malagasy, francais, math, svt, pc, hg, anglais, all, esp">{{ old('bepc_print_order', $globalSetting?->bepc_print_order ?? '') }}</textarea>
                                        <p class="mt-1 text-xs text-slate-500">Clés acceptées: malagasy, svt, francais, anglais, esp, pc, math, hg, all</p>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-sm font-semibold text-slate-700" for="cepe_print_order">CEPE</label>
                                        <textarea id="cepe_print_order" name="cepe_print_order" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="francais, connaissances_usuelles, geographie, malagasy, operation, probleme, tffmom">{{ old('cepe_print_order', $globalSetting?->cepe_print_order ?? '') }}</textarea>
                                        <p class="mt-1 text-xs text-slate-500">Clés acceptées: francais, connaissances_usuelles, geographie, malagasy, operation, probleme, tffmom</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">Enregistrer</button>
                            </div>
                        </form>
                    </div>

                    <form method="GET" class="mb-6 grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-8">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="annee">Année</label>
                            <select id="annee" name="annee" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                <option value="">Toutes</option>
                                @foreach($annees as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="type_examen">Type d'examen</label>
                            <select id="type_examen" name="type_examen" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                                <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="dren_id">DREN</label>
                            <select id="dren_id" name="dren_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                <option value="">Toutes</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren->id }}" {{ (int) ($filters['dren_id'] ?? 0) === (int) $dren->id ? 'selected' : '' }}>{{ $dren->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="cisco_id">CISCO</label>
                            <select id="cisco_id" name="cisco_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                <option value="">Tous</option>
                                @foreach($ciscos as $cisco)
                                    <option value="{{ $cisco->id }}" {{ (int) ($filters['cisco_id'] ?? 0) === (int) $cisco->id ? 'selected' : '' }}>{{ $cisco->dren->nom ?? '-' }} / {{ $cisco->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="centre_correction_id">Centre correction</label>
                            <select id="centre_correction_id" name="centre_correction_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                <option value="">Tous</option>
                                @foreach($centresCorrection as $cc)
                                    <option value="{{ $cc->id }}" {{ (int) ($filters['centre_correction_id'] ?? 0) === (int) $cc->id ? 'selected' : '' }}>{{ $cc->cisco->dren->nom ?? '-' }} / {{ $cc->cisco->nom ?? '-' }} / {{ $cc->nom }} ({{ $cc->type_examen }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="centre_ecrit_id">Centre écrit</label>
                            <select id="centre_ecrit_id" name="centre_ecrit_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                <option value="">Tous</option>
                                @foreach($centresEcrit as $centre)
                                    <option value="{{ $centre->id }}" {{ (int) ($filters['centre_ecrit_id'] ?? 0) === (int) $centre->id ? 'selected' : '' }}>
                                        {{ $centre->centreCorrection->cisco->nom ?? '-' }} / {{ $centre->nom }} ({{ $centre->type_examen }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="centre_search">Recherche centre</label>
                            <input id="centre_search" name="centre_search" type="text" value="{{ $filters['centre_search'] ?? '' }}" placeholder="Nom du centre" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                        </div>
                        <div class="flex items-end">
                            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">Filtrer</button>
                        </div>
                        <div class="flex items-end">
                            <button class="rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700" type="submit">Rechercher centre</button>
                        </div>
                    </form>

                    @php
                        $hasBulkStats = isset($bulkStats) && $bulkStats->isNotEmpty();
                        $bulkCentre = $hasBulkStats ? $bulkStats->first()?->centreEcrit : null;
                    @endphp
                    <div id="dispatching-centres" class="mb-6 scroll-mt-24 rounded-xl border border-teal-200 bg-teal-50 p-5 shadow-sm">
                        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Axes et points des centres déjà saisis</h2>
                                <p class="text-sm text-slate-600">Corrige l’axe de dispatching ou le point de largage sur toutes les lignes d’un centre pour l’année et le type affichés.</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-2 text-xs font-black uppercase tracking-wide text-teal-700">
                                {{ number_format(($dispatchingRows ?? collect())->count(), 0, ',', ' ') }} centre(s)
                            </span>
                        </div>

                        @php
                            $axisOptions = collect($dispatchingAxes ?? [])
                                ->merge($allExistingDispatchingAxes ?? [])
                                ->merge(($dispatchingRows ?? collect())->pluck('axe_dispatching'))
                                ->filter()
                                ->unique()
                                ->sort()
                                ->values();

                            $dropPointOptions = collect($dispatchingDropPoints ?? [])
                                ->merge($allExistingDispatchingDropPoints ?? [])
                                ->merge(($dispatchingRows ?? collect())->pluck('point_largage'))
                                ->filter()
                                ->unique()
                                ->sort()
                                ->values();
                        @endphp

                        <div class="overflow-x-auto rounded-xl border border-teal-200 bg-white">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-teal-50/80">
                                    <th class="border border-teal-100 px-3 py-2 text-left font-semibold">Centre</th>
                                    <th class="border border-teal-100 px-3 py-2 text-left font-semibold">Année / type</th>
                                    <th class="border border-teal-100 px-3 py-2 text-right font-semibold">Candidats</th>
                                    <th class="border border-teal-100 px-3 py-2 text-left font-semibold">Axe dispatching</th>
                                    <th class="border border-teal-100 px-3 py-2 text-left font-semibold">Point largage</th>
                                    <th class="border border-teal-100 px-3 py-2 text-left font-semibold">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($dispatchingRows ?? [] as $dispatchRow)
                                    <tr class="hover:bg-slate-50">
                                        <td class="border border-teal-100 px-3 py-2">
                                            <div class="font-bold text-slate-900">{{ $dispatchRow->centre_ecrit }}</div>
                                            <div class="text-xs text-slate-500">{{ $dispatchRow->dren }} / {{ $dispatchRow->cisco }} / {{ $dispatchRow->centre_correction }}</div>
                                        </td>
                                        <td class="border border-teal-100 px-3 py-2">
                                            <div class="font-bold text-slate-900">{{ $dispatchRow->annee }}</div>
                                            <div class="text-xs font-semibold text-teal-700">{{ $dispatchRow->type_examen }} · {{ number_format($dispatchRow->total_salles, 0, ',', ' ') }} salle(s)</div>
                                        </td>
                                        <td class="border border-teal-100 px-3 py-2 text-right font-black text-slate-900">{{ number_format((int) $dispatchRow->total_candidats, 0, ',', ' ') }}</td>
                                        <td class="border border-teal-100 px-3 py-2">
                                            <form id="dispatching-centre-{{ $loop->index }}" method="POST" action="{{ route('admin.statistics.dispatching.update') }}" class="contents">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="centre_ecrit_id" value="{{ $dispatchRow->centre_ecrit_id }}">
                                                <input type="hidden" name="annee" value="{{ $dispatchRow->annee }}">
                                                <input type="hidden" name="type_examen" value="{{ $dispatchRow->type_examen }}">
                                                <input type="hidden" name="filter_annee" value="{{ $filters['annee'] ?? '' }}">
                                                <input type="hidden" name="filter_type_examen" value="{{ $filters['type_examen'] ?? 'ALL' }}">
                                                <input type="hidden" name="filter_dren_id" value="{{ $filters['dren_id'] ?? 0 }}">
                                                <input type="hidden" name="filter_cisco_id" value="{{ $filters['cisco_id'] ?? 0 }}">
                                                <input type="hidden" name="filter_centre_correction_id" value="{{ $filters['centre_correction_id'] ?? 0 }}">
                                                <input type="hidden" name="filter_centre_ecrit_id" value="{{ $filters['centre_ecrit_id'] ?? 0 }}">
                                                <input type="hidden" name="filter_centre_search" value="{{ $filters['centre_search'] ?? '' }}">
                                                <select name="axe_dispatching" required class="w-64 rounded-lg border border-teal-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700">
                                                    <option value="">Choisir un axe</option>
                                                    @if($dispatchRow->axe_dispatching !== '' && ! $axisOptions->contains($dispatchRow->axe_dispatching))
                                                        <option value="{{ $dispatchRow->axe_dispatching }}" selected>{{ $dispatchRow->axe_dispatching }}</option>
                                                    @endif
                                                    @foreach($axisOptions as $axis)
                                                        <option value="{{ $axis }}" {{ $dispatchRow->axe_dispatching === $axis ? 'selected' : '' }}>{{ $axis }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </td>
                                        <td class="border border-teal-100 px-3 py-2">
                                            <select form="dispatching-centre-{{ $loop->index }}" name="point_largage" class="w-56 rounded-lg border border-teal-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700">
                                                <option value="">Aucun point</option>
                                                @if($dispatchRow->point_largage !== '' && ! $dropPointOptions->contains($dispatchRow->point_largage))
                                                    <option value="{{ $dispatchRow->point_largage }}" selected>{{ $dispatchRow->point_largage }}</option>
                                                @endif
                                                @foreach($dropPointOptions as $point)
                                                    <option value="{{ $point }}" {{ $dispatchRow->point_largage === $point ? 'selected' : '' }}>{{ $point }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="border border-teal-100 px-3 py-2">
                                            <button form="dispatching-centre-{{ $loop->index }}" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-black text-white shadow-sm ring-1 ring-slate-900/10 hover:bg-amber-600 hover:ring-amber-700" type="submit">Modifier</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="border border-teal-100 px-3 py-6 text-center text-sm font-semibold text-slate-500">Aucun centre saisi pour les filtres actuels.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-3 text-xs font-medium text-teal-800">Astuce: les listes proposent les valeurs des référentiels, mais tu peux aussi taper une correction manuelle si un point manque encore.</p>
                    </div>

                    @if($hasBulkStats)
                        <div id="modification-globale" class="mb-6 scroll-mt-24 rounded-xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">Modification globale</h2>
                                    <p class="text-sm text-slate-600">
                                        Centre: <strong>{{ $bulkCentre->nom ?? '-' }}</strong>
                                        | CISCO: <strong>{{ $bulkCentre->centreCorrection->cisco->nom ?? '-' }}</strong>
                                        | DREN: <strong>{{ $bulkCentre->centreCorrection->cisco->dren->nom ?? '-' }}</strong>
                                    </p>
                                </div>
                                <div class="rounded-lg border border-indigo-200 bg-white px-3 py-2 text-right">
                                    <div class="text-[10px] font-black uppercase tracking-wide text-indigo-500">Lignes</div>
                                    <div class="text-lg font-black text-slate-900">{{ number_format($bulkStats->count(), 0, ',', ' ') }}</div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.statistics.bulk-update') }}">
                                @csrf
                                <input type="hidden" name="centre_ecrit_id" value="{{ $bulkCentreEcritId }}">
                                <input type="hidden" name="annee" value="{{ $filters['annee'] ?? '' }}">
                                <input type="hidden" name="type_examen" value="{{ $filters['type_examen'] ?? 'ALL' }}">
                                <input type="hidden" name="dren_id" value="{{ $filters['dren_id'] ?? 0 }}">
                                <input type="hidden" name="cisco_id" value="{{ $filters['cisco_id'] ?? 0 }}">
                                <input type="hidden" name="centre_correction_id" value="{{ $filters['centre_correction_id'] ?? 0 }}">
                                <input type="hidden" name="centre_search" value="{{ $filters['centre_search'] ?? '' }}">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full border-collapse text-sm">
                                        <thead>
                                        <tr class="bg-white">
                                            <th class="border border-indigo-200 px-3 py-2 text-left font-semibold">ID</th>
                                            <th class="border border-indigo-200 px-3 py-2 text-left font-semibold">Année</th>
                                            <th class="border border-indigo-200 px-3 py-2 text-left font-semibold">Langue / Option</th>
                                            <th class="border border-indigo-200 px-3 py-2 text-left font-semibold">Salle</th>
                                            <th class="border border-indigo-200 px-3 py-2 text-left font-semibold">Effectif</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($bulkStats as $row)
                                            <tr>
                                                <td class="border border-indigo-200 px-3 py-2">{{ $row->id }}</td>
                                                <td class="border border-indigo-200 px-3 py-2">{{ $row->annee }}</td>
                                                <td class="border border-indigo-200 px-3 py-2">{{ $row->langue }}</td>
                                                <td class="border border-indigo-200 px-3 py-2">{{ $row->numero_salle }}</td>
                                                <td class="border border-indigo-200 px-3 py-2">
                                                    <input type="number" min="0" max="1000" name="rows[{{ $row->id }}][effectif]" value="{{ $row->effectif }}" class="w-28 rounded-lg border border-indigo-200 bg-white px-3 py-2 text-sm">
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                    <p class="text-xs text-indigo-700">Le bouton salle par salle reste disponible plus bas. Cette zone sert à modifier plusieurs salles d'un même centre d'un seul coup.</p>
                                    <button class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-800" type="submit">Enregistrer la modification globale</button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-slate-100/80">
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">ID</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">DREN</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">CISCO</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Centre correction</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Centre écrit</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Année</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Langue / Option</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Salle</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Effectif</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Saisi par</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Actions salle</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Suppression centre</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $centresAlreadyRendered = [];
                                @endphp
                                @foreach($stats as $stat)
                                    <tr class="transition-colors duration-150 hover:bg-slate-50/80">
                                        <td class="border border-slate-200 px-3 py-2">{{ $stat->id }}</td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $stat->centreEcrit->centreCorrection->cisco->dren->nom ?? '-' }}</td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $stat->centreEcrit->centreCorrection->cisco->nom ?? '-' }}</td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $stat->centreEcrit->centreCorrection->nom ?? '-' }}</td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $stat->centreEcrit->nom ?? '-' }}</td>
                                        <td class="border border-slate-200 px-3 py-2 bg-slate-50">
                                            <form method="POST" action="{{ route('admin.statistics.update', $stat) }}">
                                                @csrf
                                                @method('PUT')
                                                <input class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-600" name="annee" value="{{ $stat->annee }}" readonly disabled>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2 bg-slate-50"><input class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-600" name="langue" value="{{ $stat->langue }}" readonly disabled></td>
                                        <td class="border border-slate-200 px-3 py-2 bg-yellow-50 text-center font-bold text-slate-900"> <!-- Highlighted Salle column -->
                                            <span class="block text-lg">{{ $stat->numero_salle }}</span> <!-- Displayed as plain text for better visibility -->
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2">
                                            <input class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 placeholder-slate-400" type="number" name="effectif" value="{{ $stat->effectif }}" min="0" required placeholder="Entrez l'effectif" style="width: 100px;"> <!-- Adjusted width for Effectif -->
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2">{{ $stat->saisi_par }}</td>
                                        <td class="border border-slate-200 px-3 py-2">
                                            <button class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-slate-700" type="submit">Modifier</button>
                                            </form>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2">
                                            @if(!in_array($stat->centre_ecrit_id, $centresAlreadyRendered, true))
                                                @php
                                                    $centresAlreadyRendered[] = $stat->centre_ecrit_id;
                                                @endphp
                                                <form method="POST" action="{{ route('admin.statistics.destroy-centre', $stat->centre_ecrit_id) }}" onsubmit="return confirm('Supprimer toutes les statistiques du centre {{ addslashes($stat->centreEcrit->nom ?? '') }} ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50" type="submit">Supprimer le centre</button>
                                                </form>
                                            @else
                                                <span class="text-xs text-slate-500">deja affiche</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $stats->links() }}</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@php
    $ciscosJs = $ciscos->map(function ($item) {
        return [
            'id' => $item->id,
            'dren_id' => $item->dren_id,
            'nom' => $item->nom,
            'dren_nom' => $item->dren->nom ?? '',
        ];
    })->values()->all();

    $centresCorrectionJs = $centresCorrection->map(function ($item) {
        return [
            'id' => $item->id,
            'cisco_id' => $item->cisco_id,
            'nom' => $item->nom,
            'type_examen' => $item->type_examen,
            'cisco_nom' => $item->cisco->nom ?? '',
            'dren_nom' => $item->cisco->dren->nom ?? '',
        ];
    })->values()->all();

    $centresEcritJs = $centresEcrit->map(function ($item) {
        return [
            'id' => $item->id,
            'centre_correction_id' => $item->centre_correction_id,
            'cisco_id' => $item->centreCorrection->cisco->id ?? null,
            'dren_id' => $item->centreCorrection->cisco->dren->id ?? null,
            'nom' => $item->nom,
            'type_examen' => $item->type_examen,
            'cisco_nom' => $item->centreCorrection->cisco->nom ?? '',
        ];
    })->values()->all();
@endphp
<script>
    (function () {
        const drenSelect = document.getElementById('dren_id');
        const ciscoSelect = document.getElementById('cisco_id');
        const centreCorrectionSelect = document.getElementById('centre_correction_id');
        const centreEcritSelect = document.getElementById('centre_ecrit_id');
        const centreSearchInput = document.getElementById('centre_search');

        const ciscos = @json($ciscosJs);
        const centresCorrection = @json($centresCorrectionJs);
        const centresEcrit = @json($centresEcritJs);

        if (!drenSelect || !ciscoSelect || !centreCorrectionSelect || !centreEcritSelect) {
            return;
        }

        function fillSelect(select, items, placeholder, selectedValue, labelBuilder) {
            select.innerHTML = '';

            const firstOption = document.createElement('option');
            firstOption.value = '';
            firstOption.textContent = placeholder;
            select.appendChild(firstOption);

            items.forEach((item) => {
                const option = document.createElement('option');
                option.value = String(item.id);
                option.textContent = labelBuilder(item);
                option.selected = String(selectedValue || '') === String(item.id);
                select.appendChild(option);
            });
        }

        function filteredCiscos() {
            if (!drenSelect.value) {
                return ciscos;
            }

            return ciscos.filter((item) => String(item.dren_id) === String(drenSelect.value));
        }

        function filteredCentresCorrection() {
            return centresCorrection.filter((item) => {
                if (drenSelect.value && !filteredCiscos().some((cisco) => String(cisco.id) === String(item.cisco_id))) {
                    return false;
                }

                if (ciscoSelect.value && String(item.cisco_id) !== String(ciscoSelect.value)) {
                    return false;
                }

                return true;
            });
        }

        function filteredCentresEcrit() {
            const needle = String(centreSearchInput?.value || '').trim().toLowerCase();

            return centresEcrit.filter((item) => {
                if (drenSelect.value && String(item.dren_id) !== String(drenSelect.value)) {
                    return false;
                }

                if (ciscoSelect.value && String(item.cisco_id) !== String(ciscoSelect.value)) {
                    return false;
                }

                if (centreCorrectionSelect.value && String(item.centre_correction_id) !== String(centreCorrectionSelect.value)) {
                    return false;
                }

                if (needle !== '' && !String(item.nom).toLowerCase().includes(needle)) {
                    return false;
                }

                return true;
            });
        }

        function refreshCiscos(selectedValue = '') {
            const items = filteredCiscos();
            fillSelect(ciscoSelect, items, 'Tous', selectedValue, (item) => `${item.dren_nom} / ${item.nom}`);

            if (!items.some((item) => String(item.id) === String(ciscoSelect.value))) {
                ciscoSelect.value = '';
            }
        }

        function refreshCentresCorrection(selectedValue = '') {
            const items = filteredCentresCorrection();
            fillSelect(centreCorrectionSelect, items, 'Tous', selectedValue, (item) => `${item.dren_nom} / ${item.cisco_nom} / ${item.nom} (${item.type_examen})`);

            if (!items.some((item) => String(item.id) === String(centreCorrectionSelect.value))) {
                centreCorrectionSelect.value = '';
            }
        }

        function refreshCentresEcrit(selectedValue = '') {
            const items = filteredCentresEcrit();
            fillSelect(centreEcritSelect, items, 'Tous', selectedValue, (item) => `${item.cisco_nom} / ${item.nom} (${item.type_examen})`);

            if (!items.some((item) => String(item.id) === String(centreEcritSelect.value))) {
                centreEcritSelect.value = '';
            }
        }

        drenSelect.addEventListener('change', function () {
            refreshCiscos('');
            refreshCentresCorrection('');
            refreshCentresEcrit('');
        });

        ciscoSelect.addEventListener('change', function () {
            refreshCentresCorrection('');
            refreshCentresEcrit('');
        });

        centreCorrectionSelect.addEventListener('change', function () {
            refreshCentresEcrit('');
        });

        if (centreSearchInput) {
            centreSearchInput.addEventListener('input', function () {
                refreshCentresEcrit(centreEcritSelect.value);
            });
        }

        refreshCiscos(@json((string) ($filters['cisco_id'] ?? '')));
        refreshCentresCorrection(@json((string) ($filters['centre_correction_id'] ?? '')));
        refreshCentresEcrit(@json((string) ($filters['centre_ecrit_id'] ?? '')));
    })();
</script>
</body>
</html>
