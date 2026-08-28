<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Référentiels</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')
    <style>
        :root {
            --brand-primary: #0f766e;
            --brand-accent: #14b8a6;
            --brand-dark: #0f172a;
        }

        body { 
            font-family: var(--app-font-sans, system-ui, -apple-system, sans-serif); 
        }

        /* Effets de verre (Glassmorphism) */
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 20px 50px -10px rgba(15, 23, 42, 0.08), 0 10px 20px -5px rgba(15, 23, 42, 0.04);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.08);
            border-color: rgba(20, 184, 166, 0.3);
        }

        /* Top accent line animation */
        .accent-top-bar {
            position: relative;
            overflow: hidden;
        }
        .accent-top-bar::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0f766e, #14b8a6, #6366f1);
        }

        /* Custom Inputs */
        .ref-input, .ref-select {
            width: 100%;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: rgba(255, 255, 255, 0.9);
            padding: 0.65rem 0.9rem;
            font-size: 0.875rem;
            color: #0f172a;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .ref-input:focus, .ref-select:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.12);
            background: #ffffff;
        }

        /* Buttons styling */
        .ref-btn-primary {
            border-radius: 12px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            font-weight: 600;
            letter-spacing: 0.01em;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }
        .ref-btn-primary:hover { 
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.25);
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }
        .ref-btn-primary:active { transform: translateY(0); }

        .ref-btn-secondary {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #334155;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .ref-btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .ref-btn-danger {
            border-radius: 12px;
            border: 1px solid #fecdd3;
            background: #fff1f2;
            color: #e11d48;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .ref-btn-danger:hover {
            background: #ffe4e6;
            border-color: #fda4af;
            color: #be123c;
        }

        /* Modern Details Accordion */
        .ref-collapsible {
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(10px);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .ref-collapsible[open] {
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.05);
            background: rgba(255, 255, 255, 0.95);
        }
        .ref-collapsible summary {
            list-style: none;
            cursor: pointer;
            user-select: none;
        }
        .ref-collapsible summary::-webkit-details-marker { display: none; }
        .ref-collapsible-toggle {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .ref-collapsible[open] .ref-collapsible-toggle {
            transform: rotate(180deg);
        }

        /* Modern Table */
        .ref-table-wrap {
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            overflow: hidden;
        }
        .ref-table th {
            font-size: 0.70rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
        }
        .ref-table td {
            border-bottom: 1px solid #f1f5f9;
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }
        .ref-table tbody tr:last-child td { border-bottom: none; }
        .ref-table tbody tr { transition: background-color 0.15s ease; }
        .ref-table tbody tr:hover { background-color: #f8fafc; }
    </style>
</head>
<body class="min-h-full bg-gradient-to-br from-slate-900 via-slate-800 to-teal-950 text-slate-800 antialiased selection:bg-teal-500 selection:text-white">

<div class="mx-auto max-w-[1700px] p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col gap-6 md:flex-row md:items-start">
        
        @include('partials.sidebar')

        <main class="min-w-0 flex-1">
            <div class="glass-panel overflow-hidden rounded-[28px]">
                
                <!-- Header section avec gradient subtil -->
                <div class="relative border-b border-slate-200/70 bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 px-6 py-8 md:px-10 md:py-10 text-white">
                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-teal-500/10 via-transparent to-transparent pointer-events-none"></div>
                    
                    <div class="relative flex flex-wrap items-center justify-between gap-6">
                        <div class="space-y-1.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-500/10 px-3 py-1 text-xs font-semibold tracking-wide text-teal-300 backdrop-blur-md border border-teal-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                                Administration Centrale
                            </span>
                            <h1 class="text-3xl font-black tracking-tight text-white md:text-5xl">Référentiels</h1>
                            <p class="max-w-2xl text-sm font-normal text-slate-300/90 leading-relaxed">
                                Ajout, organisation et maintenance des DREN, CISCO, centres de correction, centres d'écrit et paramètres de dispatching.
                            </p>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <a class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-md border border-white/10 transition-all hover:bg-white/20 hover:border-white/20 shadow-lg" href="{{ route('admin.statistics.index') }}">
                                <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                Statistiques
                            </a>
                            <a class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-md border border-white/10 transition-all hover:bg-white/20 hover:border-white/20 shadow-lg" href="{{ route('admin.users.index') }}">
                                <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                Utilisateurs
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8 space-y-8">
                    
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-4 xl:grid-cols-5">
                        <div class="glass-card rounded-2xl p-4 flex flex-col justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">DREN</span>
                            <div class="mt-2 flex items-baseline justify-between">
                                <span class="text-3xl font-black text-slate-900">{{ number_format($drens->count(), 0, ',', ' ') }}</span>
                                <span class="rounded-lg bg-teal-50 p-2 text-teal-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="glass-card rounded-2xl p-4 flex flex-col justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">CISCO</span>
                            <div class="mt-2 flex items-baseline justify-between">
                                <span class="text-3xl font-black text-slate-900">{{ number_format($formCiscos->count(), 0, ',', ' ') }}</span>
                                <span class="rounded-lg bg-sky-50 p-2 text-sky-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="glass-card rounded-2xl p-4 flex flex-col justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Correction</span>
                            <div class="mt-2 flex items-baseline justify-between">
                                <span class="text-3xl font-black text-slate-900">{{ number_format($formCentresCorrection->count(), 0, ',', ' ') }}</span>
                                <span class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="glass-card rounded-2xl p-4 flex flex-col justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Centres Écrit</span>
                            <div class="mt-2 flex items-baseline justify-between">
                                <span class="text-3xl font-black text-slate-900">{{ number_format($centresEcritPage->total(), 0, ',', ' ') }}</span>
                                <span class="rounded-lg bg-amber-50 p-2 text-amber-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="glass-card rounded-2xl p-4 flex flex-col justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Dispatching</span>
                            <div class="mt-2 flex items-baseline justify-between">
                                <span class="text-3xl font-black text-slate-900">{{ number_format(count($dispatchingAxes) + count($dispatchingDropPoints), 0, ',', ' ') }}</span>
                                <span class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Flash Notifications -->
                    @if(session('status'))
                        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50/90 px-4 py-3.5 text-sm font-semibold text-emerald-800 shadow-sm backdrop-blur-md">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50/90 p-4 text-sm font-semibold text-rose-800 shadow-sm backdrop-blur-md">
                            <div class="flex items-center gap-2 mb-1 text-rose-900 font-bold">
                                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Veuillez corriger les erreurs suivantes :
                            </div>
                            <ul class="list-disc pl-9 space-y-1 text-rose-700">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Section Création -->
                    <div class="space-y-4">
                        
                        <details class="ref-collapsible" open>
                            <summary class="flex items-center justify-between gap-4 px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 rounded-xl bg-slate-900 text-teal-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider text-teal-600">Création</span>
                                        <h2 class="text-lg font-black text-slate-900">Ajouter DREN et CISCO</h2>
                                    </div>
                                </div>
                                <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <div class="px-6 pb-6 pt-2 border-t border-slate-100">
                                <div class="grid gap-6 xl:grid-cols-2">
                                    
                                    <div class="glass-card accent-top-bar rounded-2xl p-6">
                                        <div class="mb-4">
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Structure Régionale</span>
                                            <h3 class="text-base font-black text-slate-900">Ajouter DREN</h3>
                                        </div>
                                        <form method="POST" action="{{ route('admin.references.drens.store') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                                            @csrf
                                            <input class="ref-input sm:col-span-3" name="nom" placeholder="Nom de la DREN" required>
                                            <button class="ref-btn-primary px-4 py-2.5 text-sm" type="submit">Ajouter</button>
                                        </form>
                                    </div>

                                    <div class="glass-card accent-top-bar rounded-2xl p-6">
                                        <div class="mb-4">
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Circonscription</span>
                                            <h3 class="text-base font-black text-slate-900">Ajouter CISCO</h3>
                                        </div>
                                        <form method="POST" action="{{ route('admin.references.ciscos.store') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                                            @csrf
                                            <select class="ref-select sm:col-span-1" name="dren_id" required>
                                                <option value="">DREN</option>
                                                @foreach($drens as $dren)
                                                    <option value="{{ $dren->id }}">{{ $dren->nom }}</option>
                                                @endforeach
                                            </select>
                                            <input class="ref-input sm:col-span-2" name="nom" placeholder="Nom de la CISCO" required>
                                            <button class="ref-btn-primary px-4 py-2.5 text-sm" type="submit">Ajouter</button>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </details>

                        <details class="ref-collapsible">
                            <summary class="flex items-center justify-between gap-4 px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 rounded-xl bg-slate-900 text-teal-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider text-teal-600">Création</span>
                                        <h2 class="text-lg font-black text-slate-900">Ajouter Centres</h2>
                                    </div>
                                </div>
                                <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <div class="px-6 pb-6 pt-2 border-t border-slate-100">
                                <div class="grid gap-6 xl:grid-cols-2">
                                    
                                    <div class="glass-card accent-top-bar rounded-2xl p-6">
                                        <div class="mb-4">
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Examen & Correction</span>
                                            <h3 class="text-base font-black text-slate-900">Ajouter Centre de Correction</h3>
                                        </div>
                                        <form method="POST" action="{{ route('admin.references.centres-correction.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                                            @csrf
                                            <select class="ref-select md:col-span-2" name="cisco_id" required>
                                                <option value="">CISCO Rattachée</option>
                                                @foreach($formCiscos as $cisco)
                                                    <option value="{{ $cisco->id }}">{{ $cisco->dren->nom ?? '-' }} / {{ $cisco->nom }}</option>
                                                @endforeach
                                            </select>
                                            <input class="ref-input" name="nom" placeholder="Nom du centre" required>
                                            <div class="inline-flex items-center justify-center rounded-xl border border-teal-200 bg-teal-50 px-3 py-2 text-xs font-bold text-teal-800 tracking-wider uppercase">{{ $centreTypeForForms }}</div>
                                            <input type="hidden" name="type_examen" value="{{ $centreTypeForForms }}">
                                            <div class="md:col-span-4 mt-2">
                                                <button class="ref-btn-primary w-full px-4 py-2.5 text-sm flex justify-center items-center gap-2" type="submit">
                                                    <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                    Ajouter Centre correction
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="glass-card accent-top-bar rounded-2xl p-6">
                                        <div class="mb-4">
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Épreuves Écrites</span>
                                            <h3 class="text-base font-black text-slate-900">Ajouter Centre d'Écrit</h3>
                                        </div>
                                        <form method="POST" action="{{ route('admin.references.centres-ecrit.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                                            @csrf
                                            <select class="ref-select md:col-span-2" name="centre_correction_id" required>
                                                <option value="">Centre correction rattaché</option>
                                                @foreach($formCentresCorrection as $cc)
                                                    <option value="{{ $cc->id }}">{{ $cc->cisco->dren->nom ?? '-' }} / {{ $cc->cisco->nom ?? '-' }} / {{ $cc->nom }} ({{ $cc->type_examen }})</option>
                                                @endforeach
                                            </select>
                                            <input class="ref-input" name="nom" placeholder="Nom centre écrit" required>
                                            <div class="inline-flex items-center justify-center rounded-xl border border-teal-200 bg-teal-50 px-3 py-2 text-xs font-bold text-teal-800 tracking-wider uppercase">{{ $centreTypeForForms }}</div>
                                            <input type="hidden" name="type_examen" value="{{ $centreTypeForForms }}">
                                            <div class="md:col-span-4 mt-2">
                                                <button class="ref-btn-primary w-full px-4 py-2.5 text-sm flex justify-center items-center gap-2" type="submit">
                                                    <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                    Ajouter Centre écrit
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </details>
                    </div>

                    <!-- Dispatching Section -->
                    <details id="zone-dispatching-referentiels" class="ref-collapsible scroll-mt-24">
                        <summary class="flex items-center justify-between gap-4 px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 rounded-xl bg-slate-900 text-teal-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-teal-600">Paramètres Métier</span>
                                    <h2 class="text-lg font-black text-slate-900">Axes de Dispatching et Points de Largage</h2>
                                </div>
                            </div>
                            <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="px-6 pb-6 pt-4 border-t border-slate-100 space-y-6">
                            <p class="text-xs font-medium text-slate-500">
                                Gestion rapide des listes utilisées dans la saisie. Les axes par défaut ont été préchargés et restent modifiables à tout moment.
                            </p>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-5">
                                    <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-600">Ajouter un axe</h3>
                                    <form method="POST" action="{{ route('admin.references.dispatching-axes.store') }}" class="flex flex-col gap-3 sm:flex-row">
                                        @csrf
                                        <input class="ref-input" name="nom" placeholder="Nom de l'axe" required>
                                        <button class="ref-btn-primary shrink-0 px-4 py-2.5 text-sm" type="submit">Ajouter axe</button>
                                    </form>
                                </div>
                                
                                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-5">
                                    <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-600">Ajouter un point de largage</h3>
                                    <form method="POST" action="{{ route('admin.references.dispatching-drop-points.store') }}" class="flex flex-col gap-3 sm:flex-row">
                                        @csrf
                                        <input class="ref-input" name="nom" placeholder="Nom du point de largage" required>
                                        <button class="ref-btn-primary shrink-0 px-4 py-2.5 text-sm" type="submit">Ajouter point</button>
                                    </form>
                                </div>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-600">Axes configurés</h3>
                                    <div class="ref-table-wrap">
                                        <table class="ref-table w-full text-sm">
                                            <thead>
                                                <tr>
                                                    <th class="text-left">Axe</th>
                                                    <th class="text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($dispatchingAxes as $index => $axis)
                                                <tr>
                                                    <td>
                                                        <form method="POST" action="{{ route('admin.references.dispatching-axes.update', $index) }}" id="axis-form-{{ $index }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <input class="ref-input" name="nom" value="{{ $axis }}" required>
                                                        </form>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="inline-flex items-center gap-2">
                                                            <button form="axis-form-{{ $index }}" class="ref-btn-primary px-3 py-1.5 text-xs" type="submit">Modifier</button>
                                                            <form method="POST" action="{{ route('admin.references.dispatching-axes.destroy', $index) }}" onsubmit="return confirm('Supprimer l\\'axe {{ addslashes($axis) }} ?');" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="ref-btn-danger px-3 py-1.5 text-xs" type="submit">Supprimer</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td class="text-slate-400 italic text-center py-4" colspan="2">Aucun axe configuré.</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-600">Points de largage configurés</h3>
                                    <div class="ref-table-wrap">
                                        <table class="ref-table w-full text-sm">
                                            <thead>
                                                <tr>
                                                    <th class="text-left">Point</th>
                                                    <th class="text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($dispatchingDropPoints as $index => $point)
                                                <tr>
                                                    <td>
                                                        <form method="POST" action="{{ route('admin.references.dispatching-drop-points.update', $index) }}" id="drop-form-{{ $index }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <input class="ref-input" name="nom" value="{{ $point }}" required>
                                                        </form>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="inline-flex items-center gap-2">
                                                            <button form="drop-form-{{ $index }}" class="ref-btn-primary px-3 py-1.5 text-xs" type="submit">Modifier</button>
                                                            <form method="POST" action="{{ route('admin.references.dispatching-drop-points.destroy', $index) }}" onsubmit="return confirm('Supprimer le point de largage {{ addslashes($point) }} ?');" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="ref-btn-danger px-3 py-1.5 text-xs" type="submit">Supprimer</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td class="text-slate-400 italic text-center py-4" colspan="2">Aucun point de largage configuré.</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </details>

                    <!-- Section Maintenance / Filtres -->
                    <details id="zone-filtres-referentiels" class="ref-collapsible scroll-mt-24" open>
                        <summary class="flex items-center justify-between gap-4 px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 rounded-xl bg-slate-900 text-teal-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-teal-600">Maintenance</span>
                                    <h2 class="text-lg font-black text-slate-900">Modifier Référentiels Existants</h2>
                                </div>
                            </div>
                            <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="px-6 pb-6 pt-4 border-t border-slate-100">

                            <!-- Formulaire de filtrage -->
                            <form method="GET" action="{{ route('admin.references.index') }}#zone-filtres-referentiels" id="heritageFilterForm" class="mb-8 grid grid-cols-1 gap-4 rounded-2xl border border-slate-200/80 bg-slate-50/80 p-5 md:grid-cols-5 items-end">
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600" for="filter_type_examen">Examen</label>
                                    <select id="filter_type_examen" name="filter_type_examen" class="ref-select">
                                        <option value="ALL" {{ $selectedTypeExamen === 'ALL' ? 'selected' : '' }}>Tous</option>
                                        <option value="BEPC" {{ $selectedTypeExamen === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                        <option value="CEPE" {{ $selectedTypeExamen === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600" for="filter_dren_id">DREN</label>
                                    <select id="filter_dren_id" name="filter_dren_id" class="ref-select">
                                        <option value="">Toutes</option>
                                        @foreach($drens as $dren)
                                            <option value="{{ $dren->id }}" {{ (int) $selectedDrenId === (int) $dren->id ? 'selected' : '' }}>{{ $dren->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600" for="filter_cisco_id">CISCO</label>
                                    <select id="filter_cisco_id" name="filter_cisco_id" class="ref-select">
                                        <option value="">Tous</option>
                                        @foreach($filterCiscos as $cisco)
                                            <option value="{{ $cisco->id }}" {{ (int) $selectedCiscoId === (int) $cisco->id ? 'selected' : '' }}>{{ $cisco->dren->nom ?? '-' }} / {{ $cisco->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600" for="filter_centre_correction_id">Centre correction</label>
                                    <select id="filter_centre_correction_id" name="filter_centre_correction_id" class="ref-select">
                                        <option value="">Tous</option>
                                        @foreach($filterCentresCorrection as $cc)
                                            <option value="{{ $cc->id }}" {{ (int) $selectedCentreCorrectionId === (int) $cc->id ? 'selected' : '' }}>{{ $cc->cisco->dren->nom ?? '-' }} / {{ $cc->cisco->nom ?? '-' }} / {{ $cc->nom }} ({{ $cc->type_examen }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex gap-2">
                                    <button class="ref-btn-primary flex-1 py-2.5 text-sm" type="submit">Filtrer</button>
                                    <a class="ref-btn-secondary flex-1 py-2.5 text-center text-sm" href="{{ route('admin.references.index') }}#zone-filtres-referentiels">Réinitialiser</a>
                                </div>
                            </form>

                            <!-- Table DREN -->
                            <div class="mb-6">
                                <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-600">DREN</h3>
                                <div class="ref-table-wrap">
                                    <table class="ref-table w-full text-sm">
                                        <thead>
                                            <tr>
                                                <th class="text-left">Nom</th>
                                                <th class="text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($drensPage