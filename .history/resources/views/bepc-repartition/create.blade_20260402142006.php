<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service de l'Organisation des Examens - Saisie</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')

    <style>
        body { font-family: var(--app-font-sans); }
        .glass-panel { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(226, 232, 240, 0.6); }
        .input-focus-ring:focus { border-color: #6366f1; ring: 4px; ring-color: rgba(99, 102, 241, 0.1); outline: none; }
        .sticky-col { position: sticky; left: 0; z-index: 10; background-color: white; border-right: 2px solid #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    </style>
</head>

<body class="h-full antialiased text-slate-900 bg-[radial-gradient(at_top_right,_var(--tw-gradient-stops))] from-indigo-50 via-slate-50 to-slate-100">

<div class="mx-auto max-w-[1700px] p-4 md:p-6 lg:p-8">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1 space-y-6">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50">
                <div class="border-b border-slate-100 bg-white px-6 py-8 md:px-10">
                    <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                        <div class="space-y-2">
                            <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-indigo-600">
                                <span class="relative flex h-2 w-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-indigo-500"></span>
                                </span>
                                Module de Saisie
                            </div>
                            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">
                                Répartition <span class="text-indigo-600">{{ old('type_examen', $typeExamen) }}</span>
                            </h1>
                            <p class="text-slate-500 font-medium italic">Configuration hiérarchique et saisie par salle d'examen.</p>
                        </div>
                        
                        <div class="flex flex-wrap gap-3">
                            <a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-300" href="{{ route('repartition.dashboard') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                Dashboard
                            </a>
                            <a class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-slate-200 transition-all hover:bg-slate-800" href="{{ route('repartition.livre.preview') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                Aperçu Livre
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-10">
                    @php
                        $stickyTypeExamen = old('type_examen', $stickyContext['type_examen'] ?? $typeExamen);
                        $stickyDrenId = old('dren_id', $stickyContext['dren_id'] ?? '');
                        $stickyCiscoId = old('cisco_id', $stickyContext['cisco_id'] ?? '');
                        $stickyCentreCorrectionId = old('centre_correction_id', $stickyContext['centre_correction_id'] ?? '');
                        $stickyAnnee = old('annee', $stickyContext['annee'] ?? '');
                        $stickyAxeDispatching = old('axe_dispatching', $stickyContext['axe_dispatching'] ?? '');
                        $stickyPointLargage = old('point_largage', $stickyContext['point_largage'] ?? '');
                        $stickyPointLargageOther = old('point_largage_other', $stickyContext['point_largage_other'] ?? '');
                    @endphp
                    @if(session('status'))
                        <div class="mb-8 flex items-center gap-3 rounded-2xl bg-emerald-50 p-4 text-emerald-800 border border-emerald-100">
                            <i class="fas fa-check-circle text-lg"></i>
                            <span class="font-bold">{{ session('status') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-8 rounded-2xl bg-rose-50 p-5 text-rose-800 border border-rose-100">
                            <div class="flex items-center gap-3 mb-2 font-bold uppercase tracking-widest text-xs">
                                <i class="fas fa-exclamation-triangle"></i> Erreurs détectées
                            </div>
                            <ul class="list-inside list-disc text-sm font-medium space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-10 rounded-2xl border border-slate-200 bg-slate-50/50 p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xs font-black uppercase tracking-widest text-slate-400">Paramètres du canevas</h2>
                        </div>
                        <form method="GET" action="{{ route('bepc.repartition.create') }}" class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <input type="hidden" name="generated" value="1">
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-700 ml-1">Examen cible</label>
                                <select name="type_examen" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition-all focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                                    <option value="BEPC" {{ $stickyTypeExamen === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                    <option value="CEPE" {{ $stickyTypeExamen === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-700 ml-1">Capacité (Salles)</label>
                                <input type="number" name="nombre_salles" min="1" max="50" value="{{ old('nombre_salles', $nombreSalles) }}" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm transition-all focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                            </div>
                            <div class="flex items-end">
                                <button class="w-full rounded-xl bg-white border border-slate-200 py-3 text-sm font-black uppercase tracking-widest text-slate-600 shadow-sm transition-all hover:bg-slate-100 hover:border-slate-300 active:scale-95" type="submit">
                                    Générer le canevas
                                </button>
                            </div>
                        </form>
                    </div>

                    <form method="POST" action="{{ route('bepc.repartition.store') }}">
                        @csrf
                        <input type="hidden" name="nombre_salles" value="{{ $nombreSalles }}">
                        <input type="hidden" name="type_examen" value="{{ $stickyTypeExamen }}">

                        <div class="mb-10 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="mb-6 text-sm font-black uppercase tracking-widest text-slate-400 pb-4 border-b border-slate-100">
                                <i class="fas fa-sitemap mr-2"></i> Localisation & Contexte
                            </h2>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700">DREN</label>
                                    <select id="dren_id" name="dren_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold focus:bg-white transition-all outline-none focus:ring-4 focus:ring-indigo-500/10">
                                        <option value="">Sélectionner</option>
                                        @foreach($drens as $dren)
                                            <option value="{{ $dren->id }}" {{ (string) $stickyDrenId === (string) $dren->id ? 'selected' : '' }}>{{ $dren->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700">CISCO</label>
                                    <select id="cisco_id" name="cisco_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold focus:bg-white transition-all outline-none">
                                        <option value="">Sélectionner</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700">Centre de Correction</label>
                                    <select id="centre_correction_id" name="centre_correction_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold focus:bg-white transition-all outline-none">
                                        <option value="">Sélectionner</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700">Centre d'écrit</label>
                                    <select id="centre_ecrit_id" name="centre_ecrit_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold focus:bg-white transition-all outline-none">
                                        <option value="">Sélectionner</option>
                                    </select>
                                </div>
                                
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <label class="text-sm font-bold text-slate-700">Axe de Dispatching</label>
                                        @if(auth()->user()?->isAdmin())
                                            <a href="{{ route('admin.references.index') }}#zone-dispatching-referentiels" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50">Ajouter / modifier</a>
                                        @endif
                                    </div>
                                    <select id="axe_dispatching" name="axe_dispatching" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:ring-4 focus:ring-indigo-500/10">
                                        <option value="">Sélectionner</option>
                                        @foreach($dispatchingAxes as $axe)
                                            <option value="{{ $axe }}" {{ $stickyAxeDispatching === $axe ? 'selected' : '' }}>{{ $axe }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <label class="text-sm font-bold text-slate-700">Point de largage</label>
                                        @if(auth()->user()?->isAdmin())
                                            <a href="{{ route('admin.references.index') }}#zone-dispatching-referentiels" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50">Ajouter / modifier</a>
                                        @endif
                                    </div>
                                    <select id="point_largage" name="point_largage" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:ring-4 focus:ring-indigo-500/10">
                                        <option value="">Sélectionner</option>
                                    </select>
                                    <input id="point_largage_other" type="text" name="point_largage_other" value="{{ $stickyPointLargageOther }}" placeholder="Préciser le point de largage" class="hidden w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:ring-4 focus:ring-indigo-500/10">
                                    <p id="point_largage_help" class="text-xs font-medium text-slate-500">
                                        CEPE: le point de largage reprend automatiquement la CISCO. BEPC: vous pouvez choisir n'importe quelle CISCO, un point défini par l'admin, ou saisir un autre point.
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700">Année scolaire</label>
                                    <input id="annee" type="text" name="annee" placeholder="2025-2026" value="{{ $stickyAnnee }}" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700 italic text-slate-400">Saisisseur : {{ auth()->user()->name }}</label>
                                    <input id="salles_inutilisables" type="text" name="salles_inutilisables" placeholder="Salles à ignorer (ex: 4, 9)" value="{{ old('salles_inutilisables') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700">Candidats à besoins spécifiques</label>
                                    <textarea name="candidats_specifiques" placeholder="Salle, Type Handicap" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none"></textarea>
                                </div>
                            </div>
                        </div>

                        @if($stickyTypeExamen === 'BEPC')
                            <div class="mb-10 overflow-hidden rounded-2xl border border-amber-200 bg-amber-50/30 p-6">
                                <div class="mb-6 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-500 text-white">
                                            <i class="fas fa-globe-africa text-sm"></i>
                                        </div>
                                        <h3 class="text-sm font-black uppercase tracking-widest text-amber-800">Candidats Étrangers</h3>
                                    </div>
                                    <label class="relative inline-flex cursor-pointer items-center">
                                        <input id="has_foreign_candidates" type="checkbox" name="has_foreign_candidates" value="1" {{ old('has_foreign_candidates') ? 'checked' : '' }} class="peer sr-only">
                                        <div class="h-6 w-11 rounded-full bg-slate-200 peer-checked:bg-amber-500 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-full"></div>
                                        <span class="ml-3 text-xs font-bold text-amber-900">Activer l'exception</span>
                                    </label>
                                </div>

                                <div id="foreign-config" class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-amber-800 uppercase">LV Etrangère</label>
                                        <select name="foreign_option_a_lv" class="w-full rounded-xl border border-amber-200 bg-white px-4 py-3 text-sm font-bold shadow-sm outline-none">
                                            <option value="Allemand" {{ old('foreign_option_a_lv', 'Allemand') === 'Allemand' ? 'selected' : '' }}>Allemand</option>
                                            <option value="Esp" {{ old('foreign_option_a_lv') === 'Esp' ? 'selected' : '' }}>Esp (Espagnol)</option>
                                            <option value="Anglais" {{ old('foreign_option_a_lv') === 'Anglais' ? 'selected' : '' }}>Anglais</option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-amber-800 uppercase">Rempl. Malagasy A</label>
                                        <input type="text" name="foreign_option_a_replace_malagasy" value="{{ old('foreign_option_a_replace_malagasy') }}" placeholder="Ex: Français" class="w-full rounded-xl border border-amber-200 bg-white px-4 py-3 text-sm font-bold outline-none">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-amber-800 uppercase">Rempl. Malagasy B</label>
                                        <input type="text" name="foreign_option_b_replace_malagasy" value="{{ old('foreign_option_b_replace_malagasy') }}" placeholder="Ex: Français" class="w-full rounded-xl border border-amber-200 bg-white px-4 py-3 text-sm font-bold outline-none">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-10 rounded-3xl border border-slate-200 bg-white shadow-xl overflow-hidden">
                            <div class="bg-indigo-600 px-8 py-5 flex items-center justify-between">
                                <h3 class="font-black uppercase tracking-widest text-white text-xs">Canevas des Effectifs</h3>
                                <span class="text-[10px] font-bold text-indigo-100 uppercase">Utilisez TAB pour naviguer rapidement</span>
                            </div>
                            <div class="overflow-x-auto custom-scrollbar">
                                <table class="w-full border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50">
                                            <th class="sticky-col px-6 py-5 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Catégorie / Langue</th>
                                            @for($salle = 1; $salle <= $nombreSalles; $salle++)
                                                <th class="px-4 py-5 text-center text-[10px] font-black uppercase tracking-widest text-slate-400 border-l border-slate-100 min-w-[100px]">Salle {{ $salle }}</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @if($stickyTypeExamen === 'BEPC')
                                            @foreach($langues as $index => $langue)
                                                <tr class="hover:bg-indigo-50/30 transition-colors">
                                                    <td class="sticky-col px-6 py-4 font-black text-slate-700 text-sm whitespace-nowrap">{{ $langue }}</td>
                                                    @for($salle = 1; $salle <= $nombreSalles; $salle++)
                                                        <td class="px-3 py-3 border-l border-slate-50">
                                                            <input type="number" min="0" name="effectifs[{{ $langue }}][{{ $salle }}]" value="{{ old("effectifs.$langue.$salle", 0) }}" required data-salle="{{ $salle }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-center text-sm font-bold shadow-sm input-focus-ring input-effectif">
                                                        </td>
                                                    @endfor
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="hover:bg-indigo-50/30 transition-colors">
                                                <td class="sticky-col px-6 py-8 font-black text-indigo-600 text-sm whitespace-nowrap uppercase tracking-wider">Total CEPE</td>
                                                @for($salle = 1; $salle <= $nombreSalles; $salle++)
                                                    <td class="px-3 py-3 border-l border-slate-50">
                                                        <input type="number" min="0" name="effectifs_total[{{ $salle }}]" value="{{ old("effectifs_total.$salle", 0) }}" required data-salle="{{ $salle }}" class="w-full rounded-xl border border-indigo-100 bg-white px-3 py-4 text-center text-lg font-black text-indigo-600 shadow-sm input-focus-ring input-effectif">
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if($stickyTypeExamen === 'BEPC')
                            <div id="foreign-effectifs" class="mb-10 overflow-hidden rounded-3xl border border-amber-200 bg-white shadow-lg opacity-50 transition-all duration-300">
                                <div class="bg-amber-500 px-8 py-4 text-white text-xs font-black uppercase tracking-widest">Effectifs Étrangers par Salle</div>
                                <div class="overflow-x-auto custom-scrollbar">
                                    <table class="w-full border-collapse">
                                        <tbody class="divide-y divide-amber-50">
                                            <tr class="hover:bg-amber-50">
                                                <td class="sticky-col px-6 py-4 font-bold text-amber-700 text-xs uppercase bg-amber-50 border-r-amber-100">Option A</td>
                                                @for($salle = 1; $salle <= $nombreSalles; $salle++)
                                                    <td class="px-3 py-3 border-l border-amber-50">
                                                        <input type="number" min="0" name="foreign_effectifs[option_a][{{ $salle }}]" value="{{ old("foreign_effectifs.option_a.$salle", 0) }}" data-salle="{{ $salle }}" class="w-full rounded-lg border border-amber-200 bg-white px-3 py-2 text-center text-sm font-bold outline-none focus:ring-4 focus:ring-amber-500/20 input-effectif">
                                                    </td>
                                                @endfor
                                            </tr>
                                            <tr class="hover:bg-amber-50">
                                                <td class="sticky-col px-6 py-4 font-bold text-amber-700 text-xs uppercase bg-amber-50 border-r-amber-100">Option B</td>
                                                @for($salle = 1; $salle <= $nombreSalles; $salle++)
                                                    <td class="px-3 py-3 border-l border-amber-50">
                                                        <input type="number" min="0" name="foreign_effectifs[option_b][{{ $salle }}]" value="{{ old("foreign_effectifs.option_b.$salle", 0) }}" data-salle="{{ $salle }}" class="w-full rounded-lg border border-amber-200 bg-white px-3 py-2 text-center text-sm font-bold outline-none focus:ring-4 focus:ring-amber-500/20 input-effectif">
                                                    </td>
                                                @endfor
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="flex justify-end pt-6 border-t border-slate-200">
                            <button id="save-button" class="group relative inline-flex items-center gap-3 overflow-hidden rounded-2xl bg-slate-300 px-10 py-5 text-sm font-black uppercase tracking-widest text-slate-500 shadow-xl transition-all disabled:cursor-not-allowed" type="submit" disabled>
                                <span class="relative z-10 flex items-center gap-2">
                                    <i class="fas fa-save group-hover:animate-bounce"></i>
                                    Enregistrer la saisie
                                </span>
                                <div class="absolute inset-0 z-0 bg-gradient-to-r from-indigo-600 to-indigo-700 opacity-0 transition-opacity group-enabled:opacity-100"></div>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<div id="selectionToast" class="fixed bottom-6 right-6 z-50 hidden rounded-2xl border border-indigo-200 bg-white/90 p-4 text-xs font-bold text-slate-700 shadow-2xl backdrop-blur-md"></div>

<div id="filterAlert" class="fixed right-4 top-24 z-50 w-80 translate-x-full rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-2xl transition-transform duration-500">
    <div class="flex gap-4">
        <i class="fas fa-map-marked-alt text-2xl text-amber-500"></i>
        <div>
            <h4 class="text-sm font-black text-amber-900 uppercase">Localisation Requise</h4>
            <p class="text-xs font-bold text-amber-700 mt-1">Veuillez compléter la hiérarchie DREN > CISCO > Correction > Écrit.</p>
        </div>
    </div>
</div>

<style>
    /* Triggered state */
    .show-alert { transform: translateX(0) !important; }
    
    /* Modern details styling */
    details summary::-webkit-details-marker { display:none; }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-slideDown { animation: slideDown 0.4s ease-out forwards; }
</style>

<script>
    (function () {
        const ciscos = @json($ciscos);
        const centresCorrection = @json($centresCorrection);
        const centresEcrit = @json($centresEcrit);
        const dispatchingDropPoints = @json($dispatchingDropPoints);
        const typeExamen = @json($stickyTypeExamen);

        const drenSelect = document.getElementById('dren_id');
        const ciscoSelect = document.getElementById('cisco_id');
        const ccSelect = document.getElementById('centre_correction_id');
        const ceSelect = document.getElementById('centre_ecrit_id');
        const axeInput = document.getElementById('axe_dispatching');
        const pointSelect = document.getElementById('point_largage');
        const pointOtherInput = document.getElementById('point_largage_other');
        const saveButton = document.getElementById('save-button');
        const filterAlert = document.getElementById('filterAlert');
        const foreignModeCheckbox = document.getElementById('has_foreign_candidates');
        const foreignConfig = document.getElementById('foreign-config');
        const foreignEffectifsBlock = document.getElementById('foreign-effectifs');

        const oldCisco = @json((string) $stickyCiscoId);
        const oldCc = @json((string) $stickyCentreCorrectionId);
        const oldCe = "{{ old('centre_ecrit_id') }}";
        const oldPoint = @json($stickyPointLargage);
        const oldPointOther = @json($stickyPointLargageOther);
        const allCiscoNames = [...new Set(ciscos
            .map((item) => String(item.nom || '').trim())
            .filter((value) => value !== ''))].sort((a, b) => a.localeCompare(b, 'fr', { sensitivity: 'base' }));

        if (!drenSelect || !ciscoSelect || !ccSelect || !ceSelect) {
            return;
        }

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
            const ciscoId = ciscoSelect.value;
            const ccId = ccSelect.value;
            const filtered = centresEcrit.filter((item) =>
                String(item.cisco_id) === String(ciscoId) &&
                (String(ccId) === '' || String(item.centre_correction_id) === String(ccId))
            );
            fillSelect(ceSelect, filtered, 'Sélectionner', selectedCe);
            if (!filtered.some((item) => String(item.id) === String(ceSelect.value))) {
                ceSelect.value = '';
            }
        }

        function toggleOtherPointInput() {
            if (!pointSelect || !pointOtherInput) return;
            const showOther = typeExamen === 'BEPC' && pointSelect.value === '__other__';
            pointOtherInput.classList.toggle('hidden', !showOther);
            pointOtherInput.required = showOther;
            pointOtherInput.disabled = !showOther;
            if (!showOther && oldPointOther === '') {
                pointOtherInput.value = '';
            }
        }

        function refreshPointOptions(selectedValue = '') {
            if (!pointSelect) return;

            const ciscoLabel = ciscoSelect.options[ciscoSelect.selectedIndex]?.text || '';
            pointSelect.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Sélectionner';
            pointSelect.appendChild(placeholder);

            if (typeExamen === 'CEPE') {
                const option = document.createElement('option');
                option.value = ciscoLabel;
                option.textContent = ciscoLabel || 'CISCO sélectionnée';
                option.selected = true;
                pointSelect.appendChild(option);
                pointSelect.value = ciscoLabel;
                pointSelect.disabled = true;
                toggleOtherPointInput();
                return;
            }

            pointSelect.disabled = false;

            const bepcPointOptions = [];

            allCiscoNames.forEach((value) => {
                bepcPointOptions.push({
                    value,
                    label: value === ciscoLabel ? `CISCO actuelle: ${value}` : `CISCO: ${value}`,
                });
            });

            dispatchingDropPoints.forEach((value) => {
                const trimmedValue = String(value || '').trim();
                if (trimmedValue === '' || bepcPointOptions.some((option) => option.value === trimmedValue)) {
                    return;
                }

                bepcPointOptions.push({
                    value: trimmedValue,
                    label: `Point configuré: ${trimmedValue}`,
                });
            });

            bepcPointOptions.forEach(({ value, label }) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = label;
                pointSelect.appendChild(option);
            });

            const otherOption = document.createElement('option');
            otherOption.value = '__other__';
            otherOption.textContent = 'Autre...';
            pointSelect.appendChild(otherOption);

            if (selectedValue) {
                const exists = Array.from(pointSelect.options).some((option) => option.value === selectedValue);
                pointSelect.value = exists ? selectedValue : '__other__';
                if (!exists && pointOtherInput) {
                    pointOtherInput.value = selectedValue;
                }
            }

            toggleOtherPointInput();
        }

        function updateSaveState() {
            const complete = drenSelect.value && ciscoSelect.value && ccSelect.value && ceSelect.value;
            if (saveButton) {
                saveButton.disabled = !complete;
                if (complete) {
                    saveButton.classList.remove('bg-slate-300', 'text-slate-500');
                    saveButton.classList.add('bg-slate-900', 'text-white', 'hover:bg-slate-800');
                } else {
                    saveButton.classList.add('bg-slate-300', 'text-slate-500');
                    saveButton.classList.remove('bg-slate-900', 'text-white', 'hover:bg-slate-800');
                }
            }
            if (filterAlert) {
                filterAlert.classList.toggle('show-alert', !complete);
            }
        }

        function updateForeignState() {
            if (!foreignModeCheckbox) return;
            const enabled = foreignModeCheckbox.checked;
            if (foreignConfig) {
                foreignConfig.querySelectorAll('input,select').forEach((el) => el.disabled = !enabled);
                foreignConfig.style.opacity = enabled ? '1' : '0.45';
            }
            if (foreignEffectifsBlock) {
                foreignEffectifsBlock.querySelectorAll('input').forEach((el) => el.disabled = !enabled);
                foreignEffectifsBlock.style.opacity = enabled ? '1' : '0.45';
            }
        }

        drenSelect.addEventListener('change', () => { refreshCisco(''); updateSaveState(); });
        ciscoSelect.addEventListener('change', () => { refreshCentreCorrection(''); refreshPointOptions(''); updateSaveState(); });
        ccSelect.addEventListener('change', () => { refreshCentreEcrit(''); updateSaveState(); });
        ceSelect.addEventListener('change', updateSaveState);
        if (axeInput) {
            axeInput.addEventListener('change', () => refreshPointOptions(pointSelect ? pointSelect.value : ''));
        }
        if (pointSelect) {
            pointSelect.addEventListener('change', toggleOtherPointInput);
        }
        if (foreignModeCheckbox) {
            foreignModeCheckbox.addEventListener('change', updateForeignState);
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
        refreshPointOptions(oldPoint);
        updateForeignState();
        updateSaveState();
    })();
</script>
</body>
</html>
