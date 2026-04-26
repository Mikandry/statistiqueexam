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
                <h2 class="text-lg font-semibold text-slate-900">3) Plafonds décret et jours par activité (niveau central)</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100 text-slate-700">
                            <th class="border border-slate-200 px-3 py-2 text-left">Examen</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Activité</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Affectés</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Max agents</th>
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
