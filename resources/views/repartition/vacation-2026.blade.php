<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traitement de vacation pour 2026</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
    @endif
</head>
<body class="bg-slate-100 text-slate-900">
<div class="mx-auto max-w-[1800px] p-4 md:p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1 space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-5 md:p-6">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Traitement de vacation pour 2026</h1>
                    <p class="mt-1 text-sm text-slate-600">Import de la base centrale, affectation CEPE/BEPC/CAP, et génération des documents (note, décompte, décision, présence).</p>
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
                            <div class="mb-2 font-medium text-slate-700">Etat de décompte (Excel)</div>
                            <form method="GET" action="{{ route('vacation2026.exports.xlsx', ['document' => 'decompte']) }}" class="grid grid-cols-1 gap-2 md:grid-cols-4">
                                <input type="number" step="0.01" min="0" name="irsa_percent" value="{{ request('irsa_percent', 0) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="IRSA %">
                                <button type="submit" class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-600">Décompte (Excel)</button>
                            </form>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-3">
                            <div class="mb-2 font-medium text-slate-700">Fiche de présence (Excel paysage)</div>
                            <a href="{{ route('vacation2026.exports.xlsx', ['document' => 'presence']) }}" class="inline-flex rounded-lg bg-blue-700 px-3 py-2 text-xs font-medium text-white hover:bg-blue-600">Présence (Excel)</a>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Format demandé: note/décision en Word, décompte/présence en Excel.</p>
                </section>
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">5) Liste des participants par activité</h2>
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

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">6) Tableau d'équilibre des montants</h2>
                <p class="mt-1 text-sm text-slate-600">Vue de contrôle pour voir qui est dans quelle activité et comparer les montants reçus.</p>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100 text-slate-700">
                            <th class="border border-slate-200 px-3 py-2 text-left">Examen</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Activité</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Participants</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Jours</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Taux moyen</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Montant moyen</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Montant total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($activityBalance as $line)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $line['examen'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $line['activite'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format($line['participants'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($line['jours'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format((float) $line['average_taux'], 2, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format((float) $line['average_montant'], 2, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format((float) $line['total_montant'], 2, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="border border-slate-200 px-3 py-4 text-center text-slate-500">Aucune donnée d'équilibrage pour le moment.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100 text-slate-700">
                            <th class="border border-slate-200 px-3 py-2 text-left">Examen</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Activité</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Participant</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">IM</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Jours</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Taux</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Montant reçu</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Écart vs moyenne activité</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($participantBalance as $line)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $line['examen'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $line['activite'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $line['nom'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $line['im'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format((float) $line['jours'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format((float) $line['taux'], 2, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format((float) $line['montant'], 2, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right {{ $line['ecart_montant'] < 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                    {{ $line['ecart_montant'] > 0 ? '+' : '' }}{{ number_format((float) $line['ecart_montant'], 2, ',', ' ') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="border border-slate-200 px-3 py-4 text-center text-slate-500">Aucun participant affecté.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

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
                                <td class="border border-slate-200 px-3 py-2">{{ $agent->assignment?->activity?->examen }} {{ $agent->assignment?->activity?->libelle }}</td>
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
        </main>
    </div>
</div>
</body>
</html>
