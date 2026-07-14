<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Référentiels</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')
    <style>
        :root {
            --ref-ink: #0f172a;
            --ref-muted: #64748b;
            --ref-line: #e2e8f0;
            --ref-soft: #f8fafc;
            --ref-accent: #0f766e;
            --ref-accent-soft: #ccfbf1;
        }

        body { font-family: var(--app-font-sans); }
        .ref-shell {
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            letter-spacing: 0;
        }
        .ref-panel {
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        }
        .ref-card {
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: #fff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        }
        .ref-grid-card {
            position: relative;
            overflow: hidden;
        }
        .ref-grid-card::before {
            content: "";
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #0f766e, #14b8a6);
        }
        .ref-label {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .ref-input, .ref-select {
            width: 100%;
            min-height: 44px;
            border-radius: 12px;
            border: 1px solid var(--ref-line);
            background: #fff;
            padding: .7rem .9rem;
            font-size: .9rem;
            font-weight: 650;
            color: var(--ref-ink);
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .ref-input:focus, .ref-select:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.12);
            background: #fff;
        }
        .ref-btn-primary {
            border-radius: 12px;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #fff;
            font-weight: 700;
            transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.14);
        }
        .ref-btn-primary:hover { transform: translateY(-1px); }
        .ref-btn-secondary {
            border-radius: 12px;
            border: 1px solid var(--ref-line);
            background: #fff;
            color: #334155;
            font-weight: 700;
        }
        .ref-btn-danger {
            border-radius: 12px;
            border: 1px solid #fecdd3;
            background: #fff1f2;
            color: #be123c;
            font-weight: 700;
        }
        .ref-stat {
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            background:
                radial-gradient(circle at top right, rgba(20, 184, 166, 0.10), transparent 32%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }
        .ref-table-wrap {
            overflow: hidden;
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: #fff;
        }
        .ref-table thead tr { background: #f8fafc; }
        .ref-table th {
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #64748b;
        }
        .ref-table td, .ref-table th {
            border-bottom: 1px solid rgba(226, 232, 240, 0.85);
            padding: .9rem .95rem;
            vertical-align: top;
        }
        .ref-table tbody tr:hover { background: rgba(248, 250, 252, 0.9); }
        .ref-quick-link {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 999px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.88);
            padding: .55rem .8rem;
            font-size: .78rem;
            font-weight: 850;
            color: #334155;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }
        .ref-quick-link:hover {
            border-color: rgba(20, 184, 166, 0.45);
            color: #0f766e;
        }
        .ref-section-title {
            display: flex;
            align-items: flex-start;
            gap: .85rem;
        }
        .ref-section-icon {
            display: inline-flex;
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            border: 1px solid rgba(20, 184, 166, 0.18);
            background: #f0fdfa;
            color: #0f766e;
            font-weight: 900;
        }
        .ref-edit-form {
            display: grid;
            grid-template-columns: minmax(86px, 110px) minmax(180px, 1fr) auto;
            gap: .55rem;
            align-items: center;
        }
        @media (max-width: 760px) {
            .ref-edit-form { grid-template-columns: 1fr; }
        }
        .ref-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .28rem .65rem;
            font-size: 11px;
            font-weight: 800;
        }
        .ref-collapsible {
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.95) 100%);
            overflow: hidden;
        }
        .ref-collapsible summary {
            list-style: none;
            cursor: pointer;
        }
        .ref-collapsible summary::-webkit-details-marker { display: none; }
        .ref-collapsible-toggle {
            transition: transform .22s ease;
        }
        .ref-collapsible[open] .ref-collapsible-toggle {
            transform: rotate(180deg);
        }
        .ref-collapsible-body {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows .24s ease;
        }
        .ref-collapsible[open] .ref-collapsible-body {
            grid-template-rows: 1fr;
        }
        .ref-collapsible-inner {
            overflow: hidden;
        }
    </style>
</head>
<body class="ref-shell bg-gradient-to-br from-slate-50 to-slate-100 text-slate-900">
<div class="mx-auto max-w-[1700px] p-4 md:p-6 lg:p-8">
    <div class="flex flex-col gap-5 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1">
            <div class="ref-panel overflow-hidden rounded-[30px] backdrop-blur-sm transition-all duration-200 hover:shadow-xl">
                <div class="border-b border-slate-200/80 bg-[radial-gradient(circle_at_top_right,_rgba(20,184,166,0.12),_transparent_28%),linear-gradient(120deg,_rgba(255,255,255,0.96),_rgba(248,250,252,0.96))] px-6 py-6 md:px-8 md:py-7">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-1">
                            <p class="ref-label">Administration Centrale</p>
                            <h1 class="text-3xl font-black tracking-tight text-slate-900 md:text-4xl">Référentiels</h1>
                            <p class="max-w-3xl text-sm font-medium leading-relaxed text-slate-500">Ajout, organisation et maintenance des DREN, CISCO, centres de correction, centres d'écrit et paramètres de dispatching.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('admin.statistics.index') }}">Statistiques</a>
                            <a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('admin.users.index') }}">Utilisateurs</a>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <div class="mb-5 flex flex-wrap gap-2">
                        <a class="ref-quick-link" href="#zone-besoins-specifiques"><i class="fas fa-wheelchair"></i> Besoins spécifiques</a>
                        <a class="ref-quick-link" href="#zone-dispatching-referentiels"><i class="fas fa-route"></i> Dispatching</a>
                        <a class="ref-quick-link" href="#zone-doublons-centres"><i class="fas fa-copy"></i> Doublons centres</a>
                        <a class="ref-quick-link" href="#zone-filtres-referentiels"><i class="fas fa-edit"></i> Modifier les référentiels</a>
                    </div>

                    <div class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-6">
                        <div class="ref-stat p-4">
                            <p class="ref-label">DREN</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($drens->count(), 0, ',', ' ') }}</p>
                        </div>
                        <div class="ref-stat p-4">
                            <p class="ref-label">CISCO</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($formCiscos->count(), 0, ',', ' ') }}</p>
                        </div>
                        <div class="ref-stat p-4">
                            <p class="ref-label">Correction</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($formCentresCorrection->count(), 0, ',', ' ') }}</p>
                        </div>
                        <div class="ref-stat p-4">
                            <p class="ref-label">Centres Écrit</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($centresEcritPage->total(), 0, ',', ' ') }}</p>
                        </div>
                        <div class="ref-stat p-4">
                            <p class="ref-label">Doublons Centres</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($duplicateCentreCorrectionRowsCount + $duplicateCentreEcritRowsCount, 0, ',', ' ') }}</p>
                        </div>
                        <div class="ref-stat p-4">
                            <p class="ref-label">Dispatching</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format(count($dispatchingAxes) + count($dispatchingDropPoints), 0, ',', ' ') }}</p>
                        </div>
                    </div>

                    @if(session('status'))
                        <div class="mb-6 rounded-lg border border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-white px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">{{ session('status') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="mb-6 rounded-lg border border-rose-200/80 bg-gradient-to-r from-rose-50 to-white px-4 py-3 text-sm font-medium text-rose-700 shadow-sm">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mb-6 space-y-4">
                        <details class="ref-collapsible" open>
                            <summary class="flex items-center justify-between gap-4 px-5 py-4 md:px-6">
                                <div class="ref-section-title">
                                    <span class="ref-section-icon"><i class="fas fa-sitemap"></i></span>
                                    <div>
                                        <p class="ref-label">Création</p>
                                        <h2 class="mt-1 text-xl font-black text-slate-900">Ajouter DREN et CISCO</h2>
                                        <p class="mt-1 text-sm font-medium text-slate-500">Les entités de base sont regroupées ici pour garder la hiérarchie claire.</p>
                                    </div>
                                </div>
                                <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <div class="ref-collapsible-body">
                                <div class="ref-collapsible-inner px-5 pb-5 md:px-6 md:pb-6">
                                    <div class="grid gap-5 xl:grid-cols-2">
                                        <div class="ref-card ref-grid-card rounded-[24px] p-5 md:p-6">
                                            <div class="mb-4">
                                                <p class="ref-label">Création</p>
                                                <h2 class="mt-1 text-xl font-black text-slate-900">Ajouter DREN</h2>
                                            </div>
                                            <form method="POST" action="{{ route('admin.references.drens.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                                                @csrf
                                                <input class="ref-input md:col-span-3" name="nom" placeholder="Nom DREN" required>
                                                <button class="ref-btn-primary inline-flex items-center justify-center px-4 py-3 text-sm" type="submit">Ajouter</button>
                                            </form>
                                        </div>

                                        <div class="ref-card ref-grid-card rounded-[24px] p-5 md:p-6">
                                            <div class="mb-4">
                                                <p class="ref-label">Création</p>
                                                <h2 class="mt-1 text-xl font-black text-slate-900">Ajouter CISCO</h2>
                                            </div>
                                            <form method="POST" action="{{ route('admin.references.ciscos.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                                                @csrf
                                                <select class="ref-select" name="dren_id" required>
                                                    <option value="">DREN</option>
                                                    @foreach($drens as $dren)
                                                        <option value="{{ $dren->id }}">{{ $dren->nom }}</option>
                                                    @endforeach
                                                </select>
                                                <input class="ref-input md:col-span-2" name="nom" placeholder="Nom CISCO" required>
                                                <button class="ref-btn-primary inline-flex items-center justify-center px-4 py-3 text-sm" type="submit">Ajouter</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <details class="ref-collapsible">
                            <summary class="flex items-center justify-between gap-4 px-5 py-4 md:px-6">
                                <div class="ref-section-title">
                                    <span class="ref-section-icon"><i class="fas fa-school"></i></span>
                                    <div>
                                        <p class="ref-label">Création</p>
                                        <h2 class="mt-1 text-xl font-black text-slate-900">Ajouter Centres</h2>
                                        <p class="mt-1 text-sm font-medium text-slate-500">Centres de correction puis centres d’écrit, avec le type d’examen sélectionné.</p>
                                    </div>
                                </div>
                                <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <div class="ref-collapsible-body">
                                <div class="ref-collapsible-inner px-5 pb-5 md:px-6 md:pb-6">
                                    <div class="grid gap-5 xl:grid-cols-2">
                                        <div class="ref-card ref-grid-card rounded-[24px] p-5 md:p-6">
                                            <div class="mb-4">
                                                <p class="ref-label">Création</p>
                                                <h2 class="mt-1 text-xl font-black text-slate-900">Ajouter Centre de Correction</h2>
                                            </div>
                                            <form method="POST" action="{{ route('admin.references.centres-correction.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                                                @csrf
                                                <select class="ref-select md:col-span-2" name="cisco_id" required>
                                                    <option value="">CISCO</option>
                                                    @foreach($formCiscos as $cisco)
                                                        <option value="{{ $cisco->id }}">{{ $cisco->dren->nom ?? '-' }} / {{ $cisco->nom }}</option>
                                                    @endforeach
                                                </select>
                                                <input class="ref-input" name="nom" placeholder="Nom centre correction" required>
                                                <div class="inline-flex items-center rounded-2xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-bold text-teal-800">{{ $centreTypeForForms }}</div>
                                                <input type="hidden" name="type_examen" value="{{ $centreTypeForForms }}">
                                                <div class="md:col-span-4">
                                                    <button class="ref-btn-primary inline-flex items-center justify-center px-4 py-3 text-sm" type="submit">Ajouter Centre correction</button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="ref-card ref-grid-card rounded-[24px] p-5 md:p-6">
                                            <div class="mb-4">
                                                <p class="ref-label">Création</p>
                                                <h2 class="mt-1 text-xl font-black text-slate-900">Ajouter Centre d'Écrit</h2>
                                            </div>
                                            <form method="POST" action="{{ route('admin.references.centres-ecrit.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                                                @csrf
                                                <select class="ref-select md:col-span-2" name="centre_correction_id" required>
                                                    <option value="">Centre correction</option>
                                                    @foreach($formCentresCorrection as $cc)
                                                        <option value="{{ $cc->id }}">{{ $cc->cisco->dren->nom ?? '-' }} / {{ $cc->cisco->nom ?? '-' }} / {{ $cc->nom }} ({{ $cc->type_examen }})</option>
                                                    @endforeach
                                                </select>
                                                <input class="ref-input" name="nom" placeholder="Nom centre écrit" required>
                                                <div class="inline-flex items-center rounded-2xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-bold text-teal-800">{{ $centreTypeForForms }}</div>
                                                <input type="hidden" name="type_examen" value="{{ $centreTypeForForms }}">
                                                <div class="md:col-span-4">
                                                    <button class="ref-btn-primary inline-flex items-center justify-center px-4 py-3 text-sm" type="submit">Ajouter Centre écrit</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </details>
                    </div>

                    <details id="zone-besoins-specifiques" class="ref-collapsible mb-6 scroll-mt-24">
                        <summary class="flex items-center justify-between gap-4 px-5 py-4 md:px-6">
                            <div class="ref-section-title">
                                <span class="ref-section-icon"><i class="fas fa-wheelchair"></i></span>
                                <div>
                                    <p class="ref-label">Paramètres Saisie</p>
                                    <h2 class="mt-1 text-xl font-black text-slate-900">Candidats à besoins spécifiques</h2>
                                    <p class="mt-1 max-w-3xl text-sm font-medium text-slate-500">Ajout et correction après coup pour les centres ayant déjà une répartition par salle.</p>
                                </div>
                            </div>
                            <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="ref-collapsible-body">
                        <div class="ref-collapsible-inner px-5 pb-5 md:px-6 md:pb-6">
                            <form method="GET" action="{{ route('admin.references.index') }}#zone-besoins-specifiques" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4 md:grid-cols-4">
                                <div>
                                    <label class="ref-label mb-1 block" for="special_annee_filter">Année</label>
                                    <select id="special_annee_filter" name="special_annee" class="ref-select">
                                        @foreach($repartitionAnnees as $annee)
                                            <option value="{{ $annee }}" {{ $specialAnnee === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="ref-label mb-1 block" for="special_type_filter">Type</label>
                                    <select id="special_type_filter" name="special_type_examen" class="ref-select">
                                        <option value="BEPC" {{ $specialTypeExamen === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                        <option value="CEPE" {{ $specialTypeExamen === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                    </select>
                                </div>
                                <div class="flex items-end md:col-span-2">
                                    <button class="ref-btn-secondary w-full px-4 py-3 text-sm" type="submit">Afficher les centres répartis</button>
                                </div>
                            </form>

                            <div class="grid gap-5 xl:grid-cols-[minmax(360px,0.85fr)_minmax(0,1.15fr)]">
                                <div class="ref-card ref-grid-card rounded-2xl p-5 md:p-6">
                                    <div class="mb-4">
                                        <p class="ref-label">Ajout</p>
                                        <h3 class="mt-1 text-lg font-black text-slate-900">Déclarer les candidats oubliés</h3>
                                    </div>
                                    <form method="POST" action="{{ route('admin.references.special-candidates.store') }}" class="grid grid-cols-1 gap-3">
                                        @csrf
                                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                            <div>
                                                <label class="ref-label mb-1 block" for="special_annee">Année</label>
                                                <select id="special_annee" name="annee" class="ref-select" required>
                                                    @foreach($repartitionAnnees as $annee)
                                                        <option value="{{ $annee }}" {{ old('annee', $specialAnnee) === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="ref-label mb-1 block" for="special_type_examen">Type</label>
                                                <select id="special_type_examen" name="type_examen" class="ref-select" required>
                                                    <option value="BEPC" {{ old('type_examen', $specialTypeExamen) === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                                    <option value="CEPE" {{ old('type_examen', $specialTypeExamen) === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="ref-label mb-1 block">Centres éligibles</label>
                                                <div class="min-h-[44px] rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-black text-teal-800">{{ number_format($centresWithRepartition->count(), 0, ',', ' ') }}</div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="ref-label mb-1 block" for="special_centre_ecrit_id">Centre avec répartition</label>
                                            <select id="special_centre_ecrit_id" name="centre_ecrit_id" class="ref-select" required>
                                                <option value="">Choisir un centre</option>
                                                @foreach($centresWithRepartition as $centre)
                                                    <option value="{{ $centre->id }}" {{ (int) old('centre_ecrit_id') === (int) $centre->id ? 'selected' : '' }}>
                                                        {{ $centre->dren }} / {{ $centre->cisco }} / {{ $centre->centre_correction }} / {{ $centre->centre_ecrit }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="ref-label mb-1 block" for="candidats_specifiques_admin">Candidats spécifiques</label>
                                            <textarea id="candidats_specifiques_admin" name="candidats_specifiques" rows="5" placeholder="Exemple: 2, Visuel&#10;5, Auditif" class="ref-input" required>{{ old('candidats_specifiques') }}</textarea>
                                            <p class="mt-2 text-xs font-medium text-slate-500">Une ligne par candidat: numéro de salle, type handicap. Les salles doivent exister dans la répartition du centre.</p>
                                        </div>
                                        <div>
                                            <button class="ref-btn-primary px-4 py-3 text-sm" type="submit">Ajouter besoins spécifiques</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="ref-table-wrap overflow-x-auto p-4">
                                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <p class="ref-label">Correction rapide</p>
                                            <h3 class="text-lg font-black text-slate-900">Déclarations existantes</h3>
                                        </div>
                                        <span class="ref-chip bg-amber-100 text-amber-800">{{ number_format($specialCandidatesPage->total(), 0, ',', ' ') }} lignes</span>
                                    </div>
                                    <table class="ref-table min-w-full border-collapse text-sm">
                                        <thead>
                                        <tr>
                                            <th class="text-left">Centre</th>
                                            <th class="text-left">Salle / Handicap</th>
                                            <th class="text-left">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($specialCandidatesPage as $candidate)
                                            <tr>
                                                <td>
                                                    <div class="font-bold text-slate-900">{{ $candidate->centre_ecrit }}</div>
                                                    <div class="text-xs font-medium text-slate-500">{{ $candidate->annee }} / {{ $candidate->type_examen }} / {{ $candidate->dren }} / {{ $candidate->cisco }}</div>
                                                </td>
                                                <td>
                                                    <form id="special-candidate-ref-{{ $candidate->id }}" method="POST" action="{{ route('repartition.special-candidates.update', $candidate->id) }}" class="ref-edit-form">
                                                        @csrf
                                                        @method('PUT')
                                                        <input class="ref-input" type="number" min="1" name="numero_salle" value="{{ $candidate->numero_salle }}" aria-label="Salle">
                                                        <input class="ref-input" name="type_handicap" value="{{ $candidate->type_handicap }}" aria-label="Type handicap">
                                                    </form>
                                                </td>
                                                <td>
                                                    <button class="ref-btn-primary px-3 py-2 text-sm" form="special-candidate-ref-{{ $candidate->id }}" type="submit">Modifier</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td class="text-slate-500" colspan="3">Aucun candidat spécifique pour ce filtre.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                    @if($specialCandidatesPage->hasPages())
                                        <div class="mt-3">{{ $specialCandidatesPage->links() }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        </div>
                    </details>

                    <details id="zone-dispatching-referentiels" class="ref-collapsible mb-6 scroll-mt-24">
                        <summary class="flex items-center justify-between gap-4 px-5 py-4 md:px-6">
                            <div>
                                <p class="ref-label">Paramètres Métier</p>
                                <h2 class="mt-1 text-xl font-black text-slate-900">Axes de Dispatching et Points de Largage</h2>
                                <p class="mt-1 max-w-2xl text-sm text-slate-500">Gestion rapide des listes utilisées dans la saisie. Les axes par défaut ont été préchargés et restent modifiables à tout moment.</p>
                            </div>
                            <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="ref-collapsible-body">
                        <div class="ref-collapsible-inner px-5 pb-5 md:px-6 md:pb-6">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="rounded-[22px] border border-slate-200 bg-slate-50/80 p-4">
                                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Ajouter un axe</h3>
                                <form method="POST" action="{{ route('admin.references.dispatching-axes.store') }}" class="flex flex-col gap-3 sm:flex-row">
                                    @csrf
                                    <input class="ref-input" name="nom" placeholder="Nom de l'axe" required>
                                    <button class="ref-btn-primary px-4 py-3 text-sm" type="submit">Ajouter axe</button>
                                </form>
                            </div>
                            <div class="rounded-[22px] border border-slate-200 bg-slate-50/80 p-4">
                                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Ajouter un point de largage</h3>
                                <form method="POST" action="{{ route('admin.references.dispatching-drop-points.store') }}" class="flex flex-col gap-3 sm:flex-row">
                                    @csrf
                                    <input class="ref-input" name="nom" placeholder="Nom du point de largage" required>
                                    <button class="ref-btn-primary px-4 py-3 text-sm" type="submit">Ajouter point</button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <div class="ref-table-wrap overflow-x-auto">
                                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">Axes configurés</h3>
                                <table class="ref-table min-w-full border-collapse text-sm">
                                    <thead><tr><th class="text-left">Axe</th><th class="text-left">Action</th></tr></thead>
                                    <tbody>
                                    @forelse($dispatchingAxes as $index => $axis)
                                        <tr>
                                            <td>
                                                <form method="POST" action="{{ route('admin.references.dispatching-axes.update', $index) }}" class="flex flex-wrap gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input class="ref-input" name="nom" value="{{ $axis }}" required>
                                            </td>
                                            <td>
                                                    <div class="flex flex-wrap gap-2">
                                                        <button class="ref-btn-primary px-3 py-2 text-sm" type="submit">Modifier</button>
                                                </form>
                                                        <form method="POST" action="{{ route('admin.references.dispatching-axes.destroy', $index) }}" onsubmit="return confirm('Supprimer l\\'axe {{ addslashes($axis) }} ?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="ref-btn-danger px-3 py-2 text-sm" type="submit">Supprimer</button>
                                                        </form>
                                                    </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td class="text-slate-500" colspan="2">Aucun axe configuré.</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="ref-table-wrap overflow-x-auto">
                                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">Points de largage configurés</h3>
                                <table class="ref-table min-w-full border-collapse text-sm">
                                    <thead><tr><th class="text-left">Point</th><th class="text-left">Action</th></tr></thead>
                                    <tbody>
                                    @forelse($dispatchingDropPoints as $index => $point)
                                        <tr>
                                            <td>
                                                <form method="POST" action="{{ route('admin.references.dispatching-drop-points.update', $index) }}" class="flex flex-wrap gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input class="ref-input" name="nom" value="{{ $point }}" required>
                                            </td>
                                            <td>
                                                    <div class="flex flex-wrap gap-2">
                                                        <button class="ref-btn-primary px-3 py-2 text-sm" type="submit">Modifier</button>
                                                </form>
                                                        <form method="POST" action="{{ route('admin.references.dispatching-drop-points.destroy', $index) }}" onsubmit="return confirm('Supprimer le point de largage {{ addslashes($point) }} ?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="ref-btn-danger px-3 py-2 text-sm" type="submit">Supprimer</button>
                                                        </form>
                                                    </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td class="text-slate-500" colspan="2">Aucun point de largage configuré.</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        </div>
                        </div>
                    </details>

                    <details id="zone-doublons-centres" class="ref-collapsible mb-6 scroll-mt-24" open>
                        <summary class="flex items-center justify-between gap-4 px-5 py-4 md:px-6">
                            <div class="ref-section-title">
                                <span class="ref-section-icon"><i class="fas fa-copy"></i></span>
                                <div>
                                    <p class="ref-label">Contrôle doublons</p>
                                    <h2 class="mt-1 text-xl font-black text-slate-900">Centres avec le même nom</h2>
                                    <p class="mt-1 max-w-3xl text-sm font-medium text-slate-500">Les noms identiques sont regroupés par type d'examen, sans filtre DREN/CISCO. Les différences majuscules/minuscules sont ignorées.</p>
                                </div>
                            </div>
                            <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="ref-collapsible-body">
                        <div class="ref-collapsible-inner px-5 pb-5 md:px-6 md:pb-6">
                            <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-3">
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                                    <p class="ref-label text-amber-700">Groupes correction</p>
                                    <p class="mt-1 text-2xl font-black text-amber-900">{{ number_format($duplicateCentreCorrectionGroups->count(), 0, ',', ' ') }}</p>
                                </div>
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                                    <p class="ref-label text-amber-700">Groupes écrit</p>
                                    <p class="mt-1 text-2xl font-black text-amber-900">{{ number_format($duplicateCentreEcritGroups->count(), 0, ',', ' ') }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <p class="ref-label">Portée</p>
                                    <p class="mt-2 text-sm font-black text-slate-900">Par type, sans filtre CISCO</p>
                                </div>
                            </div>

                            <div class="mb-4 flex items-center justify-between gap-3">
                                <div>
                                    <p class="ref-label">Centres de correction</p>
                                    <h3 class="mt-1 text-lg font-black text-slate-900">Noms répétés dans le total centres correction</h3>
                                </div>
                                <span class="ref-chip bg-amber-100 text-amber-800">{{ number_format($duplicateCentreCorrectionRowsCount, 0, ',', ' ') }} lignes</span>
                            </div>
                            @forelse($duplicateCentreCorrectionGroups as $group)
                                <div class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3">
                                        <div>
                                            <p class="ref-label">Nom répété</p>
                                            <h3 class="mt-1 text-lg font-black text-slate-900">{{ $group['nom'] }}</h3>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="ref-chip bg-teal-100 text-teal-800">{{ $group['type_examen'] ?: 'Type non défini' }}</span>
                                            <span class="ref-chip bg-amber-100 text-amber-800">{{ $group['count'] }} centres</span>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="ref-table min-w-full border-collapse text-sm">
                                            <thead>
                                            <tr>
                                                <th class="text-left">Localisation</th>
                                                <th class="text-left">Type</th>
                                                <th class="text-left">Nouveau nom centre correction</th>
                                                <th class="text-left">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($group['centres'] as $centre)
                                                <tr>
                                                    <td>
                                                        <div class="font-bold text-slate-900">{{ $centre->cisco->dren->nom ?? '-' }}</div>
                                                        <div class="text-xs font-medium text-slate-500">{{ $centre->cisco->nom ?? '-' }} / ID centre correction: {{ $centre->id }}</div>
                                                    </td>
                                                    <td>
                                                        <span class="ref-chip bg-slate-100 text-slate-700">{{ $centre->type_examen ?: '-' }}</span>
                                                    </td>
                                                    <td>
                                                        <form id="duplicate-centre-correction-{{ $centre->id }}" method="POST" action="{{ route('admin.references.centres-correction.duplicate-name.update', array_merge(['centreCorrection' => $centre->id], request()->query())) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <input class="ref-input" name="nom" value="{{ $centre->nom }}" required>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <button class="ref-btn-primary px-3 py-2 text-sm" form="duplicate-centre-correction-{{ $centre->id }}" type="submit">Modifier</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @empty
                                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-6 text-sm font-bold text-emerald-800">
                                    Aucun doublon de centre de correction.
                                </div>
                            @endforelse

                            <div class="mb-4 mt-8 flex items-center justify-between gap-3">
                                <div>
                                    <p class="ref-label">Centres d'écrit</p>
                                    <h3 class="mt-1 text-lg font-black text-slate-900">Noms répétés dans le total centres écrit</h3>
                                </div>
                                <span class="ref-chip bg-amber-100 text-amber-800">{{ number_format($duplicateCentreEcritRowsCount, 0, ',', ' ') }} lignes</span>
                            </div>
                            @forelse($duplicateCentreEcritGroups as $group)
                                <div class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3">
                                        <div>
                                            <p class="ref-label">Nom répété</p>
                                            <h3 class="mt-1 text-lg font-black text-slate-900">{{ $group['nom'] }}</h3>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="ref-chip bg-teal-100 text-teal-800">{{ $group['type_examen'] ?: 'Type non défini' }}</span>
                                            <span class="ref-chip bg-amber-100 text-amber-800">{{ $group['count'] }} centres</span>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="ref-table min-w-full border-collapse text-sm">
                                            <thead>
                                            <tr>
                                                <th class="text-left">Localisation</th>
                                                <th class="text-left">Centre correction</th>
                                                <th class="text-left">Type</th>
                                                <th class="text-left">Nouveau nom centre écrit</th>
                                                <th class="text-left">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($group['centres'] as $centre)
                                                <tr>
                                                    <td>
                                                        <div class="font-bold text-slate-900">{{ $centre->centreCorrection->cisco->dren->nom ?? '-' }}</div>
                                                        <div class="text-xs font-medium text-slate-500">{{ $centre->centreCorrection->cisco->nom ?? '-' }}</div>
                                                    </td>
                                                    <td>
                                                        <div class="font-bold text-slate-900">{{ $centre->centreCorrection->nom ?? '-' }}</div>
                                                        <div class="text-xs font-medium text-slate-500">ID centre écrit: {{ $centre->id }}</div>
                                                    </td>
                                                    <td>
                                                        <span class="ref-chip bg-slate-100 text-slate-700">{{ $centre->type_examen ?: '-' }}</span>
                                                    </td>
                                                    <td>
                                                        <form id="duplicate-centre-ecrit-{{ $centre->id }}" method="POST" action="{{ route('admin.references.centres-ecrit.duplicate-name.update', array_merge(['centreEcrit' => $centre->id], request()->query())) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <input class="ref-input" name="nom" value="{{ $centre->nom }}" required>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <button class="ref-btn-primary px-3 py-2 text-sm" form="duplicate-centre-ecrit-{{ $centre->id }}" type="submit">Modifier</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-6 text-sm font-bold text-emerald-800">
                                    Aucun doublon de centre d'écrit.
                                </div>
                            @endforelse
                        </div>
                        </div>
                    </details>

                    <details id="zone-filtres-referentiels" class="ref-collapsible scroll-mt-24" open>
                        <summary class="flex items-center justify-between gap-4 px-5 py-4 md:px-6">
                            <div>
                                <p class="ref-label">Maintenance</p>
                                <h2 class="mt-1 text-xl font-black text-slate-900">Modifier Référentiels Existants</h2>
                            </div>
                            <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="ref-collapsible-body">
                        <div class="ref-collapsible-inner px-5 pb-5 md:px-6 md:pb-6">

                        <form method="GET" action="{{ route('admin.references.index') }}#zone-filtres-referentiels" id="heritageFilterForm" class="mb-6 grid grid-cols-1 gap-3 rounded-[24px] border border-slate-200/80 bg-slate-50/90 p-4 md:grid-cols-5">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700" for="filter_type_examen">Filtre Examen</label>
                                <select id="filter_type_examen" name="filter_type_examen" class="ref-select">
                                    <option value="ALL" {{ $selectedTypeExamen === 'ALL' ? 'selected' : '' }}>Tous</option>
                                    <option value="BEPC" {{ $selectedTypeExamen === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                    <option value="CEPE" {{ $selectedTypeExamen === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700" for="filter_dren_id">Filtre DREN</label>
                                <select id="filter_dren_id" name="filter_dren_id" class="ref-select">
                                    <option value="">Toutes</option>
                                    @foreach($drens as $dren)
                                        <option value="{{ $dren->id }}" {{ (int) $selectedDrenId === (int) $dren->id ? 'selected' : '' }}>{{ $dren->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700" for="filter_cisco_id">Filtre CISCO</label>
                                <select id="filter_cisco_id" name="filter_cisco_id" class="ref-select">
                                    <option value="">Tous</option>
                                    @foreach($filterCiscos as $cisco)
                                        <option value="{{ $cisco->id }}" {{ (int) $selectedCiscoId === (int) $cisco->id ? 'selected' : '' }}>{{ $cisco->dren->nom ?? '-' }} / {{ $cisco->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700" for="filter_centre_correction_id">Filtre Centre correction</label>
                                <select id="filter_centre_correction_id" name="filter_centre_correction_id" class="ref-select">
                                    <option value="">Tous</option>
                                    @foreach($filterCentresCorrection as $cc)
                                        <option value="{{ $cc->id }}" {{ (int) $selectedCentreCorrectionId === (int) $cc->id ? 'selected' : '' }}>{{ $cc->cisco->dren->nom ?? '-' }} / {{ $cc->cisco->nom ?? '-' }} / {{ $cc->nom }} ({{ $cc->type_examen }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end gap-2 md:col-span-2">
                                <button class="ref-btn-primary w-full px-4 py-3 text-sm" type="submit">Filtrer</button>
                                <a class="ref-btn-secondary w-full px-4 py-3 text-center text-sm" href="{{ route('admin.references.index') }}#zone-filtres-referentiels">Réinitialiser</a>
                            </div>
                        </form>

                        <div class="mb-6 overflow-x-auto">
                            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">DREN</h3>
                            <div class="ref-table-wrap">
                            <table class="ref-table min-w-full border-collapse text-sm">
                                <thead><tr><th class="text-left">Nom</th><th class="text-left">Action</th></tr></thead>
                                <tbody>
                                @forelse($drensPage as $dren)
                                    <tr>
                                        <td>
                                            <form method="POST" action="{{ route('admin.references.drens.update', $dren) }}" class="flex flex-wrap gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input class="ref-input" name="nom" value="{{ $dren->nom }}" required>
                                        </td>
                                        <td>
                                                <div class="flex flex-wrap gap-2">
                                                    <button class="ref-btn-primary px-3 py-2 text-sm" type="submit">Modifier</button>
                                            </form>
                                                    <form method="POST" action="{{ route('admin.references.drens.destroy', $dren) }}" onsubmit="return confirm('Supprimer la DREN {{ addslashes($dren->nom) }} et tout son héritage (CISCO, centres, statistiques) ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="ref-btn-danger px-3 py-2 text-sm" type="submit">Supprimer</button>
                                                    </form>
                                                </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="text-slate-500" colspan="2">Aucune DREN trouvée.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                            </div>
                            @if($drensPage->hasPages())
                                <div class="mt-3">{{ $drensPage->links() }}</div>
                            @endif
                        </div>

                        <div class="mb-6 overflow-x-auto">
                            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">CISCO</h3>
                            <div class="ref-table-wrap">
                            <table class="ref-table min-w-full border-collapse text-sm">
                                <thead><tr><th class="text-left">DREN</th><th class="text-left">Nom</th><th class="text-left">Action</th></tr></thead>
                                <tbody>
                                @forelse($ciscosPage as $cisco)
                                    <tr>
                                        <td>
                                            <form method="POST" action="{{ route('admin.references.ciscos.update', $cisco) }}" class="flex flex-wrap gap-2">
                                                @csrf
                                                @method('PUT')
                                                <select class="ref-select" name="dren_id" required>
                                                    @foreach($drens as $dren)
                                                        <option value="{{ $dren->id }}" {{ (int)$cisco->dren_id === (int)$dren->id ? 'selected' : '' }}>{{ $dren->nom }}</option>
                                                    @endforeach
                                                </select>
                                        </td>
                                        <td><input class="ref-input" name="nom" value="{{ $cisco->nom }}" required></td>
                                        <td>
                                                <div class="flex flex-wrap gap-2">
                                                    <button class="ref-btn-primary px-3 py-2 text-sm" type="submit">Modifier</button>
                                            </form>
                                                    <form method="POST" action="{{ route('admin.references.ciscos.destroy', $cisco) }}" onsubmit="return confirm('Supprimer le CISCO {{ addslashes($cisco->nom) }} et tous ses centres/statistiques ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="ref-btn-danger px-3 py-2 text-sm" type="submit">Supprimer</button>
                                                    </form>
                                                </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="text-slate-500" colspan="3">Aucun CISCO trouvé.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                            </div>
                            @if($ciscosPage->hasPages())
                                <div class="mt-3">{{ $ciscosPage->links() }}</div>
                            @endif
                        </div>

                        <div class="mb-6 overflow-x-auto">
                            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Centres de correction</h3>
                            <div class="ref-table-wrap">
                            <table class="ref-table min-w-full border-collapse text-sm">
                                <thead><tr><th class="text-left">CISCO</th><th class="text-left">Nom</th><th class="text-left">Type</th><th class="text-left">Action</th></tr></thead>
                                <tbody>
                                @forelse($centresCorrectionPage as $cc)
                                    <tr>
                                        <td>
                                            <form method="POST" action="{{ route('admin.references.centres-correction.update', $cc) }}">
                                                @csrf
                                                @method('PUT')
                                                <select class="ref-select" name="cisco_id" required>
                                                    @foreach($allCiscos as $cisco)
                                                        <option value="{{ $cisco->id }}" {{ (int)$cc->cisco_id === (int)$cisco->id ? 'selected' : '' }}>{{ $cisco->dren->nom ?? '-' }} / {{ $cisco->nom }}</option>
                                                    @endforeach
                                                </select>
                                        </td>
                                        <td><input class="ref-input" name="nom" value="{{ $cc->nom }}" required></td>
                                        <td>
                                                <select class="ref-select" name="type_examen" required>
                                                    <option value="BEPC" {{ $cc->type_examen === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                                    <option value="CEPE" {{ $cc->type_examen === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                                </select>
                                        </td>
                                        <td>
                                                <div class="flex flex-wrap gap-2">
                                                    <button class="ref-btn-primary px-3 py-2 text-sm" type="submit">Modifier</button>
                                            </form>
                                                    <form method="POST" action="{{ route('admin.references.centres-correction.destroy', $cc) }}" onsubmit="return confirm('Supprimer le centre de correction {{ addslashes($cc->nom) }} ({{ $cc->type_examen }}) et tous ses centres d\\'écrit/statistiques ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="ref-btn-danger px-3 py-2 text-sm" type="submit">Supprimer</button>
                                                    </form>
                                                </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="text-slate-500" colspan="4">Aucun centre de correction trouvé.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                            </div>
                            @if($centresCorrectionPage->hasPages())
                                <div class="mt-3">{{ $centresCorrectionPage->links() }}</div>
                            @endif
                        </div>

                        <div class="overflow-x-auto">
                            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">Centres d'écrit</h3>
                            <div class="ref-table-wrap">
                            <table class="ref-table min-w-full border-collapse text-sm">
                                <thead><tr><th class="text-left">Centre correction</th><th class="text-left">Nom</th><th class="text-left">Type</th><th class="text-left">Action</th></tr></thead>
                                <tbody>
                                @forelse($centresEcritPage as $ce)
                                    <tr>
                                        <td>
                                            <form method="POST" action="{{ route('admin.references.centres-ecrit.update', $ce) }}">
                                                @csrf
                                                @method('PUT')
                                                <select class="ref-select" name="centre_correction_id" required>
                                                    @foreach($allCentresCorrection as $cc)
                                                        <option value="{{ $cc->id }}" {{ (int)$ce->centre_correction_id === (int)$cc->id ? 'selected' : '' }}>{{ $cc->cisco->dren->nom ?? '-' }} / {{ $cc->cisco->nom ?? '-' }} / {{ $cc->nom }} ({{ $cc->type_examen }})</option>
                                                    @endforeach
                                                </select>
                                        </td>
                                        <td><input class="ref-input" name="nom" value="{{ $ce->nom }}" required></td>
                                        <td>
                                                <select class="ref-select" name="type_examen" required>
                                                    <option value="BEPC" {{ $ce->type_examen === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                                    <option value="CEPE" {{ $ce->type_examen === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                                </select>
                                        </td>
                                        <td>
                                                <div class="flex flex-wrap gap-2">
                                                    <button class="ref-btn-primary px-3 py-2 text-sm" type="submit">Modifier</button>
                                            </form>
                                                    <form method="POST" action="{{ route('admin.references.centres-ecrit.destroy', $ce) }}" onsubmit="return confirm('Supprimer le centre d\\'écrit {{ addslashes($ce->nom) }} ({{ $ce->type_examen }}) et ses statistiques ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="ref-btn-danger px-3 py-2 text-sm" type="submit">Supprimer</button>
                                                    </form>
                                                </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="text-slate-500" colspan="4">Aucun centre d'écrit trouvé.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                            </div>
                            @if($centresEcritPage->hasPages())
                                <div class="mt-3">{{ $centresEcritPage->links() }}</div>
                            @endif
                        </div>
                        </div>
                        </div>
                    </details>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
    (function () {
        const form = document.getElementById('heritageFilterForm');
        const examen = document.getElementById('filter_type_examen');
        const dren = document.getElementById('filter_dren_id');
        const cisco = document.getElementById('filter_cisco_id');
        const centre = document.getElementById('filter_centre_correction_id');
        if (!form || !examen || !dren || !cisco || !centre) {
            return;
        }

        examen.addEventListener('change', () => form.submit());
        dren.addEventListener('change', () => form.submit());
        cisco.addEventListener('change', () => form.submit());
        centre.addEventListener('change', () => form.submit());
    })();
</script>
</body>
</html>
