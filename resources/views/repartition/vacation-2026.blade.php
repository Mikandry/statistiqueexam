<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traitement de vacation pour 2026</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')
</head>
<body class="bg-slate-100 text-slate-900">
<div class="mx-auto max-w-[1800px] p-4 md:p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1 space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-5 md:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Traitement de vacation pour 2026</h1>
                            <!-- Dashboards Navigation -->
                            <div class="rounded-2xl border border-purple-200 bg-gradient-to-r from-purple-50 to-blue-50 p-5 shadow-sm">
                                <div class="mb-4 flex items-center gap-2">
                                    <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <h2 class="text-lg font-semibold text-slate-900">📊 Dashboards de Vacation 2026</h2>
                                </div>
                                <p class="mb-4 text-sm text-slate-600">Consultez les statistiques et analyses de vacation pour 2026 par niveau administratif.</p>
                                <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                                    <a href="{{ route('vacation2026.dashboard.global') }}" class="flex items-center gap-3 rounded-lg border border-blue-300 bg-white p-3 text-left transition hover:border-blue-500 hover:bg-blue-50">
                                        <div class="rounded-lg bg-blue-100 p-2 text-blue-600">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4z"></path><path fill-rule="evenodd" d="M3 10a1 1 0 011-1h6v6H4a1 1 0 01-1-1v-4zm7 0a1 1 0 011-1h6v6h-6v-6zM4 15a1 1 0 00-1 1v2a1 1 0 001 1h4v-4H4z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-900">Dashboard Global</p>
                                            <p class="text-xs text-slate-500">Vue d'ensemble tous niveaux</p>
                                        </div>
                                    </a>
                                    <a href="{{ route('vacation2026.dashboard.men-central') }}" class="flex items-center gap-3 rounded-lg border border-amber-300 bg-white p-3 text-left transition hover:border-amber-500 hover:bg-amber-50">
                                        <div class="rounded-lg bg-amber-100 p-2 text-amber-600">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M13 7H7v6h6V7z"></path><path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2V2a1 1 0 112 0v1a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2v1a1 1 0 11-2 0v-1h-2v1a1 1 0 11-2 0v-1H9a2 2 0 01-2-2v-2H6a1 1 0 110-2h1V9H6a1 1 0 010-2h1V5a2 2 0 012-2V2a1 1 0 010-2z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-900">MEN Central</p>
                                            <p class="text-xs text-slate-500">Activités au niveau central</p>
                                        </div>
                                    </a>
                                    <a href="{{ route('vacation2026.dren') }}" class="flex items-center gap-3 rounded-lg border border-green-300 bg-white p-3 text-left transition hover:border-green-500 hover:bg-green-50">
                                        <div class="rounded-lg bg-green-100 p-2 text-green-600">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.5 1.5H5.75A2.25 2.25 0 003.5 3.75v12.5A2.25 2.25 0 005.75 18.5h8.5a2.25 2.25 0 002.25-2.25V6.5m-9-5v5m6-5v5"></path></svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-900">DREN</p>
                                            <p class="text-xs text-slate-500">Régions éducatives</p>
                                        </div>
                                    </a>
                                    <a href="{{ route('vacation2026.cisco') }}" class="flex items-center gap-3 rounded-lg border border-red-300 bg-white p-3 text-left transition hover:border-red-500 hover:bg-red-50">
                                        <div class="rounded-lg bg-red-100 p-2 text-red-600">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a1 1 0 001 1h12a1 1 0 001-1V6a2 2 0 00-2-2H4zm12 12H4a2 2 0 01-2-2v-4a1 1 0 00-1-1H.5a1.5 1.5 0 011.5 1.5v4a4 4 0 004 4h12a1.5 1.5 0 001.5-1.5v-4a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-900">CISCO</p>
                                            <p class="text-xs text-slate-500">Circonscriptions scolaires</p>
                                        </div>
                                    </a>
                                    <a href="{{ route('vacation2026.centre') }}" class="flex items-center gap-3 rounded-lg border border-indigo-300 bg-white p-3 text-left transition hover:border-indigo-500 hover:bg-indigo-50">
                                        <div class="rounded-lg bg-indigo-100 p-2 text-indigo-600">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-900">Centre d'examen</p>
                                            <p class="text-xs text-slate-500">Centres d'écriture</p>
                                        </div>
                                    </a>
                                    <a href="{{ route('vacation2026.dashboard.eps') }}" class="flex items-center gap-3 rounded-lg border border-pink-300 bg-white p-3 text-left transition hover:border-pink-500 hover:bg-pink-50">
                                        <div class="rounded-lg bg-pink-100 p-2 text-pink-600">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-900">EPS / Gymnase</p>
                                            <p class="text-xs text-slate-500">Centres EPS et gymnastiques</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-slate-600">Import de la base centrale, affectation CEPE/BEPC/CAP, et génération des documents (note, décompte, décision, présence).</p>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white">SOE</div>
                            <div class="text-sm">
                                <div class="font-semibold text-slate-900">SOE</div>
                                <div class="text-xs text-slate-500">Service de l'Organisation des Examens</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 p-5 md:grid-cols-3 md:p-6">
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                        <p class="text-xs uppercase tracking-wider text-blue-700">Agents importés</p>
                        <p class="mt-1 text-2xl font-semibold text-blue-900">{{ number_format($stats['agents_total'], 0, ',', ' ') }}</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-xs uppercase tracking-wider text-emerald-700">Agents affectés</p>
                        <p class="mt-1 text-2xl font-semibold text-emerald-900">{{ number_format($stats['agents_affectes'], 0, ',', ' ') }}</p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-xs uppercase tracking-wider text-amber-700">Agents non affectés</p>
                        <p class="mt-1 text-2xl font-semibold text-amber-900">{{ number_format($stats['agents_disponibles'], 0, ',', ' ') }}</p>
                    </div>
                </div>
            </div>

            @php($activeTab = $tab ?? 'main')
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('vacation2026.index', ['tab' => 'main']) }}"
                   class="rounded-full border px-4 py-2 text-sm font-medium {{ $activeTab === 'main' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700' }}">
                    Traitement
                </a>
                <a href="{{ route('vacation2026.index', ['tab' => 'balance']) }}"
                   class="rounded-full border px-4 py-2 text-sm font-medium {{ $activeTab === 'balance' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700' }}">
                    Equilibre
                </a>
            </div>

            @if($activeTab !== 'balance')
            @if(session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('import_rejects'))
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <p class="font-semibold">Lignes rejetées:</p>
                    <ul class="mt-2 max-h-48 space-y-1 overflow-auto text-xs">
                        @foreach((array) session('import_rejects') as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">1) Import par activité</h2>
                    <p class="mt-1 text-sm text-slate-600">Choisissez une activité puis importez la liste des participants. Le taux est défini et conservé dans le tableau 3.</p>
                    <form method="POST" action="{{ route('vacation2026.import') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Activité cible</label>
                            <select name="activity_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                                <option value="">Choisir une activité</option>
                                @foreach($activities as $activity)
                                    <option value="{{ $activity->id }}" {{ (string) old('activity_id') === (string) $activity->id ? 'selected' : '' }}>
                                        {{ $activity->examen }} - {{ $activity->libelle }} ({{ $activity->assignments_count }}/{{ $activity->max_agents }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="file" name="agents_file" accept=".xlsx,.xls,.csv,.txt" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Importer la liste</button>
                    </form>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">2) Paramètres des documents</h2>
                    <form method="POST" action="{{ route('vacation2026.settings.update') }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Entête</label>
                            <textarea name="entete" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('entete', $setting?->entete) }}</textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Considérant</label>
                            <textarea name="considerant" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('considerant', $setting?->considerant) }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Titre note de service</label>
                                <input type="text" name="note_titre" value="{{ old('note_titre', $setting?->note_titre) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="NOTE DE SERVICE">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Titre décision</label>
                                <input type="text" name="decision_titre" value="{{ old('decision_titre', $setting?->decision_titre) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="DECISION">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Titre fiche de présence</label>
                                <input type="text" name="presence_titre" value="{{ old('presence_titre', $setting?->presence_titre) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="FICHE DE PRÉSENCE">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Titre état de décompte</label>
                                <input type="text" name="decompte_titre" value="{{ old('decompte_titre', $setting?->decompte_titre) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="ÉTAT DE DÉCOMPTE">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Article 1 de la décision</label>
                            <textarea name="decision_article_1" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Sont nommés pour participer aux travaux...">{{ old('decision_article_1', $setting?->decision_article_1) }}</textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Article 2 de la décision</label>
                            <textarea name="decision_article_2" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Les intéressés percevront les vacations prévues...">{{ old('decision_article_2', $setting?->decision_article_2) }}</textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Signataire</label>
                            <input type="text" name="signature" value="{{ old('signature', $setting?->signature) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600" type="submit">Enregistrer</button>
                    </form>
                </section>
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">3) Plafonds décret et jours par activité</h2>
                        <p class="mt-1 text-sm text-slate-600">Filtrez par niveau administratif pour afficher les activités correspondantes.</p>
                    </div>
                    <form method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="main">
                        <select name="filter_level" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach($availableLevels as $levelValue => $levelLabel)
                                <option value="{{ $levelValue }}" {{ (string) $filterLevel === (string) $levelValue ? 'selected' : '' }}>{{ $levelLabel }}</option>
                            @endforeach
                        </select>
                        @if($filterLevel !== '')
                            <a href="{{ route('vacation2026.index', ['tab' => 'main']) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">Réinitialiser</a>
                        @endif
                    </form>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100 text-slate-700">
                            <th class="border border-slate-200 px-3 py-2 text-left">Examen</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Activité</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Affectés</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Max agents</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Niveau</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Nb jours</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Taux activité</th>
                            <th class="border border-slate-200 px-3 py-2"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($activities as $activity)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $activity->examen }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $activity->libelle }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ $activity->assignments_count }}</td>
                                <td class="border border-slate-200 px-3 py-2">
                                    <form method="POST" action="{{ route('vacation2026.activities.update', $activity) }}" class="flex items-center gap-2 justify-end">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" min="1" name="max_agents" value="{{ $activity->max_agents }}" class="w-20 rounded-lg border border-slate-300 px-2 py-1 text-right">
                                </td>
                                <td class="border border-slate-200 px-3 py-2">
                                        <select name="level" class="rounded-lg border border-slate-300 px-2 py-1 text-sm">
                                            <option value="">—</option>
                                            @foreach($availableLevels as $levelValue => $levelLabel)
                                                @if($levelValue !== '')
                                                <option value="{{ $levelValue }}" @selected((string)($activity->level ?? '') === (string)$levelValue)>{{ $levelLabel }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                </td>
                                <td class="border border-slate-200 px-3 py-2 text-right">
                                        <input type="number" min="1" name="nb_jours" value="{{ $activity->nb_jours }}" class="w-20 rounded-lg border border-slate-300 px-2 py-1 text-right">
                                </td>
                                <td class="border border-slate-200 px-3 py-2 text-right">
                                        <input type="number" step="0.01" min="0" name="taux_activite" value="{{ $activity->taux_activite ?? ($assignmentRatesByActivity[$activity->id] ?? '') }}" class="w-24 rounded-lg border border-slate-300 px-2 py-1 text-right">
                                </td>
                                <td class="border border-slate-200 px-3 py-2 text-right">
                                        <button class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-700" type="submit">Mettre à jour</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <form method="POST" action="{{ route('vacation2026.activities.store') }}" class="mt-4 grid grid-cols-1 gap-2 md:grid-cols-6">
                    @csrf
                    <input type="text" name="examen" placeholder="Examen" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    <input type="text" name="libelle" placeholder="Libellé activité" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    <select name="level" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Niveau (optionnel)</option>
                        @foreach($availableLevels as $levelValue => $levelLabel)
                            @if($levelValue !== '')
                            <option value="{{ $levelValue }}">{{ $levelLabel }}</option>
                            @endif
                        @endforeach
                    </select>
                    <input type="number" min="1" name="max_agents" placeholder="Max agents" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    <input type="number" min="1" name="nb_jours" placeholder="Nb jours" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    <input type="number" step="0.01" min="0" name="taux_activite" placeholder="Taux activité" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input type="number" min="0" name="ordre" placeholder="Ordre" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700 md:col-span-6">Ajouter activité</button>
                </form>
            </section>

            <div class="grid grid-cols-1 gap-4">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">4) Exports par document</h2>
                    <div class="mt-4 space-y-4 text-sm">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-3">
                            <div class="mb-2 font-medium text-slate-700">Note de service / Décision (Word)</div>
                            <form method="GET" action="{{ route('vacation2026.exports.word', ['document' => 'note-service']) }}" class="grid grid-cols-1 gap-2 md:grid-cols-4">
                                <select name="activity_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <option value="">Toutes activités</option>
                                    @foreach($activities as $activity)
                                        <option value="{{ $activity->id }}">{{ $activity->examen }} - {{ $activity->libelle }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-700">Note de service (Word)</button>
                                <button type="submit" formaction="{{ route('vacation2026.exports.word', ['document' => 'decision']) }}" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-700">Décision (Word)</button>
                                <button type="submit" formaction="{{ route('vacation2026.exports.pdf', ['document' => 'note-service']) }}" formtarget="_blank" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100">Prévisualiser Note (PDF)</button>
                                <button type="submit" formaction="{{ route('vacation2026.exports.pdf', ['document' => 'decision']) }}" formtarget="_blank" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100">Prévisualiser Décision (PDF)</button>
                            </form>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-3">
                            <div class="mb-2 font-medium text-slate-700">État de décompte (Excel)</div>
                            <form method="GET" action="{{ route('vacation2026.exports.xlsx', ['document' => 'decompte']) }}" class="grid grid-cols-1 gap-2 md:grid-cols-6">
                                <select name="activity_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <option value="">Toutes activités</option>
                                    @foreach($activities as $activity)
                                        <option value="{{ $activity->id }}">{{ $activity->examen }} - {{ $activity->libelle }}</option>
                                    @endforeach
                                </select>
                                <input type="number" step="0.01" min="0" name="irsa_percent" value="{{ request('irsa_percent', 0) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="IRSA %">
                                <input type="number" min="5" name="rows_per_page" value="{{ request('rows_per_page', 25) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Lignes / page">
                                <input type="number" min="1" name="first_page_rows" value="{{ request('first_page_rows', 6) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Lignes 1ère page">
                                <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                    <input type="checkbox" name="page_reports" value="1" class="rounded border-slate-300">
                                    Report & total par page
                                </label>
                                <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                    <input type="checkbox" name="first_page_header" value="1" checked class="rounded border-slate-300">
                                    Entête sur 1ère page
                                </label>
                                <button type="submit" class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-600">Décompte (Excel)</button>
                            </form>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-3">
                            <div class="mb-2 font-medium text-slate-700">Fiche de présence (Excel paysage)</div>
                            <form method="GET" action="{{ route('vacation2026.exports.xlsx', ['document' => 'presence']) }}" class="grid grid-cols-1 gap-2 md:grid-cols-4">
                                <select name="activity_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <option value="">Toutes activités</option>
                                    @foreach($activities as $activity)
                                        <option value="{{ $activity->id }}">{{ $activity->examen }} - {{ $activity->libelle }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="rounded-lg bg-blue-700 px-3 py-2 text-xs font-medium text-white hover:bg-blue-600">Présence (Excel)</button>
                            </form>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-3">
                            <div class="mb-2 font-medium text-slate-700">Récap par activité (Excel)</div>
                            <form method="GET" action="{{ route('vacation2026.exports.xlsx', ['document' => 'recap']) }}">
                                <button type="submit" class="rounded-lg bg-indigo-700 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-600">Récapitulatif (Excel)</button>
                            </form>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Format demandé: note/décision en Word, décompte/présence en Excel.</p>
                </section>
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">5) Liste des participants par activité</h2>
                @php($examens = $activities->pluck('examen')->unique()->values())
                <form method="GET" class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                    <input type="hidden" name="tab" value="main">
                    <select name="filter_examen" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Tous examens</option>
                        @foreach($examens as $examen)
                            <option value="{{ $examen }}" {{ (string) $filterExamen === (string) $examen ? 'selected' : '' }}>{{ $examen }}</option>
                        @endforeach
                    </select>
                    <select name="filter_activity" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Toutes activités</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}" {{ (string) $filterActivity === (string) $activity->id ? 'selected' : '' }}>
                                {{ $activity->examen }} - {{ $activity->libelle }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-700">Filtrer</button>
                </form>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100 text-slate-700">
                            <th class="border border-slate-200 px-3 py-2 text-left">Examen</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Activité</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Nom</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">IM</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Localité</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Jours</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Taux</th>
                            <th class="border border-slate-200 px-3 py-2"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($assignments as $assignment)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $assignment->activity?->examen }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $assignment->activity?->libelle }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $assignment->agent?->nom }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $assignment->agent?->im }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $assignment->agent?->localite_service }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ $assignment->activity?->nb_jours }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">
                                    @php($displayRate = $assignment->activity?->taux_activite ?? $assignment->taux)
                                    {{ $displayRate !== null ? number_format((float) $displayRate, 2, ',', ' ') : '-' }}
                                </td>
                                <td class="border border-slate-200 px-3 py-2 text-right">
                                    <form method="POST" action="{{ route('vacation2026.assignments.destroy', $assignment) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-100">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="border border-slate-200 px-3 py-4 text-center text-slate-500">Aucune affectation.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @endif

            @if($activeTab === 'balance')
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900"> Tableau d'équilibre des montants</h2>
                <p class="mt-1 text-sm text-slate-600">Vue consolidée par agent. Le rapprochement se fait d'abord par IM, puis par nom et localité quand l'IM manque. L'écart est calculé par localité ou service.</p>
                <form method="GET" class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                    <input type="hidden" name="tab" value="balance">
                    <input type="text" name="balance_localite" value="{{ $balanceLocalite }}" placeholder="Filtrer par localité de service" class="w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-700">Filtrer</button>
                    <a href="{{ route('vacation2026.exports.xlsx', ['document' => 'equilibre', 'balance_localite' => $balanceLocalite]) }}"
                       class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-700">
                        Export Excel TCD
                    </a>
                </form>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100 text-slate-700">
                            <th class="border border-slate-200 px-3 py-2 text-left">Nom</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">IM</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Localité / Service</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Nombre d'affectations</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Montant total</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Écart localité/service</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Anomalie</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($participantEquilibre as $line)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">
                                    <div class="font-semibold text-slate-900">{{ $line['nom'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $line['activities'] }}</div>
                                </td>
                                <td class="border border-slate-200 px-3 py-2">{{ $line['im'] !== '' ? $line['im'] : '-' }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $line['localite'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format((float) $line['nb_affectations'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format((float) $line['montant_total'], 2, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">
                                    <div class="{{ $line['ecart_affectations_localite'] < 0 ? 'text-amber-700' : ($line['ecart_affectations_localite'] > 0 ? 'text-emerald-700' : 'text-slate-600') }}">
                                        Affect.: {{ $line['ecart_affectations_localite'] > 0 ? '+' : '' }}{{ number_format((float) $line['ecart_affectations_localite'], 2, ',', ' ') }}
                                    </div>
                                    <div class="{{ $line['ecart_montant_localite'] < 0 ? 'text-amber-700' : ($line['ecart_montant_localite'] > 0 ? 'text-emerald-700' : 'text-slate-600') }}">
                                        Montant: {{ $line['ecart_montant_localite'] > 0 ? '+' : '' }}{{ number_format((float) $line['ecart_montant_localite'], 2, ',', ' ') }}
                                    </div>
                                </td>
                                <td class="border border-slate-200 px-3 py-2">{{ $line['anomalie'] !== '' ? $line['anomalie'] : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="border border-slate-200 px-3 py-4 text-center text-slate-500">Aucune donnée d'équilibrage pour le moment.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

            @if($activeTab !== 'balance')
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-slate-900">Base importée</h2>
                    <form method="GET" class="flex items-center gap-2">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher nom/IM/localité" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Filtrer</button>
                    </form>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100 text-slate-700">
                            <th class="border border-slate-200 px-3 py-2 text-left">Nom</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">IM</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Localité de service</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">CIN</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Affectation</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($agents as $agent)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $agent->nom }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $agent->im }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $agent->localite_service }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $agent->cin }}</td>
                                <td class="border border-slate-200 px-3 py-2">
                                    {{ $agent->assignments->map(fn ($assignment) => trim(($assignment->activity?->examen ?? '').' '.$assignment->activity?->libelle))->filter()->unique()->implode(', ') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="border border-slate-200 px-3 py-4 text-center text-slate-500">Aucun agent importé.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $agents->links() }}</div>
            </section>
            @endif
        </main>
    </div>
</div>
</body>
</html>
